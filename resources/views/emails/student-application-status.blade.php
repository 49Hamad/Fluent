@php
    $app = $application;
    $status = $app->status;
    $first = trim(explode(' ', trim($app->full_name))[0] ?? '');
    $cohort = $app->cohort;
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
          <p style="margin:0 0 16px">حالة طلبك في Fluent الآن:
            <b style="display:inline-block;background:#f7c800;color:#0f0f0f;padding:2px 10px;border-radius:20px">{{ $status->getLabel() }}</b>
          </p>
          <p style="margin:0 0 16px">{{ $status->studentMessage() }}</p>

          @if ($status === \App\Enums\ApplicationStatus::Interview && ($app->interview_at || $app->interview_location || $app->interview_note))
            <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;background:#f4f4f4;border-radius:8px;margin:0 0 16px">
              <tr><td style="padding:14px 16px;font-size:15px;line-height:1.9">
                @if ($app->interview_at)<b>موعد المقابلة:</b> {{ $app->interview_at->locale('ar')->translatedFormat('l j F Y — g:i A') }}<br>@endif
                @if ($app->interview_mode)<b>نوع المقابلة:</b> {{ \App\Models\StudentApplication::INTERVIEW_MODES[$app->interview_mode] ?? $app->interview_mode }}<br>@endif
                @if ($app->interview_location)<b>المكان / الرابط:</b> <span dir="ltr">{{ $app->interview_location }}</span><br>@endif
                @if ($app->interview_note){{ $app->interview_note }}@endif
              </td></tr>
            </table>
          @endif

          @if ($status === \App\Enums\ApplicationStatus::FinalAccepted && $cohort)
            <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;background:#f4f4f4;border-radius:8px;margin:0 0 16px">
              <tr><td style="padding:14px 16px;font-size:15px;line-height:1.9">
                <b>الدفعة:</b> {{ $cohort->name }}<br>
                @if ($cohort->start_date)<b>تاريخ البداية:</b> {{ $cohort->start_date->locale('ar')->translatedFormat('j F Y') }}<br>@endif
                @if ($cohort->schedule)<b>الوقت:</b> {{ $cohort->schedule }}<br>@endif
                @if ($cohort->location)<b>الموقع:</b> {{ $cohort->location }}<br>@endif
                @if ($cohort->instructions)<b>تعليمات:</b> {{ $cohort->instructions }}<br>@endif
                @if ($cohort->what_to_bring)<b>ما تحتاج تجهيزه:</b> {{ $cohort->what_to_bring }}@endif
              </td></tr>
            </table>
          @endif

          @if (filled($extraMessage))
            <p style="margin:0 0 16px;white-space:pre-line">{{ $extraMessage }}</p>
          @endif

          <p style="margin:0 0 6px;color:#6e6e6d;font-size:13px">رقم الطلب: <span dir="ltr">{{ $app->reference }}</span></p>
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
