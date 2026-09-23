/* ============================================================
   FLUENT — تعريف حقول النماذج
   ------------------------------------------------------------
   👈 هذا هو الملف الوحيد الذي تُعدَّل فيه أسئلة النماذج.
   لا تحتاج لمس HTML ولا CSS ولا محرّك النموذج.

   ✅ أسئلة «سجّل في المحاكاة» و«شاركنا تحديًا» معتمدة ومربوطة بـ Laravel.
   أي تغيير في مفتاح (name / value) يجب أن يُعدَّل معه ملف الخادم
   المقابل (app/Support/StudentApplicationForm.php أو BusinessChallengeForm.php).

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

    /* بعد الإرسال: زر «ادخل مساحتك» (دخول الطالب ومساحته حقيقيان منذ المرحلة الثانية) */
    portalCta: { href:'/login', label:'ادخل مساحتك' },
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
     ----------------------------------------------------------
     ✅ الأسئلة المعتمدة (المرحلة الثانية). مفاتيح الخيارات يجب
     أن تطابق app/Support/BusinessChallengeForm.php — نفس القيم
     تُستخدم في التحقق على الخادم وفي عرض Filament.
     عن قصد: لا يوجد سؤال «ما الحل الذي تريده؟» ولا «عنوان للتحدي».
     ========================================================== */
  challenge: {
    key: 'challenge',
    table: 'challenge',
    submissionType: 'challenge',

    steps: [
      {
        title: 'عن الجهة',
        hint:  'من أنتم وكيف نتواصل معكم',
        fields: [
          { name:'org_name', label:'اسم الجهة', type:'text', required:true, width:'half', autocomplete:'organization', maxlength:150 },
          { name:'org_type', label:'نوع الجهة', type:'select', required:true, width:'half', options:[
              { value:'company',    label:'شركة' },
              { value:'government', label:'جهة حكومية' },
              { value:'nonprofit',  label:'جهة غير ربحية' },
              { value:'startup',    label:'مشروع ناشئ' },
              { value:'other',      label:'أخرى' }
          ]},
          { name:'org_type_other', label:'وضّح نوع الجهة', type:'text', required:true, width:'full', maxlength:150,
            showIf:{ field:'org_type', equals:'other' } },
          { name:'sector', label:'القطاع', type:'text', required:true, width:'half', maxlength:150 },
          { name:'contact_name', label:'اسم مسؤول التواصل', type:'text', required:true, width:'half', autocomplete:'name', maxlength:120 },
          { name:'job_title', label:'المسمى الوظيفي', type:'text', width:'half', autocomplete:'organization-title', maxlength:120 },
          { name:'email', label:'البريد الإلكتروني', type:'email', required:true, width:'half', autocomplete:'email', placeholder:'name@company.com', maxlength:190 },
          { name:'phone', label:'رقم الجوال', type:'tel', required:true, width:'half', autocomplete:'tel', placeholder:'05XXXXXXXX' }
        ]
      },
      {
        title: 'عن التحدي',
        hint:  'المشكلة كما هي — الحلول يعمل عليها المشاركون',
        fields: [
          { name:'challenge_description', label:'وش التحدي اللي تواجهه الجهة؟', type:'textarea', required:true, maxlength:3000,
            help:'صف لنا المشكلة كما هي، ما نحتاج منك تقترح الحل.' },
          { name:'affected_parties', label:'مين يتأثر بهذا التحدي؟', type:'textarea', required:true, maxlength:1500,
            help:'مثال: العملاء، الموظفون، المستفيدون، أو فريق معين داخل الجهة.' },
          { name:'current_impact', label:'وش الأثر الحالي للمشكلة على الجهة؟', type:'textarea', required:true, maxlength:1500 },
          { name:'expected_outputs', label:'وش تتوقعون من المشاركين في نهاية المحاكاة؟', type:'checkbox', required:true, options:[
              { value:'ideas',           label:'أفكار وحلول' },
              { value:'research',        label:'بحث وتحليل' },
              { value:'prototype',       label:'نموذج أولي' },
              { value:'experience',      label:'تحسين تجربة أو رحلة' },
              { value:'recommendations', label:'توصيات عملية' },
              { value:'other',           label:'أخرى' }
          ]},
          { name:'expected_outputs_other', label:'اكتب إجابتك', type:'text', required:true, maxlength:150,
            showIf:{ field:'expected_outputs', includes:'other' } },
          { name:'can_share_materials', label:'هل تستطيعون مشاركة معلومات أو مواد تساعد المشاركين على فهم التحدي؟', type:'radio', required:true, inline:true, options:[
              { value:'yes', label:'نعم' },
              { value:'no',  label:'لا' }
          ]},
          { name:'has_confidential_info', label:'هل توجد معلومات سرية أو قيود لازم نعرفها قبل عرض التحدي على المشاركين؟', type:'radio', required:true, inline:true, options:[
              { value:'yes', label:'نعم' },
              { value:'no',  label:'لا' }
          ]},
          { name:'confidential_details', label:'وضح لنا المعلومات السرية أو القيود التي يجب مراعاتها', type:'textarea', required:true, maxlength:2000,
            showIf:{ field:'has_confidential_info', equals:'yes' } },
          { name:'challenge_file', label:'ملف عن التحدي أو الجهة', type:'file', maxSizeMB:5,
            help:'اختياري · PDF فقط · الحد الأقصى 5 ميجابايت' },
          { name:'consent', type:'consent', required:true,
            label:'أوافق على استخدام المعلومات المقدمة لغرض دراسة التحدي وتقييم ملاءمته للمحاكاة المهنية والتواصل معي بشأنه.' }
        ]
      }
    ],
    submitLabel: 'إرسال التحدي',
    doneTitle: 'وصلنا تحديكم.',
    doneText: 'يراجع فريق Fluent التحدي ويعود لكم خلال أيام عمل قليلة لمناقشة إمكانية إدراجه ضمن محاكاة قادمة.'
  },

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
