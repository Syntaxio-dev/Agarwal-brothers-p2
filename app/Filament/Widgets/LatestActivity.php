<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Enquiry;
use App\Models\JobApplication;
use Filament\Widgets\Widget;

/** The most recent customer / candidate activity, newest first. */
class LatestActivity extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.latest-activity';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && ($user->canManage('enquiries') || $user->canManage('contact-messages') || $user->canManage('job-applications'));
    }

    /** @return \Illuminate\Support\Collection<int, array<string, mixed>> */
    public function getRows()
    {
        $user = auth()->user();
        $rows = collect();

        if ($user->canManage('enquiries')) {
            Enquiry::with('product')->withCount('items')->latest()->limit(6)->get()->each(fn ($e) => $rows->push([
                'type' => 'Enquiry',
                'tone' => 'warning',
                'title' => $e->name,
                'detail' => $e->items_count ? 'group enquiry, ' . $e->items_count . ' products' : ($e->product?->name ? 'about ' . $e->product->name : 'general enquiry'),
                'status' => $e->status,
                'at' => $e->created_at,
                'url' => route('filament.admin.resources.enquiries.edit', $e),
            ]));
        }

        if ($user->canManage('contact-messages')) {
            ContactMessage::latest()->limit(6)->get()->each(fn ($m) => $rows->push([
                'type' => 'Message',
                'tone' => 'info',
                'title' => $m->name,
                'detail' => str($m->message)->limit(70)->toString(),
                'status' => $m->status,
                'at' => $m->created_at,
                'url' => route('filament.admin.resources.contact-messages.edit', $m),
            ]));
        }

        if ($user->canManage('job-applications')) {
            JobApplication::latest()->limit(6)->get()->each(fn ($a) => $rows->push([
                'type' => 'Application',
                'tone' => 'success',
                'title' => $a->name,
                'detail' => 'for ' . ($a->position ?: 'general application'),
                'status' => $a->status,
                'at' => $a->created_at,
                'url' => route('filament.admin.resources.job-applications.edit', $a),
            ]));
        }

        return $rows->sortByDesc('at')->take(8)->values();
    }
}
