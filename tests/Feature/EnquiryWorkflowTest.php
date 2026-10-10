<?php

namespace Tests\Feature;

use App\Filament\RelationManagers\TeamNotesRelationManager;
use App\Filament\Resources\Enquiries\Pages\EditEnquiry;
use App\Filament\Resources\Enquiries\Pages\ListEnquiries;
use App\Mail\CustomerMessage;
use App\Mail\EnquiryAssigned;
use App\Models\ContactMessage;
use App\Models\EmailTemplate;
use App\Models\Enquiry;
use App\Models\EnquiryItem;
use App\Models\Note;
use App\Models\User;
use App\Support\EnquiryExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class EnquiryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'admin', array $over = []): User
    {
        return User::factory()->create(array_merge(['role' => $role, 'is_active' => true], $over));
    }

    private function enquiry(array $over = []): Enquiry
    {
        return Enquiry::create($over + ['name' => 'Ravi Sharma', 'email' => 'ravi@example.com', 'phone' => '+91 9876543210', 'status' => 'new']);
    }

    // ------------------------------------------------------------ owner, status log

    public function test_status_and_owner_changes_are_logged_and_the_new_owner_is_emailed(): void
    {
        $admin = $this->user('admin');
        $sales = $this->user('sales', ['name' => 'Sneha Sales', 'email' => 'sneha@example.com']);
        $this->actingAs($admin);
        $enquiry = $this->enquiry();

        $enquiry->update(['status' => 'contacted']);
        $enquiry->update(['assigned_to' => $sales->id]);

        $log = $enquiry->teamNotes()->pluck('body')->all();
        $this->assertContains('Status changed from New to Contacted', $log);
        $this->assertContains('Assigned to Sneha Sales', $log);
        $this->assertSame(Note::KIND_SYSTEM, $enquiry->teamNotes()->first()->kind);
        $this->assertSame($admin->id, $enquiry->teamNotes()->first()->user_id);

        Mail::assertQueued(EnquiryAssigned::class, fn ($m) => $m->hasTo('sneha@example.com'));

        $enquiry->update(['assigned_to' => null]);
        $this->assertContains('Assignment removed', $enquiry->teamNotes()->pluck('body')->all());
    }

    public function test_assigning_to_yourself_sends_no_email(): void
    {
        $sales = $this->user('sales');
        $this->actingAs($sales);

        $this->enquiry()->update(['assigned_to' => $sales->id]);

        Mail::assertNotQueued(EnquiryAssigned::class);
    }

    public function test_only_active_staff_with_enquiry_access_can_be_assigned(): void
    {
        $sales = $this->user('sales', ['name' => 'Sales One']);
        $this->user('hr', ['name' => 'Hr One']);
        $this->user('editor', ['name' => 'Editor One']);
        $this->user('sales', ['name' => 'Gone Sales', 'is_active' => false]);

        $names = collect(User::assignable('enquiries'))->values()->implode('|');

        $this->assertStringContainsString('Sales One', $names);
        $this->assertStringNotContainsString('Hr One', $names);
        $this->assertStringNotContainsString('Editor One', $names);
        $this->assertStringNotContainsString('Gone Sales', $names);
    }

    // ------------------------------------------------------------ list: filters and bulk actions

    public function test_filters_find_mine_unassigned_and_open_enquiries(): void
    {
        $me = $this->user('sales');
        $this->actingAs($me);
        $mine = $this->enquiry(['name' => 'Mine One', 'assigned_to' => $me->id]);
        $free = $this->enquiry(['name' => 'Free One']);
        $closed = $this->enquiry(['name' => 'Closed One', 'status' => 'closed']);

        Livewire::test(ListEnquiries::class)->filterTable('mine')->assertCanSeeTableRecords([$mine])->assertCanNotSeeTableRecords([$free, $closed]);
        Livewire::test(ListEnquiries::class)->filterTable('unassigned')->assertCanSeeTableRecords([$free, $closed])->assertCanNotSeeTableRecords([$mine]);
        Livewire::test(ListEnquiries::class)->filterTable('open')->assertCanSeeTableRecords([$mine, $free])->assertCanNotSeeTableRecords([$closed]);
        Livewire::test(ListEnquiries::class)->filterTable('status', 'quoted')->assertCanNotSeeTableRecords([$mine, $free, $closed]);
    }

    public function test_bulk_assign_and_bulk_status(): void
    {
        $this->actingAs($this->user('admin'));
        $sales = $this->user('sales');
        $a = $this->enquiry();
        $b = $this->enquiry(['name' => 'Second']);

        Livewire::test(ListEnquiries::class)->callTableBulkAction('assign', [$a, $b], data: ['assigned_to' => $sales->id]);
        $this->assertSame([$sales->id, $sales->id], [$a->fresh()->assigned_to, $b->fresh()->assigned_to]);

        Livewire::test(ListEnquiries::class)->callTableBulkAction('set_status', [$a, $b], data: ['status' => 'quoted']);
        $this->assertSame(['quoted', 'quoted'], [$a->fresh()->status, $b->fresh()->status]);
    }

    // ------------------------------------------------------------ notes

    public function test_team_members_can_add_notes_and_only_authors_or_admins_can_delete_them(): void
    {
        $sales = $this->user('sales');
        $other = $this->user('sales');
        $enquiry = $this->enquiry();

        $this->actingAs($sales);
        Livewire::test(TeamNotesRelationManager::class, ['ownerRecord' => $enquiry, 'pageClass' => EditEnquiry::class])
            ->callTableAction('create', data: ['body' => 'Customer wants delivery in 2 weeks.'])
            ->assertHasNoTableActionErrors();

        $note = $enquiry->teamNotes()->where('kind', 'note')->firstOrFail();
        $this->assertSame([$sales->id, 'Customer wants delivery in 2 weeks.'], [$note->user_id, $note->body]);

        $this->actingAs($other);
        Livewire::test(TeamNotesRelationManager::class, ['ownerRecord' => $enquiry, 'pageClass' => EditEnquiry::class])
            ->assertTableActionHidden('delete', $note);

        $this->actingAs($sales);
        Livewire::test(TeamNotesRelationManager::class, ['ownerRecord' => $enquiry, 'pageClass' => EditEnquiry::class])
            ->assertTableActionVisible('delete', $note)->callTableAction('delete', $note);
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_automatic_log_lines_cannot_be_deleted(): void
    {
        $this->actingAs($this->user('admin'));
        $enquiry = $this->enquiry();
        $enquiry->update(['status' => 'contacted']);
        $system = $enquiry->teamNotes()->firstOrFail();

        Livewire::test(TeamNotesRelationManager::class, ['ownerRecord' => $enquiry, 'pageClass' => EditEnquiry::class])
            ->assertTableActionHidden('delete', $system);
    }

    public function test_edit_page_shows_owner_notes_and_reply_button(): void
    {
        $this->actingAs($this->user('admin'));
        $enquiry = $this->enquiry();

        Livewire::test(EditEnquiry::class, ['record' => $enquiry->getRouteKey()])
            ->assertSee('Assigned to')->assertSee('Quote sent')->assertActionExists('reply_email');
        $this->get("/admin/enquiries/{$enquiry->id}/edit")->assertOk();

        // the notes area (a lazy-loaded tab on the page) renders with its button
        Livewire::test(TeamNotesRelationManager::class, ['ownerRecord' => $enquiry, 'pageClass' => EditEnquiry::class])
            ->assertSee('Add note')->assertSee('No notes yet');
    }

    // ------------------------------------------------------------ reply by email

    public function test_reply_by_email_uses_a_template_goes_to_the_customer_and_is_logged(): void
    {
        $sales = $this->user('sales', ['name' => 'Sneha Sales', 'email' => 'sneha@example.com']);
        $this->actingAs($sales);
        $enquiry = $this->enquiry();
        $enquiry->items()->create(['product_name' => 'Hei-VAP Core', 'quantity' => 2]);
        $template = EmailTemplate::forKey('reply_quote');

        $component = Livewire::test(EditEnquiry::class, ['record' => $enquiry->getRouteKey()])
            ->mountAction('reply_email')
            ->setActionData(['template' => $template->id])
            ->assertSet('mountedActions.0.data.subject', "Quotation for your enquiry (ENQ-" . str_pad((string) $enquiry->id, 6, '0', STR_PAD_LEFT) . ")");

        $component->callMountedAction()->assertHasNoActionErrors();

        Mail::assertQueued(CustomerMessage::class, function (CustomerMessage $m) {
            return $m->hasTo('ravi@example.com') && $m->replyToEmail === 'sneha@example.com'
                && str_contains($m->body, 'Hei-VAP Core (qty 2)') && str_contains($m->body, 'Dear Ravi');
        });
        $this->assertSame('contacted', $enquiry->fresh()->status);                       // "mark as contacted" was on
        $this->assertTrue($enquiry->teamNotes()->where('body', 'like', 'Emailed the customer%')->exists());
    }

    public function test_reply_text_can_be_edited_before_sending(): void
    {
        $this->actingAs($this->user('admin'));
        $enquiry = $this->enquiry();

        Livewire::test(EditEnquiry::class, ['record' => $enquiry->getRouteKey()])
            ->callAction('reply_email', data: ['subject' => 'Hello Ravi', 'body' => 'Custom text only.', 'mark_contacted' => false]);

        Mail::assertQueued(CustomerMessage::class, fn ($m) => $m->mailSubject === 'Hello Ravi' && $m->body === 'Custom text only.');
        $this->assertSame('new', $enquiry->fresh()->status);
    }

    public function test_contact_messages_can_also_be_replied_to(): void
    {
        $this->actingAs($this->user('admin'));
        $message = ContactMessage::create(['name' => 'Asha Verma', 'email' => 'asha@example.com', 'phone' => '+91 9999999999', 'message' => 'Hi', 'status' => 'new']);

        Livewire::test(\App\Filament\Resources\ContactMessages\Pages\EditContactMessage::class, ['record' => $message->getRouteKey()])
            ->callAction('reply_email', data: ['subject' => 'Re: Hi', 'body' => 'Thanks for writing.', 'mark_contacted' => true]);

        Mail::assertQueued(CustomerMessage::class, fn ($m) => $m->hasTo('asha@example.com'));
        $this->assertSame('contacted', $message->fresh()->status);
    }

    // ------------------------------------------------------------ export

    private function download(\Symfony\Component\HttpFoundation\StreamedResponse $response): string
    {
        ob_start();
        $response->sendContent();

        return (string) ob_get_clean();
    }

    public function test_csv_export_contains_the_right_rows_and_neutralises_formulas(): void
    {
        $sales = $this->user('sales', ['name' => 'Sneha Sales']);
        $group = $this->enquiry(['name' => 'Group Buyer', 'assigned_to' => null, 'company' => 'Sharma Labs']);
        $group->items()->create(['product_name' => 'Oven', 'quantity' => 2]);
        $group->items()->create(['product_name' => 'Chiller', 'quantity' => 1]);
        $evil = $this->enquiry(['name' => '=HYPERLINK("http://evil.example","click")', 'message' => '@SUM(1+1)', 'status' => 'closed', 'assigned_to' => $sales->id]);

        $csv = $this->download(EnquiryExporter::download(Enquiry::query(), 'csv'));

        $this->assertStringContainsString('Reference,"Received (IST)",Name', $csv);
        $this->assertStringContainsString('Group Buyer', $csv);
        $this->assertStringContainsString('Group (2 products)', $csv);
        $this->assertStringContainsString('Oven x2; Chiller', $csv);
        $this->assertStringContainsString('Sharma Labs', $csv);
        $this->assertStringContainsString('Sneha Sales', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);            // formulas become plain text
        $this->assertStringContainsString("'@SUM", $csv);
        $this->assertStringContainsString('+91 9876543210', $csv);          // phone numbers stay untouched
        $this->assertStringNotContainsString("'+91", $csv);

        // only the filtered rows are exported
        $only = $this->download(EnquiryExporter::download(Enquiry::where('status', 'closed'), 'csv'));
        $this->assertStringContainsString('HYPERLINK', $only);
        $this->assertStringNotContainsString('Group Buyer', $only);
    }

    public function test_excel_export_is_a_real_xlsx_file(): void
    {
        $this->enquiry();

        $xlsx = $this->download(EnquiryExporter::download(Enquiry::query(), 'xlsx'));

        $this->assertSame('PK', substr($xlsx, 0, 2));          // xlsx files are zip archives
    }

    public function test_export_buttons_work_from_the_list(): void
    {
        $this->actingAs($this->user('admin'));
        $a = $this->enquiry();

        Livewire::test(ListEnquiries::class)->callTableAction('export', data: ['format' => 'csv'])->assertFileDownloaded();
        Livewire::test(ListEnquiries::class)->callTableBulkAction('export_selected', [$a], data: ['format' => 'xlsx'])->assertFileDownloaded();
    }

    public function test_safe_cells(): void
    {
        $this->assertSame("'=1+1", EnquiryExporter::safe('=1+1'));
        $this->assertSame("'-2+3", EnquiryExporter::safe('-2+3'));
        $this->assertSame('+91 98765 43210', EnquiryExporter::safe('+91 98765 43210'));
        $this->assertSame('Normal text', EnquiryExporter::safe('Normal text'));
        $this->assertSame('', EnquiryExporter::safe(''));
    }
}
