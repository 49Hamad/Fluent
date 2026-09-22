/* ============================================================
   FLUENT — تعريف حقول النماذج
   ------------------------------------------------------------
   👈 هذا هو الملف الوحيد الذي تُعدَّل فيه أسئلة النماذج.
   لا تحتاج لمس HTML ولا CSS ولا محرّك النموذج.

   ⚠️⚠️ تنبيه مهم ⚠️⚠️
   الحقول الموجودة الآن **مبدئية (placeholders)** وُضعت فقط
   لتشغيل الواجهة وعرض التجربة. كل حقل مبدئي مُعلَّم بـ:
        draft: true
   عند وصول الأسئلة المعتمدة: احذف الحقول المبدئية، اكتب الحقول
   النهائية بنفس الصيغة، واحذف draft.

   ------------------------------------------------------------
   صيغة الحقل:
   {
     name:        'full_name',        // اسم المفتاح في قاعدة البيانات (إنجليزي، بدون مسافات)
     label:       'الاسم الكامل',      // النص الظاهر
     type:        'text',             // انظر الأنواع أدناه
     required:    true,               // اختياري (الافتراضي false)
     width:       'half' | 'full',    // عرض الحقل في الشبكة (الافتراضي full)
     placeholder: 'اكتب هنا…',
     help:        'نص توضيحي صغير تحت الحقل',
     maxlength:   120,                // يُفعّل عدّاد الأحرف مع textarea
     minlength:   20,
     min:/max:    (للأرقام والتواريخ)
     options:     [{value:'a', label:'أ'}, …]   // للقوائم والاختيارات
     inline:      true                // لعرض الاختيارات بجانب بعض
     autocomplete:'name' | 'email' | 'tel' | 'organization' …
   }

   الأنواع المدعومة:
     text · email · tel · url · number · date · textarea
     select · radio · checkbox (اختيارات متعددة) · consent (موافقة مفردة)
   ============================================================ */

