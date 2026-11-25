<?php

namespace Tests\Feature\Sales;

use App\Sales\Filament\Resources\SalesTasks\Pages\CreateSalesTask;
use App\Sales\Filament\Resources\SalesTasks\Pages\ListSalesTasks;
use App\Sales\Filament\Resources\SalesTasks\SalesTaskResource;
use App\Sales\Models\SalesMemo;
use App\Sales\Models\SalesTask;
use App\Sales\Models\SalesTaskFile;
use App\Sales\Models\SalesTodo;
use App\Sales\Support\RepAccount;
use App\Sales\Support\SalesInsights;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Tasks (app v1.20): the manager gives a rep work with files and an e-mail to send the result to; the rep moves it on from the phone (status + note);
 * the manager checks the status. Plus the rep's private to-do list and notes, kept at the office only so a new phone gets them back.
 */
class SalesTasksTest extends SalesTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');   // the files of a task never touch the real disk in a test
    }

    private function task(int $repUserId, array $over = []): SalesTask
    {
        return SalesTask::create(array_merge([
            'assigned_to' => $repUserId,
            'created_by' => $this->manager()->id,
            'title' => 'Quotation for Greenlam',
            'description' => 'Two muffle furnaces, quote with 18% GST.',
            'send_to_email' => 'buyer@greenlam.example',
            'due_at' => now()->addDays(2),
        ], $over));
    }

    private function file(SalesTask $task, string $name = 'specs.pdf', string $bytes = "%PDF-1.4 specs\n"): SalesTaskFile
    {
        Storage::disk('local')->put($path = "sales/tasks/{$task->id}/" . Str::uuid() . '.pdf', $bytes);

        return $task->files()->create(['name' => $name, 'mime' => 'application/pdf', 'size' => strlen($bytes), 'path' => $path]);
    }

    private function pull(array $headers): array
    {
        return $this->getJson('/api/sales/v1/sync/pull', $headers)->assertOk()->json();
    }

    public function test_a_task_reaches_the_rep_with_its_files_and_nobody_elses_task_does(): void
    {
        $a = $this->rep('REP001', '9000000001', name: 'Ayush');
        $b = $this->rep('REP002', '9000000002');
        $task = $this->task($a->user_id);
        $file = $this->file($task);
        $this->task($b->user_id, ['title' => 'Another rep']);

        $mine = collect($this->pull($this->headers($a))['tasks'])->firstWhere('uuid', $task->uuid);
        $this->assertSame('Quotation for Greenlam', $mine['title']);
        $this->assertSame('buyer@greenlam.example', $mine['send_to_email']);
        $this->assertSame('new', $mine['status']);
        $this->assertSame(1, $mine['version']);
        $this->assertSame([['id' => (string) $file->id, 'name' => 'specs.pdf', 'mime' => 'application/pdf', 'size' => strlen("%PDF-1.4 specs\n")]], $mine['files']);
        $this->assertCount(1, $this->pull($this->headers($a))['tasks'], 'only the rep\'s own task');

        // the file, for the assigned rep only
        $headers = $this->headers($a);
        $got = $this->getJson("/api/sales/v1/tasks/{$task->uuid}/files/{$file->id}", $headers)->assertOk()->json();
        $this->assertSame('specs.pdf', $got['name']);
        $this->assertSame("%PDF-1.4 specs\n", base64_decode($got['data_base64']));
        $this->getJson("/api/sales/v1/tasks/{$task->uuid}/files/{$file->id}", $this->headers($b))->assertNotFound();
    }

    public function test_the_rep_moves_a_task_on_and_only_status_and_note_come_up(): void
    {
        $rep = $this->rep();
        $h = $this->headers($rep);
        $task = $this->task($rep->user_id);

        $update = ['uuid' => $task->uuid, 'status' => 'in_progress', 'rep_note' => 'Working on it', 'rep_updated_at' => $this->moment(), 'updated_at' => $this->moment(), 'title' => 'HIJACKED', 'send_to_email' => 'evil@example.com'];
        $this->assertSame('accepted', $this->answer($this->push($h, ['tasks' => [$update]])->assertOk(), 'tasks', $task->uuid)['status']);

        $task->refresh();
        $this->assertSame('in_progress', $task->status);
        $this->assertSame('Working on it', $task->rep_note);
        $this->assertSame('Quotation for Greenlam', $task->title, 'the office\'s fields are never taken from a phone');
        $this->assertSame('buyer@greenlam.example', $task->send_to_email);
        $this->assertSame(2, (int) $task->version, 'every change is a new version');

        // an older update from the same rep is a duplicate; a newer one goes through
        $older = $update;
        $older['status'] = 'done';
        $older['rep_updated_at'] = $this->moment('-1 hour');
        $this->assertSame('duplicate', $this->answer($this->push($h, ['tasks' => [$older]])->assertOk(), 'tasks', $task->uuid)['status']);
        $done = $update;
        $done['status'] = 'done';
        $done['rep_note'] = 'Sent to buyer@greenlam.example';
        $done['rep_updated_at'] = $this->moment('+1 second');
        $this->assertSame('accepted', $this->answer($this->push($h, ['tasks' => [$done]])->assertOk(), 'tasks', $task->uuid)['status']);
        $this->assertSame('done', $task->fresh()->status);
    }

    public function test_the_rep_cannot_set_the_offices_statuses_or_say_cannot_do_without_a_reason(): void
    {
        $rep = $this->rep();
        $h = $this->headers($rep);
        $task = $this->task($rep->user_id);
        $base = ['uuid' => $task->uuid, 'rep_note' => '', 'rep_updated_at' => $this->moment(), 'updated_at' => $this->moment()];

        foreach (['new', 'cancelled', 'whatever'] as $status) {
            $this->assertSame('INVALID', $this->answer($this->push($h, ['tasks' => [$base + ['status' => $status]]])->assertOk(), 'tasks', $task->uuid)['code'], $status);
        }
        $this->assertSame('INVALID', $this->answer($this->push($h, ['tasks' => [$base + ['status' => 'blocked']]])->assertOk(), 'tasks', $task->uuid)['code'], 'cannot do needs a reason');
        $this->assertSame('accepted', $this->answer($this->push($h, ['tasks' => [array_merge($base, ['status' => 'blocked', 'rep_note' => 'No specs for the furnace'])]])->assertOk(), 'tasks', $task->uuid)['status']);
    }

    public function test_another_reps_task_and_a_cancelled_task_cannot_be_changed_from_a_phone(): void
    {
        $a = $this->rep('REP001', '9000000001');
        $b = $this->rep('REP002', '9000000002');
        $task = $this->task($a->user_id);
        $update = ['uuid' => $task->uuid, 'status' => 'done', 'rep_note' => 'x', 'rep_updated_at' => $this->moment(), 'updated_at' => $this->moment()];

        $this->assertSame('NOT_ALLOWED', $this->answer($this->push($this->headers($b), ['tasks' => [$update]])->assertOk(), 'tasks', $task->uuid)['code']);

        $task->update(['status' => 'cancelled']);
        $this->assertSame('LOCKED', $this->answer($this->push($this->headers($a), ['tasks' => [$update]])->assertOk(), 'tasks', $task->uuid)['code']);

        // a cancelled task is still listed (the phone shows it cancelled) but without files
        $this->file($task);
        $pulled = collect($this->pull($this->headers($a))['tasks'])->firstWhere('uuid', $task->uuid);
        $this->assertSame('cancelled', $pulled['status']);
        $this->assertSame([], $pulled['files']);
        $this->getJson("/api/sales/v1/tasks/{$task->uuid}/files/" . $task->files()->first()->id, $this->headers($a))->assertNotFound();
    }

    public function test_the_rep_keeps_a_private_todo_list_and_notes_that_come_back_on_a_new_phone(): void
    {
        $a = $this->rep('REP001', '9000000001');
        $b = $this->rep('REP002', '9000000002');
        $ha = $this->headers($a);   // (a sign-in is limited to a few a minute: once per rep)
        $hb = $this->headers($b);
        $todo = ['uuid' => (string) Str::uuid(), 'title' => 'Call Greenlam about the specs', 'done' => false, 'done_at' => null, 'due_date' => now()->addDay()->toDateString(), 'updated_at' => $this->moment()];
        $memo = ['uuid' => (string) Str::uuid(), 'title' => 'Greenlam', 'body' => "Prefers Sartorius.\nCall after 11.", 'updated_at' => $this->moment()];

        $this->assertSame('accepted', $this->answer($this->push($ha, ['todos' => [$todo], 'memos' => [$memo]])->assertOk(), 'todos', $todo['uuid'])['status']);
        $this->assertSame('duplicate', $this->answer($this->push($ha, ['memos' => [$memo]])->assertOk(), 'memos', $memo['uuid'])['status'], 'sending a note again makes no second one');
        $this->assertSame(1, SalesTodo::count());
        $this->assertSame(1, SalesMemo::count());

        $pull = $this->pull($ha);
        $this->assertSame('Call Greenlam about the specs', $pull['todos'][0]['title']);
        $this->assertSame("Prefers Sartorius.\nCall after 11.", $pull['memos'][0]['body']);
        $this->assertSame([], $this->pull($hb)['todos'], 'private: another rep never gets them');

        // ticking it, then deleting it
        $done = $todo;
        $done['done'] = true;
        $done['done_at'] = $this->moment();
        $done['updated_at'] = $this->moment('+1 minute');
        $this->push($ha, ['todos' => [$done]])->assertOk();
        $this->assertTrue(SalesTodo::first()->done);

        $this->assertSame('accepted', $this->push($ha, ['deleted' => ['todos' => [$todo['uuid']], 'memos' => [$memo['uuid']]]])->assertOk()->json('results.deleted.todos.0.status'));
        $this->assertSame(0, SalesTodo::count());
        $this->assertSame(0, SalesMemo::count());

        foreach ([['title' => 'x'], ['title' => str_repeat('x', 201)], ['due_date' => '31-12-2026']] as $bad) {
            $t = array_merge($todo, ['uuid' => (string) Str::uuid()], $bad);
            $this->assertSame('INVALID', $this->answer($this->push($ha, ['todos' => [$t]])->assertOk(), 'todos', $t['uuid'])['code'], json_encode($bad));
        }
    }

    public function test_the_manager_gives_a_task_with_files_from_the_admin_and_checks_its_status(): void
    {
        Storage::fake('local');
        $rep = $this->rep('REP001', '9000000001', name: 'Ayush Sharma');
        $this->actingAs($this->manager());

        $pdf = \Illuminate\Http\UploadedFile::fake()->createWithContent('specs.pdf', "%PDF-1.4 specs\n");
        $exe = \Illuminate\Http\UploadedFile::fake()->createWithContent('run.pdf', "MZ-not-a-pdf");

        Livewire::test(CreateSalesTask::class)
            ->fillForm([
                'assigned_to' => $rep->user_id,
                'title' => 'Send quotation to Greenlam',
                'description' => 'Two furnaces, see the specs.',
                'send_to_email' => 'buyer@greenlam.example',
                'priority' => 'high',
                'attachments' => [$pdf, $exe],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $task = SalesTask::firstOrFail();
        $this->assertSame($rep->user_id, $task->assigned_to);
        $this->assertSame('new', $task->status);
        $this->assertSame('high', $task->priority);
        $this->assertSame(1, $task->files()->count(), 'the file that is not really a PDF is refused, the real one is kept');
        $stored = $task->files()->first();
        $this->assertSame('specs.pdf', $stored->name);
        $this->assertSame('application/pdf', $stored->mime);
        $this->assertTrue(Storage::disk('local')->exists($stored->path));
        $this->assertStringStartsWith("sales/tasks/{$task->id}/", $stored->path);

        // it is on the phone's pull with the file listed
        $pulled = collect($this->pull($this->headers($rep))['tasks'])->firstWhere('uuid', $task->uuid);
        $this->assertCount(1, $pulled['files']);
        $this->assertNotSame('', $pulled['assigned_by_name'], 'the rep sees who gave the task');

        // the rep moves it; the manager sees the status and the note in the list
        $this->push($this->headers($rep), ['tasks' => [['uuid' => $task->uuid, 'status' => 'done', 'rep_note' => 'Sent on 26 Nov', 'rep_updated_at' => $this->moment(), 'updated_at' => $this->moment()]]])->assertOk();
        Livewire::test(ListSalesTasks::class)
            ->filterTable('status', ['done'])
            ->assertCanSeeTableRecords([$task])
            ->assertSee('Sent on 26 Nov')
            ->assertSee('Ayush Sharma');

        // the manager cancels it: the phone is told
        Livewire::test(ListSalesTasks::class)->filterTable('status', ['done'])->callTableAction('cancelTask', $task);
        $this->assertSame('cancelled', $task->fresh()->status);
        $this->assertSame('cancelled', collect($this->pull($this->headers($rep))['tasks'])->firstWhere('uuid', $task->uuid)['status']);

        // the details popup renders as plain text
        $html = view('filament.sales.task-details', ['task' => $task->fresh()->load(['assignee.salesProfile', 'creator', 'files'])])->render();
        $this->assertStringContainsString('buyer@greenlam.example', $html);
        $this->assertStringContainsString('Sent on 26 Nov', $html);
        $this->assertStringContainsString('specs.pdf', $html);
    }

    public function test_only_a_manager_with_the_right_opens_the_tasks_screen_and_a_rep_never_does(): void
    {
        $rep = $this->rep();
        $this->actingAs($this->manager());
        $this->assertTrue(SalesTaskResource::canViewAny());

        $this->actingAs(\App\Models\User::factory()->create(['role' => 'editor', 'is_active' => true]));
        $this->assertFalse(SalesTaskResource::canViewAny());
        $this->assertFalse($rep->user->can('viewAny', SalesTask::class));
    }

    public function test_a_file_is_judged_by_its_name_and_its_first_bytes(): void
    {
        $this->assertSame('application/pdf', SalesTaskFile::typeOf('a.pdf', "%PDF-1.7\n"));
        $this->assertNull(SalesTaskFile::typeOf('a.pdf', 'MZ program'), 'a program renamed to .pdf');
        $this->assertNull(SalesTaskFile::typeOf('a.exe', 'MZ'), 'not an allowed kind');
        $this->assertNull(SalesTaskFile::typeOf('a.php', '<?php echo 1;'));
        $this->assertSame('image/png', SalesTaskFile::typeOf('a.PNG', "\x89PNG\r\n"));
        $this->assertSame('text/csv', SalesTaskFile::typeOf('a.csv', "a,b\n1,2\n"));
        $this->assertNull(SalesTaskFile::typeOf('a.csv', "a\0b"), 'text with a NUL byte');
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', SalesTaskFile::typeOf('q.xlsx', "PK\x03\x04..."));
        $this->assertSame('file', SalesTaskFile::cleanName('../../'));
        $this->assertSame('evil.pdf', SalesTaskFile::cleanName('..\\..\\evil.pdf'));
        $this->assertSame('a b.pdf', SalesTaskFile::cleanName("a\x01 b<>.pdf"));
    }

    public function test_the_management_page_figures_and_deleting_a_rep(): void
    {
        $profile = $this->rep();
        $late = $this->task($profile->user_id, ['due_at' => now()->subDay()]);
        $this->file($late);
        $this->task($profile->user_id, ['status' => 'blocked']);
        $this->task($profile->user_id, ['status' => 'done', 'rep_updated_at' => now()]);
        $this->assertSame(['open' => 2, 'overdue' => 1, 'blocked' => 1, 'new' => 1, 'done_week' => 1], SalesInsights::tasks());

        $path = $late->files()->first()->path;
        $profile->update(['is_active' => false]);
        RepAccount::deleteWithRecords($profile->fresh(), $this->manager());

        $this->assertSame(0, SalesTask::withTrashed()->count());
        $this->assertSame(0, SalesTaskFile::count());
        $this->assertFalse(Storage::disk('local')->exists($path), 'the files of the deleted rep\'s tasks are removed from the disk');
    }
}
