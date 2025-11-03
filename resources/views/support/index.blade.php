<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدعم والمساعدة - Sarea</title>

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

        .support-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
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
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        .logo-container {
            position: relative;
            z-index: 2;
            margin-bottom: 2rem;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        .logo-container img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.3);
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
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
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
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
            font-size: 2rem;
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

        .support-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border: none;
            border-radius: 20px;
            padding: 2.5rem;
            margin: 1.5rem 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .support-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(79, 172, 254, 0.1), transparent);
            transition: left 0.5s;
        }

        .support-card:hover::before {
            left: 100%;
        }

        .support-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(79, 172, 254, 0.2);
        }

        .support-icon {
            font-size: 4rem;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .support-card:hover .support-icon {
            transform: scale(1.2) rotate(10deg);
        }

        .support-card h4 {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }

        .contact-method {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin: 1.5rem 0;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .contact-method::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transition: all 0.5s ease;
            transform: translate(-50%, -50%);
        }

        .contact-method:hover::before {
            width: 300px;
            height: 300px;
        }

        .contact-method:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .contact-method i {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 2;
        }

        .contact-method h5 {
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 2;
        }

        .contact-method p {
            font-size: 1.1rem;
            position: relative;
            z-index: 2;
        }

        .faq-item {
            background: white;
            border: none;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .faq-question {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            cursor: pointer;
            border-bottom: 1px solid #dee2e6;
            transition: all 0.3s ease;
            position: relative;
        }

        .faq-question::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .faq-question:hover::before {
            transform: scaleY(1);
        }

        .faq-question:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            padding-right: 2.5rem;
        }

        .faq-question h5 {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.2rem;
        }

        .faq-question i {
            transition: transform 0.3s ease;
            color: #4facfe;
        }

        .faq-answer {
            padding: 2rem;
            display: none;
            background: #fff;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .faq-answer.show {
            display: block;
        }

        .highlight-box {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin: 2rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .highlight-box::before {
            content: '✨';
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 2rem;
            animation: sparkle 2s ease-in-out infinite;
        }

        @keyframes sparkle {

            0%,
            100% {
                transform: scale(1) rotate(0deg);
                opacity: 0.7;
            }

            50% {
                transform: scale(1.2) rotate(180deg);
                opacity: 1;
            }
        }

        .emergency-contact {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 3rem;
            border-radius: 20px;
            margin: 2rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .emergency-contact::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-conic-gradient(from 0deg, transparent 0deg, rgba(255, 255, 255, 0.1) 1deg, transparent 2deg);
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .emergency-contact i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 2;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .emergency-contact h3,
        .emergency-contact h2,
        .emergency-contact p {
            position: relative;
            z-index: 2;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .support-container {
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

            .support-card {
                padding: 2rem;
            }

            .contact-method {
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
    </style>
</head>

<body>
    <div class="container">
        <div class="support-container">
            <div class="header-section">
                <div class="logo-container">
                    <img src="{{ asset('public/logo/Layer_2_Image.png') }}" alt="Sarea Logo" class="img-fluid">
                </div>
                <h1 class="display-4 fw-bold mb-3">الدعم والمساعدة</h1>
                <p class="lead">نحن هنا لمساعدتك في أي وقت - فريق دعم Sarea جاهز لخدمتك</p>
            </div>

            <div class="content-section">
                <div class="section-content animate-on-scroll">
                    <h2 class="section-title">كيف يمكننا مساعدتك؟</h2>
                    <p>في Sarea، نسعى لتقديم أفضل تجربة لمستخدمينا. إذا كان لديك أي استفسار أو تحتاج للمساعدة، فريقنا
                        متاح لخدمتك على مدار الساعة.</p>
                </div>

                <div class="row animate-on-scroll">
                    <div class="col-md-4">
                        <div class="support-card text-center">
                            <i class="fas fa-headset support-icon"></i>
                            <h4>الدعم الفني</h4>
                            <p>مساعدة في استخدام التطبيق وحل المشاكل التقنية</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="support-card text-center">
                            <i class="fas fa-credit-card support-icon"></i>
                            <h4>المدفوعات والفواتير</h4>
                            <p>استفسارات حول الدفع والفواتير واسترداد الأموال</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="support-card text-center">
                            <i class="fas fa-car support-icon"></i>
                            <h4>الرحلات</h4>
                            <p>مساعدة في حجز الرحلات ومتابعة حالة الطلبات</p>
                        </div>
                    </div>
                </div>

                <div class="section-content animate-on-scroll">
                    <h2 class="section-title">طرق التواصل معنا</h2>
                    <p class="text-center mb-4">اختر الطريقة الأنسب لك للتواصل مع فريق الدعم</p>

                    <div class="row justify-content-center">
                        <div class="col-lg-6 col-md-8">
                            <div class="contact-method">
                                <i class="fas fa-envelope"></i>
                                <h5>البريد الإلكتروني</h5>
                                <p class="mb-2 fw-bold">Aom.sst25@gmail.com</p>
                                <small>نرد خلال 24 ساعة</small>
                                <div class="mt-3">
                                    <a href="mailto:Aom.sst25@gmail.com" class="btn btn-light btn-sm"
                                        style="border-radius: 20px;">
                                        <i class="fas fa-paper-plane me-2"></i>إرسال رسالة
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center mt-3">
                        <div class="col-lg-6 col-md-8">
                            <div class="contact-method"
                                style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                <i class="fas fa-comments"></i>
                                <h5>الدردشة المباشرة</h5>
                                <p class="mb-2">داخل التطبيق</p>
                                <small>متاح 24/7 - استجابة فورية</small>
                                <div class="mt-3">
                                    <span class="badge bg-light text-dark"
                                        style="border-radius: 15px; padding: 8px 15px;">
                                        <i class="fas fa-circle text-success me-2" style="font-size: 0.8rem;"></i>متصل
                                        الآن
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="emergency-contact animate-on-scroll">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>حالات الطوارئ</h3>
                    <p>في حالة الطوارئ أثناء الرحلة، اتصل فوراً على:</p>
                    <h2>911</h2>
                    <p>أو تواصل معنا على الخط الساخن: <strong>+962-6-EMERGENCY</strong></p>
                </div>

                <div class="section-content animate-on-scroll">
                    <h2 class="section-title">الأسئلة الشائعة</h2>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(1)">
                            <h5 class="mb-0">كيف يمكنني حجز رحلة؟ <i class="fas fa-chevron-down float-end"></i></h5>
                        </div>
                        <div class="faq-answer" id="faq-1">
                            <p>يمكنك حجز رحلة بسهولة من خلال التطبيق:</p>
                            <ol>
                                <li>افتح تطبيق Sarea</li>
                                <li>حدد موقع الانطلاق والوجهة</li>
                                <li>اختر نوع السيارة المناسب</li>
                                <li>اضغط على "احجز الآن"</li>
                                <li>انتظر تأكيد السائق</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(2)">
                            <h5 class="mb-0">كيف يتم احتساب تكلفة الرحلة؟ <i
                                    class="fas fa-chevron-down float-end"></i></h5>
                        </div>
                        <div class="faq-answer" id="faq-2">
                            <p>تحتسب تكلفة الرحلة بناءً على:</p>
                            <ul>
                                <li>المسافة المقطوعة</li>
                                <li>الوقت المستغرق</li>
                                <li>نوع السيارة المختارة</li>
                                <li>الطلب في المنطقة (في أوقات الذروة)</li>
                                <li>أي رسوم إضافية (مثل رسوم المطار)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(3)">
                            <h5 class="mb-0">ماذا لو تأخر السائق؟ <i class="fas fa-chevron-down float-end"></i></h5>
                        </div>
                        <div class="faq-answer" id="faq-3">
                            <p>إذا تأخر السائق عن الوقت المحدد:</p>
                            <ul>
                                <li>يمكنك التواصل معه مباشرة عبر التطبيق</li>
                                <li>إذا تأخر أكثر من 10 دقائق، يمكنك إلغاء الرحلة بدون رسوم</li>
                                <li>ستحصل على تعويض إذا كان التأخير بسبب السائق</li>
                                <li>يمكنك تقييم السائق بعد الرحلة</li>
                            </ul>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(4)">
                            <h5 class="mb-0">كيف يمكنني استرداد الأموال؟ <i
                                    class="fas fa-chevron-down float-end"></i></h5>
                        </div>
                        <div class="faq-answer" id="faq-4">
                            <p>لاسترداد الأموال:</p>
                            <ol>
                                <li>اذهب إلى قسم "تاريخ الرحلات" في التطبيق</li>
                                <li>اختر الرحلة التي تريد استرداد أموالها</li>
                                <li>اضغط على "طلب استرداد"</li>
                                <li>اختر سبب الاسترداد</li>
                                <li>سيتم مراجعة طلبك خلال 3-5 أيام عمل</li>
                            </ol>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question" onclick="toggleFaq(5)">
                            <h5 class="mb-0">هل يمكنني تغيير وجهتي أثناء الرحلة؟ <i
                                    class="fas fa-chevron-down float-end"></i></h5>
                        </div>
                        <div class="faq-answer" id="faq-5">
                            <p>نعم، يمكنك تغيير الوجهة أثناء الرحلة:</p>
                            <ul>
                                <li>أخبر السائق بالوجهة الجديدة</li>
                                <li>سيتم تحديث التكلفة تلقائياً</li>
                                <li>يمكنك إضافة توقفات إضافية</li>
                                <li>قد تطبق رسوم إضافية حسب المسافة الجديدة</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="highlight-box animate-on-scroll">
                    <h4>لم تجد إجابة لسؤالك؟</h4>
                    <p class="mb-0">لا تتردد في التواصل معنا مباشرة عبر أي من طرق التواصل المتاحة. فريقنا جاهز
                        لمساعدتك!</p>
                </div>

                <div class="section-content animate-on-scroll">
                    <h2 class="section-title">معلومات إضافية</h2>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="support-card text-center">
                                <i class="fas fa-clock support-icon"></i>
                                <h5>ساعات العمل</h5>
                                <p class="mb-0">
                                    <strong>الأحد - الخميس:</strong> 9:00 ص - 5:00 م<br>
                                    <strong>الجمعة - السبت:</strong> 10:00 ص - 3:00 م
                                </p>
                                <div class="mt-3">
                                    <span class="badge bg-success" style="border-radius: 15px; padding: 8px 15px;">
                                    </span>
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
        // FAQ Toggle Function
        function toggleFaq(id) {
            const answer = document.getElementById('faq-' + id);
            const icon = document.querySelector(`[onclick="toggleFaq(${id})"] i`);

            if (answer.classList.contains('show')) {
                answer.classList.remove('show');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                // Close all other FAQs
                document.querySelectorAll('.faq-answer').forEach(item => {
                    item.classList.remove('show');
                });
                document.querySelectorAll('.faq-question i').forEach(item => {
                    item.classList.remove('fa-chevron-up');
                    item.classList.add('fa-chevron-down');
                });

                // Open clicked FAQ
                answer.classList.add('show');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }

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
        });

        // Add hover effects to support cards
        document.addEventListener('DOMContentLoaded', function() {
            const supportCards = document.querySelectorAll('.support-card');

            supportCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-10px) scale(1.02)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>

</html>
