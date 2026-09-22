{{--
    Development-only preview hooks (FLUENT_FRONTEND_PREVIEW).
    When the flag is off this outputs an empty object, so the login screen
    shows no portal-preview box and no preview URL exists.
--}}
@php
    $previewOn = config('fluent.frontend_preview');
    $previewStates = [
        ['id' => 'received',  'label' => 'تم استلام الطلب', 'example' => 'طلب جديد'],
        ['id' => 'review',    'label' => 'قيد المراجعة',    'example' => 'طلب قيد المراجعة'],
        ['id' => 'interview', 'label' => 'مرشح للمقابلة',   'example' => 'مرشح لمقابلة'],
        ['id' => 'accepted',  'label' => 'مقبول',           'example' => 'طلب مقبول + تفاصيل الدفعة'],
        ['id' => 'waitlist',  'label' => 'قائمة الانتظار',  'example' => 'على قائمة الانتظار'],
        ['id' => 'rejected',  'label' => 'غير مقبول',       'example' => 'اكتمل النظر في الطلب'],
    ];
    $previewPayload = $previewOn
        ? ['portalUrl' => route('fluent.preview.portal'), 'states' => $previewStates, 'state' => $state ?? null]
        : new \stdClass;
@endphp
<script>
window.FLUENT_PREVIEW = {!! json_encode($previewPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!};
</script>
