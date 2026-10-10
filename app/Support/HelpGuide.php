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
                    'You can upload a photo straight from the camera or phone. The website saves a lighter copy by itself (as WebP, sharp and clean, with transparent backgrounds kept) and makes very large pictures a little smaller (longest side 2400 pixels, still sharp on big screens). The picture looks the same; the page just loads faster.',
                    'Product photos: a clean shot on a white or transparent background (PNG is best for transparent).',
                    'Logos: PNG with a transparent background, wide rather than tall.',
                    'Banners: wide images. Blogs and webinars about 3:1 (for example 1800x600). Team photos about 4:5 (portrait).',
                    'PDFs: up to 20 MB. Resumes sent by candidates are private and can only be downloaded from the admin panel.',
                    'Videos for hero slides: MP4 or WebM, up to 50 MB. Videos are saved exactly as uploaded, so shrink them first with a free tool such as HandBrake (MP4, 1080p or smaller). For long videos paste a YouTube link instead.',
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
                    'Rows can be added and removed any time. Every product can have a different number of specifications.',
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
                'id' => 'duplicate',
                'title' => 'Adding a similar product fast (Duplicate)',
                'area' => 'products',
                'url' => 'admin/products',
                'intro' => 'Use Duplicate when the new product looks like one you already added, for example a balance from another brand with similar specifications. You get a copy of the text details and only change what is different.',
                'steps' => [
                    'Open Catalogue, then Products. On the row of the product you want to copy, press Duplicate. You can also open the product and press Duplicate at the top right.',
                    'Type the name of the new product, for example "Acculab ATL-224".',
                    'Choose its Brand and product line in "Brand - Category". Pick the new brand here if the copy belongs to a different brand.',
                    'Leave "Also copy the pictures" off when the brand is different, so the old brand\'s photo is not reused. Switch it on only if the new product really looks the same.',
                    'Press Create copy. You land on the edit page of the new product.',
                    'Technical specifications: the old rows are already there. Change the values, press "Add specification" for extra rows and use the bin icon to remove rows you do not need. The new product can have more or fewer specifications than the original, for example 12 instead of 9, or only 6.',
                    'Do the same with Key features, Key advantages and FAQs: edit the text, press "Add" for new items, and remove the ones that do not apply.',
                    'Upload the new product\'s own pictures, add its page heading, documents and video if it has them. These are not copied because they belong to the original product.',
                    'When everything is ready, switch the product to Active and press Save. Until then the copy stays hidden from the website.',
                ],
                'tips' => [
                    'What is copied: model group, short description, overview, specifications, features, advantages and FAQs.',
                    'What is not copied: page heading, documents, video, Google (SEO) fields, top-pick status and, unless you ask for it, the pictures.',
                    'The copy is saved as inactive on purpose, so a half-finished product never appears on the website by mistake.',
                    'To compare two products on the compare page, write the same specification names on both (for example "Capacity" on each). Matching names line up in one row; different names get their own rows.',
                    'Always check the copied text. Remove brand names, model numbers and claims that belong to the original product.',
                ],
            ],
            [
                'id' => 'import',
                'title' => 'Uploading many products at once (CSV or Excel)',
                'area' => 'products',
                'url' => 'admin/import-products',
                'intro' => 'Use Import products when you have a list of products in Excel or a CSV file. It is much faster than adding them one by one.',
                'steps' => [
                    'Open Catalogue, then Import products.',
                    'Press "Download sample file". It opens in Excel with example rows and the correct column names.',
                    'Fill one row per product. Required columns: name, brand and category. The brand and category must already exist in the panel (or switch on "Create missing categories").',
                    'Specifications: either write them in one cell as Name: Value separated by | (example: Capacity: 220 g | Readability: 0.1 mg), or give each specification its own column named like "spec: Capacity". Products can have different numbers of specifications; leave a cell empty when it does not apply.',
                    'Save the file as CSV UTF-8 or as .xlsx. If your file is the old .xls type, open it in Excel and use Save As.',
                    'Go back to Import products, choose the file and press Import products.',
                    'If something is wrong, nothing is imported and a list shows the row number and the problem. Fix the file and upload it again.',
                    'When the import works you see how many products were created. They start as drafts without photos, so open Draft products, add the pictures, check the details and switch them to Active.',
                ],
                'tips' => [
                    'Pictures, documents and videos cannot come from the file. Add them on each product afterwards. The dashboard card "Products without a photo" shows what is still missing.',
                    'Leave "Update products that already exist" off to avoid changing products that are already on the site. Turn it on to correct text or specifications in bulk (only the filled-in cells are changed).',
                    'Use exactly the same brand and category spelling as in the panel. Capital letters do not matter.',
                    'A file can have up to 1000 products.',
                ],
            ],
            [
                'id' => 'bulk',
                'title' => 'Changing many products at once (bulk actions)',
                'area' => 'products',
                'url' => 'admin/products',
                'intro' => 'On the Products list you can select several products and change them together.',
                'steps' => [
                    'Open Catalogue, then Products.',
                    'Tick the boxes on the left of the products you want. Tick the box in the table header to select the whole page.',
                    'Press "Bulk actions" above the table.',
                    'Activate selected: shows them on the website. Deactivate selected: hides them (nothing is deleted).',
                    'Set category / brand: moves them to another product line. Choose the brand and product line and press Move products.',
                    'Use the filters at the top of the table (for example "Without a photo" or "Draft") to find the products you need first.',
                ],
                'tips' => [
                    'Hiding is safer than deleting. You can always activate the products again.',
                    'Moving products to a different brand changes where they appear on the website straight away.',
                ],
            ],
            [
                'id' => 'preview',
                'title' => 'Checking how a page looks on the website',
                'area' => null,
                'url' => 'admin',
                'intro' => 'Every edit page has a View on site button at the top right, so you can see the real page without leaving your work.',
                'steps' => [
                    'Open any product, brand, category, vertical, blog, news item or job opening in the panel.',
                    'Press "View on site" at the top right. The real page opens in a new tab.',
                    'If the item is not live yet (inactive, a draft, or its brand is hidden) the button says "Preview on site". The preview opens with a dark "Preview mode" bar. Only signed-in staff can see it; visitors cannot.',
                    'On a product, the Card preview box at the top shows how its card looks in the lists, with a checklist: photo, short description, specifications and visibility.',
                    'Changed something? Press Save first, then View on site, and refresh the tab.',
                ],
                'tips' => [
                    'Check on a phone too: open the same link on your phone while you are signed in.',
                    'If the image looks cut or blurry in the preview, upload a larger or differently shaped picture.',
                ],
            ],
            [
                'id' => 'dashboard',
                'title' => 'Dashboard and search',
                'area' => null,
                'url' => 'admin',
                'intro' => 'The dashboard tells you what needs attention. The search box at the top finds products, brands, enquiries and answers from this guide.',
                'steps' => [
                    'Quick links at the top of the dashboard add things fast: Add product, Import products, Add brand and more.',
                    'The cards below count what needs attention: new enquiries and messages, webinars coming up, and tidy-up jobs such as products without a photo, without specifications or without a Google title, draft products, and brands without a country or logo.',
                    'Click a card to open the list already filtered to exactly those items. Fix them one by one; the number goes down.',
                    'A green tick means there is nothing to do for that card.',
                    'Use the search box at the top of any page (or press Ctrl + K). Type a product name, brand, customer name, email or phone number. Results open the record directly.',
                    'You can also type a question such as "how to add a brand" or "upload product". The matching guide topics appear under "How-to guide".',
                ],
                'tips' => ['You only see cards and search results for the areas your role can open.'],
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
                'id' => 'workflow',
                'title' => 'Working on enquiries as a team',
                'area' => 'enquiries',
                'url' => 'admin/enquiries',
                'intro' => 'Every enquiry has a status, an owner and a private notes area, so your team always knows who is doing what.',
                'steps' => [
                    'Open Leads, then Enquiries. New enquiries are at the top.',
                    'Open an enquiry. Choose "Assigned to" to hand it to a team member. They receive an email. You can assign many at once: tick the rows, then Bulk actions, Assign to.',
                    'Change the Status as you work: New, Contacted, Quote sent, Closed. Every change is written in the log automatically.',
                    'Open the "Team notes & activity" tab at the bottom. Press "Add note" to write for your team only (what the customer said, what to do next). Customers never see notes.',
                    'Press "Reply by email" at the top to write to the customer. Pick a ready-made template or write your own. Their answer comes to your own email address, and the email is saved in the notes.',
                    'Use the filters above the list: Status, Assigned to, Assigned to me, Unassigned, Still open. The dashboard cards open these filters for you.',
                    'Need a spreadsheet? Press Export above the list. Choose Excel or CSV. It contains exactly the enquiries that match your filters and search.',
                ],
                'tips' => [
                    'Write down every phone call in the notes. The next person then knows the history.',
                    'Notes written by a person can be deleted only by their author or an administrator. The automatic activity lines cannot be deleted.',
                    'Contact messages and job applications also have notes and Reply by email.',
                ],
            ],
            [
                'id' => 'emails',
                'title' => 'Automatic replies and email templates (administrators)',
                'area' => 'email-templates',
                'url' => 'admin/email-templates',
                'intro' => 'When a customer sends an enquiry, a contact message or a job application, they can get a short "we will connect shortly" email by themselves. You decide the wording and who gets it.',
                'steps' => [
                    'Open Settings, then Email templates.',
                    'The list shows Automatic emails (enquiry, contact form, job application) and Manual replies (follow-up, quotation, need more details, general).',
                    'Press Edit on a template. Change the subject and the message. Use placeholders such as {first_name}, {reference}, {products} and {phone}; the list is shown on the page.',
                    'For an Automatic email, the switch "Send this email automatically" turns it on or off. Switch it off if you do not want customers to receive it.',
                    'Press "Send me a test" to receive the email on your own address with sample details. Save your changes first.',
                    'Manual replies appear in the "Start from a template" list when your team uses Reply by email on an enquiry, message or application.',
                ],
                'tips' => [
                    'Keep automatic emails short and friendly. One address receives at most 3 automatic emails a day, which protects against misuse of the forms.',
                    'Do not promise prices or delivery times in an automatic email.',
                ],
            ],
            [
                'id' => 'activity',
                'title' => 'Activity log: who changed what (administrators)',
                'area' => 'activity-log',
                'url' => 'admin/activity-logs',
                'intro' => 'Every time someone on the team adds, changes or deletes something in the admin panel, a line is written to the activity log. It helps the team coordinate and settles "who did this?".',
                'steps' => [
                    'Open Settings, then Activity log. The newest lines are at the top.',
                    'Each line shows when it happened, who did it, what they did (Created, Updated, Deleted or Signed in) and which item it was.',
                    'Press "Details" on an Updated line to see each field with the value before and after.',
                    'Click the item name to open that item in the panel (it does not work for deleted items).',
                    'Use the filters to narrow it down: Person, what they did, kind of item, and a date range. The search box finds a person or an item name.',
                    'The dashboard also has a "Team activity" box with the latest 8 changes.',
                ],
                'tips' => [
                    'Passwords are never recorded, only the fact that a password was changed.',
                    'Lines older than 6 months are removed automatically.',
                    'Enquiries, messages and applications have their own notes and history on each record. Only their deletion appears in the activity log.',
                    'Re-ordering items by dragging them is not logged, to keep the list readable.',
                ],
            ],
            [
                'id' => 'drafts',
                'title' => 'Unsaved changes and automatic drafts',
                'area' => null,
                'url' => 'admin',
                'intro' => 'Long forms can be recovered if the page closes by accident.',
                'steps' => [
                    'If you try to leave a page with unsaved changes, the browser asks you to confirm.',
                    'While you type, your work is also kept as a draft inside this browser every few seconds. Nothing is sent to the server.',
                    'If something goes wrong (browser closed, power cut), open the same page again. A bar at the top offers "Restore draft" or "Discard".',
                    'The draft disappears when you save the form. Drafts older than 7 days are deleted automatically.',
                ],
                'tips' => [
                    'Pictures and files are not part of a draft. Upload them again after restoring.',
                    'A draft stays only on the computer and browser you used.',
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
            'import-products' => 'import',
            'brands', 'categories', 'verticals', 'countries' => 'catalogue',
            'slides', 'clients', 'reviews' => 'homepage',
            'insights' => 'insights',
            'application-resources' => 'resources',
            'team-members' => 'team',
            'job-openings', 'job-applications' => 'careers',
            'enquiries' => 'workflow',
            'contact-messages' => 'leads',
            'email-templates' => 'emails',
            'activity-logs' => 'activity',
            'site-settings' => 'settings',
            'users' => 'users',
            'backups' => 'backups',
            default => null,
        };
    }

    /** Words that carry no meaning when someone types a question into the search box. */
    private const STOP_WORDS = ['how', 'to', 'do', 'does', 'i', 'a', 'an', 'the', 'can', 'my', 'in', 'on', 'of', 'for', 'is', 'it', 'and', 'or', 'new', 'what', 'where', 'when', 'help', 'please', 'me', 'we', 'you'];

    /** Extra words people may use for a topic (matched in addition to the guide text). */
    private const KEYWORDS = [
        'start' => 'dashboard menu save login sign in first time begin basics',
        'images' => 'image photo picture upload size video compress webp jpg png alt text',
        'products' => 'upload product add product new product create product specification specs images gallery feature faq price',
        'import' => 'bulk upload csv excel xlsx sheet import many products template sample spreadsheet xls',
        'bulk' => 'bulk select many activate deactivate hide show move category change multiple',
        'duplicate' => 'duplicate copy clone similar product same specs',
        'preview' => 'preview view on site see page live check how looks card',
        'dashboard' => 'dashboard quick updates search cards to do missing photo seo country todo',
        'catalogue' => 'add brand new brand brand logo country map category product line vertical delete brand',
        'homepage' => 'slide slider banner hero client review testimonial top picks homepage home',
        'insights' => 'blog news webinar article post event cover pdf recording registration',
        'resources' => 'application note resource brochure guide download',
        'team' => 'team member leader leadership our story photo quote',
        'careers' => 'job opening vacancy hiring career application resume cv candidate',
        'leads' => 'enquiry enquiries message contact customer lead reply status',
        'settings' => 'site settings whatsapp catalogue banner our story home page seo',
        'workflow' => 'enquiry workflow assign assigned owner note notes status filter export csv excel team reply coordinate',
        'emails' => 'email template automatic reply auto reply autoreply acknowledgement message customer placeholder subject',
        'drafts' => 'draft autosave auto save unsaved changes restore lost recover',
        'activity' => 'activity log audit history who changed what did edited deleted team coordinate track',
        'seo' => 'seo google title description share image search engine',
        'users' => 'user role password permission admin editor sales hr active inactive login account',
        'backups' => 'backup restore download data database copy',
        'problems' => 'problem error not showing cannot delete not working fix help missing',
    ];

    /**
     * Topics matching a typed question, best first. Used by the admin search box.
     *
     * @return list<array{id: string, title: string, snippet: string}>
     */
    public static function search(string $query, $user, int $limit = 4): array
    {
        $words = collect(preg_split('/[^a-z0-9]+/', mb_strtolower($query), -1, PREG_SPLIT_NO_EMPTY))
            ->reject(fn ($w) => in_array($w, self::STOP_WORDS, true) || mb_strlen($w) < 2)
            ->map(fn ($w) => static::stem($w))
            ->unique()->values();

        if ($words->isEmpty()) {
            return [];
        }

        $hits = [];

        foreach (static::forUser($user) as $topic) {
            $title = mb_strtolower($topic['title']);
            $keywords = mb_strtolower(self::KEYWORDS[$topic['id']] ?? '');
            $intro = mb_strtolower($topic['intro']);
            $body = array_merge($topic['steps'], $topic['tips'] ?? []);

            $score = 0;
            $matched = 0;
            $stepScores = [];

            foreach ($words as $word) {
                $points = 0;
                $points += str_contains($title, $word) ? 5 : 0;
                $points += str_contains($keywords, $word) ? 4 : 0;
                $points += str_contains($intro, $word) ? 2 : 0;

                foreach ($body as $i => $line) {
                    if (str_contains(mb_strtolower($line), $word)) {
                        $points += 1;
                        $stepScores[$i] = ($stepScores[$i] ?? 0) + 1;
                    }
                }

                if ($points > 0) {
                    $matched++;
                    $score += $points;
                }
            }

            // Most of the typed words must be found in this topic.
            if ($matched < max(1, (int) ceil($words->count() * 0.6))) {
                continue;
            }

            $best = $stepScores ? array_keys($stepScores, max($stepScores))[0] : null;
            $snippet = $best !== null ? $body[$best] : $topic['intro'];

            $hits[] = ['id' => $topic['id'], 'title' => $topic['title'], 'snippet' => \Illuminate\Support\Str::limit($snippet, 110), 'score' => $score];
        }

        usort($hits, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_map(fn ($h) => ['id' => $h['id'], 'title' => $h['title'], 'snippet' => $h['snippet']], array_slice($hits, 0, $limit));
    }

    /** "uploading" and "uploads" should both find "upload". */
    private static function stem(string $word): string
    {
        foreach (['ing', 'ed', 'es', 's'] as $suffix) {
            if (mb_strlen($word) > mb_strlen($suffix) + 3 && str_ends_with($word, $suffix)) {
                return mb_substr($word, 0, -mb_strlen($suffix));
            }
        }

        return $word;
    }
}
