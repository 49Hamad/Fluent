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
     ----------------------------------------------------------
     ✅ الأسئلة المعتمدة (المرحلة الثانية). مفاتيح الخيارات يجب
     أن تطابق app/Support/StudentApplicationForm.php — نفس القيم
     تُستخدم في التحقق على الخادم وفي عرض Filament.

     showIf  → الحقل يظهر (ويصبح مطلوبًا) فقط إذا تحقّق الشرط:
               { field:'x', equals:'yes' }  أو  { field:'x', includes:'other' }
     ========================================================== */
  student: {
    key: 'student',
    table: 'student',
    submissionType: 'application',
    submitLabel: 'إرسال الطلب',

    /* زر «ادخل مساحتك» مخفي مؤقتًا حتى يصبح دخول الطالب ومساحته حقيقيين.
       لإعادته: portalCta: { href:'/login', label:'ادخل مساحتك' }, */
    doneTitle: 'وصلنا طلبك.',
    doneText: 'أنشأنا لك مساحة في Fluent تتابع منها حالة طلبك. الدخول إليها بنفس بريدك في أي وقت.',

    steps: [
      {
        title: 'بياناتك',
        fields: [
          { name:'full_name', label:'الاسم الثلاثي', type:'text', required:true, width:'half', autocomplete:'name', maxlength:120 },
          { name:'email', label:'البريد الإلكتروني', type:'email', required:true, width:'half', autocomplete:'email', placeholder:'name@example.com', maxlength:190 },
          { name:'phone', label:'رقم الجوال', type:'tel', required:true, width:'half', autocomplete:'tel', placeholder:'05XXXXXXXX' },
          { name:'gender', label:'الجنس', type:'radio', required:true, inline:true, width:'half', options:[
              { value:'male',   label:'ذكر' },
              { value:'female', label:'أنثى' }
          ]},
          { name:'city', label:'المدينة', type:'text', required:true, width:'half', autocomplete:'address-level2', maxlength:100 },
          { name:'university', label:'الجامعة / الجهة التعليمية', type:'text', required:true, width:'half', maxlength:150 },
          { name:'major', label:'التخصص', type:'text', required:true, width:'half', maxlength:150 },
          { name:'study_status', label:'الحالة الدراسية', type:'radio', required:true, inline:true, width:'half', options:[
              { value:'student',  label:'طالب' },
              { value:'graduate', label:'خريج' }
          ]},
          { name:'graduation_year', label:'سنة التخرج المتوقعة أو سنة التخرج', type:'number', required:true, width:'half',
            min:1980, max:(new Date().getFullYear() + 8), placeholder:'مثال: 2026' }
        ]
      },
      {
        title: 'نبي نعرفك أكثر',
        fields: [
          { name:'motivation', label:'ليش تبي تدخل تجربة Fluent؟', type:'textarea', required:true, maxlength:1500,
            help:'ما نبحث عن إجابة مثالية، نبي نفهم وش تبي تطلع فيه من التجربة.' },
          { name:'gaps', label:'وش أكثر شيء تحس ينقصك قبل دخول بيئة العمل؟', type:'checkbox', required:true, options:[
              { value:'practical_experience',       label:'خبرة عملية' },
              { value:'teamwork',                   label:'العمل ضمن فريق' },
              { value:'professional_communication', label:'التواصل المهني' },
              { value:'real_tasks',                 label:'التعامل مع مهام حقيقية' },
              { value:'time_management',            label:'إدارة الوقت والالتزام' },
              { value:'workplace_confidence',       label:'الثقة في بيئة العمل' },
              { value:'portfolio',                  label:'بناء ملف أعمال' },
              { value:'other',                      label:'أخرى' }
          ]},
          { name:'gaps_other', label:'اكتب إجابتك', type:'text', required:true, maxlength:150,
            showIf:{ field:'gaps', includes:'other' } },
          { name:'has_experience', label:'هل سبق اشتغلت على مشروع حقيقي أو تجربة عملية؟', type:'radio', required:true, inline:true, options:[
              { value:'yes', label:'نعم' },
              { value:'no',  label:'لا' }
          ]},
          { name:'experience_details', label:'احكِ لنا عنها باختصار', type:'textarea', required:true, maxlength:1500,
            showIf:{ field:'has_experience', equals:'yes' } },
          { name:'team_scenario', label:'لو كنت ضمن فريق وعندكم تسليم قريب وأحد أعضاء الفريق ما أنجز الجزء المطلوب منه، وش بتسوي؟',
            type:'textarea', required:true, maxlength:1500 },
          { name:'weekly_commitment', label:'كم تقدر تلتزم أسبوعيًا بالتجربة؟', type:'radio', required:true, options:[
              { value:'lt5',   label:'أقل من 5 ساعات' },
              { value:'5_10',  label:'5–10 ساعات' },
              { value:'10_15', label:'10–15 ساعة' },
              { value:'gt15',  label:'أكثر من 15 ساعة' }
          ]}
        ]
      },
      {
        title: 'أخيرًا',
        fields: [
          { name:'linkedin_url', label:'رابط LinkedIn', type:'url', width:'half', placeholder:'https://linkedin.com/in/…' },
          { name:'portfolio_url', label:'رابط Portfolio / Behance / أعمال سابقة', type:'url', width:'half', placeholder:'https://' },
          { name:'cv', label:'السيرة الذاتية CV', type:'file', required:true, maxSizeMB:5,
            help:'PDF فقط · الحد الأقصى 5 ميجابايت' },
          { name:'consent', type:'consent', required:true,
            label:'أوافق على استخدام بياناتي المقدمة لأغراض دراسة الطلب والتواصل معي بشأن برامج وتجارب Fluent.' }
        ]
      }
    ]
  },

  /* ==========================================================
     ب) وضع «قائمة الانتظار» — نفس النموذج المعتمد بالكامل.
        يُستخدم تلقائيًا حين تكون حالة الدفعة في Filament «قائمة انتظار».
        الطلب يُحفظ بحالة «تم الاستلام» ويُعلَّم أنه وصل أثناء قائمة الانتظار.
        (لا يوجد نموذج «نبّهني عند فتح التسجيل» في هذه المرحلة.)
     ========================================================== */
  get studentWaitlist() {
    var s = Object.assign({}, this.student);
    s.doneTitle = 'وصلنا طلبك.';
    s.doneText = 'مقاعد هذه الدفعة شبه مكتملة، وطلبك الآن على قائمة الانتظار. سنراجعه ونراسلك على بريدك بأي تحديث.';
    return s;
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
