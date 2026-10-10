<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Mail\CustomerMessage;
use App\Models\Enquiry;
use App\Models\EnquiryItem;
use App\Support\EmailTemplates;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')
                ->label('Send me a test')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('gray')
                ->tooltip('Sends this email to your own address with sample customer details. Save your changes first.')
                ->action(function () {
                    $user = auth()->user();
                    $sample = new Enquiry(['name' => 'Rohit Sharma', 'email' => $user->email]);
                    $sample->id = 123;
                    $sample->setRelation('product', null);
                    $sample->setRelation('items', collect([
                        new EnquiryItem(['product_name' => 'Hei-VAP Core Rotary Evaporator', 'quantity' => 2]),
                        new EnquiryItem(['product_name' => 'Entris II Analytical Balance', 'quantity' => 1]),
                    ]));
                    $sample->position = 'Sales Executive';

                    $template = $this->getRecord();
                    Mail::to($user->email)->queue(new CustomerMessage(
                        '[TEST] ' . EmailTemplates::render($template->subject, $sample),
                        EmailTemplates::render($template->body, $sample),
                    ));

                    Notification::make()->title('Test email sent to ' . $user->email)->success()->send();
                }),
        ];
    }
}
