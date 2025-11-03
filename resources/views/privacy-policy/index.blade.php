<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سياسة الخصوصية - Sarea</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('public/logo/Layer_2_Image.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('public/logo/Layer_2_Image.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('public/logo/Layer_2_Image.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            line-height: 1.8;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated background particles */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="particles" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1" fill="white" opacity="0.1"><animate attributeName="opacity" values="0.1;0.3;0.1" dur="3s" repeatCount="indefinite"/></circle><circle cx="18" cy="18" r="0.5" fill="white" opacity="0.1"><animate attributeName="opacity" values="0.1;0.2;0.1" dur="4s" repeatCount="indefinite"/></circle></pattern></defs><rect width="100" height="100" fill="url(%23particles)"/></svg>');
            pointer-events: none;
            z-index: 0;
        }

        .privacy-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            margin: 2rem auto;
            max-width: 1000px;
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-section {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .logo-container {
            position: relative;
            z-index: 2;
            margin-bottom: 2rem;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .logo-container img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 5px solid rgba(255,255,255,0.3);
            padding: 15px;
            background: rgba(255,255,255,0.1);
            transition: transform 0.3s ease;
        }

        .logo-container img:hover {
            transform: scale(1.1) rotate(5deg);
        }

        .header-section h1 {
            position: relative;
            z-index: 2;
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header-section .lead {
            position: relative;
            z-index: 2;
            font-size: 1.3rem;
            opacity: 0.9;
        }

        .content-section {
            padding: 4rem 3rem;
        }

        .section-title {
            color: #2c3e50;
            font-weight: 800;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 4px solid #4facfe;
            display: inline-block;
            position: relative;
            font-size: 1.8rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 50%;
            height: 4px;
            background: linear-gradient(90deg, #f093fb, #f5576c);
            border-radius: 2px;
        }

        .section-content {
            margin-bottom: 3rem;
        }

        .section-content p {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 1.5rem;
        }

        .highlight-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin: 2rem 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(240, 147, 251, 0.3);
        }

        .highlight-box::before {
            content: '🔒';
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 2rem;
            animation: sparkle 2s ease-in-out infinite;
        }

        @keyframes sparkle {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.7; }
            50% { transform: scale(1.2) rotate(180deg); opacity: 1; }
        }

        .info-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border: none;
            border-left: 5px solid #4facfe;
            padding: 2.5rem;
            margin: 2rem 0;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(79, 172, 254, 0.1), transparent);
            transition: left 0.5s;
        }

        .info-card:hover::before {
            left: 100%;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(79, 172, 254, 0.2);
        }

        .contact-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            margin-top: 2rem;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .contact-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-conic-gradient(from 0deg, transparent 0deg, rgba(255,255,255,0.1) 1deg, transparent 2deg);
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .contact-section h2,
        .contact-section p,
        .contact-section .row {
            position: relative;
            z-index: 2;
        }

        .contact-section .section-title {
            color: white;
            border-bottom-color: rgba(255,255,255,0.3);
        }

        .contact-section .section-title::after {
            background: linear-gradient(90deg, rgba(255,255,255,0.5), rgba(255,255,255,0.3));
        }

        ul {
            padding-right: 1.5rem;
        }

        li {
            margin-bottom: 0.8rem;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            padding: 0.3rem 0;
        }

        li:hover {
            transform: translateX(-5px);
            color: #4facfe;
        }

        li strong {
            color: #2c3e50;
        }

        /* Privacy specific icons */
        .privacy-icon {
            font-size: 2.5rem;
            color: #4facfe;
            margin-bottom: 1rem;
            display: block;
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .privacy-container {
                margin: 1rem;
                border-radius: 20px;
            }

            .header-section {
                padding: 3rem 1.5rem;
            }

            .header-section h1 {
                font-size: 2.5rem;
            }

            .content-section {
                padding: 2rem 1.5rem;
            }

            .info-card, .highlight-box {
                padding: 2rem;
            }
        }

        /* Scroll animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }

        /* Enhanced list styling */
        .info-card ul li::before {
            content: '✓';
            color: #28a745;
            font-weight: bold;
            margin-left: 10px;
        }

        .section-content ul li::before {
            content: '•';
            color: #4facfe;
            font-weight: bold;
            margin-left: 10px;
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
                <div class="section-content animate-on-scroll">
                    <i class="fas fa-shield-alt privacy-icon"></i>
                    <h2 class="section-title">1. المقدمة</h2>
                    <p>في Sarea، نقدر خصوصيتك ونلتزم بحماية بياناتك الشخصية. تشرح هذه السياسة كيفية جمعنا واستخدامنا وحماية معلوماتك عند استخدام تطبيقنا وخدماتنا.</p>
                </div>

                <div class="section-content animate-on-scroll">
                    <i class="fas fa-database privacy-icon"></i>
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

                <div class="section-content animate-on-scroll">
                    <i class="fas fa-cogs privacy-icon"></i>
                    <h2 class="section-title">3. كيفية استخدام بياناتك</h2>
                    <div class="info-card">
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
                </div>

                <div class="section-content animate-on-scroll">
                    <i class="fas fa-share-alt privacy-icon"></i>
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

                <div class="section-content animate-on-scroll">
                    <i class="fas fa-lock privacy-icon"></i>
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

                <div class="section-content animate-on-scroll">
                    <i class="fas fa-user-shield privacy-icon"></i>
                    <h2 class="section-title">6. حقوق البيانات الخاصة بك</h2>
                    <p>لديك الحق في:</p>
                    <div class="info-card">
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
                </div>

                <div class="section-content animate-on-scroll">
                    <i class="fas fa-calendar-alt privacy-icon"></i>
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

                <div class="section-content animate-on-scroll">
                    <i class="fas fa-cookie-bite privacy-icon"></i>
                    <h2 class="section-title">8. ملفات تعريف الارتباط والتتبع</h2>
                    <p>نستخدم ملفات تعريف الارتباط وتقنيات التتبع من أجل:</p>
                    <div class="info-card">
                        <ul>
                            <li>حفظ تفضيلات المستخدم</li>
                            <li>تحليل استخدام التطبيق</li>
                            <li>تحسين الأداء والتجربة</li>
                            <li>عرض محتوى مخصص</li>
                        </ul>
                    </div>
                    <p>يمكنك إدارة إعدادات ملفات تعريف الارتباط من خلال إعدادات جهازك.</p>
                </div>

                <div class="section-content animate-on-scroll">
                    <i class="fas fa-globe privacy-icon"></i>
                    <h2 class="section-title">9. النقل الدولي للبيانات</h2>
                    <p>قد تتم معالجة بياناتك في الأردن أو دول أخرى لأغراض تشغيلية. نضمن مستوى حماية مناسب للبيانات المنقولة خارج الأردن من خلال:</p>
                    <div class="info-card">
                        <ul>
                            <li>اتفاقيات حماية البيانات المعيارية</li>
                            <li>شهادات الأمان الدولية</li>
                            <li>الامتثال للقوانين المحلية والدولية</li>
                        </ul>
                    </div>
                </div>

                <div class="section-content animate-on-scroll">
                    <i class="fas fa-child privacy-icon"></i>
                    <h2 class="section-title">10. خصوصية الأطفال</h2>
                    <div class="highlight-box">
                        <p class="mb-0">تطبيق Sarea مخصص للأشخاص الذين تبلغ أعمارهم 18 عاماً فما فوق. نحن لا نجمع عن قصد معلومات شخصية من الأطفال دون سن 18 عاماً. إذا علمنا بجمع مثل هذه المعلومات، فسنقوم بحذفها فوراً.</p>
                    </div>
                </div>

                <div class="section-content animate-on-scroll">
                    <i class="fas fa-sync-alt privacy-icon"></i>
                    <h2 class="section-title">11. تحديثات السياسة</h2>
                    <p>قد نقوم بتحديث هذه السياسة من وقت لآخر لتعكس التغييرات في خدماتنا أو القوانين. سنخطرك بأي تغييرات جوهرية عبر التطبيق أو البريد الإلكتروني قبل 30 يوماً من دخولها حيز التنفيذ.</p>
                </div>

                <div class="contact-section animate-on-scroll">
                    <i class="fas fa-envelope privacy-icon" style="color: white; font-size: 3rem;"></i>
                    <h2 class="section-title">12. اتصل بنا</h2>
                    <p class="text-center mb-4">للاستفسارات حول خصوصيتك أو هذه السياسة</p>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="text-center">
                                <h5><strong>مسؤول حماية البيانات</strong></h5>
                                <p class="mb-3">
                                    <i class="fas fa-envelope me-2"></i>
                                    <strong>Aom.sst25@gmail.com</strong>
                                </p>
                                <p class="mb-3">
                                    <i class="fas fa-clock me-2"></i>
                                    <strong>ساعات العمل:</strong> الأحد - الخميس، 9:00 ص - 5:00 م
                                </p>
                                <div class="mt-4">
                                    <a href="mailto:Aom.sst25@gmail.com" class="btn btn-light btn-lg" style="border-radius: 25px; padding: 12px 30px;">
                                        <i class="fas fa-paper-plane me-2"></i>تواصل معنا الآن
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Scroll Animation
        function animateOnScroll() {
            const elements = document.querySelectorAll('.animate-on-scroll');

            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;

                if (elementTop < window.innerHeight - elementVisible) {
                    element.classList.add('animated');
                }
            });
        }

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            // Initial check
            animateOnScroll();

            // Check on scroll
            window.addEventListener('scroll', animateOnScroll);

            // Add stagger delay to elements
            const animatedElements = document.querySelectorAll('.animate-on-scroll');
            animatedElements.forEach((element, index) => {
                element.style.transitionDelay = `${index * 0.1}s`;
            });

            // Add hover effects to info cards
            const infoCards = document.querySelectorAll('.info-card');
            infoCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Add floating animation to privacy icons
            const privacyIcons = document.querySelectorAll('.privacy-icon');
            privacyIcons.forEach((icon, index) => {
                icon.style.animationDelay = `${index * 0.2}s`;
                icon.style.animation = 'bounce 2s ease-in-out infinite';
            });
        });

        // Add smooth scrolling for better UX
        document.documentElement.style.scrollBehavior = 'smooth';

        // Add parallax effect to header
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const header = document.querySelector('.header-section');
            if (header) {
                header.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
        });
    </script>
</body>
</html>
