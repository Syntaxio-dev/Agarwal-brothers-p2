<?php

namespace App\Sales\Filament\Resources\SalesReps\Pages;

use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
use App\Sales\Support\PhoneNumber;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\Locked;

/**
 * Add a rep, step 2 of 2: "<Name> can sign in". The password was made in step 1 and is shown here only once (it is read from the
 * manager's own session and removed at once, so a reload does not show it again). The message with the code and the password is
 * built in one place (lang/en/sales_reps.php); the WhatsApp link uses the normalised phone number.
 */
class RepSignInReady extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SalesRepResource::class;

    protected string $view = 'filament.sales.reps.ready';

    #[Locked]
    public string $password = '';

    public bool $given = false;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $stored = session()->pull(CreateSalesRep::sessionKey($this->record->getKey()));

        try {
            $this->password = $stored ? Crypt::decryptString($stored) : '';
        } catch (DecryptException) {
            $this->password = '';
        }

        if ($this->password === '') {
            // a reload, or somebody else's link: the password is not shown twice
            $this->redirect(SalesRepResource::getUrl('view', ['record' => $this->record]), navigate: false);
        }
    }

    public function getTitle(): string
    {
        return $this->record->user->name . ' can sign in';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function done(): void
    {
        static::authorizeResourceAccess();

        if (! $this->given) {
            return;
        }

        $this->password = '';
        $this->redirect(SalesRepResource::getUrl('view', ['record' => $this->record]));
    }

    /** The message to give to the rep. */
    public function message(): string
    {
        return __('sales_reps.sign_in_message', ['name' => $this->record->user->name, 'code' => $this->record->employee_code, 'password' => $this->password]);
    }

    protected function getViewData(): array
    {
        $digits = preg_replace('/\D+/', '', (string) $this->record->phone);

        return [
            'name' => $this->record->user->name,
            'code' => $this->record->employee_code,
            'phoneText' => PhoneNumber::display($this->record->phone),
            'message' => $this->message(),
            'whatsapp' => $digits !== '' ? 'https://wa.me/' . $digits . '?text=' . rawurlencode($this->message()) : null,
            'profileUrl' => SalesRepResource::getUrl('view', ['record' => $this->record]),
        ];
    }
}
