@php
    $isGroup = $enquiry->items->isNotEmpty();

    // One list for both kinds of enquiry: name, brand, category, qty (group only), product url
    $lines = $isGroup
        ? $enquiry->items->map(fn ($i) => [
            'name' => $i->product_name, 'brand' => $i->brand_name, 'category' => $i->category_name, 'qty' => $i->quantity,
            'url' => $i->product ? route('product.show', $i->product->slug) : null,
        ])
        : collect($enquiry->product ? [[
            'name' => $enquiry->product->name, 'brand' => $enquiry->product->category?->brand?->name, 'category' => $enquiry->product->category?->name,
            'qty' => null, 'url' => route('product.show', $enquiry->product->slug),
        ]] : []);

    $units = $isGroup ? $enquiry->items->sum('quantity') : null;
    $adminUrl = route('filament.admin.resources.enquiries.edit', $enquiry);

    $details = [
        'Name' => $enquiry->name,
        'Email' => $enquiry->email,
        'Phone' => $enquiry->phone,
        'Company' => $enquiry->company,
        'Order location' => $enquiry->order_location,
        'Application budget' => $enquiry->budget,
    ];
@endphp
<div style="background:#F4F9FB; padding:24px 12px; font-family: Arial, Helvetica, sans-serif; color:#0B2545;">
<div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #E5E7EB; border-radius:10px; overflow:hidden;">

    {{-- Header --}}
    <div style="background:#0B2545; padding:20px 24px; border-bottom:3px solid #00B4D8;">
        <p style="margin:0; font-size:11px; letter-spacing:2px; text-transform:uppercase; color:#00B4D8;">Agarwal Brothers &middot; Website</p>
        <h1 style="margin:6px 0 0; font-size:20px; color:#ffffff;">
            {{ $isGroup ? 'New group enquiry' : 'New product enquiry' }}
        </h1>
        <p style="margin:6px 0 0; font-size:13px; color:#B8C7D9;">
            Received {{ $enquiry->created_at->format('d M Y, h:i A') }}
            @if ($isGroup) &middot; {{ $lines->count() }} {{ $lines->count() === 1 ? 'product' : 'products' }}, {{ $units }} {{ $units === 1 ? 'unit' : 'units' }} in total @endif
        </p>
    </div>

    <div style="padding:24px;">

        {{-- Customer --}}
        <h2 style="margin:0 0 8px; font-size:12px; letter-spacing:1.5px; text-transform:uppercase; color:#0077B6;">Customer details</h2>
        <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; font-size:14px;">
            @foreach ($details as $label => $value)
                @if (filled($value))
                    <tr>
                        <td style="width:150px; padding:7px 0; border-bottom:1px solid #EEF2F6; color:#6C757D;">{{ $label }}</td>
                        <td style="padding:7px 0; border-bottom:1px solid #EEF2F6;">
                            @if ($label === 'Email')
                                <a href="mailto:{{ $value }}" style="color:#0077B6; font-weight:bold; text-decoration:none;">{{ $value }}</a>
                            @elseif ($label === 'Phone')
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $value) }}" style="color:#0B2545; font-weight:bold; text-decoration:none;">{{ $value }}</a>
                            @else
                                <strong>{{ $value }}</strong>
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </table>

        {{-- Products --}}
        <h2 style="margin:26px 0 8px; font-size:12px; letter-spacing:1.5px; text-transform:uppercase; color:#0077B6;">
            {{ $isGroup ? 'Requested products' : 'Product' }}
        </h2>
        @if ($lines->isEmpty())
            <p style="margin:0; font-size:14px; color:#6C757D;">General enquiry (no specific product).</p>
        @else
            <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; font-size:14px; border:1px solid #D9E6EF;">
                <thead>
                    <tr style="background:#0077B6;">
                        <th align="left" style="width:48px; padding:10px 12px; color:#ffffff; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Sr.</th>
                        <th align="left" style="padding:10px 12px; color:#ffffff; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Product name</th>
                        @if ($isGroup)
                            <th align="right" style="width:70px; padding:10px 12px; color:#ffffff; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Qty</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lines as $line)
                        <tr style="background:{{ $loop->even ? '#F4F9FB' : '#ffffff' }};">
                            <td valign="top" style="padding:12px; border-top:1px solid #E3EDF4; color:#6C757D;">{{ $loop->iteration }}</td>
                            <td valign="top" style="padding:12px; border-top:1px solid #E3EDF4;">
                                @if ($line['url'])
                                    <a href="{{ $line['url'] }}" style="color:#0B2545; font-weight:bold; text-decoration:none;">{{ $line['name'] }}</a>
                                @else
                                    <strong>{{ $line['name'] }}</strong>
                                @endif
                                @if ($line['brand'] || $line['category'])
                                    <div style="margin-top:3px; font-size:12px; color:#0077B6;">{{ collect([$line['brand'], $line['category']])->filter()->implode(' · ') }}</div>
                                @endif
                            </td>
                            @if ($isGroup)
                                <td valign="top" align="right" style="padding:12px; border-top:1px solid #E3EDF4; font-weight:bold; color:#0B2545;">{{ $line['qty'] }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                @if ($isGroup)
                    <tfoot>
                        <tr style="background:#EAF4FA;">
                            <td colspan="2" align="right" style="padding:10px 12px; border-top:1px solid #D9E6EF; font-size:12px; text-transform:uppercase; letter-spacing:1px; color:#6C757D;">Total units</td>
                            <td align="right" style="padding:10px 12px; border-top:1px solid #D9E6EF; font-weight:bold;">{{ $units }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        @endif

        {{-- Message --}}
        @if (filled($enquiry->message))
            <h2 style="margin:26px 0 8px; font-size:12px; letter-spacing:1.5px; text-transform:uppercase; color:#0077B6;">Customer message</h2>
            <div style="background:#F4F9FB; border-left:3px solid #00B4D8; padding:14px; font-size:14px; line-height:1.6; white-space:pre-line;">{{ $enquiry->message }}</div>
        @endif

        {{-- Action --}}
        <div style="margin-top:28px; text-align:center;">
            <a href="{{ $adminUrl }}" style="display:inline-block; background:#0B2545; color:#ffffff; text-decoration:none; font-weight:bold; font-size:14px; padding:12px 26px; border-radius:6px; border-bottom:2px solid #00B4D8;">Open in admin panel</a>
            <p style="margin:14px 0 0; font-size:12px; color:#6C757D;">Reply to this email to answer the customer directly. Status can be updated in the admin panel.</p>
        </div>
    </div>
</div>
</div>
