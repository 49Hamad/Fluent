<section id="ContactUs">
    <div class="pattern-layer ">
        <div class="container" style=" overflow: hidden;">
            <div class="text-center auto">
                <span class="px-5 py-2 sub-title-two fs-6">{{ $ContactText->title ?? null }}</span>
            </div>


            <div class="row">
                <!-- Contact info cards -->
                <div class="mb-3 col-lg-4 col-md-12 col-sm-12" dir="ltr">
                    <div class="info-content">

                        @if(isset($Setting->Address) && $Setting->Address != null)
                        <div class="single-info-box">
                            <div class="icon-box"><i class="icon-22"></i></div>
                            <p>
                                @foreach ($Setting->Address  as  $address )

                                @if ($address['social_type'] === "phone")
                                <a href="tel:{{ $address['name'] }}">{{ $address['name'] }}</a>
                                @endif
                                @endforeach
                            </p>
                        </div>

                        <div class="single-info-box">
                            <div class="icon-box"><i class="icon-23"></i></div>
                            @foreach ($Setting->Address  as  $address )

                            @if ($address['social_type'] === "address")
                           <p>
                            {{ $address['name'] }}
                           </p>
                            @endif
                            @endforeach
                        </div>

                        <div class="single-info-box">
                            <div class="icon-box"><i class="icon-24"></i></div>
                            <p>
                                @foreach ($Setting->Address  as  $address )

                                @if ($address['social_type'] === "email")
                                <a href="mailTo:{{ $address['name'] }}">{{ $address['name'] }}</a>
                                @endif
                                @endforeach
                            </p>
                        </div>

                        @endif

                    </div>
                </div>

                <div class="col-lg-8 col-md-12 col-sm-12">
                    <div class="mb-4">
                        <div class="map-box">
                            <iframe class="rounded embed-responsive-item w-100"
                                src="{{ $Setting->location }}"
                                style="border:0;" allowfullscreen="" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>


                    </div>
                    <div class="mb-4 col-md-12">
                        @if (isset($ContactText->description) && $ContactText->description != null)
                        <h2 class="mb-4 "> <span class="text-warning">
                            {{ $ContactText->description ?? null  }}
                        </h2>
                        @endif


                        <div>


                            <form wire:submit.prevent="submit">
                                <div class="mb-3 row">
                                    <div class="mb-3 col-md-6 mb-md-0">
                                        <input type="text" class="form-control" placeholder="الاسم" wire:model="name" aria-label="الاسم">
                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="email" class="form-control" placeholder="الايميل" wire:model="email" aria-label="الايميل">
                                        @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <div class="mb-3 col-md-6 mb-md-0">
                                        <select class="form-select" wire:model="extra_service" aria-label="اختر نوع الخدمة">
                                            <option value="">اختر نوع الخدمة</option>
                                            @foreach ($extraServices as $service)
                                                <option value="{{ $service->name }}">{{ $service->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('extra_service') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" placeholder="الموضوع" wire:model="subject" aria-label="الموضوع">
                                        @error('subject') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" rows="5" placeholder="الرسالة" wire:model="description"></textarea>
                                    @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="my-2 text-center">
                                    <button type="submit" class="theme-btn rounded-5">إرسال</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

            </div>







        </div>
    </div>

</section>