window.FLUENT_SCHEMAS = {

  /* ==========================================================
     أ) الطلاب والخريجون — سجّل في المحاكاة
     ========================================================== */
  student: {
    key: 'student',
    table: 'student',
    submissionType: 'application',

    /* الأعمدة الحقيقية في الجدول. أي حقل آخر يُحفظ تلقائيًا
       داخل عمود data من نوع jsonb — أي أن إضافة أسئلة جديدة
       لا تحتاج أي تعديل على قاعدة البيانات. */
    columns: { full_name:'full_name', email:'email', phone:'phone' },

    /* الخطوات. خطوة واحدة = نموذج عادي بلا مؤشّر.
       أكثر من خطوة = تجربة متعدّدة الخطوات تلقائيًا. */
    /* بعد الإرسال ينتقل الطالب إلى مساحته مباشرة */
    portalCta: { href:'/login', label:'ادخل مساحتك' },
    doneTitle: 'وصلنا طلبك.',
    doneText: 'أنشأنا لك مساحة في Fluent تتابع منها حالة طلبك. الدخول إليها بنفس بريدك في أي وقت.',

    steps: [
      {
        title: 'بياناتك',
        hint:  'المعلومات الأساسية للتواصل معك',
        fields: [
          { draft:true, name:'full_name', label:'الاسم الكامل', type:'text', required:true, width:'half', autocomplete:'name', placeholder:'الاسم كما في الهوية' },
          { draft:true, name:'email', label:'البريد الإلكتروني', type:'email', required:true, width:'half', autocomplete:'email', placeholder:'name@example.com', help:'سنرسل نتيجة الطلب على هذا البريد.' },
          { draft:true, name:'phone', label:'رقم الجوال', type:'tel', required:true, width:'half', autocomplete:'tel', placeholder:'05XXXXXXXX' },
          { draft:true, name:'city', label:'المدينة', type:'text', width:'half', autocomplete:'address-level2', placeholder:'الرياض' }
        ]
      },
      {
        title: 'خلفيتك',
        hint:  'حتى نضعك في الفريق والدور المناسبين',
        fields: [
          { draft:true, name:'status', label:'وضعك الحالي', type:'radio', required:true, inline:true, options:[
              { value:'student',  label:'طالب' },
              { value:'graduate', label:'خريج' },
              { value:'other',    label:'غير ذلك' }
          ]},
          { draft:true, name:'major', label:'التخصص', type:'text', width:'half', placeholder:'مثال: نظم المعلومات' },
          { draft:true, name:'motivation', label:'ليش تبي تشارك في المحاكاة؟', type:'textarea', required:true, maxlength:600, minlength:40, placeholder:'اكتب بصراحة — ما نبحث عن إجابة مثالية.' },

          /* رفع ملف — الملف يُرفع إلى سلة Supabase خاصة، ولا يُحفظ في
             قاعدة البيانات إلا مساره. bucket = اسم السلة، column = العمود
             الذي يُخزَّن فيه المسار (اختياري؛ المسار يُحفظ في data دائمًا). */
          { draft:true, name:'cv', label:'السيرة الذاتية', type:'file', required:true,
            maxSizeMB:5, bucket:'fluent-cv', column:'cv_path',
            help:'PDF فقط · الحد الأقصى 5 ميجابايت' },

          { draft:true, name:'consent', type:'consent', required:true, label:'أوافق على استخدام بياناتي لغرض تقييم الطلب والتواصل معي بخصوص تجارب Fluent.' }
        ]
      }
    ]
  },

  /* ==========================================================
     ب) قائمة الانتظار — تُستخدم تلقائيًا حين تكون
        registration.student = 'waitlist'
        تُحفظ في نفس الجدول بنوع مختلف.
     ========================================================== */
  studentWaitlist: {
    key: 'student',
    table: 'student',
    submissionType: 'waitlist',
    columns: { full_name:'full_name', email:'email', phone:'phone' },
    steps: [
      {
        fields: [
          { name:'full_name', label:'الاسم الكامل', type:'text', required:true, width:'half', autocomplete:'name' },
          { name:'email', label:'البريد الإلكتروني', type:'email', required:true, width:'half', autocomplete:'email', placeholder:'name@example.com' },
          { name:'phone', label:'رقم الجوال', type:'tel', required:true, width:'full', autocomplete:'tel', placeholder:'05XXXXXXXX' },
          { name:'consent', type:'consent', required:true, label:'أوافق على أن تراسلني Fluent عند فتح التسجيل القادم.' }
        ]
      }
    ],
    submitLabel: 'أضفني لقائمة الانتظار',
    doneTitle: 'تم تسجيلك في قائمة الانتظار.',
    doneText: 'أول ما نفتح الدورة القادمة، ستصلك رسالة قبل الإعلان العام.'
  },

  /* ==========================================================
     ب-٢) نموذج التنبيه — يُستخدم تلقائيًا حين تكون
        registration.student = 'closed'
        يظهر بعد ضغط زر «نبّهني عند فتح التسجيل».
        يُحفظ في نفس الجدول بـ submission_type = 'notification'.
     ========================================================== */
  studentNotify: {
    key: 'student',
    table: 'student',
    submissionType: 'notification',
    columns: { full_name:'full_name', email:'email', phone:'phone' },
    steps: [
      {
        fields: [
          { name:'full_name', label:'الاسم', type:'text', required:true, width:'half', autocomplete:'name' },
          { name:'email', label:'البريد الإلكتروني', type:'email', required:true, width:'half', autocomplete:'email', placeholder:'name@example.com' },
          { name:'phone', label:'رقم الجوال', type:'tel', required:true, width:'full', autocomplete:'tel', placeholder:'05XXXXXXXX' }
        ]
      }
    ],
    submitLabel: 'نبّهوني',
    doneTitle: 'سجّلنا تنبيهك.',
    doneText: 'أول ما يُفتح التسجيل على الدورة القادمة، تصلك رسالة على بريدك.'
  },

  /* ==========================================================
     ج) الشركات والجهات — شاركنا تحديًا
     ========================================================== */
  challenge: {
    key: 'challenge',
    table: 'challenge',
    submissionType: 'challenge',
    columns: { org_name:'org_name', contact_name:'contact_name', email:'email', phone:'phone' },

    steps: [
      {
        title: 'الجهة',
        hint:  'من أنتم وكيف نتواصل معكم',
        fields: [
          { draft:true, name:'org_name', label:'اسم الجهة', type:'text', required:true, width:'half', autocomplete:'organization', placeholder:'الاسم الرسمي للجهة' },
          { draft:true, name:'org_type', label:'نوع الجهة', type:'select', required:true, width:'half', options:[
              { value:'company',  label:'شركة' },
              { value:'startup',  label:'شركة ناشئة' },
              { value:'nonprofit',label:'جهة غير ربحية' },
              { value:'gov',      label:'جهة حكومية' },
              { value:'edu',      label:'جهة تعليمية' },
              { value:'other',    label:'أخرى' }
          ]},
          { draft:true, name:'contact_name', label:'اسم مسؤول التواصل', type:'text', required:true, width:'half', autocomplete:'name' },
          { draft:true, name:'job_title', label:'المسمى الوظيفي', type:'text', width:'half', autocomplete:'organization-title' },
          { draft:true, name:'email', label:'البريد الإلكتروني', type:'email', required:true, width:'half', autocomplete:'email', placeholder:'name@company.com' },
          { draft:true, name:'phone', label:'رقم التواصل', type:'tel', width:'half', autocomplete:'tel', placeholder:'05XXXXXXXX' },
          { draft:true, name:'website', label:'الموقع الإلكتروني', type:'url', width:'full', placeholder:'https://example.com' }
        ]
      },
      {
        title: 'التحدي',
        hint:  'المشكلة أو المشروع الذي يعمل عليه المشاركون',
        fields: [
          { draft:true, name:'challenge_title', label:'عنوان مختصر للتحدي', type:'text', required:true, maxlength:90, placeholder:'جملة واحدة تلخّص التحدي' },
          { draft:true, name:'challenge_desc', label:'اشرح التحدي', type:'textarea', required:true, minlength:60, maxlength:1200, help:'ما المشكلة؟ ولماذا هي مهمة لكم الآن؟', placeholder:'اكتب التحدي كما تشرحه لموظف جديد في فريقكم.' },
          { draft:true, name:'expected_output', label:'ما المخرج المفيد لكم؟', type:'textarea', maxlength:600, placeholder:'مثال: تصوّر مبدئي، دراسة، مقترح حل، نموذج أولي…' },

          /* اختياري — لا يُشترط وجود ملف تعريفي لإرسال التحدي */
          { draft:true, name:'org_profile', label:'الملف التعريفي للجهة', type:'file',
            maxSizeMB:10, bucket:'fluent-profiles', column:'profile_path',
            help:'اختياري · PDF فقط · الحد الأقصى 10 ميجابايت' },
          { draft:true, name:'confidential', type:'consent', label:'التحدي يتضمّن معلومات حسّاسة وأرغب في مناقشة حدود المشاركة قبل الاعتماد.' },
          { draft:true, name:'consent', type:'consent', required:true, label:'أوافق على مراجعة Fluent لهذا الطلب والتواصل معنا بشأنه.' }
        ]
      }
    ],
    submitLabel: 'أرسل التحدي',
    doneTitle: 'وصلنا تحديكم.',
    doneText: 'يراجع فريق Fluent التحدي ويعود لكم خلال أيام عمل قليلة لمناقشة إمكانية إدراجه ضمن محاكاة قادمة.'
  }
,

  /* ==========================================================
     ج-٢) تنبيه للجهات — يُستخدم إذا أُغلق مسار التحديات يومًا
     ========================================================== */
  challengeNotify: {
    key: 'challenge',
    table: 'challenge',
    submissionType: 'notification',
    columns: { org_name:'org_name', contact_name:'contact_name', email:'email', phone:'phone' },
    steps: [
      {
        fields: [
          { name:'contact_name', label:'الاسم', type:'text', required:true, width:'half', autocomplete:'name' },
          { name:'org_name', label:'اسم الجهة', type:'text', required:true, width:'half', autocomplete:'organization' },
          { name:'email', label:'البريد الإلكتروني', type:'email', required:true, width:'half', autocomplete:'email', placeholder:'name@company.com' },
          { name:'phone', label:'رقم الجوال', type:'tel', required:true, width:'half', autocomplete:'tel', placeholder:'05XXXXXXXX' }
        ]
      }
    ],
    submitLabel: 'نبّهوني',
    doneTitle: 'سجّلنا تنبيهكم.',
    doneText: 'سنراسلكم أول ما يُفتح استقبال التحديات مرة أخرى.'
  }
};
