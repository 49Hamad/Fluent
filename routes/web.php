<?php

use App\Livewire\FormFeedBack;
use Illuminate\Support\Facades\Route;
use App\Livewire\HomePage\ShowHomePage;

Route::get('/',ShowHomePage::class)->name('home');
Route::get('/feedback-form/{id}',FormFeedBack::class)->name('feedback_form');

/*
|--------------------------------------------------------------------------
| Redesign — TEMPORARY public destinations (development only)
|--------------------------------------------------------------------------
| The new design links to three product pages. Their real workflows
| (student application, business challenge, student portal + auth) are
| built in later phases. Until then these routes show a clearly labelled
| "in preparation" page: no form, no data collection, no fake login.
| Replace each closure with the real controller/component when ready.
*/
Route::view('/apply', 'fluent.pages.coming-soon', [
    'eyebrow'   => 'للطلاب والخريجين',
    'pageTitle' => 'سجّل في المحاكاة',
    'lead'      => 'تنضم إلى بيئة عمل محاكية: فريق، دور محدّد، مشروع، ومواعيد تسليم. تخرج بخبرة عملية تقدر تتكلم عنها بثقة — قبل وظيفتك الأولى.',
    'soonTitle' => 'نموذج التسجيل قيد التجهيز.',
    'soonText'  => 'نعمل حاليًا على تجهيز التسجيل في المحاكاة. هذه الصفحة مؤقتة ولا تستقبل أي طلبات بعد.',
])->name('fluent.apply');

Route::view('/challenge', 'fluent.pages.coming-soon', [
    'eyebrow'   => 'للشركات والجهات',
    'pageTitle' => 'شاركنا تحديًا',
    'lead'      => 'عندكم مشكلة حقيقية أو مشروع يستحق التفكير؟ حوّلوه إلى تحدٍّ داخل محاكاة Fluent. تعمل عليه فرق من الطلاب والخريجين بأدوار ومواعيد تسليم، وتستلمون مخرجات مكتوبة من زوايا متعدّدة.',
    'soonTitle' => 'نموذج مشاركة التحديات قيد التجهيز.',
    'soonText'  => 'نعمل حاليًا على تجهيز استقبال التحديات من الجهات. هذه الصفحة مؤقتة ولا تستقبل أي طلبات بعد.',
])->name('fluent.challenge');

Route::view('/portal', 'fluent.pages.coming-soon', [
    'eyebrow'   => 'للمشاركين',
    'pageTitle' => 'مساحتي في Fluent',
    'lead'      => 'مساحة المشارك لمتابعة الطلب وتفاصيل التجربة.',
    'soonTitle' => 'مساحة المشاركين قيد التجهيز.',
    'soonText'  => 'تسجيل الدخول ومساحة المشارك غير متاحة بعد. هذه الصفحة مؤقتة ولا تطلب أي بيانات دخول.',
])->name('fluent.portal');
