<div style="font-family: Arial, sans-serif; color: #0B2545; max-width: 560px;">
    <h2 style="margin: 0 0 4px; color: #0077B6;">New contact form message</h2>
    <p style="margin: 0 0 16px; color: #6C757D; font-size: 13px;">Received on {{ $contact->created_at->format('d M Y, h:i A') }}. Reply to this email to answer directly.</p>

    <table cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 14px;">
        @foreach ([
            'Name' => $contact->name,
            'Email' => $contact->email,
            'Phone' => $contact->phone,
            'Company' => $contact->company,
            'City' => $contact->city,
            'Application / Product' => $contact->subject,
        ] as $label => $value)
            @if ($value)
                <tr style="border-bottom: 1px solid #E5E7EB;">
                    <td style="width: 170px; color: #6C757D;">{{ $label }}</td>
                    <td><strong>{{ $value }}</strong></td>
                </tr>
            @endif
        @endforeach
    </table>

    <h3 style="margin: 20px 0 6px; font-size: 14px; color: #6C757D;">Message</h3>
    <div style="background: #F4F9FB; border-radius: 8px; padding: 14px; font-size: 14px; line-height: 1.6; white-space: pre-line;">{{ $contact->message }}</div>
</div>
