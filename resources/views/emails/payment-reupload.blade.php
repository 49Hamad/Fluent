@php
    $first = trim(explode(' ', trim($application->full_name))[0] ?? '');
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;background:#f4f4f4;font-family:Tahoma,Arial,sans-serif;color:#0f0f0f;direction:rtl;text-align:right">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:24px 12px">
    <tr><td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:12px;overflow:hidden">
        <tr><td style="background:#0f0f0f;padding:20px 24px;color:#f4f4f4;font-size:20px;font-weight:bold">Fluent</td></tr>
        <tr><td style="padding:28px 24px 8px;font-size:16px;line-height:1.9">
          <p style="margin:0 0 12px">مرحبًا {{ $first }}،</p>
          <p style="margin:0 0 16px">راجعنا إيصال التحويل الذي رفعته، ونحتاج منك رفع إيصال جديد لإكمال تأكيد مقعدك.</p>
          <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;background:#f4f4f4;border-radius:8px;margin:0 0 16px">
            <tr><td style="padding:14px 16px;font-size:15px;line-height:1.9;white-space:pre-line"><b>السبب:</b> {{ $reason }}</td></tr>
          </table>
          <p style="margin:0 0 20px">
            <a href="{{ route('fluent.login') }}" style="display:inline-block;background:#0f0f0f;color:#f4f4f4;text-decoration:none;padding:10px 20px;border-radius:24px;font-size:15px">ادخل مساحتك وارفع الإيصال</a>
          </p>
          <p style="margin:0 0 6px;color:#6e6e6d;font-size:13px">رقم الطلب: <span dir="ltr">{{ $application->reference }}</span></p>
        </td></tr>
        <tr><td style="padding:16px 24px 24px;color:#6e6e6d;font-size:13px;line-height:1.8;border-top:1px solid #eee">
          فريق Fluent<br>
          @if ($email = \App\Support\FluentContact::email())<span dir="ltr">{{ $email }}</span>@endif
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
