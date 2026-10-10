<?php

namespace App\Support;

/**
 * Plain-English how-to notes shown in the admin panel ("How-to Guide").
 * Edit the text here; the page and the per-page help links update by themselves.
 *
 * area: the admin area (config/roles.php key) the topic belongs to. People only
 *       see topics for areas their role can open. null = shown to everyone.
 * url:  admin path prefix used for the "Read the guide" link on that page.
 */
class HelpGuide
{
    public static function topics(): array
    {
        return [
            [
                'id' => 'start',
                'title' => 'Start here: how the admin panel works',
                'area' => null,
                'url' => 'admin',
                'intro' => 'This panel controls everything on the website. Anything you save here appears on the live site straight away, so read the notes below before you change something important.',
                'steps' => [
                    'Use the menu on the left. It is grouped: Homepage, Catalogue, Content, Leads, Careers and Settings. You only see the groups your role can use.',
                    'The Dashboard shows "Quick updates": new enquiries, messages and applications, and small to-dos such as products without a photo. Click a card to go straight to it.',
                    'To add something, open its list and press the button at the top right (for example "New product"). To change something, click its row.',
                    'Press the blue Save (or Create) button at the bottom of the form. A green message confirms it. If you leave without saving, your changes are lost.',
                    'Many lists have an Active switch. Switching it off hides the item from the website without deleting it. Use this instead of deleting when you are unsure.',
                    'Lists with a drag handle can be re-ordered by dragging rows. Items higher in the list appear first on the website.',
                ],
                'tips' => [
                    'Fields marked with a red star are required.',
                    'Slugs (the part of the web address) fill in automatically from the name. Do not change a slug after the page is live, or old links will break.',
                    'If something is not showing on the website, check: is it Active? is its brand Active? is it linked to a vertical or category?',
                ],
            ],
            [
                'id' => 'images',
                'title' => 'Images, PDFs and file rules',
                'area' => null,
                'url' => 'admin',
                'intro' => 'Good files make the site look professional and load fast. Follow these rules for every upload.',
                'steps' => [
                    'Allowed images: JPG, PNG, WebP or GIF, up to 8 MB. SVG files are not allowed for safety.',
                    'Make images small before uploading. A product photo of 1000 to 1600 pixels wide is plenty. Very large photos slow the site down.',
                    'Product photos: a clean shot on a white or transparent background (PNG is best for transparent).',
                    'Logos: PNG with a transparent background, wide rather than tall.',
                    'Banners: wide images. Blogs and webinars about 3:1 (for example 1800x600). Team photos about 4:5 (portrait).',
                    'PDFs: up to 20 MB. Resumes sent by candidates are private and can only be downloaded from the admin panel.',
                    'Videos for hero slides: MP4 or WebM, up to 50 MB. For long videos paste a YouTube link instead.',
                    'Image description (alt text): hero slides ask for a short description of the picture. Write what a person would see, for example "Scientists working in a modern laboratory". It helps people who use screen readers and it helps Google. Other images use their name or title automatically.',
                ],
                'tips' => [
                    'Name files clearly before uploading (for example shimadzu-lc-2050.png).',
                    'If an upload fails, the file is usually too large or the wrong type.',
                ],
            ],
            [
                'id' => 'products',
                'title' => 'Adding a product (full steps)',
                'area' => 'products',
                'url' => 'admin/products',
                'intro' => 'A product lives inside a category (a product line), and a category belongs to a brand. So the order is always: Brand, then Category, then Product. If the brand or category does not exist yet, create it first.',
                'steps' => [
                    'Open Catalogue, then Products, then press New product.',
                    'Brand · Category: pick the product line it belongs to. It shows as "Brand - Category".',
                    'Name: the product name, for example "Shimadzu LC-2050". The slug fills in by itself.',
                    'Page heading (optional): a longer title for the big banner on the product page, for example "Shimadzu LC-2050 HPLC, Distributor and Service Provider in India". Leave empty to use the name.',
                    'Model group (optional): products with the same group are listed together on the category page, for example "Standard Models" and "Advanced Models".',
                    'Short description: one or two lines. It shows on product cards and in the product banner.',
                    'Images and video: upload a main image. Add extra photos to the gallery (they open with the "Show Image" button). Paste a YouTube link to show a video.',
                    'Overview: a longer introduction. If empty, the short description is used.',
                    'Key features and Key advantages: press "Add feature" or "Add advantage", then write a short title and one line of detail. You can drag to re-order.',
                    'Technical specifications: add one row per specification, for example "Flow rate" and "0.001 to 5 mL/min". This table appears near the top of the product page, so customers see it first.',
                    'Documents and research papers: add brochures, datasheets or papers. Upload a PDF or paste a link.',
                    'FAQs: add questions customers often ask, with short answers.',
                    'Visibility: keep Active on. Switch on "Show in Precision Picks" to feature it on the homepage (the first 6 by "Top pick order" are shown).',
                    'Open the SEO box only if you want a custom Google title or description. Otherwise it is made automatically.',
                    'Press Create. Then open the website product page to check it.',
                ],
                'tips' => [
                    'Only the first field group is needed to start. You can add features, documents and FAQs later.',
                    'Every product page has an Enquiry form. Enquiries arrive under Leads, then Enquiries.',
                    'Products of an inactive brand are hidden from the whole website.',
                ],
            ],
            [
                'id' => 'catalogue',
                'title' => 'Brands, categories (product lines) and verticals',
                'area' => 'brands',
                'url' => 'admin/brands',
                'intro' => 'These three control how the catalogue is organised on the website. A vertical (such as "Analytical Chemistry") shows brand cards; each brand card lists its categories; each category lists its products.',
                'steps' => [
                    'Brands: add the name, upload a logo and pick the country. The country puts a pin on the world map on the homepage. A brand without a country is not on the map.',
                    'Countries: if a country is missing from the list, add it under Catalogue, then Countries, with its latitude and longitude (find them on any map site).',
                    'Categories: choose the brand, then tick every vertical where this product line should appear. Add the page heading, intro and detailed content that shows on the category page. Add FAQs if useful.',
                    'Verticals: add the name, a short description and a square image or icon. The homepage shows the first 8 by order number.',
                    'To show a product line in two verticals, tick both in the category form.',
                ],
                'tips' => [
                    'A brand cannot be deleted while it still has categories, and a category cannot be deleted while it has products. This protects you from losing products by mistake. Delete or move the products first.',
                    'To hide a whole brand quickly, switch Active off. All of its products disappear from the site until you switch it on again.',
                ],
            ],
            [
                'id' => 'homepage',
                'title' => 'Homepage: slides, clients, reviews and top picks',
                'area' => 'slides',
                'url' => 'admin/slides',
                'intro' => 'These items appear on the homepage. Active items show; inactive ones are hidden.',
                'steps' => [
                    'Hero Slides: add a title and subtitle if the image does not already contain text. Upload an image or a video. The optional button link must be a page on this site (for example /verticals) or a full https:// link.',
                    'Clients: add the logo, a short note, and switch on "Top client" to show it in the homepage carousel (up to 10). Drag rows to set the order.',
                    'Reviews: add the reviewer name, role, company and the review text. Rating is 1 to 5. If there are more than three active reviews, the homepage carousel slides automatically.',
                    'Precision Picks (homepage products) are chosen on each product with "Show in Precision Picks".',
                ],
                'tips' => [
                    'Use real reviews only, with the customer\'s permission.',
                    'Keep slide text short. It sits on top of a photo.',
                ],
            ],
            [
                'id' => 'insights',
                'title' => 'Blogs, news and webinars',
                'area' => 'insights',
                'url' => 'admin/insights',
                'intro' => 'All three live under Content, then Blogs, News and Webinars. Pick the type first because the form changes with it.',
                'steps' => [
                    'Press New, then choose the Type: Blog, News and Events, or Webinar.',
                    'Title and excerpt: the excerpt is the short summary shown on cards and in Google.',
                    'Cover image: wide (16:9) for blogs and webinars, portrait (4:5) for news posters and invitations.',
                    'PDF (optional): attach a brochure or invitation. Visitors can read and download it on the page.',
                    'Content: write the article with the editor. Use headings for sections. The content is optional when you attach a PDF.',
                    'News: you can set an Event date. The "Upcoming only" filter on the website uses it.',
                    'Webinars: choose the principal (brand), set the start date and time in Indian time, and paste the registration link. Tick "Time to be announced" if the time is not final. After the event, paste the recording link.',
                    'Switch on "Feature on homepage" to show it in the homepage section (up to 4 blogs and 3 news items).',
                    'Keep "Published" on to make it visible.',
                ],
                'tips' => [
                    'The webinar page counts down to the start time automatically and moves the webinar to "Past" after it ends.',
                    'Do not paste text straight from Word with odd formatting. Paste as plain text, then format in the editor.',
                ],
            ],
            [
                'id' => 'resources',
                'title' => 'Application resources',
                'area' => 'application-resources',
                'url' => 'admin/application-resources',
                'intro' => 'Technical notes, guides, brochures and videos customers can download.',
                'steps' => [
                    'Add a title, pick the category (Application Note, Technical Guide, Video, Brochure) and write a short description.',
                    'Upload a cover image so the card looks good.',
                    'Upload the PDF. For videos, paste the link instead.',
                    'If neither a PDF nor a link is added, the card shows a "Request" button that opens the contact page.',
                ],
                'tips' => ['Drag rows to change the order on the website.'],
            ],
            [
                'id' => 'team',
                'title' => 'Team and leadership (Our Story page)',
                'area' => 'team-members',
                'url' => 'admin/team-members',
                'intro' => 'People shown on the Our Story page.',
                'steps' => [
                    'Add the name, designation and photo.',
                    'Switch on "Show as leadership block" for the big quote blocks, and write the quote. Leaders alternate left and right.',
                    'People who are not leaders appear in the "Meet the team" grid.',
                    'The text and photos on the rest of the Our Story page are edited under Settings, then Site Settings.',
                ],
                'tips' => ['A portrait photo with a transparent background looks best in leadership blocks.'],
            ],
            [
                'id' => 'careers',
                'title' => 'Job openings and applications',
                'area' => 'job-openings',
                'url' => 'admin/job-openings',
                'intro' => 'Each opening gets its own page with an application form. Applications arrive in the admin panel.',
                'steps' => [
                    'Careers, then Job Openings, then New. Add the title, location, type and department.',
                    'Write a short summary (shown in the list) and the role description.',
                    'Application questions: add extra questions for this role. Choose the answer type (short text, long text, dropdown, yes or no) and whether it is required. Name, email, phone and resume are always asked.',
                    'Turn "Open for hiring" off when the role is filled. The role disappears from the careers page but is kept.',
                    'Applications: open Careers, then Applications. Click "Resume" to download the CV. Change the status (New, Shortlisted, Interviewed, Hired, Rejected) and add private notes.',
                ],
                'tips' => [
                    'Applications without a specific role are labelled "General application".',
                    'Resumes are private. They cannot be opened from the website, only from here.',
                ],
            ],
            [
                'id' => 'leads',
                'title' => 'Enquiries and contact messages',
                'area' => 'enquiries',
                'url' => 'admin/enquiries',
                'intro' => 'Customers reach you through the product Enquiry form and the Contact Us form. Both are saved here and also emailed to the company inbox.',
                'steps' => [
                    'Check Leads, then Enquiries and Contact Messages every day. New ones have a "New" status.',
                    'Open one to read all the details. Call or email the customer.',
                    'Change the status to Contacted after you reply, and to Closed when it is finished.',
                    'The Dashboard "Quick updates" card shows how many are still new.',
                ],
                'tips' => [
                    'If an email did not arrive, the message is still saved here. Nothing is lost.',
                    'Replying to the notification email goes straight to the customer.',
                ],
            ],
            [
                'id' => 'settings',
                'title' => 'Site settings (administrators)',
                'area' => 'site-settings',
                'url' => 'admin/site-settings',
                'intro' => 'Global content: catalogue file, WhatsApp number, banners, the Our Story page and Google defaults.',
                'steps' => [
                    'Catalogue PDF: upload it to show the "Product Catalogue" button in the sidebar.',
                    'WhatsApp number: digits only with the country code, for example 919876543210. This powers the green chat button.',
                    'Page banners: the wide images at the top of the Blogs, News and Webinars pages.',
                    'Our Story page: photos, intro text, the checklist, mission, vision and goal, and the year founded.',
                    'Home page SEO: the title and description Google shows for the homepage, and the default share image.',
                ],
                'tips' => ['There is only one Site Settings record. Edit it, do not create another.'],
            ],
            [
                'id' => 'seo',
                'title' => 'SEO: titles, descriptions and share images',
                'area' => 'products',
                'url' => 'admin/seo',
                'intro' => 'Every page gets a Google title, description and share image automatically. Use the "SEO (optional)" box on a form only when you want to improve it.',
                'steps' => [
                    'SEO title: what appears as the blue link in Google. Keep it under about 60 characters. " | Agarwal Brothers" is added for you.',
                    'SEO description: one clear sentence about the page, up to 160 characters.',
                    'Share image: shown when the page is shared on WhatsApp or LinkedIn. Use 1200x630 pixels.',
                    'Leave all three empty to use the automatic version.',
                ],
                'tips' => ['Write for people first. Do not repeat the same keyword again and again.'],
            ],
            [
                'id' => 'users',
                'title' => 'Users and roles (administrators)',
                'area' => 'users',
                'url' => 'admin/users',
                'intro' => 'Give each team member their own login and only the access they need.',
                'steps' => [
                    'Settings, then Users and Roles, then Add user. Enter the name, email and a password of at least 8 characters.',
                    'Choose the role. Administrator: everything. Content editor: catalogue and website content. Sales: enquiries, messages and products. HR: job openings and applications.',
                    'Switch Active off to block someone from signing in without deleting their account (for example when they leave).',
                    'To reset a password, open the user, type a new password and save. Leave it empty to keep the old one.',
                ],
                'tips' => [
                    'You cannot deactivate, change the role of, or delete your own account, and the last active administrator is always protected.',
                    'Never share one login between people. Create one user each so you can see who did what.',
                ],
            ],
            [
                'id' => 'backups',
                'title' => 'Backups (administrators)',
                'area' => 'backups',
                'url' => 'admin/backups',
                'intro' => 'A backup is a copy of your data that you can restore if something goes wrong.',
                'steps' => [
                    'Open Settings, then Backups. Press "Create backup now" for a full copy (database plus all uploads and resumes).',
                    'Use "Download database only" for a quick copy of the text data, or "Download uploads" for all images and PDFs.',
                    'A backup is also created automatically every night. The last 7 are kept on the server.',
                    'Download a fresh backup once a week and keep it somewhere else (your computer or cloud drive). A backup stored only on the server is not safe if the server fails.',
                ],
                'tips' => ['Backup files contain customer data and password hashes. Keep them private.'],
            ],
            [
                'id' => 'problems',
                'title' => 'Something is not working? Quick fixes',
                'area' => null,
                'url' => 'admin/help',
                'intro' => 'The most common questions.',
                'steps' => [
                    '"I added a product but cannot see it": check it is Active, its brand is Active, and its category is ticked under a vertical (for the vertical page) or browse via the brand page.',
                    '"A brand is not on the world map": the brand has no country, or it is not Active.',
                    '"I cannot delete a brand or category": it still has items under it. Delete or move them first, or switch Active off.',
                    '"My image will not upload": check the type (JPG, PNG, WebP, GIF) and the size (under 8 MB).',
                    '"The webinar shows as past": check the start date and time are in Indian time and not in the past.',
                    '"I do not see a menu item": your role does not include it. Ask an administrator.',
                    '"Emails are not arriving": the messages are still saved in the panel. Ask the developer to check the mail settings.',
                ],
                'tips' => ['Still stuck? Write down the page, what you clicked and the message you saw, and send it to the developer.'],
            ],
        ];
    }

    /** Topic visible to this user's role. */
    public static function forUser($user): array
    {
        return array_values(array_filter(
            static::topics(),
            fn (array $t) => $t['area'] === null || ($user && $user->canManage($t['area']))
        ));
    }

    /** The topic id to link to from an admin page URL such as /admin/products/3/edit. */
    public static function topicFor(string $path): ?string
    {
        $segment = explode('/', trim($path, '/'))[1] ?? null;

        return match ($segment) {
            null, '' => 'start',
            'products' => 'products',
            'brands', 'categories', 'verticals', 'countries' => 'catalogue',
            'slides', 'clients', 'reviews' => 'homepage',
            'insights' => 'insights',
            'application-resources' => 'resources',
            'team-members' => 'team',
            'job-openings', 'job-applications' => 'careers',
            'enquiries', 'contact-messages' => 'leads',
            'site-settings' => 'settings',
            'users' => 'users',
            'backups' => 'backups',
            default => null,
        };
    }
}
