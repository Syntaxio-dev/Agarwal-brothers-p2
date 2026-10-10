<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Internal team notes and activity log, shared by enquiries, contact messages and job applications.
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->morphs('noteable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 20)->default('note');   // note = written by a person, system = automatic log line
            $table->text('body');
            $table->timestamps();
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });

        // Editable e-mails: automatic acknowledgements and ready-made replies.
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('kind', 20);                    // auto or reply
            $table->string('name');
            $table->string('subject');
            $table->text('body');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        $now = now();
        $sign = "Regards,\nTeam Agarwal Brothers";

        $rows = [
            ['auto_enquiry', 'auto', 'Auto-reply: product enquiry',
                'We have received your enquiry ({reference})',
                "Dear {first_name},\n\nThank you for contacting Agarwal Brothers. We have received your enquiry about:\n{products}\n\nOur team will connect with you shortly. If your requirement is urgent, please call us on {phone}.\n\n{$sign}"],
            ['auto_contact', 'auto', 'Auto-reply: contact form',
                'Thank you for contacting Agarwal Brothers',
                "Dear {first_name},\n\nThank you for reaching out to Agarwal Brothers. We have received your message and our team will get back to you shortly.\n\nIf your matter is urgent, please call us on {phone}.\n\n{$sign}"],
            ['auto_application', 'auto', 'Auto-reply: job application',
                'We have received your application',
                "Dear {first_name},\n\nThank you for applying to Agarwal Brothers{position_line}. Our HR team will review your profile and connect with you shortly if it matches an opening.\n\n{$sign}"],
            ['reply_followup', 'reply', 'Reply: follow-up',
                'Following up on your enquiry ({reference})',
                "Dear {first_name},\n\nThank you for your interest in Agarwal Brothers. We are following up on your enquiry:\n{products}\n\nPlease let us know a convenient time to speak, or call us on {phone}.\n\n{$sign}"],
            ['reply_quote', 'reply', 'Reply: quotation',
                'Quotation for your enquiry ({reference})',
                "Dear {first_name},\n\nThank you for your enquiry. We are pleased to share our quotation for:\n{products}\n\nPlease review it and let us know if you need any changes. We will be happy to assist you further.\n\n{$sign}"],
            ['reply_more_info', 'reply', 'Reply: need more details',
                'A few details we need ({reference})',
                "Dear {first_name},\n\nThank you for your enquiry. To prepare the best offer for you, could you please share a few more details, such as your application, required quantity and delivery location?\n\n{$sign}"],
            ['reply_general', 'reply', 'Reply: general',
                'Regarding your enquiry ({reference})',
                "Dear {first_name},\n\nThank you for contacting Agarwal Brothers.\n\n\n\n{$sign}"],
        ];

        foreach ($rows as [$key, $kind, $name, $subject, $body]) {
            DB::table('email_templates')->insert([
                'key' => $key, 'kind' => $kind, 'name' => $name, 'subject' => $subject, 'body' => $body,
                'is_enabled' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
        });
        Schema::dropIfExists('notes');
    }
};
