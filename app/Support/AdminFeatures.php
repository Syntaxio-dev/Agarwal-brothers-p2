<?php

namespace App\Support;

use App\Filament\Pages\Backups;
use App\Filament\Pages\HelpGuide;
use App\Filament\Pages\ImportProducts;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\ApplicationResources\ApplicationResourceResource;
use App\Filament\Resources\Brands\BrandResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Countries\CountryResource;
use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\EnquiryQuestions\EnquiryQuestionResource;
use App\Filament\Resources\Insights\InsightResource;
use App\Filament\Resources\JobApplications\JobApplicationResource;
use App\Filament\Resources\JobOpenings\JobOpeningResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Reviews\ReviewResource;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Filament\Resources\Slides\SlideResource;
use App\Filament\Resources\TeamMembers\TeamMemberResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Verticals\VerticalResource;
use App\Models\User;

/**
 * The "What you can do here" box on the admin dashboard: every area of the admin panel in plain words,
 * limited to what the signed-in role can open. Add a line here when a new admin area is built.
 */
class AdminFeatures
{
    /**
     * [group => [[area, title, text, target]]]. `area` is the role key (config/roles.php), `target` the
     * Filament resource/page class that gives the link.
     */
    public static function all(): array
    {
        return [
            'Catalogue' => [
                ['products', 'Products', 'Add and edit products: photos and more photos, video, overview, key features and advantages, technical specifications, documents and FAQs. Save as a hidden draft, duplicate a product, activate / deactivate / move many at once, and preview on the site before it is live.', ProductResource::class],
                ['products', 'Import products', 'Upload a CSV or Excel file with many products. The file is checked first and nothing is saved until you confirm.', ImportProducts::class],
                ['brands', 'Brands', 'Brand name, logo, country and description. Brands show on the home page, the Brands pages and the search.', BrandResource::class],
                ['categories', 'Product lines', 'A brand\'s product series (for example BSA Series) with its own heading, description and FAQs. Link each one to the verticals it belongs to.', CategoryResource::class],
                ['verticals', 'Verticals', 'The main product areas of the site (for example Weighing Balances) with picture and order. Visitors browse vertical, brand, product line, product.', VerticalResource::class],
                ['countries', 'Countries', 'Countries and pins for the world map of principals.', CountryResource::class],
            ],
            'Homepage' => [
                ['slides', 'Slides', 'The big banner at the top of the home page. A slide can open a product, a link, or nothing.', SlideResource::class],
                ['clients', 'Clients', 'Client logos, category, city and a short note for the "Our Clients" section. Client categories can be added and ordered.', ClientResource::class],
                ['reviews', 'Reviews', 'Customer reviews for the home page and Our Story. Choose which one shows in the big quote card.', ReviewResource::class],
                ['team-members', 'Team', 'People shown on the Our Story page.', TeamMemberResource::class],
            ],
            'Content' => [
                ['insights', 'Blogs, News & Webinars', 'Write articles from parts: text, pictures, photo sliders and a sources list. Keep a draft or publish; webinars have date, time and a register link.', InsightResource::class],
                ['application-resources', 'Application resources', 'PDFs and links (application notes, guides) that visitors can download.', ApplicationResourceResource::class],
            ],
            'Leads' => [
                ['enquiries', 'Enquiries', 'Product and group enquiries. Set status, assign an owner, write private team notes, reply by email with a template, filter, and export to Excel or CSV.', EnquiryResource::class],
                ['enquiries', 'Enquiry questions', 'The quick questions visitors answer on the enquiry form (budget, application...), different for each vertical. The answers come with the enquiry.', EnquiryQuestionResource::class],
                ['contact-messages', 'Contact messages', 'Messages from the Contact Us page, with status, notes and reply by email.', ContactMessageResource::class],
            ],
            'Careers' => [
                ['job-openings', 'Job openings', 'Post roles with department, location, experience, salary and your own application questions. Switch them off when filled.', JobOpeningResource::class],
                ['job-applications', 'Applications', 'Read applications, download resumes (kept private), move them through stages and write notes.', JobApplicationResource::class],
            ],
            'Settings (administrators)' => [
                ['site-settings', 'Site settings', 'Phone, WhatsApp, catalogue PDFs, page banners, Our Story text and photos, home page search description.', SiteSettingResource::class],
                ['users', 'Users & roles', 'Create staff logins and give each a role: Administrator, Content editor, Sales or HR. Switch a person off without deleting them.', UserResource::class],
                ['email-templates', 'Email templates', 'The wording of the automatic "we will connect shortly" replies and the manual reply templates.', EmailTemplateResource::class],
                ['backups', 'Backups', 'Download a full backup (database + uploaded files). One also runs every night.', Backups::class],
                ['activity-log', 'Activity log', 'Who changed what and when, including sign-ins.', ActivityLogResource::class],
            ],
        ];
    }

    /**
     * What this user can open, in the same groups, each item with its link.
     *
     * @return array<string, list<array{title:string,text:string,url:string}>>
     */
    public static function forUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $out = [];

        foreach (static::all() as $group => $items) {
            foreach ($items as [$area, $title, $text, $target]) {
                if (! $user->canManage($area)) {
                    continue;
                }

                $url = $target === SiteSettingResource::class ? SiteSettingResource::getNavigationUrl() : $target::getUrl();
                $out[$group][] = ['key' => \Illuminate\Support\Str::slug($title), 'title' => $title, 'text' => $text, 'url' => $url];
            }
        }

        return $out;
    }

    /** Flat [key => title] list of what this user can open (for the shortcut picker). @return array<string, string> */
    public static function choices(?User $user): array
    {
        return collect(static::forUser($user))->flatten(1)->pluck('title', 'key')->all();
    }

    /** Things every signed-in person can do. */
    public static function everyone(): array
    {
        return [
            ['Search everything', 'Press Ctrl+K (or click the search box at the top) to find products, brands, enquiries and How-to answers.'],
            ['Your work is protected', 'If you leave a form with unsaved changes you get a warning, and a browser-only draft can be restored for 7 days.'],
            ['Needs attention', 'The cards on this dashboard show what needs attention (new enquiries, products without a photo...). Click one to open the list already filtered.'],
            ['How-to guide', 'Step-by-step help for every area is in the Help menu, with a search box.'],
        ];
    }

    public static function helpUrl(): string
    {
        return HelpGuide::getUrl();
    }
}
