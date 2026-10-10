<div style="background:#F4F9FB; padding:24px 12px; font-family: Arial, Helvetica, sans-serif; color:#0B2545;">
<div style="max-width:600px; margin:0 auto; background:#ffffff; border:1px solid #E5E7EB; border-radius:10px; overflow:hidden;">

    <div style="background:#0B2545; padding:18px 24px; border-bottom:3px solid #00B4D8;">
        <p style="margin:0; font-size:18px; font-weight:bold; color:#ffffff;">Agarwal Brothers</p>
        <p style="margin:4px 0 0; font-size:11px; letter-spacing:2px; text-transform:uppercase; color:#00B4D8;">Laboratory equipment &amp; chemicals</p>
    </div>

    <div style="padding:26px 24px; font-size:15px; line-height:1.65;">
        {!! nl2br(e($body)) !!}
    </div>

    <div style="background:#F4F9FB; padding:16px 24px; border-top:1px solid #E5E7EB; font-size:12px; line-height:1.6; color:#6C757D;">
        <strong style="color:#0B2545;">Agarwal Brothers</strong><br>
        {{ config('contact.head_office.address') }}<br>
        Phone: <a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) config('contact.call')) }}" style="color:#0077B6; text-decoration:none;">{{ config('contact.call') }}</a>
        &nbsp;|&nbsp; Email: <a href="mailto:{{ config('contact.mail_24x7') }}" style="color:#0077B6; text-decoration:none;">{{ config('contact.mail_24x7') }}</a>
    </div>
</div>
</div>
