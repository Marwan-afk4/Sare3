<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سياسة الخصوصية - Sarea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            line-height: 1.8;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .privacy-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 2rem auto;
            max-width: 900px;
            overflow: hidden;
        }

        .header-section {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
        }

        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .logo-container {
            position: relative;
            z-index: 2;
            margin-bottom: 1rem;
        }

        .logo-container img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.3);
            padding: 10px;
            background: rgba(255,255,255,0.1);
        }

        .content-section {
            padding: 3rem 2rem;
        }

        .section-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #4facfe;
            display: inline-block;
        }

        .section-content {
            margin-bottom: 2.5rem;
        }

        .highlight-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin: 1.5rem 0;
        }

        .info-card {
            background: #f8f9fa;
            border-left: 4px solid #4facfe;
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: 0 10px 10px 0;
        }

        .contact-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            margin-top: 2rem;
            border-radius: 15px;
        }

        ul {
            padding-right: 1.5rem;
        }

        li {
            margin-bottom: 0.5rem;
        }


    </style>
</head>
<body>
    <div class="container">
        <div class="privacy-container">
            <div class="header-section">
                <div class="logo-container">
                    <img src="{{ asset('public/logo/Layer_2_Image.png') }}" alt="Sarea Logo" class="img-fluid">
                </div>
                <h1 class="display-4 fw-bold mb-3">سياسة الخصوصية</h1>
                <p class="lead">نحن في Sarea نقدر خصوصيتك ونلتزم بحماية بياناتك الشخصية</p>
            </div>

            <div class="content-section">
                <div class="section-content">
                    <h2 class="section-title">1. المقدمة</h2>
                    <p>في Sarea، نقدر خصوصيتك ونلتزم بحماية بياناتك الشخصية. تشرح هذه السياسة كيفية جمعنا واستخدامنا وحماية معلوماتك عند استخدام تطبيقنا وخدماتنا.</p>
                </div>

                <div class="section-content">
                    <h2 class="section-title">2. المعلومات التي نجمعها</h2>
                    <div class="info-card">
                        <ul>
                            <li><strong>معلومات الحساب:</strong> الاسم، رقم الهاتف، عنوان البريد الإلكتروني</li>
                            <li><strong>معلومات الموقع:</strong> الموقع الحالي ووجهات الرحلة</li>
                            <li><strong>معلومات الجهاز:</strong> نوع الجهاز، نظام التشغيل، معرفات التطبيق</li>
                            <li><strong>معلومات الاستخدام:</strong> تفاصيل الرحلات، التفضيلات، تاريخ الاستخدام</li>
                            <li><strong>معلومات الدفع:</strong> تفاصيل بطاقة الائتمان والمحافظ الرقمية</li>
                        </ul>
                    </div>
                </div>

                <div class="section-content">
                    <h2 class="section-title">3. كيفية استخدام بياناتك</h2>
                    <ul>
                        <li>تقديم خدمات مشاركة الرحلات</li>
                        <li>ربط الركاب بالسائقين المناسبين</li>
                        <li>معالجة المدفوعات والفواتير</li>
                        <li>تحسين جودة الخدمة ووظائف التطبيق</li>
                        <li>إرسال تحديثات مهمة للحساب</li>
                        <li>الامتثال للمتطلبات القانونية والتنظيمية</li>
                        <li>منع الاحتيال وضمان الأمان</li>
                    </ul>
                </div>

                <div class="section-content">
                    <h2 class="section-title">4. مشاركة البيانات</h2>
                    <p>نشارك بياناتك فقط في الحالات التالية:</p>
                    <div class="highlight-box">
                        <ul class="mb-0">
                            <li><strong>مع السائقين:</strong> معلومات الاتصال والموقع اللازمة للرحلات</li>
                            <li><strong>مقدمي الخدمات:</strong> شركات الدفع والتحليلات والدعم التقني</li>
                            <li><strong>المتطلبات القانونية:</strong> عند طلبها من السلطات المختصة</li>
                            <li><strong>نقل الأعمال:</strong> في حالة بيع الشركة أو الاندماج</li>
                        </ul>
                    </div>
                    <p class="mt-3"><strong>نحن لا نبيع بياناتك الشخصية لأطراف ثالثة.</strong></p>
                </div>

                <div class="section-content">
                    <h2 class="section-title">5. تخزين البيانات والأمان</h2>
                    <p>نستخدم تدابير أمنية متقدمة لحماية بياناتك:</p>
                    <div class="info-card">
                        <ul>
                            <li>تشفير البيانات أثناء النقل والتخزين</li>
                            <li>خوادم آمنة في مراكز بيانات معتمدة</li>
                            <li>وصول محدود للموظفين المخولين فقط</li>
                            <li>عمليات تدقيق أمنية منتظمة وتحديثات الحماية</li>
                            <li>الامتثال للمعايير الأمنية الدولية</li>
                        </ul>
                    </div>
                </div>

                <div class="section-content">
                    <h2 class="section-title">6. حقوق البيانات الخاصة بك</h2>
                    <p>لديك الحق في:</p>
                    <ul>
                        <li>الوصول إلى بياناتك الشخصية</li>
                        <li>تصحيح المعلومات غير الدقيقة</li>
                        <li>حذف بياناتك (الحق في النسيان)</li>
                        <li>تقييد معالجة البيانات</li>
                        <li>نقل البيانات إلى خدمة أخرى</li>
                        <li>الاعتراض على معالجة معينة</li>
                        <li>سحب الموافقة في أي وقت</li>
                    </ul>
                </div>

                <div class="section-content">
                    <h2 class="section-title">7. الاحتفاظ بالبيانات</h2>
                    <p>نحتفظ ببياناتك للفترة اللازمة لتقديم خدماتنا والامتثال للقوانين:</p>
                    <div class="info-card">
                        <ul>
                            <li><strong>بيانات الحساب:</strong> خلال فترة الحساب النشط + 3 سنوات</li>
                            <li><strong>بيانات الرحلة:</strong> 7 سنوات لأغراض المحاسبة والقانونية</li>
                            <li><strong>بيانات الدفع:</strong> وفقاً لمتطلبات البنك وشركة البطاقة</li>
                            <li><strong>سجلات الأمان:</strong> 5 سنوات لأغراض الحماية والتحقيق</li>
                        </ul>
                    </div>
                </div>

                <div class="section-content">
                    <h2 class="section-title">8. ملفات تعريف الارتباط والتتبع</h2>
                    <p>نستخدم ملفات تعريف الارتباط وتقنيات التتبع من أجل:</p>
                    <ul>
                        <li>حفظ تفضيلات المستخدم</li>
                        <li>تحليل استخدام التطبيق</li>
                        <li>تحسين الأداء والتجربة</li>
                        <li>عرض محتوى مخصص</li>
                    </ul>
                    <p>يمكنك إدارة إعدادات ملفات تعريف الارتباط من خلال إعدادات جهازك.</p>
                </div>

                <div class="section-content">
                    <h2 class="section-title">9. النقل الدولي للبيانات</h2>
                    <p>قد تتم معالجة بياناتك في الأردن أو دول أخرى لأغراض تشغيلية. نضمن مستوى حماية مناسب للبيانات المنقولة خارج الأردن من خلال:</p>
                    <ul>
                        <li>اتفاقيات حماية البيانات المعيارية</li>
                        <li>شهادات الأمان الدولية</li>
                        <li>الامتثال للقوانين المحلية والدولية</li>
                    </ul>
                </div>

                <div class="section-content">
                    <h2 class="section-title">10. خصوصية الأطفال</h2>
                    <div class="highlight-box">
                        <p class="mb-0">تطبيق Sarea مخصص للأشخاص الذين تبلغ أعمارهم 18 عاماً فما فوق. نحن لا نجمع عن قصد معلومات شخصية من الأطفال دون سن 18 عاماً. إذا علمنا بجمع مثل هذه المعلومات، فسنقوم بحذفها فوراً.</p>
                    </div>
                </div>

                <div class="section-content">
                    <h2 class="section-title">11. تحديثات السياسة</h2>
                    <p>قد نقوم بتحديث هذه السياسة من وقت لآخر لتعكس التغييرات في خدماتنا أو القوانين. سنخطرك بأي تغييرات جوهرية عبر التطبيق أو البريد الإلكتروني قبل 30 يوماً من دخولها حيز التنفيذ.</p>
                </div>

                <div class="contact-section">
                    <h2 class="section-title text-white border-white">12. اتصل بنا</h2>
                    <p>للاستفسارات حول خصوصيتك أو هذه السياسة:</p>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>مسؤول حماية البيانات</strong></p>
                            <p>📧 البريد الإلكتروني: Aom.sst25@gmail.com</p>
                        </div>
                        <div class="col-md-6">
                            <p>🕒 ساعات العمل: الأحد - الخميس، 9:00 ص - 5:00 م</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
