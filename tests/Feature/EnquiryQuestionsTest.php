<?php

namespace Tests\Feature;

use App\Filament\Resources\EnquiryQuestions\Pages\CreateEnquiryQuestion;
use App\Filament\Resources\EnquiryQuestions\Pages\ListEnquiryQuestions;
use App\Mail\NewEnquiry;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\EnquiryQuestion;
use App\Models\Product;
use App\Models\User;
use App\Models\Vertical;
use App\Support\EnquiryExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class EnquiryQuestionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    /** @return array{0: Product, 1: Vertical, 2: Vertical} a balance in the Balances vertical, plus another vertical */
    private function setUpCatalogue(): array
    {
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);
        $balances = Vertical::create(['name' => 'Balances', 'slug' => 'balances', 'is_active' => true]);
        $other = Vertical::create(['name' => 'Microscopes', 'slug' => 'microscopes', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'BSA', 'slug' => 'bsa']);
        $cat->verticals()->attach($balances->id);
        $p = Product::create(['category_id' => $cat->id, 'name' => 'BSA224S', 'slug' => 'bsa224s', 'is_active' => true]);

        return [$p, $balances, $other];
    }

    private function question(array $o = []): EnquiryQuestion
    {
        return EnquiryQuestion::create($o + ['question' => 'What will you weigh?', 'type' => 'choice', 'options' => ['Powders', 'Liquids'], 'is_active' => true]);
    }

    public function test_only_questions_of_the_products_vertical_and_general_ones_are_shown(): void
    {
        [$p, $balances, $other] = $this->setUpCatalogue();
        $this->question(['question' => 'Balance question', 'vertical_id' => $balances->id]);
        $this->question(['question' => 'Microscope question', 'vertical_id' => $other->id]);
        $this->question(['question' => 'General question', 'vertical_id' => null, 'sort_order' => 0]);
        $this->question(['question' => 'Switched off', 'vertical_id' => null, 'is_active' => false]);

        $names = EnquiryQuestion::forProduct($p)->pluck('question')->all();

        $this->assertSame(['Balance question', 'General question'], $names);   // vertical ones first
        $this->get(route('product.show', $p->slug))
            ->assertSee('Balance question')
            ->assertSee('General question')
            ->assertDontSee('Microscope question')
            ->assertDontSee('Switched off');
    }

    public function test_the_form_never_shows_more_than_four_and_hides_broken_questions(): void
    {
        [$p] = $this->setUpCatalogue();
        foreach (range(1, 6) as $i) {
            $this->question(['question' => "Q{$i}", 'sort_order' => $i]);
        }
        $this->question(['question' => 'No real choices', 'options' => ['Only one'], 'sort_order' => 0]);

        $names = EnquiryQuestion::forProduct($p)->pluck('question')->all();

        $this->assertSame(['Q1', 'Q2', 'Q3', 'Q4'], $names);
    }

    public function test_answers_are_saved_with_the_enquiry_and_reach_the_office_email_and_the_export(): void
    {
        [$p, $balances] = $this->setUpCatalogue();
        $choice = $this->question(['vertical_id' => $balances->id]);
        $typed = $this->question(['question' => 'Your application?', 'type' => 'text', 'options' => null]);

        $this->post(route('enquiry.store'), [
            'product_id' => $p->id, 'name' => 'Ravi Sharma', 'email' => 'ravi@example.com',
            'answers' => [$choice->id => 'Powders', $typed->id => '  Pharma QC lab '],
        ])->assertRedirect();

        $e = Enquiry::firstOrFail();
        $this->assertSame([
            ['question' => 'What will you weigh?', 'answer' => 'Powders'],
            ['question' => 'Your application?', 'answer' => 'Pharma QC lab'],
        ], $e->answers);

        Mail::assertQueued(NewEnquiry::class, function (NewEnquiry $m) {
            $html = $m->render();

            return str_contains($html, 'What will you weigh?') && str_contains($html, 'Powders') && str_contains($html, 'Pharma QC lab');
        });

        $row = EnquiryExporter::row($e->load('items', 'product'));
        $this->assertContains('What will you weigh?: Powders; Your application?: Pharma QC lab', $row);
    }

    public function test_every_shown_question_must_be_answered_with_a_listed_choice(): void
    {
        [$p, $balances, $other] = $this->setUpCatalogue();
        $choice = $this->question(['vertical_id' => $balances->id]);
        $foreign = $this->question(['question' => 'Not for this product', 'vertical_id' => $other->id]);
        $typed = $this->question(['question' => 'Application?', 'type' => 'text', 'options' => null]);
        $base = ['product_id' => $p->id, 'name' => 'Ravi Sharma', 'email' => 'ravi@example.com'];

        // nothing answered
        $this->post(route('enquiry.store'), $base)
            ->assertSessionHasErrors(["answers.{$choice->id}", "answers.{$typed->id}"])
            ->assertSessionDoesntHaveErrors("answers.{$foreign->id}");

        // a choice that is not in the list, and a blank typed answer
        $this->post(route('enquiry.store'), $base + ['answers' => [$choice->id => 'Gold bars', $typed->id => '   ']])
            ->assertSessionHasErrors(["answers.{$choice->id}", "answers.{$typed->id}"]);

        $this->assertSame(0, Enquiry::count());

        // all answered; the foreign question and an unknown id are ignored
        $this->post(route('enquiry.store'), $base + ['answers' => [$choice->id => 'Powders', $typed->id => 'QC lab', $foreign->id => 'Yes', 999 => 'x']])
            ->assertSessionHasNoErrors();

        $this->assertSame([
            ['question' => 'What will you weigh?', 'answer' => 'Powders'],
            ['question' => 'Application?', 'answer' => 'QC lab'],
        ], Enquiry::firstOrFail()->answers);
    }

    public function test_the_form_marks_the_questions_as_required_and_checks_them_in_the_browser(): void
    {
        [$p] = $this->setUpCatalogue();
        $this->question();
        $this->question(['question' => 'Application?', 'type' => 'text', 'options' => null]);

        $html = $this->get(route('product.show', $p->slug))->getContent();

        $this->assertStringContainsString('data-rule="required" data-label="Please choose an answer"', $html);
        $this->assertStringContainsString('data-label="Please answer this"', $html);
        $this->assertStringNotContainsString('(optional', $html);
    }

    public function test_an_enquiry_without_answers_still_works(): void
    {
        [$p] = $this->setUpCatalogue();
        $this->post(route('enquiry.store'), ['product_id' => $p->id, 'name' => 'Ravi Sharma', 'email' => 'ravi@example.com'])->assertRedirect();

        $this->assertSame(1, Enquiry::count());
        $this->assertSame([], Enquiry::first()->answerList());
    }

    public function test_the_old_answer_text_does_not_change_when_the_question_is_edited_later(): void
    {
        [$p] = $this->setUpCatalogue();
        $q = $this->question();
        $this->post(route('enquiry.store'), ['product_id' => $p->id, 'name' => 'Ravi Sharma', 'email' => 'ravi@example.com', 'answers' => [$q->id => 'Liquids']]);

        $q->update(['question' => 'Something else?']);

        $this->assertSame('What will you weigh?', Enquiry::first()->answers[0]['question']);
    }

    public function test_staff_manage_questions_in_the_admin_and_a_pick_one_question_needs_two_choices(): void
    {
        $this->setUpCatalogue();
        $this->actingAs(User::factory()->create(['role' => 'sales', 'is_active' => true]));
        $vertical = Vertical::first();

        Livewire::test(CreateEnquiryQuestion::class)
            ->fillForm(['vertical_id' => $vertical->id, 'questions' => [['question' => 'Budget?', 'type' => 'choice', 'options' => ['Only one']]]])
            ->call('create')
            ->assertHasFormErrors(['questions.0.options']);

        // several questions for one vertical in a single save
        Livewire::test(CreateEnquiryQuestion::class)
            ->fillForm(['vertical_id' => $vertical->id, 'questions' => [
                ['question' => 'Budget?', 'type' => 'choice', 'options' => ['Low', 'High']],
                ['question' => 'Application?', 'type' => 'text'],
                ['question' => 'Capacity?', 'type' => 'choice', 'options' => ['Small', 'Large']],
            ]])
            ->call('create')
            ->assertHasNoFormErrors();

        $saved = EnquiryQuestion::orderBy('sort_order')->get();
        $this->assertSame(['Budget?', 'Application?', 'Capacity?'], $saved->pluck('question')->all());
        $this->assertSame([1, 2, 3], $saved->pluck('sort_order')->all());
        $this->assertSame([$vertical->id], $saved->pluck('vertical_id')->unique()->values()->all());
        $this->assertSame(['Low', 'High'], $saved[0]->options);
        $this->assertNull($saved[1]->options);

        Livewire::test(ListEnquiryQuestions::class)->assertCanSeeTableRecords($saved);
    }

    public function test_hr_cannot_open_the_questions(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hr', 'is_active' => true]));
        $this->get('/admin/enquiry-questions')->assertForbidden();
    }
}
