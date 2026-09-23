@php $first = trim(explode(' ', trim($name))[0] ?? ''); @endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;background:#f4f4f4;font-family:Tahoma,Arial,sans-serif;color:#0f0f0f;direction:rtl;text-align:right">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:24px 12px">
    <tr><td align="center">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:12px;overflow:hidden">
        <tr><td style="background:#0f0f0f;padding:20px 24px;color:#f4f4f4;font-size:20px;font-weight:bold">Fluent</td></tr>
        <tr><td style="padding:28px 24px 12px;font-size:16px;line-height:1.9">
          <p style="margin:0 0 12px">مرحبًا {{ $first }}،</p>
          <p style="margin:0 0 18px">رمز الدخول إلى مساحتك في Fluent:</p>
          <p style="margin:0 0 18px;text-align:center">
            <span dir="ltr" style="display:inline-block;background:#0f0f0f;color:#f7c800;font-size:30px;letter-spacing:10px;font-weight:bold;padding:12px 22px;border-radius:10px;font-family:Consolas,Menlo,monospace">{{ $code }}</span>
          </p>
          <p style="margin:0 0 8px">الرمز صالح لمدة {{ $minutes }} دقائق ولمرة واحدة فقط.</p>
          <p style="margin:0 0 8px;color:#6e6e6d;font-size:14px">إذا لم تطلب هذا الرمز، تجاهل الرسالة — لن يدخل أحد إلى مساحتك بدونه. فريق Fluent لن يطلب منك هذا الرمز أبدًا.</p>
        </td></tr>
        <tr><td style="padding:16px 24px 24px;color:#6e6e6d;font-size:13px;border-top:1px solid #eee">فريق Fluent</td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
