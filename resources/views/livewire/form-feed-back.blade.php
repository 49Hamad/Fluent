<div class="background-achievements">
    <div class="pt-5 mt-5">
        <div class="mb-3 shadow-sm evaluation-header" style="    border-top: 20px #f7c800 solid;">
            <h2 class="mt-2 evaluation-title">
                <span>{{ $tourName }}</span>
            </h2>
            <hr>

            <p class="evaluation-description">
                تاريخ إجراء التقييم: <span>{{ formatDateInArabic(now()) }}</span>
            </p>
            {{-- <p class="evaluation-email">
                البريد الإلكتروني المسجل: <span>{{ Auth::guard('admin')->user()->email}}</span>
            </p> --}}
        </div>
        <form wire:submit.prevent="submitResponses" class="p-4 rounded shadow-sm" style="background-color: #ffffff12;    border: 1px solid #f7c80075;">

   <!-- Client Details Card -->
   <div class="p-4 mb-4 rounded shadow-sm" style="background-color: #ffffff12; border: 1px solid #f7c80075;">

    <h4 class="mb-3">تفاصيل العميل</h4>
    <div class="row">
        <div class="mb-3 col-lg-6 col-md-6 col-sm-12">
            <label for="client_name" class="form-label">اسم العميل</label>
            <input type="text" id="client_name" wire:model.defer="client_name" class="form-control" placeholder="أدخل اسم العميل" required minlength="3" maxlength="50">
            @error('client_name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3 col-lg-6 col-md-6 col-sm-12">
            <label for="company_name" class="form-label">اسم الشركة</label>
            <input type="text" id="company_name" wire:model.defer="company_name" class="form-control" placeholder="أدخل اسم الشركة" required minlength="3" maxlength="50">
            @error('company_name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3 col-lg-6 col-md-6 col-sm-12">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input type="email" id="email" wire:model.defer="email" class="form-control" placeholder="أدخل البريد الإلكتروني" required maxlength="100">
            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3 col-lg-6 col-md-6 col-sm-12">
            <label for="start_project_date" class="form-label">تاريخ بدء المشروع</label>
            <input type="date" id="start_project_date" wire:model.defer="start_project_date" class="form-control" required>
            @error('start_project_date') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3 col-lg-12 col-md-12 col-sm-12">
            <label for="feedback" class="form-label">ملاحظات</label>
            <textarea id="feedback" wire:model.defer="feedback" class="form-control" rows="3" placeholder="أدخل ملاحظاتك" required minlength="3" maxlength="500"></textarea>
            @error('feedback') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

    </div>
</div>




<hr>


            @foreach($questions->questions as $question)
                <div class="mb-4">
                    <div class="mb-2 fw-bold">
                        {{ $question->question_text }}
                        @if($question->is_required)
                            <span class="text-danger">*</span>
                        @endif
                    </div>
                    <div class="p-3 border rounded " style="background-color: #f8f9fa1a">
                        @switch($question->question_type)
                            @case('اجابة قصيرة') <!-- Short Answer -->
                                <input type="text" wire:model.defer="responses.{{ $question->id }}.text" class="form-control" placeholder="أدخل إجابتك هنا">
                                @error('responses.' . $question->id . '.text')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                @break

                            @case('فقرة') <!-- Paragraph -->
                                <textarea wire:model.defer="responses.{{ $question->id }}.text" class="form-control" rows="4" placeholder="أدخل إجابتك هنا"></textarea>
                                @error('responses.' . $question->id . '.text')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                @break

                            @case('اختيار واحد') <!-- Single Choice -->
                                @foreach($question->options as $option)
                                    <div class="form-check">
                                        <input type="radio" wire:model.defer="responses.{{ $question->id }}.selected_option" value="{{ $option->option_text }}" class="form-check-input" id="radio-{{ $question->id }}-{{ $loop->index }}">
                                        <label class="form-check-label" for="radio-{{ $question->id }}-{{ $loop->index }}">{{ $option->option_text }}</label>
                                    </div>
                                @endforeach
                                @error('responses.' . $question->id . '.selected_option')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                @break

                                @case('متعدد الأختيارات')
                                @foreach($question->options as $option)
                                    <div class="form-check">
                                        <input type="checkbox"
                                               wire:model.defer="responses.{{ $question->id }}.selected_option.{{ $option->option_text }}"
                                               value="{{ $option->option_text }}"
                                               class="form-check-input"
                                               id="checkbox-{{ $question->id }}-{{ $loop->index }}">
                                        <label class="form-check-label" for="checkbox-{{ $question->id }}-{{ $loop->index }}">{{ $option->option_text }}</label>
                                    </div>
                                @endforeach
                                @error('responses.' . $question->id . '.selected_option')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                @break


                            @case('صورة') <!-- Image -->
                                <input type="file" wire:model="responses.{{ $question->id }}.image" accept="image/*" class="form-control">
                                @error('responses.' . $question->id . '.image')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                @break

                                @case('التقييم') <!-- Rating -->
                                <h6>التقييم</h6>
                                <div class="flex-row flex-wrap my-2 d-flex">
                                    @for ($i = 1; $i <= $question->number_of_stars; $i++)
                                        <div class="rating-option" wire:click="toggleRatingValue({{ $question->id }}, {{ $i }})" style="cursor: pointer; flex: 1 0 10%;">
                                            <span>{{ $i }}</span>
                                            <br>
                                            @if ($question->type_of_stars == "نجوم")
                                                <i class="fas fa-star fs-2 mx-1  star {{ in_array($i, $responses[$question->id]['number_of_stars'] ?? []) ? 'selected' : '' }}"></i>
                                            @elseif ($question->type_of_stars == "قلوب")
                                                <i class="fa fa-heart fs-2 mx-1  heart {{ in_array($i, $responses[$question->id]['number_of_stars'] ?? []) ? 'selected' : '' }}"></i>
                                            @elseif ($question->type_of_stars == "إعجاب")
                                                <i class="fas fa-thumbs-up fs-2 mx-1  thumbs-up {{ in_array($i, $responses[$question->id]['number_of_stars'] ?? []) ? 'selected' : '' }}"></i>
                                            @endif
                                        </div>
                                    @endfor
                                </div>
                                @error('responses.' . $question->id . '.number_of_stars')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            @break


                            @case('القائمة المنسدلة') <!-- Dropdown -->
                                <select wire:model.defer="responses.{{ $question->id }}.selected_option" class="form-select">
                                    <option value="" >اختر خيارًا</option>
                                    @foreach($question->options as $option)
                                        <option value="{{ $option->option_text }}">{{ $option->option_text }}</option>
                                    @endforeach
                                </select>
                                @error('responses.' . $question->id . '.selected_option')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                @break
                        @endswitch
                    </div>
                </div>
            @endforeach

            <button type="submit" class="mx-2 mt-3 btn btn-warning rounded-2 btn-sm">إرسال بيانات التقييم</button>
            <a href="{{ route('home') }}"  class="mx-2 mt-3 btn btn-danger rounded-2 btn-sm ">الغاء التقييم والعودة</a>
        </form>
    </div>

    @section('style')
    <style>

    @import url('https://fonts.googleapis.com/css2?family=Changa:wght@200..800&display=swap');

    .evaluation-header {
        background-color: #ffffff; /* خلفية بيضاء */
        border-radius: 8px; /* زوايا دائرية */
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* ظل خفيف */
        max-width: 600px; /* عرض أقصى */
        margin: auto; /* مركزي */
        font-family: "Changa", sans-serif;
      font-optical-sizing: auto;
    }

    .evaluation-title-container {
        display: flex; /* استخدام الفلكس للتنسيق */
        justify-content: space-between; /* توزيع المساحة بين العناصر */
        align-items: center; /* محاذاة العمودي */
    }

    .evaluation-title {
        font-size: 24px; /* حجم الخط */
        font-weight: bold; /* سمك الخط */
        color: #000000; /* لون النص */
        margin: 0; /* إزالة الهوامش */
    }

    .evaluation-email {
        font-size: 14px; /* حجم الخط للبريد الإلكتروني */
        color: #00796b; /* لون النص */
        margin: 0; /* إزالة الهوامش */
    }

    .evaluation-model-name {
        font-size: 18px; /* حجم الخط للعنوان الفرعي */
        color: #00796b; /* لون النص */
        margin: 10px 0; /* هوامش */
    }

    .evaluation-description {
        font-size: 16px; /* حجم الخط للوصف */
        color: #555; /* لون نص رمادي */
        line-height: 1.6; /* تباعد الأسطر */
    }

    .evaluation-description span {
        font-weight: bold; /* سمك النص للأوصاف */
    }



        form {
            max-width: 600px; /* Max width for the form */
            margin: auto; /* Center the form */
            background-color: #f9f9f9; /* Light background color */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            overflow: hidden; /* Prevent overflow */
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            form {
                padding: 2rem; /* More padding for smaller screens */
            }

            .fs-2 {
                font-size: 1.5rem; /* Smaller icon size on mobile */
            }
        }

        /* Styles for rating icons */
        .rating-option {
            transition: transform 0.2s, color 0.3s; /* Smooth transform and color transition on hover */
        }

        /* Default colors */
        .star, .heart, .thumbs-up {
            color: lightgray; /* Default color for all icons */
        }

        /* Selected colors */
        .star.selected {
            color: gold; /* Gold color for selected stars */
        }

        .heart.selected {
            color: red; /* Red color for selected hearts */
        }

        .thumbs-up.selected {
            color: blue; /* Blue color for selected thumbs-up */
        }

        /* Hover Effect */
        .rating-option:hover {
            transform: scale(1.2); /* Scale up on hover */
        }

        /* Selected Effect */
        .selected {
            animation: pulse 0.3s, wiggle 0.3s; /* Add a pulse and wiggle animation */
        }

        /* Pulse Animation */
        @keyframes pulse {
            0% {
                transform: scale(1.2);
            }
            50% {
                transform: scale(1);
            }
            100% {
                transform: scale(1.2);
            }
        }

        /* Unique Animations for Different Icons */
        .star.selected {
            animation: star-shine 0.6s forwards; /* Star shine animation */
        }

        @keyframes star-shine {
            0% {
                transform: scale(1.2);
                filter: drop-shadow(0 0 5px gold);
            }
            100% {
                transform: scale(1);
                filter: drop-shadow(0 0 0px gold);
            }
        }

        .heart.selected {
            animation: heart-beat 0.5s forwards; /* Heart beat animation */
        }

        @keyframes heart-beat {
            0%, 20%, 40%, 60%, 100% {
                transform: scale(1);
            }
            10%, 30%, 50% {
                transform: scale(1.1);
            }
        }

        .thumbs-up.selected {
            animation: thumbs-up-jump 0.5s forwards; /* Thumbs-up jump animation */
        }

        @keyframes thumbs-up-jump {
            0% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px); /* Move up */
            }
            100% {
                transform: translateY(0);
            }
        }

        /* Additional styles for improved appearance */
        .form-control, .form-select {
            transition: border-color 0.2s ease; /* Smooth transition for focus effects */
        }

        .form-control:focus, .form-select:focus {
            border-color: #007bff; /* Change border color on focus */
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); /* Focus shadow */
        }

        .rating-option {
        transition: transform 0.2s, color 0.3s;
        text-align: center; /* Center align icons */
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .rating-option {
            flex: 1 0 30%; /* Adjusts icon size on smaller screens */
        }
    }

    /* Optional: Ensure icons stay within bounds */
    .rating-option i {
        max-width: 50px; /* Optional: Limit the max width of icons */
        overflow: hidden; /* Prevent overflow */

    }
    .form-control:focus, .form-select:focus {
    border-color: #ffffff80;
    box-shadow: 0 0 0 0.2rem rgb(255 193 7 / 0%);
}

hr {
    margin: 1rem 0;
    color: inherit;
    border: 1px solid #000000bd;
    border-top: 1px solid;
    opacity: 1;
}
    </style>
    @endsection

</div>
