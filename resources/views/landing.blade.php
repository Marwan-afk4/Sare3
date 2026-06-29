<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سريع | Sare3 - منصة النقل الذكي</title>
    
    <!-- Meta Tags for SEO -->
    <meta name="description" content="سريع (Sare3) هي منصتك الأمثل للرحلات السريعة والآمنة. اطلب رحلتك الآن أو انضم ككابتن معنا لتحقيق أرباح ممتازة.">
    <meta name="keywords" content="سريع, توصيل, تاكسي, اوبر, رحلات, كابتن, sare3, ride-hailing, taxi">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo/Layer_2_Image.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo/Layer_2_Image.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('logo/Layer_2_Image.png') }}">

    <!-- Google Fonts: Tajawal for Arabic, Outfit for English -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            500: '#10b981', // Emerald primary
                            600: '#059669',
                            700: '#047857',
                            900: '#064e3b',
                        },
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            900: '#070a13',
                            800: '#0f1423',
                            700: '#192137',
                            600: '#25304f',
                        }
                    },
                    fontFamily: {
                        sans: ['Tajawal', 'Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Tajawal', 'Outfit', sans-serif;
            background-color: #070a13;
        }
        
        /* Ambient Light Backgrounds */
        .ambient-glow-1 {
            background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, rgba(7, 10, 19, 0) 70%);
        }
        .ambient-glow-2 {
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, rgba(7, 10, 19, 0) 70%);
        }

        /* Float & Pulse Animations for premium feeling */
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(1deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes pulse-slow {
            0%, 100% { opacity: 0.2; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.05); }
        }
        .animate-pulse-slow {
            animation: pulse-slow 8s ease-in-out infinite;
        }

        /* Glassmorphism Classes */
        .glass-panel {
            background: rgba(25, 33, 55, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glass-panel-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-panel-hover:hover {
            background: rgba(25, 33, 55, 0.6);
            border-color: rgba(16, 185, 129, 0.2);
            transform: translateY(-4px);
            box-shadow: 0 12px 30px -10px rgba(16, 185, 129, 0.15);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #070a13;
        }
        ::-webkit-scrollbar-thumb {
            background: #192137;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #10b981;
        }
    </style>
</head>
<body class="text-slate-100 overflow-x-hidden antialiased">

    <!-- Ambient Glowing Orbs -->
    <div class="absolute top-0 right-0 w-[600px] h-[600px] ambient-glow-1 -z-10 pointer-events-none"></div>
    <div class="absolute top-[800px] left-0 w-[500px] h-[500px] ambient-glow-2 -z-10 pointer-events-none"></div>
    <div class="absolute bottom-[400px] right-10 w-[700px] h-[700px] ambient-glow-1 -z-10 pointer-events-none"></div>

    <!-- Header / Navbar -->
    <header class="fixed top-0 left-0 right-0 z-50 px-4 md:px-8 py-4">
        <nav class="max-w-7xl mx-auto flex items-center justify-between px-6 py-3 rounded-2xl glass-panel border border-white/5 shadow-2xl">
            <!-- Logo Section -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="relative w-11 h-11 bg-brand-500/10 rounded-xl flex items-center justify-center border border-brand-500/20 overflow-hidden transition-transform group-hover:scale-105">
                    <img src="{{ asset('logo/Layer_2_Image.png') }}" alt="سريع" class="w-8 h-8 object-contain">
                    <div class="absolute inset-0 bg-brand-500/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-black tracking-tight text-white group-hover:text-brand-500 transition-colors">سريع</span>
                    <span class="text-[10px] text-slate-400 font-semibold tracking-widest uppercase">Sare3 Rides</span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                <a href="#features" class="hover:text-brand-500 transition-colors">المميزات</a>
                <a href="#how-it-works" class="hover:text-brand-500 transition-colors">كيف نعمل</a>
                <a href="#drivers" class="hover:text-brand-500 transition-colors">كن كابتن</a>
                <a href="/support" class="hover:text-brand-500 transition-colors">الدعم الفني</a>
            </div>

            <!-- Action Button -->
            {{-- <div class="flex items-center gap-3">
                @if(Auth::check() || Auth::guard('sanctum')->check())
                    <a href="{{ route('home') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-emerald-600 text-white font-bold text-sm shadow-lg shadow-brand-500/20 hover:shadow-brand-500/35 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                        <span>لوحة التحكم</span>
                        <i class="fa-solid fa-gauge text-xs"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-500 to-emerald-600 text-white font-bold text-sm shadow-lg shadow-brand-500/20 hover:shadow-brand-500/35 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                        <span>تسجيل الدخول</span>
                        <i class="fa-solid fa-arrow-left-to-bracket text-xs"></i>
                    </a>
                @endif
            </div> --}}
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 md:pt-40 md:pb-28 max-w-7xl mx-auto px-4 md:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Hero Content -->
            <div class="lg:col-span-7 flex flex-col text-right">
                <!-- Promo Badge -->
                <div class="inline-flex self-start items-center gap-2 px-3 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-500 text-xs font-semibold mb-6 shadow-sm">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                    </span>
                    <span>الجيل الجديد من النقل الذكي في مصر</span>
                </div>

                <!-- Main Title -->
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-black text-white leading-[1.15] mb-6">
                    مشوارك أسرع، أسهل <br>
                    <span class="bg-gradient-to-l from-brand-500 via-emerald-400 to-teal-400 bg-clip-text text-transparent">وبأمان كامل مع سريع</span>
                </h1>

                <!-- Subtitle -->
                <p class="text-slate-300 text-base md:text-lg leading-relaxed mb-8 max-w-2xl font-light">
                    سريع (Sare3) هو الحل المتكامل للتنقل اليومي السهل. احجز مشاويرك بضغطة زر، تتبع موقع الكابتن مباشرة، واستمتع بتعريفة عادلة ورحلات مريحة مع كباتن مدربين وموثوقين.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-wrap gap-4 mb-10">
                    <a href="#download" class="px-8 py-4 rounded-xl bg-gradient-to-r from-brand-500 to-emerald-600 text-white font-bold text-base shadow-xl shadow-brand-500/20 hover:shadow-brand-500/35 hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center gap-3">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <span>حمل التطبيق الآن</span>
                    </a>
                    <a href="#drivers" class="px-8 py-4 rounded-xl glass-panel hover:bg-slate-800/50 text-white font-semibold text-base border border-white/10 hover:border-brand-500/30 hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center gap-3">
                        <i class="fa-solid fa-car text-brand-500"></i>
                        <span>انضم كـ كابتن</span>
                    </a>
                </div>

                <!-- Features Badges -->
                <div class="grid grid-cols-3 gap-4 border-t border-white/5 pt-8 max-w-lg">
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-white mb-1">١٠٠٪</span>
                        <span class="text-xs text-slate-400">سائقين مؤهلين</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-white mb-1">٢٤/٧</span>
                        <span class="text-xs text-slate-400">دعم متواصل</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-white mb-1">دقيقة</span>
                        <span class="text-xs text-slate-400">سرعة الاستجابة</span>
                    </div>
                </div>
            </div>

            <!-- Hero Phone Mockup / Visual -->
            <div class="lg:col-span-5 flex justify-center items-center relative">
                <!-- Floating Decorative elements -->
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-brand-500/10 rounded-2xl border border-brand-500/20 flex items-center justify-center animate-float shadow-xl backdrop-blur-sm pointer-events-none">
                    <i class="fa-solid fa-map-pin text-3xl text-brand-500"></i>
                </div>
                <div class="absolute -bottom-6 -left-8 w-28 h-28 bg-dark-700/60 rounded-2xl border border-white/5 flex flex-col p-3 shadow-2xl backdrop-blur-sm pointer-events-none" style="animation: float 8s ease-in-out infinite 1s;">
                    <div class="flex items-center gap-2 mb-1.5">
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        <span class="text-[10px] text-slate-400 font-semibold">كابتن قريب</span>
                    </div>
                    <span class="text-xs font-bold text-white">وصل الكابتن!</span>
                    <span class="text-[9px] text-slate-400 mt-1">كابتن محمد • فورد فوكس</span>
                </div>

                <!-- Smartphone CSS Mockup -->
                <div class="relative w-[300px] h-[610px] bg-slate-950 rounded-[48px] border-[10px] border-slate-800 shadow-2xl overflow-hidden flex flex-col">
                    <!-- Screen Notch / Dynamic Island -->
                    <div class="absolute top-2 left-1/2 -translate-x-1/2 w-32 h-6 bg-slate-950 rounded-2xl z-20"></div>

                    <!-- Screen Content -->
                    <div class="flex-1 flex flex-col relative bg-slate-900 p-4 pt-10">
                        <!-- Simulated Map Area -->
                        <div class="h-1/2 rounded-2xl bg-slate-850 overflow-hidden relative border border-white/5">
                            <!-- Simulated grid map -->
                            <div class="absolute inset-0 opacity-10 bg-[linear-gradient(rgba(255,255,255,.1)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.1)_1px,transparent_1px)] bg-[size:20px_20px]"></div>
                            
                            <!-- Simulating path -->
                            <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                                <path d="M 50 180 Q 120 80, 200 130 T 230 40" fill="none" stroke="#10b981" stroke-width="4" stroke-linecap="round" stroke-dasharray="8 4" />
                            </svg>

                            <!-- Pin 1 -->
                            <div class="absolute top-[180px] left-[50px] -translate-x-1/2 -translate-y-1/2 flex flex-col items-center">
                                <div class="bg-indigo-500 w-3 h-3 rounded-full border-2 border-white ring-4 ring-indigo-500/20"></div>
                            </div>
                            
                            <!-- Pin 2 -->
                            <div class="absolute top-[40px] left-[230px] -translate-x-1/2 -translate-y-1/2 flex flex-col items-center">
                                <div class="bg-brand-500 w-4 h-4 rounded-full border-2 border-white ring-4 ring-brand-500/30 flex items-center justify-center">
                                    <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
                                </div>
                            </div>

                            <!-- Floating Car representation on Map -->
                            <div class="absolute top-[100px] left-[130px] -translate-x-1/2 -translate-y-1/2 rotate-12 transition-all">
                                <div class="w-8 h-8 bg-brand-500 rounded-full flex items-center justify-center border-2 border-white shadow-lg animate-pulse">
                                    <i class="fa-solid fa-car-side text-xs text-white"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Simulated Interface -->
                        <div class="flex-1 flex flex-col justify-end gap-3 mt-4">
                            <!-- Booking panel -->
                            <div class="bg-slate-800/80 backdrop-blur-md border border-white/5 rounded-2xl p-3 flex flex-col text-right">
                                <div class="text-[10px] text-brand-500 font-bold mb-1">رحلة جارية الآن</div>
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center overflow-hidden">
                                            <i class="fa-solid fa-user text-slate-400"></i>
                                        </div>
                                        <div class="flex flex-col text-left">
                                            <span class="text-xs font-bold text-white">أحمد م.</span>
                                            <span class="text-[9px] text-slate-400">★ 4.9 (240 رحلة)</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-emerald-400">وصل في ٣ دقائق</span>
                                </div>
                            </div>

                            <!-- Destination Inputs -->
                            <div class="bg-slate-850 border border-white/5 rounded-2xl p-3 flex flex-col gap-2 text-right">
                                <div class="flex items-center gap-2 border-b border-white/5 pb-2">
                                    <div class="w-2 h-2 rounded-full bg-indigo-400"></div>
                                    <span class="text-xs text-slate-300">موقع الالتقاء: المعادي، القاهرة</span>
                                </div>
                                <div class="flex items-center gap-2 pt-1">
                                    <div class="w-2 h-2 rounded-full bg-brand-500"></div>
                                    <span class="text-xs text-slate-300">الوجهة: المهندسين، الجيزة</span>
                                </div>
                            </div>

                            <!-- Ride Categories -->
                            <div class="grid grid-cols-2 gap-2">
                                <div class="border-2 border-brand-500 bg-brand-500/10 rounded-xl p-2 flex flex-col items-center">
                                    <i class="fa-solid fa-car text-brand-500 text-sm mb-1"></i>
                                    <span class="text-[10px] font-bold text-white">سريع اقتصادي</span>
                                    <span class="text-[9px] text-slate-400">٧٥ ج.م</span>
                                </div>
                                <div class="border border-white/5 bg-slate-850 rounded-xl p-2 flex flex-col items-center opacity-70">
                                    <i class="fa-solid fa-taxi text-slate-400 text-sm mb-1"></i>
                                    <span class="text-[10px] font-bold text-white">سريع برو VIP</span>
                                    <span class="text-[9px] text-slate-400">١٢٠ ج.م</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 md:py-28 bg-dark-800/40 relative">
        <div class="max-w-7xl mx-auto px-4 md:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold tracking-widest text-brand-500 uppercase mb-3">مميزات المنصة</h2>
                <p class="text-3xl sm:text-4xl font-extrabold text-white">لماذا يفضل الملايين التنقل مع سريع؟</p>
                <div class="w-16 h-1 bg-brand-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="glass-panel glass-panel-hover rounded-2xl p-6 text-right flex flex-col">
                    <div class="w-12 h-12 bg-brand-500/10 border border-brand-500/20 text-brand-500 rounded-xl flex items-center justify-center mb-6 text-xl">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">تتبع فوري ومباشر</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">تابع رحلتك وموقع كابتنك على الخريطة لحظة بلحظة مع دقة متناهية في حساب وقت الوصول المتوقع.</p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-panel glass-panel-hover rounded-2xl p-6 text-right flex flex-col">
                    <div class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 rounded-xl flex items-center justify-center mb-6 text-xl">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">أمان وسلامة قصوى</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">جميع الكباتن يخضعون لفحص خلفية أمنية كاملة واختبارات دورية للسيارات لضمان سلامتك طوال الطريق.</p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-panel glass-panel-hover rounded-2xl p-6 text-right flex flex-col">
                    <div class="w-12 h-12 bg-teal-500/10 border border-teal-500/20 text-teal-500 rounded-xl flex items-center justify-center mb-6 text-xl">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">خيارات دفع مرنة</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">ادفع نقدًا، أو عبر المحفظة الإلكترونية، أو ببطاقة الائتمان بكل سهولة وأمان دون أي تعقيد.</p>
                </div>

                <!-- Feature 4 -->
                <div class="glass-panel glass-panel-hover rounded-2xl p-6 text-right flex flex-col">
                    <div class="w-12 h-12 bg-indigo-500/10 border border-indigo-500/20 text-indigo-500 rounded-xl flex items-center justify-center mb-6 text-xl">
                        <i class="fa-solid fa-tags"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">عروض وكوبونات حصرية</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">وفر على مشاويرك اليومية بفضل الخصومات المتكررة وكوبونات التوفير التي نقدمها لعملائنا باستمرار.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 md:py-28 max-w-7xl mx-auto px-4 md:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-xs font-bold tracking-widest text-brand-500 uppercase mb-3">آلية العمل</h2>
            <p class="text-3xl sm:text-4xl font-extrabold text-white">رحلتك جاهزة في ٣ خطوات بسيطة</p>
            <div class="w-16 h-1 bg-brand-500 mx-auto mt-4 rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 relative">
            <!-- Connecting lines for desktop -->
            <div class="hidden lg:block absolute top-1/2 left-[15%] right-[15%] h-[2px] bg-gradient-to-r from-brand-500/0 via-brand-500/30 to-brand-500/0 -translate-y-12 -z-10"></div>

            <!-- Step 1 -->
            <div class="flex flex-col items-center text-center p-6">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-500/20 to-emerald-600/20 border border-brand-500/30 flex items-center justify-center text-white text-3xl font-black mb-6 shadow-lg shadow-brand-500/5 relative">
                    <span>١</span>
                    <div class="absolute -bottom-2 right-1/2 translate-x-1/2 w-6 h-6 rounded-full bg-slate-900 border border-white/10 flex items-center justify-center text-xs text-brand-500"><i class="fa-solid fa-magnifying-glass-location"></i></div>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">حدد وجهتك</h3>
                <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
                    افتح التطبيق، اختر موقعك الحالي، ثم حدد المكان الذي ترغب في الذهاب إليه واطلع على سعر الرحلة المتوقع مسبقًا.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="flex flex-col items-center text-center p-6">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-500/20 to-emerald-600/20 border border-brand-500/30 flex items-center justify-center text-white text-3xl font-black mb-6 shadow-lg shadow-brand-500/5 relative">
                    <span>٢</span>
                    <div class="absolute -bottom-2 right-1/2 translate-x-1/2 w-6 h-6 rounded-full bg-slate-900 border border-white/10 flex items-center justify-center text-xs text-brand-500"><i class="fa-solid fa-link"></i></div>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">تطابق مع كابتن قريب</h3>
                <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
                    خلال ثوانٍ، سيقوم النظام بالربط التلقائي مع أقرب كابتن متاح وسيبدأ بالتحرك فوراً نحو موقعك لتسريع وصولك.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="flex flex-col items-center text-center p-6">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-500/20 to-emerald-600/20 border border-brand-500/30 flex items-center justify-center text-white text-3xl font-black mb-6 shadow-lg shadow-brand-500/5 relative">
                    <span>٣</span>
                    <div class="absolute -bottom-2 right-1/2 translate-x-1/2 w-6 h-6 rounded-full bg-slate-900 border border-white/10 flex items-center justify-center text-xs text-brand-500"><i class="fa-solid fa-route"></i></div>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">انطلق بأمان وراحة</h3>
                <p class="text-slate-400 text-sm leading-relaxed max-w-xs">
                    اصعد للسيارة وتابع مسار رحلتك مباشرة، وبعد الوصول ادفع بالطريقة التي تناسبك وقيم تجربتك لمساعدتنا في التطوير.
                </p>
            </div>
        </div>
    </section>

    <!-- Captain Recruitment Section -->
    <section id="drivers" class="py-20 md:py-28 bg-gradient-to-b from-dark-900 to-dark-800 relative overflow-hidden">
        <!-- Accent circles -->
        <div class="absolute -right-20 top-1/2 -translate-y-1/2 w-96 h-96 bg-brand-500/5 rounded-full filter blur-3xl"></div>
        
        <div class="max-w-7xl mx-auto px-4 md:px-8 relative">
            <div class="glass-panel rounded-[32px] p-8 md:p-12 lg:p-16 border border-white/5 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-full bg-cover bg-center opacity-10" style="background-image: url('https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80');"></div>
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center relative z-10">
                    
                    <!-- Driver Info text -->
                    <div class="lg:col-span-7 text-right">
                        <span class="text-brand-500 text-sm font-bold uppercase tracking-wider mb-3 block">فرصة عمل ممتازة</span>
                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-black text-white leading-tight mb-6">
                            كن رئيس نفسك وحقق <br>دخولاً إضافية ممتازة مع سريع
                        </h2>
                        <p class="text-slate-300 text-base md:text-lg mb-8 font-light leading-relaxed">
                            انضم كابتن في منصة سريع، واعمل في الوقت الذي يناسبك. نحن نوفر لك أفضل نسبة أرباح في السوق، ودعم مستمر على مدار الساعة، بالإضافة إلى حوافز يومية وأسبوعية على أعداد الرحلات.
                        </p>
                        
                        <!-- Driver Key Benefits -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                            <div class="flex items-center gap-3 justify-start">
                                <i class="fa-solid fa-clock text-brand-500 text-lg"></i>
                                <span class="text-white text-sm font-medium">مرونة كاملة في ساعات العمل</span>
                            </div>
                            <div class="flex items-center gap-3 justify-start">
                                <i class="fa-solid fa-money-bill-trend-up text-brand-500 text-lg"></i>
                                <span class="text-white text-sm font-medium">أعلى عمولة وعائد مجزي للرحلة</span>
                            </div>
                            <div class="flex items-center gap-3 justify-start">
                                <i class="fa-solid fa-headset text-brand-500 text-lg"></i>
                                <span class="text-white text-sm font-medium">دعم فني خاص بالكباتن 24/7</span>
                            </div>
                            <div class="flex items-center gap-3 justify-start">
                                <i class="fa-solid fa-award text-brand-500 text-lg"></i>
                                <span class="text-white text-sm font-medium">حوافز وتكريمات شهرية ممتازة</span>
                            </div>
                        </div>

                        <!-- Apply button -->
                        <a href="https://play.google.com/store/apps/details?id=com.app.sareadriver" class="inline-flex items-center gap-3 px-8 py-4 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold text-base transition-all hover:scale-[1.02] shadow-xl shadow-brand-500/20">
                            <span>سجل الآن كـ كابتن في سريع</span>
                            <i class="fa-solid fa-id-card"></i>
                        </a>
                    </div>

                    <!-- Visual Side -->
                    <div class="lg:col-span-5 flex justify-center">
                        <div class="relative w-full max-w-md aspect-video md:aspect-square rounded-2xl overflow-hidden shadow-2xl border border-white/10 group">
                            <!-- Premium Driver Image placeholder styled as a driver app mockup -->
                            <div class="absolute inset-0 bg-slate-900 flex flex-col justify-between p-6">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-brand-500/10 rounded-full flex items-center justify-center text-brand-500">
                                            <i class="fa-solid fa-car text-xl"></i>
                                        </div>
                                        <div class="flex flex-col text-left">
                                            <span class="text-xs text-slate-400">لوحة التحكم للكابتن</span>
                                            <span class="text-sm font-bold text-white">كابتن سريع</span>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold">متصل 🟢</span>
                                </div>

                                <div class="bg-slate-800/80 border border-white/5 rounded-2xl p-4 my-4 flex flex-col gap-2">
                                    <div class="flex justify-between text-xs text-slate-400">
                                        <span>أرباح اليوم</span>
                                        <span>عدد الرحلات</span>
                                    </div>
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-2xl font-black text-white">450.50 ج.م</span>
                                        <span class="text-lg font-bold text-brand-500">8 رحلات</span>
                                    </div>
                                    <div class="w-full bg-slate-700 h-1.5 rounded-full overflow-hidden mt-2">
                                        <div class="bg-brand-500 h-full w-[80%]"></div>
                                    </div>
                                    <div class="flex justify-between text-[10px] text-slate-400">
                                        <span>هدف اليوم (500 ج.م)</span>
                                        <span>80% مكتمل</span>
                                    </div>
                                </div>

                                <div class="w-full py-3.5 bg-brand-500 rounded-xl text-center text-white font-bold text-sm cursor-pointer hover:bg-brand-600 transition-colors">
                                    قبول الطلب الجديد (+45 ج.م)
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Download App Section -->
    <section id="download" class="py-20 md:py-28 max-w-7xl mx-auto px-4 md:px-8 text-center relative">
        <div class="max-w-3xl mx-auto glass-panel border border-white/5 rounded-[32px] p-8 md:p-12 shadow-2xl relative overflow-hidden">
            <!-- Background glow -->
            <div class="absolute -top-12 -right-12 w-48 h-48 bg-brand-500/10 rounded-full filter blur-2xl"></div>
            
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4">احصل على التطبيق الآن مجانًا</h2>
            <p class="text-slate-300 text-sm md:text-base leading-relaxed mb-8 max-w-xl mx-auto">
                حمل التطبيق الآن على جهازك واستمتع بأولى رحلاتك بخصم خاص يصل إلى ٥٠٪ باستخدام كود الخصم الترحيبي <span class="text-brand-500 font-bold">WELCOME50</span>.
            </p>
            
            <div class="flex flex-wrap justify-center gap-4">
                <a href="https://play.google.com/store/apps/details?id=com.app.sareauser" class="px-6 py-3 bg-slate-900 border border-white/10 rounded-xl hover:border-brand-500/50 hover:bg-slate-850 transition-all flex items-center gap-3 text-right">
                    <i class="fa-brands fa-google-play text-2xl text-white"></i>
                    <div class="flex flex-col">
                        <span class="text-[9px] text-slate-400 font-semibold uppercase">تحميل من</span>
                        <span class="text-xs font-bold text-white">Google Play</span>
                    </div>
                </a>
                
                <a href="https://apps.apple.com/us/app/saree-taxi/id6758101605" class="px-6 py-3 bg-slate-900 border border-white/10 rounded-xl hover:border-brand-500/50 hover:bg-slate-850 transition-all flex items-center gap-3 text-right">
                    <i class="fa-brands fa-apple text-2xl text-white"></i>
                    <div class="flex flex-col">
                        <span class="text-[9px] text-slate-400 font-semibold uppercase">تحميل من</span>
                        <span class="text-xs font-bold text-white">App Store</span>
                        
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/5 bg-slate-950/80 pt-16 pb-12 relative z-10">
        <div class="max-w-7xl mx-auto px-4 md:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 mb-12">
                <!-- Branding -->
                <div class="md:col-span-4 flex flex-col text-right">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 bg-brand-500/10 rounded-lg flex items-center justify-center border border-brand-500/20">
                            <img src="{{ asset('logo/Layer_2_Image.png') }}" alt="سريع" class="w-6 h-6 object-contain">
                        </div>
                        <span class="text-lg font-black text-white">سريع | Sare3</span>
                    </div>
                    <p class="text-slate-400 text-xs leading-relaxed max-w-sm mb-6">
                        تطبيق النقل الذكي الرائد في مصر والوطن العربي لتوصيل المشاوير وتسهيل التحركات بكل يسر وسهولة وموثوقية تامة.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-8 h-8 rounded-lg bg-slate-900 border border-white/5 flex items-center justify-center text-slate-400 hover:text-brand-500 hover:border-brand-500/20 transition-all"><i class="fa-brands fa-facebook-f text-sm"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-slate-900 border border-white/5 flex items-center justify-center text-slate-400 hover:text-brand-500 hover:border-brand-500/20 transition-all"><i class="fa-brands fa-twitter text-sm"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-slate-900 border border-white/5 flex items-center justify-center text-slate-400 hover:text-brand-500 hover:border-brand-500/20 transition-all"><i class="fa-brands fa-instagram text-sm"></i></a>
                    </div>
                </div>

                <!-- Quick links -->
                <div class="md:col-span-3 flex flex-col text-right">
                    <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-4 border-r-2 border-brand-500 pr-2">روابط سريعة</h4>
                    <ul class="flex flex-col gap-2 text-xs text-slate-400">
                        <li><a href="#features" class="hover:text-brand-500 transition-colors">المميزات الأساسية</a></li>
                        <li><a href="#how-it-works" class="hover:text-brand-500 transition-colors">كيف تعمل المنصة</a></li>
                        <li><a href="#drivers" class="hover:text-brand-500 transition-colors">فرص العمل ككابتن</a></li>
                        <li><a href="#download" class="hover:text-brand-500 transition-colors">تحميل التطبيق الهاتفي</a></li>
                    </ul>
                </div>

                <!-- Support links -->
                <div class="md:col-span-2 flex flex-col text-right">
                    <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-4 border-r-2 border-brand-500 pr-2">الدعم والخصوصية</h4>
                    <ul class="flex flex-col gap-2 text-xs text-slate-400">
                        <li><a href="/support" class="hover:text-brand-500 transition-colors">مركز المساعدة</a></li>
                        <li><a href="/privacy-policy" class="hover:text-brand-500 transition-colors">سياسة الخصوصية</a></li>
                        <li><a href="/support" class="hover:text-brand-500 transition-colors">شروط الاستخدام</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="md:col-span-3 flex flex-col text-right text-xs text-slate-400">
                    <h4 class="text-white font-bold uppercase tracking-wider mb-4 border-r-2 border-brand-500 pr-2">تواصل معنا</h4>
                    <p class="mb-2"><i class="fa-solid fa-envelope text-brand-500 ml-2"></i> info@sare3.tld</p>
                    <p class="mb-2"><i class="fa-solid fa-phone text-brand-500 ml-2"></i> +20 123 456 7890</p>
                    <p><i class="fa-solid fa-location-dot text-brand-500 ml-2"></i> المعادي، القاهرة، مصر</p>
                </div>
            </div>

            <!-- Powered by El Manhag Featured Card -->
            <div class="mb-12 flex justify-center">
                <div class="w-full max-w-3xl px-8 py-6 rounded-3xl bg-gradient-to-br from-slate-900 to-dark-800 border border-white/10 hover:border-brand-500/40 transition-all duration-300 shadow-2xl hover:shadow-brand-500/5 group flex flex-col sm:flex-row items-center justify-between gap-6 text-center sm:text-right">
                    <div class="flex flex-col gap-1.5 flex-1">
                        <span class="text-xs md:text-sm font-bold text-brand-500 tracking-widest uppercase">شريك التطوير والتشغيل</span>
                        <h3 class="text-xl md:text-2xl font-black text-white leading-tight">تطوير وتشغيل بواسطة منصة المنهج</h3>
                        <p class="text-slate-400 text-xs">نعمل على تقديم أفضل الحلول البرمجية الذكية وتطبيقات الهاتف المتكاملة.</p>
                    </div>
                    
                    <a href="#" target="_blank" class="flex items-center gap-4 bg-slate-950/90 border border-white/10 px-6 py-4 rounded-2xl hover:border-brand-500/30 transition-all duration-300 hover:scale-[1.03] shadow-lg shrink-0" title="منصة المنهج">
                        <img src="{{ asset('logo/elmanhag.jpg') }}" alt="El Manhag" class="h-20 w-auto rounded-xl object-contain border border-white/15 shadow-md">
                        <div class="flex flex-col text-right">
                            <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider">Powered By</span>
                            <span class="text-lg md:text-xl font-extrabold text-white group-hover:text-brand-500 transition-colors">المنهج | El Manhag</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Footer Bottom: Copyright -->
            <div class="border-t border-white/5 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-center sm:text-right">
                <p class="text-slate-400 text-xs">
                    &copy; 2026 جميع الحقوق محفوظة لشركة سريع المحدودة.
                </p>
                <p class="text-slate-500 text-[10px]">
                    سريع للنقل الذكي • تجربة نقل فريدة وآمنة
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
