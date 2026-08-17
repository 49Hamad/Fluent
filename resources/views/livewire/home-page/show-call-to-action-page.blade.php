<div class="py-5 pattern-layer-2 services-section">
    <div class="container" style="overflow: hidden;">
        <div class="mt-5 text-center aut">
            <h1>{{ $Consulting->title ?? null }}</h1>
            <p class="mt-4 fs-6" style="line-height:50px">
                {{ $Consulting->description ?? null }}
            </p>

            <div class="btn-box">
                <span data-bs-toggle="modal" data-bs-target="#whatsappModel"
                    class="mx-3 sub-title-two d-inline-block fs-6">استشارة عبر الوتساب</span>

                @if ($Consulting->is_active)
                    <a href="{{ $Consulting->button_link ?? null }}">
                        <span
                            class="mx-3 sub-title-two d-inline-block fs-6">{{ $Consulting->button_text ?? null }}</span>
                    </a>
                @endif
            </div>




        </div>
    </div>

      <!-- Modal Structure -->
<div class="modal fade" id="whatsappModel" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document">
    <div class="modal-content"
        style="background-image: url({{ asset('front/assets/images/background-slider-serivces.png') }}); background-size: cover; background-position: center;">
        <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">استشارة عبر الواتساب</h5>
                <button type="button" class="text-white btn-close bg-danger" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="sendWhatsApp" class="custom-form">
                   <div class="row">
                    <div class="col-lg-6 form-group">
                        <input type="text" wire:model="name" class="text-white form-contrl" placeholder="اسمك" required>
                    </div>
                    <div class="col-lg-6 form-group">
                        <select class="form-select" wire:model="serviceType" aria-label="اختر نوع الخدمة">
                            <option value="">اختر نوع الخدمة</option>
                            @foreach ($extraServices as $service)
                                <option value="{{ $service->name }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                        @error('serviceType') <span class="error-message">{{ $message }}</span> @enderror
                    </div>
                   </div>
                    <div class="form-group">
                        <input type="text" wire:model="subject" class="text-white form-contrl" placeholder="الموضوع" required>
                    </div>
                    <div class="col-lg-12 form-group">
                        <textarea wire:model="message" class="text-white w-100" cols="6" rows="2"  style="border: 1px solid #ffffff1f;
    border-radius: 10px;
    padding: 11px 19px 0px 0px;" placeholder="الرسالة" required></textarea>
                    </div>
                    <div class="text-center">
                    <button type="submit" class="my-2 mb-5 btn btn-success w-50 btn-sm ">إرسال عبر WhatsApp</button>

                    </div>
                </form>

            </div>

        </div>
    </div>
</div>

<!-- CSS Styles -->

</div>
