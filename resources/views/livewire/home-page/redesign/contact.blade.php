{{--
    Contact form — new Fluent skin for the EXISTING Livewire component
    App\Livewire\HomePage\ShowContactUslPage. Same fields, same wire:model
    names, same submit() action → the backend (save to DB, email to
    fluent@fluent.sa, Filament database notification) is unchanged.
    Field styles are the prototype's form layer (fluent-forms.css).
    Reveal elements use wire:ignore.self so Livewire re-renders never
    strip the client-side "in" class.
--}}
@php
    $address  = collect($Setting?->Address ?? []);
    $cEmail   = $address->firstWhere('social_type', 'email')['name'] ?? null;
    $cPhone   = $address->firstWhere('social_type', 'phone')['name'] ?? null;
    $cPlace   = $address->firstWhere('social_type', 'address')['name'] ?? null;
    $errIcon  = '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg>';
@endphp
<section class="sec contact" id="contact-form" aria-labelledby="contact-h">
  <div class="wrap">
    <div class="contact-head reveal" wire:ignore.self>
      <span class="eyebrow">تواصل</span>
      <h2 class="d2" id="contact-h">{{ $ContactText->title ?? 'تواصل معنا' }}</h2>
      @if (filled($ContactText?->description))
        <p class="lead">{{ $ContactText->description }}</p>
      @endif
    </div>

    <div class="flayout">
      <div class="fcard reveal" wire:ignore.self>
        <form wire:submit.prevent="submit" novalidate>
          <div class="fgrid">
            <div @class(['ffield', 'is-err' => $errors->has('name')])>
              <label class="flabel" for="c-name">الاسم<span class="req" aria-hidden="true">*</span></label>
              <input id="c-name" type="text" class="fctrl" wire:model="name" autocomplete="name" maxlength="50" required
                     @error('name') aria-invalid="true" aria-describedby="c-name-e" @enderror>
              @error('name') <p class="ferr" id="c-name-e">{!! $errIcon !!}{{ $message }}</p> @enderror
            </div>

            <div @class(['ffield', 'is-err' => $errors->has('email')])>
              <label class="flabel" for="c-email">البريد الإلكتروني<span class="req" aria-hidden="true">*</span></label>
              <input id="c-email" type="email" class="fctrl" wire:model="email" autocomplete="email" inputmode="email" dir="ltr" maxlength="100" required
                     @error('email') aria-invalid="true" aria-describedby="c-email-e" @enderror>
              @error('email') <p class="ferr" id="c-email-e">{!! $errIcon !!}{{ $message }}</p> @enderror
            </div>

            <div @class(['ffield', 'is-err' => $errors->has('extra_service')])>
              <label class="flabel" for="c-service">نوع الطلب<span class="req" aria-hidden="true">*</span></label>
              <select id="c-service" class="fctrl" wire:model="extra_service" required
                      @error('extra_service') aria-invalid="true" aria-describedby="c-service-e" @enderror>
                <option value="">اختر نوع الطلب</option>
                @foreach ($extraServices as $service)
                  <option value="{{ $service->name }}">{{ $service->name }}</option>
                @endforeach
              </select>
              @error('extra_service') <p class="ferr" id="c-service-e">{!! $errIcon !!}{{ $message }}</p> @enderror
            </div>

            <div @class(['ffield', 'is-err' => $errors->has('subject')])>
              <label class="flabel" for="c-subject">الموضوع<span class="req" aria-hidden="true">*</span></label>
              <input id="c-subject" type="text" class="fctrl" wire:model="subject" maxlength="100" required
                     @error('subject') aria-invalid="true" aria-describedby="c-subject-e" @enderror>
              @error('subject') <p class="ferr" id="c-subject-e">{!! $errIcon !!}{{ $message }}</p> @enderror
            </div>

            <div @class(['ffield', 'w-full', 'is-err' => $errors->has('description')])>
              <label class="flabel" for="c-msg">الرسالة<span class="req" aria-hidden="true">*</span></label>
              <textarea id="c-msg" class="fctrl" rows="5" wire:model="description" maxlength="200" required
                        @error('description') aria-invalid="true" aria-describedby="c-msg-e" @enderror></textarea>
              <div class="fcount-row">
                @error('description') <p class="ferr" id="c-msg-e">{!! $errIcon !!}{{ $message }}</p> @else <span></span> @enderror
                <span class="fcount num" x-data x-text="(($wire.description || '').length) + ' / 200'"
                      :class="{ 'is-near': ($wire.description || '').length > 180 }">0 / 200</span>
              </div>
            </div>
          </div>

          <div class="factions">
            <button type="submit" class="btn btn-primary" wire:loading.attr="data-loading" wire:target="submit">
              <span class="btn-label">إرسال</span>
              <span class="spin" aria-hidden="true"></span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
            </button>
            <span class="spacer"></span>
            <span class="fmeta">نرد على رسالتك عبر البريد الإلكتروني.</span>
          </div>
        </form>
      </div>

      <aside class="faside reveal" style="--d:120ms" wire:ignore.self>
        @if ($cEmail || $cPhone || $cPlace)
          <div class="fnote">
            <h3 class="fnote-h">بيانات التواصل</h3>
            <div class="cinfo">
              @if ($cEmail)
                <div class="cinfo-row">
                  <span class="cinfo-k">البريد</span>
                  <a class="cinfo-v" href="mailto:{{ $cEmail }}" dir="ltr" style="text-align:right">{{ $cEmail }}</a>
                </div>
              @endif
              @if ($cPhone)
                <div class="cinfo-row">
                  <span class="cinfo-k">الهاتف</span>
                  <a class="cinfo-v num" href="tel:{{ preg_replace('/[^\d+]/', '', $cPhone) }}" dir="ltr" style="text-align:right">{{ $cPhone }}</a>
                </div>
              @endif
              @if ($cPlace)
                <div class="cinfo-row">
                  <span class="cinfo-k">العنوان</span>
                  <span class="cinfo-v">{{ $cPlace }}</span>
                </div>
              @endif
            </div>
          </div>
        @endif
        <div class="fnote">
          <h3 class="fnote-h">تبحث عن مسار محدد؟</h3>
          <p>للطلاب والخريجين: <a href="{{ route('fluent.apply') }}">سجّل في المحاكاة</a>.<br>
             للشركات والجهات: <a href="{{ route('fluent.challenge') }}">شاركنا تحديًا</a>.</p>
        </div>
      </aside>
    </div>
  </div>
</section>
