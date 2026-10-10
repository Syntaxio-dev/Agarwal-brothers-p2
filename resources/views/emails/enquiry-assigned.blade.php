@php
    $e = $enquiry->loadMissing('items', 'product');
    $about = $e->items->isNotEmpty()
        ? $e->items->map(fn ($i) => $i->product_name . ($i->quantity > 1 ? ' x' . $i->quantity : ''))->implode(', ')
        : ($e->product?->name ?? 'General enquiry');
@endphp
<div style="background:#F4F9FB; padding:24px 12px; font-family: Arial, Helvetica, sans-serif; color:#0B2545;">
<div style="max-width:560px; margin:0 auto; background:#ffffff; border:1px solid #E5E7EB; border-radius:10px; overflow:hidden;">
    <div style="background:#0B2545; padding:18px 24px; border-bottom:3px solid #00B4D8;">
        <p style="margin:0; font-size:11px; letter-spacing:2px; text-transform:uppercase; color:#00B4D8;">Agarwal Brothers &middot; Admin</p>
        <h1 style="margin:6px 0 0; font-size:19px; color:#ffffff;">An enquiry is now yours</h1>
    </div>
    <div style="padding:22px 24px; font-size:14px; line-height:1.6;">
        <p style="margin:0 0 14px;">
            @if ($assignedBy) <strong>{{ $assignedBy }}</strong> assigned this enquiry to you. @else This enquiry was assigned to you. @endif
        </p>
        <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; font-size:14px;">
            @foreach ([
                'Reference' => $e->reference(),
                'Customer' => $e->name,
                'Email' => $e->email,
                'Phone' => $e->phone,
                'Company' => $e->company,
                'About' => $about,
                'Status' => \App\Models\Enquiry::STATUSES[$e->status] ?? ucfirst((string) $e->status),
            ] as $label => $value)
                @if (filled($value))
                    <tr>
                        <td style="width:110px; padding:6px 0; border-bottom:1px solid #EEF2F6; color:#6C757D;">{{ $label }}</td>
                        <td style="padding:6px 0; border-bottom:1px solid #EEF2F6;"><strong>{{ $value }}</strong></td>
                    </tr>
                @endif
            @endforeach
        </table>
        <div style="margin-top:22px; text-align:center;">
            <a href="{{ route('filament.admin.resources.enquiries.edit', $e) }}" style="display:inline-block; background:#0B2545; color:#ffffff; text-decoration:none; font-weight:bold; font-size:14px; padding:11px 24px; border-radius:6px; border-bottom:2px solid #00B4D8;">Open the enquiry</a>
        </div>
    </div>
</div>
</div>
