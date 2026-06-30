<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>سريع | Sare3 - منصة النقل الذكي</title>

    <!-- SEO -->
    <meta name="description" content="سريع (Sare3) هي منصتك الأمثل للرحلات السريعة والآمنة. اطلب رحلتك الآن أو انضم ككابتن معنا لتحقيق أرباح ممتازة.">
    <meta name="keywords" content="سريع, توصيل, تاكسي, اوبر, رحلات, كابتن, sare3, ride-hailing, taxi">
    <link rel="apple-touch-icon" href="{{ asset('logo/Layer_2_Image.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('logo/Layer_2_Image.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 400:'#34d399', 500:'#10b981', 600:'#059669', 700:'#047857' },
                    },
                    fontFamily: { sans: ['Tajawal','Outfit','sans-serif'] }
                }
            }
        }
    </script>

    <style>
        * { box-sizing: border-box; }

        html, body {
            font-family: 'Tajawal', 'Outfit', sans-serif;
            background-color: #070a13;
            overflow-x: hidden;
            width: 100%;
        }

        /* Glassmorphism */
        .glass {
            background: rgba(15, 20, 35, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .glass-card {
            background: rgba(20, 28, 48, 0.5);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.06);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            background: rgba(25, 35, 60, 0.7);
            border-color: rgba(16,185,129,0.25);
            transform: translateY(-4px);
            box-shadow: 0 16px 40px -12px rgba(16,185,129,0.15);
        }

        /* Ambient glows — sized safely for mobile */
        .glow-green {
            background: radial-gradient(circle, rgba(16,185,129,0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .glow-indigo {
            background: radial-gradient(circle, rgba(99,102,241,0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Float animation */
        @keyframes float {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-10px); }
        }
        .float { animation: float 5s ease-in-out infinite; }

        /* Mobile menu slide */
        #mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
        }
        #mobile-menu.open {
            max-height: 400px;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #070a13; }
        ::-webkit-scrollbar-thumb { background: #192137; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #10b981; }
    </style>
</head>
<body class="text-slate-100 antialiased relative">

    <!-- Ambient glows (position:fixed so they don't expand page width) -->
    <div class="fixed top-0 right-0 w-64 h-64 sm:w-96 sm:h-96 glow-green -z-10"></div>
    <div class="fixed top-1/2 left-0 w-64 h-64 sm:w-80 sm:h-80 glow-indigo -z-10"></div>
    <div class="fixed bottom-0 right-0 w-64 h-64 sm:w-96 sm:h-96 glow-green -z-10"></div>

    <!-- ═══════════════════ HEADER ═══════════════════ -->
    <header class="fixed top-0 inset-x-0 z-50 px-3 sm:px-6 pt-3 pb-2">
        <nav class="max-w-7xl mx-auto glass rounded-2xl px-4 sm:px-6 py-3 shadow-2xl">
            <div class="flex items-center justify-between gap-3">

                <!-- Logo -->
                <a href="/" class="flex items-center gap-2.5 shrink-0 group">
                    <div class="w-10 h-10 bg-brand-500/10 rounded-xl border border-brand-500/20 flex items-center justify-center overflow-hidden shrink-0 transition-transform group-hover:scale-105">
                        <img src="{{ asset('logo/Layer_2_Image.png') }}" alt="سريع" class="w-7 h-7 object-contain">
                    </div>
                    <div class="flex flex-col leading-none">
                        <span class="text-base sm:text-lg font-black text-white group-hover:text-brand-500 transition-colors">سريع</span>
                        <span class="text-[9px] text-slate-400 font-semibold tracking-widest uppercase">Sare3</span>
                    </div>
                </a>

                <!-- Desktop links -->
                <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-300">
                    <a href="#features"    class="hover:text-brand-500 transition-colors">المميزات</a>
                    <a href="#how-it-works" class="hover:text-brand-500 transition-colors">كيف نعمل</a>
                    <a href="#drivers"     class="hover:text-brand-500 transition-colors">كن كابتن</a>
                    <a href="/support"     class="hover:text-brand-500 transition-colors">الدعم</a>
                </div>

                <!-- Right side: CTA + hamburger -->
                {{-- <div class="flex items-center gap-2 shrink-0">
                    @if(Auth::check() || Auth::guard('sanctum')->check())
                        <a href="{{ route('home') }}" class="hidden sm:flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-emerald-600 text-white font-bold text-xs sm:text-sm shadow-lg hover:scale-[1.02] transition-all">
                            <i class="fa-solid fa-gauge text-xs"></i>
                            <span>لوحة التحكم</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-brand-500 to-emerald-600 text-white font-bold text-xs sm:text-sm shadow-lg hover:scale-[1.02] transition-all">
                            <i class="fa-solid fa-arrow-left-to-bracket text-xs"></i>
                            <span>دخول</span>
                        </a>
                    @endif

                    <!-- Hamburger -->
                    <button id="hamburger" aria-label="القائمة" class="md:hidden flex items-center justify-center w-10 h-10 rounded-xl bg-slate-900/80 border border-white/8 text-slate-300 hover:text-brand-500 focus:outline-none transition-all">
                        <i id="hamburger-icon" class="fa-solid fa-bars text-base"></i>
                    </button>
                </div> --}}
            </div>

            <!-- Mobile dropdown -->
            <div id="mobile-menu">
                <div class="pt-4 pb-2 flex flex-col gap-1 border-t border-white/5 mt-3">
                    <a href="#features"    class="mobile-link flex items-center gap-3 py-3 px-3 rounded-xl hover:bg-slate-800/60 hover:text-brand-500 transition-all text-sm font-medium">
                        <i class="fa-solid fa-map-location-dot w-5 text-brand-500"></i> المميزات
                    </a>
                    <a href="#how-it-works" class="mobile-link flex items-center gap-3 py-3 px-3 rounded-xl hover:bg-slate-800/60 hover:text-brand-500 transition-all text-sm font-medium">
                        <i class="fa-solid fa-list-ol w-5 text-brand-500"></i> كيف نعمل
                    </a>
                    <a href="#drivers"     class="mobile-link flex items-center gap-3 py-3 px-3 rounded-xl hover:bg-slate-800/60 hover:text-brand-500 transition-all text-sm font-medium">
                        <i class="fa-solid fa-car w-5 text-brand-500"></i> كن كابتن
                    </a>
                    <a href="/support"     class="mobile-link flex items-center gap-3 py-3 px-3 rounded-xl hover:bg-slate-800/60 hover:text-brand-500 transition-all text-sm font-medium">
                        <i class="fa-solid fa-headset w-5 text-brand-500"></i> الدعم الفني
                    </a>
                    <div class="pt-2">
                        @if(Auth::check() || Auth::guard('sanctum')->check())
                            <a href="{{ route('home') }}" class="mobile-link w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r from-brand-500 to-emerald-600 text-white font-bold text-sm">
                                <i class="fa-solid fa-gauge"></i> لوحة التحكم
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="mobile-link w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r from-brand-500 to-emerald-600 text-white font-bold text-sm">
                                <i class="fa-solid fa-arrow-left-to-bracket"></i> تسجيل الدخول
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- ═══════════════════ HERO ═══════════════════ -->
    <section class="pt-28 sm:pt-32 md:pt-40 pb-16 sm:pb-20 px-4 sm:px-6 max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">

            <!-- Content -->
            <div class="flex flex-col items-center lg:items-end text-center lg:text-right order-2 lg:order-1">

                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-500/10 border border-brand-500/20 text-brand-500 text-xs font-semibold mb-5">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                    </span>
                    الجيل الجديد من النقل الذكي في مصر
                </div>

                <!-- Title -->
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white leading-tight mb-5">
                    مشوارك أسرع وأسهل <br class="hidden sm:block">
                    <span class="bg-gradient-to-l from-brand-500 via-emerald-400 to-teal-400 bg-clip-text text-transparent">وبأمان مع سريع</span>
                </h1>

                <!-- Subtitle -->
                <p class="text-slate-300 text-sm sm:text-base md:text-lg leading-relaxed mb-8 max-w-lg font-light">
                    احجز مشاويرك بضغطة زر، تتبع موقع الكابتن مباشرة، واستمتع بتعريفة عادلة ورحلات مريحة مع كباتن مدربين وموثوقين.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto mb-10">
                    <a href="#download" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-gradient-to-r from-brand-500 to-emerald-600 text-white font-bold text-sm shadow-xl shadow-brand-500/20 hover:-translate-y-0.5 transition-all">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        حمل التطبيق الآن
                    </a>
                    <a href="#drivers" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl glass-card text-white font-semibold text-sm hover:-translate-y-0.5 transition-all">
                        <i class="fa-solid fa-car text-brand-500"></i>
                        انضم كـ كابتن
                    </a>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 sm:gap-8 border-t border-white/5 pt-6 w-full max-w-sm sm:max-w-md">
                    <div class="flex flex-col items-center lg:items-end">
                        <span class="text-xl sm:text-2xl font-bold text-white">١٠٠٪</span>
                        <span class="text-[10px] sm:text-xs text-slate-400 text-center lg:text-right">سائقين مؤهلين</span>
                    </div>
                    <div class="flex flex-col items-center lg:items-end">
                        <span class="text-xl sm:text-2xl font-bold text-white">٢٤/٧</span>
                        <span class="text-[10px] sm:text-xs text-slate-400 text-center lg:text-right">دعم متواصل</span>
                    </div>
                    <div class="flex flex-col items-center lg:items-end">
                        <span class="text-xl sm:text-2xl font-bold text-white">دقيقة</span>
                        <span class="text-[10px] sm:text-xs text-slate-400 text-center lg:text-right">سرعة الاستجابة</span>
                    </div>
                </div>
            </div>

            <!-- Phone Mockup -->
            <div class="flex justify-center items-center relative order-1 lg:order-2">
                <!-- Floating badges -->
                <div class="absolute -top-4 -right-2 sm:-top-6 sm:-right-6 w-20 h-20 sm:w-24 sm:h-24 bg-brand-500/10 rounded-2xl border border-brand-500/20 flex items-center justify-center float z-10">
                    <i class="fa-solid fa-map-pin text-2xl sm:text-3xl text-brand-500"></i>
                </div>
                <div class="absolute -bottom-4 -left-2 sm:-bottom-4 sm:-left-6 glass-card rounded-2xl p-3 z-10 shadow-2xl" style="animation: float 7s ease-in-out infinite 1.5s;">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                        <span class="text-[9px] text-slate-400 font-semibold">كابتن قريب</span>
                    </div>
                    <span class="text-xs font-bold text-white block">وصل الكابتن! 🚗</span>
                    <span class="text-[9px] text-slate-400 mt-0.5 block">كابتن محمد • فورد فوكس</span>
                </div>

                <!-- Phone Frame -->
                <div class="relative w-[240px] h-[500px] sm:w-[280px] sm:h-[570px] bg-slate-950 rounded-[44px] border-[8px] sm:border-[10px] border-slate-800 shadow-2xl overflow-hidden flex flex-col">
                    <!-- Notch -->
                    <div class="absolute top-2 left-1/2 -translate-x-1/2 w-24 h-5 bg-slate-950 rounded-2xl z-20"></div>

                    <!-- Screen -->
                    <div class="flex-1 flex flex-col bg-slate-900 p-3 pt-9">
                        <!-- Map area -->
                        <div class="flex-1 rounded-2xl overflow-hidden relative border border-white/5 mb-3">
                            <div class="absolute inset-0 opacity-[0.07] bg-[linear-gradient(rgba(255,255,255,.15)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.15)_1px,transparent_1px)] bg-[size:18px_18px]"></div>
                            <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                                <path d="M 40 160 Q 100 70, 180 110 T 210 30" fill="none" stroke="#10b981" stroke-width="3.5" stroke-linecap="round" stroke-dasharray="7 4"/>
                            </svg>
                            <!-- Origin pin -->
                            <div class="absolute top-[160px] left-[40px] -translate-x-1/2 -translate-y-1/2">
                                <div class="bg-indigo-500 w-3 h-3 rounded-full border-2 border-white ring-4 ring-indigo-500/20"></div>
                            </div>
                            <!-- Destination pin -->
                            <div class="absolute top-[30px] left-[210px] -translate-x-1/2 -translate-y-1/2">
                                <div class="bg-brand-500 w-4 h-4 rounded-full border-2 border-white ring-4 ring-brand-500/30 flex items-center justify-center">
                                    <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
                                </div>
                            </div>
                            <!-- Car icon -->
                            <div class="absolute top-[95px] left-[120px] -translate-x-1/2 -translate-y-1/2 rotate-12">
                                <div class="w-8 h-8 bg-brand-500 rounded-full flex items-center justify-center border-2 border-white shadow-lg animate-pulse">
                                    <i class="fa-solid fa-car-side text-[10px] text-white"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Booking info -->
                        <div class="space-y-2">
                            <div class="bg-slate-800/80 rounded-xl p-2.5 flex justify-between items-center">
                                <span class="text-[10px] text-brand-500 font-bold">رحلة جارية</span>
                                <span class="text-[10px] text-emerald-400 font-bold">وصل في ٣ دقائق</span>
                            </div>
                            <div class="bg-slate-800/50 rounded-xl p-2.5 space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-indigo-400 shrink-0"></div>
                                    <span class="text-[10px] text-slate-300 truncate">المعادي، القاهرة</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-brand-500 shrink-0"></div>
                                    <span class="text-[10px] text-slate-300 truncate">المهندسين، الجيزة</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5">
                                <div class="border-2 border-brand-500 bg-brand-500/10 rounded-xl p-2 text-center">
                                    <i class="fa-solid fa-car text-brand-500 text-xs mb-0.5 block"></i>
                                    <span class="text-[9px] font-bold text-white">اقتصادي</span>
                                    <span class="text-[8px] text-slate-400 block">٧٥ ج.م</span>
                                </div>
                                <div class="border border-white/5 bg-slate-800/40 rounded-xl p-2 text-center opacity-60">
                                    <i class="fa-solid fa-taxi text-slate-400 text-xs mb-0.5 block"></i>
                                    <span class="text-[9px] font-bold text-white">VIP</span>
                                    <span class="text-[8px] text-slate-400 block">١٢٠ ج.م</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════ FEATURES ═══════════════════ -->
    <section id="features" class="py-16 sm:py-20 md:py-28 px-4 sm:px-6 bg-slate-950/40">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12 sm:mb-16">
                <p class="text-xs font-bold tracking-widest text-brand-500 uppercase mb-3">مميزات المنصة</p>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white">لماذا يفضل الملايين سريع؟</h2>
                <div class="w-12 h-1 bg-brand-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
                <!-- Feature -->
                <div class="glass-card rounded-2xl p-5 sm:p-6 text-right flex flex-col gap-4">
                    <div class="w-11 h-11 bg-brand-500/10 border border-brand-500/20 text-brand-500 rounded-xl flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white mb-1.5">تتبع فوري ومباشر</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">تابع رحلتك وموقع كابتنك لحظة بلحظة مع دقة متناهية في حساب وقت الوصول.</p>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-5 sm:p-6 text-right flex flex-col gap-4">
                    <div class="w-11 h-11 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 rounded-xl flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white mb-1.5">أمان وسلامة قصوى</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">جميع الكباتن يخضعون لفحص أمني كامل واختبارات دورية للسيارات لضمان سلامتك.</p>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-5 sm:p-6 text-right flex flex-col gap-4">
                    <div class="w-11 h-11 bg-teal-500/10 border border-teal-500/20 text-teal-500 rounded-xl flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white mb-1.5">خيارات دفع مرنة</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">ادفع نقدًا أو عبر المحفظة الإلكترونية أو ببطاقة الائتمان بكل سهولة وأمان.</p>
                    </div>
                </div>

                <div class="glass-card rounded-2xl p-5 sm:p-6 text-right flex flex-col gap-4">
                    <div class="w-11 h-11 bg-indigo-500/10 border border-indigo-500/20 text-indigo-500 rounded-xl flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-tags"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white mb-1.5">عروض وكوبونات حصرية</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">وفر على مشاويرك اليومية بفضل الخصومات المتكررة وكوبونات التوفير الحصرية.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════ HOW IT WORKS ═══════════════════ -->
    <section id="how-it-works" class="py-16 sm:py-20 md:py-28 px-4 sm:px-6 max-w-7xl mx-auto">
        <div class="text-center mb-12 sm:mb-16">
            <p class="text-xs font-bold tracking-widest text-brand-500 uppercase mb-3">آلية العمل</p>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white">رحلتك في ٣ خطوات بسيطة</h2>
            <div class="w-12 h-1 bg-brand-500 mx-auto mt-4 rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 sm:gap-6">
            <div class="flex flex-col items-center text-center px-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-brand-500/20 to-emerald-600/20 border border-brand-500/30 flex items-center justify-center text-white text-2xl sm:text-3xl font-black mb-5 shadow-lg relative">
                    <span>١</span>
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-6 h-6 rounded-full bg-slate-900 border border-white/10 flex items-center justify-center text-[10px] text-brand-500">
                        <i class="fa-solid fa-magnifying-glass-location"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">حدد وجهتك</h3>
                <p class="text-slate-400 text-sm leading-relaxed max-w-xs">افتح التطبيق، اختر موقعك الحالي والوجهة، واطلع على سعر الرحلة المتوقع مسبقًا.</p>
            </div>

            <div class="flex flex-col items-center text-center px-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-brand-500/20 to-emerald-600/20 border border-brand-500/30 flex items-center justify-center text-white text-2xl sm:text-3xl font-black mb-5 shadow-lg relative">
                    <span>٢</span>
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-6 h-6 rounded-full bg-slate-900 border border-white/10 flex items-center justify-center text-[10px] text-brand-500">
                        <i class="fa-solid fa-link"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">تطابق مع كابتن</h3>
                <p class="text-slate-400 text-sm leading-relaxed max-w-xs">خلال ثوانٍ سيقوم النظام بالربط مع أقرب كابتن متاح ليتحرك فوراً نحو موقعك.</p>
            </div>

            <div class="flex flex-col items-center text-center px-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-brand-500/20 to-emerald-600/20 border border-brand-500/30 flex items-center justify-center text-white text-2xl sm:text-3xl font-black mb-5 shadow-lg relative">
                    <span>٣</span>
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-6 h-6 rounded-full bg-slate-900 border border-white/10 flex items-center justify-center text-[10px] text-brand-500">
                        <i class="fa-solid fa-route"></i>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">انطلق بأمان</h3>
                <p class="text-slate-400 text-sm leading-relaxed max-w-xs">اصعد للسيارة، تابع مسارك، وبعد الوصول ادفع بالطريقة التي تناسبك وقيّم رحلتك.</p>
            </div>
        </div>
    </section>

    <!-- ═══════════════════ CAPTAIN SECTION ═══════════════════ -->
    <section id="drivers" class="py-16 sm:py-20 md:py-28 px-4 sm:px-6 bg-slate-950/60 relative overflow-hidden">
        <div class="absolute -left-20 top-1/2 -translate-y-1/2 w-72 h-72 glow-green rounded-full filter blur-3xl"></div>

        <div class="max-w-7xl mx-auto relative">
            <div class="glass rounded-3xl p-6 sm:p-10 md:p-14 lg:p-16 border border-white/5 overflow-hidden relative">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                    <!-- Text -->
                    <div class="lg:col-span-7 text-center lg:text-right order-2 lg:order-1">
                        <span class="text-brand-500 text-xs sm:text-sm font-bold uppercase tracking-wider mb-3 block">فرصة عمل ممتازة</span>
                        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black text-white leading-tight mb-5">
                            كن رئيس نفسك وحقق<br class="hidden sm:block"> دخولاً إضافية مع سريع
                        </h2>
                        <p class="text-slate-300 text-sm sm:text-base md:text-lg mb-7 font-light leading-relaxed max-w-xl mx-auto lg:mx-0">
                            اعمل في الوقت الذي يناسبك مع أفضل نسبة أرباح في السوق ودعم مستمر على مدار الساعة.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-8 text-right max-w-lg mx-auto lg:mx-0">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-clock text-brand-500 text-base shrink-0"></i>
                                <span class="text-white text-sm">مرونة في ساعات العمل</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-money-bill-trend-up text-brand-500 text-base shrink-0"></i>
                                <span class="text-white text-sm">أعلى عمولة في السوق</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-headset text-brand-500 text-base shrink-0"></i>
                                <span class="text-white text-sm">دعم فني للكباتن 24/7</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-award text-brand-500 text-base shrink-0"></i>
                                <span class="text-white text-sm">حوافز وتكريمات شهرية</span>
                            </div>
                        </div>

                        <a href="https://play.google.com/store/apps/details?id=com.app.sareadriver" class="inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-3.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm transition-all hover:scale-[1.02] shadow-xl shadow-brand-500/20">
                            <i class="fa-solid fa-id-card"></i>
                            سجل الآن كـ كابتن
                        </a>
                    </div>

                    <!-- Driver Card Mockup -->
                    <div class="lg:col-span-5 flex justify-center order-1 lg:order-2">
                        <div class="w-full max-w-xs sm:max-w-sm bg-slate-900 rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
                            <div class="p-5 flex flex-col gap-4">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 bg-brand-500/10 rounded-full flex items-center justify-center text-brand-500">
                                            <i class="fa-solid fa-car text-xl"></i>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] text-slate-400 block">لوحة الكابتن</span>
                                            <span class="text-sm font-bold text-white">كابتن سريع</span>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold">متصل 🟢</span>
                                </div>

                                <div class="bg-slate-800/80 border border-white/5 rounded-2xl p-4">
                                    <div class="flex justify-between text-xs text-slate-400 mb-2">
                                        <span>أرباح اليوم</span>
                                        <span>عدد الرحلات</span>
                                    </div>
                                    <div class="flex justify-between items-baseline mb-3">
                                        <span class="text-xl sm:text-2xl font-black text-white">٤٥٠ ج.م</span>
                                        <span class="text-base sm:text-lg font-bold text-brand-500">٨ رحلات</span>
                                    </div>
                                    <div class="w-full bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-brand-500 h-full w-[80%]"></div>
                                    </div>
                                    <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                                        <span>هدف اليوم (٥٠٠ ج.م)</span>
                                        <span>٨٠٪</span>
                                    </div>
                                </div>

                                {{-- <div class="w-full py-3 bg-brand-500 rounded-xl text-center text-white font-bold text-sm cursor-pointer hover:bg-brand-600 transition-colors">
                                    قبول الطلب الجديد (+٤٥ ج.م)
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════ DOWNLOAD ═══════════════════ -->
    <section id="download" class="py-16 sm:py-20 md:py-28 px-4 sm:px-6 max-w-7xl mx-auto">
        <div class="max-w-2xl mx-auto glass rounded-3xl p-7 sm:p-10 md:p-14 border border-white/5 shadow-2xl text-center relative overflow-hidden">
            <div class="absolute -top-10 left-1/2 -translate-x-1/2 w-48 h-48 glow-green rounded-full filter blur-3xl pointer-events-none"></div>
            <div class="relative z-10">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white mb-4">احصل على التطبيق مجانًا</h2>
                <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-8 max-w-md mx-auto">
                    استمتع بأولى رحلاتك بخصم يصل إلى ٥٠٪ بكود <span class="text-brand-500 font-bold">WELCOME50</span>
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                    <a href="https://play.google.com/store/apps/details?id=com.app.sareauser" target="_blank"
                       class="flex items-center justify-center gap-3 px-5 py-3.5 bg-slate-900 border border-white/10 rounded-xl hover:border-brand-500/50 transition-all">
                        <i class="fa-brands fa-google-play text-2xl text-white"></i>
                        <div class="flex flex-col text-right">
                            <span class="text-[9px] text-slate-400 uppercase font-semibold">تحميل من</span>
                            <span class="text-sm font-bold text-white">Google Play</span>
                        </div>
                    </a>
                    <a href="https://apps.apple.com/us/app/saree-taxi/id6758101605" target="_blank"
                       class="flex items-center justify-center gap-3 px-5 py-3.5 bg-slate-900 border border-white/10 rounded-xl hover:border-brand-500/50 transition-all">
                        <i class="fa-brands fa-apple text-2xl text-white"></i>
                        <div class="flex flex-col text-right">
                            <span class="text-[9px] text-slate-400 uppercase font-semibold">تحميل من</span>
                            <span class="text-sm font-bold text-white">App Store</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════ FOOTER ═══════════════════ -->
    <footer class="border-t border-white/5 bg-slate-950/90 pt-12 sm:pt-16 pb-8 sm:pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">

            <!-- Footer grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-8 sm:gap-10 mb-10 sm:mb-14">
                <!-- Brand -->
                <div class="sm:col-span-2 lg:col-span-4 flex flex-col text-right">
                    <div class="flex items-center gap-3 mb-4 justify-end lg:justify-end sm:justify-start">
                        <div class="w-9 h-9 bg-brand-500/10 rounded-lg flex items-center justify-center border border-brand-500/20 shrink-0">
                            <img src="{{ asset('logo/Layer_2_Image.png') }}" alt="سريع" class="w-6 h-6 object-contain">
                        </div>
                        <span class="text-base font-black text-white">سريع | Sare3</span>
                    </div>
                    <p class="text-slate-400 text-xs leading-relaxed mb-5 max-w-xs ml-auto sm:ml-0">
                        تطبيق النقل الذكي الرائد في مصر والوطن العربي لتوصيل المشاوير وتسهيل التحركات بكل يسر وموثوقية.
                    </p>
                    <div class="flex gap-3 justify-end sm:justify-start">
                        <a href="#" class="w-8 h-8 rounded-lg bg-slate-900 border border-white/5 flex items-center justify-center text-slate-400 hover:text-brand-500 hover:border-brand-500/20 transition-all"><i class="fa-brands fa-facebook-f text-xs"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-slate-900 border border-white/5 flex items-center justify-center text-slate-400 hover:text-brand-500 hover:border-brand-500/20 transition-all"><i class="fa-brands fa-twitter text-xs"></i></a>
                        <a href="#" class="w-8 h-8 rounded-lg bg-slate-900 border border-white/5 flex items-center justify-center text-slate-400 hover:text-brand-500 hover:border-brand-500/20 transition-all"><i class="fa-brands fa-instagram text-xs"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="lg:col-span-3 flex flex-col text-right">
                    <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-4 border-r-2 border-brand-500 pr-2">روابط سريعة</h4>
                    <ul class="flex flex-col gap-2.5 text-xs text-slate-400">
                        <li><a href="#features"    class="hover:text-brand-500 transition-colors">المميزات الأساسية</a></li>
                        <li><a href="#how-it-works" class="hover:text-brand-500 transition-colors">كيف تعمل المنصة</a></li>
                        <li><a href="#drivers"     class="hover:text-brand-500 transition-colors">فرص العمل ككابتن</a></li>
                        <li><a href="#download"    class="hover:text-brand-500 transition-colors">تحميل التطبيق</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="lg:col-span-2 flex flex-col text-right">
                    <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-4 border-r-2 border-brand-500 pr-2">الدعم</h4>
                    <ul class="flex flex-col gap-2.5 text-xs text-slate-400">
                        <li><a href="/support"        class="hover:text-brand-500 transition-colors">مركز المساعدة</a></li>
                        <li><a href="/privacy-policy"  class="hover:text-brand-500 transition-colors">سياسة الخصوصية</a></li>
                        <li><a href="/support"        class="hover:text-brand-500 transition-colors">شروط الاستخدام</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="lg:col-span-3 flex flex-col text-right">
                    <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-4 border-r-2 border-brand-500 pr-2">تواصل معنا</h4>
                    <ul class="flex flex-col gap-2.5 text-xs text-slate-400">
                        <li><i class="fa-solid fa-envelope text-brand-500 ml-2"></i>info@sare3.tld</li>
                        <li><i class="fa-solid fa-phone text-brand-500 ml-2"></i>+20 123 456 7890</li>
                        <li><i class="fa-solid fa-location-dot text-brand-500 ml-2"></i>المعادي، القاهرة، مصر</li>
                    </ul>
                </div>
            </div>

            <!-- ⭐ EL MANHAG FEATURED CARD ⭐ -->
            <div class="mb-10">
                <a href="" target="_blank" rel="noopener"
                   class="block w-full rounded-3xl border border-white/10 hover:border-brand-500/40 transition-all duration-300 shadow-2xl hover:shadow-brand-500/10 overflow-hidden group"
                   style="background: linear-gradient(135deg, rgba(16,185,129,0.06) 0%, rgba(15,20,35,0.9) 50%, rgba(99,102,241,0.05) 100%);">
                    <div class="flex flex-col sm:flex-row items-center gap-6 sm:gap-8 p-6 sm:p-8 md:p-10">

                        <!-- Logo -->
                        <div class="flex items-center justify-center shrink-0">
                            <div class="relative">
                                <div class="absolute inset-0 bg-brand-500/10 rounded-2xl blur-xl group-hover:bg-brand-500/20 transition-all duration-500"></div>
                                <img src="{{ asset('logo/elmanhag.jpg') }}"
                                     alt="El Manhag"
                                     class="relative h-24 sm:h-28 md:h-32 w-auto rounded-2xl object-contain border border-white/15 group-hover:border-brand-500/40 shadow-xl group-hover:scale-105 transition-all duration-300">
                            </div>
                        </div>

                        <!-- Text -->
                        <div class="flex-1 text-center sm:text-right">
                            <span class="text-brand-500 text-xs sm:text-sm font-bold uppercase tracking-widest block mb-2">Powered By — شريك التطوير الرسمي</span>
                            <h3 class="text-2xl sm:text-3xl md:text-4xl font-black text-white mb-2 group-hover:text-brand-400 transition-colors">
                                المنهج | El Manhag
                            </h3>
                            <p class="text-slate-400 text-xs sm:text-sm leading-relaxed max-w-md mx-auto sm:mx-0">
                                شريك التطوير والتشغيل الرسمي لمنصة سريع. نقدم أفضل الحلول البرمجية وتطبيقات الهاتف المتكاملة للمؤسسات.
                            </p>
                        </div>

                        <!-- Arrow -->
                        <div class="hidden md:flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-500/10 border border-brand-500/20 text-brand-500 text-xl group-hover:bg-brand-500 group-hover:text-white group-hover:scale-110 transition-all duration-300 shrink-0">
                            <i class="fa-solid fa-arrow-left"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Copyright -->
            <div class="border-t border-white/5 pt-6 flex flex-col sm:flex-row justify-between items-center gap-3 text-center">
                <p class="text-slate-500 text-xs">© 2026 جميع الحقوق محفوظة لشركة سريع المحدودة.</p>
                <p class="text-slate-600 text-[10px]">سريع للنقل الذكي • تجربة نقل فريدة وآمنة</p>
            </div>
        </div>
    </footer>

    <!-- ═══════════════════ SCRIPTS ═══════════════════ -->
    <script>
    (function () {
        const hamburger = document.getElementById('hamburger');
        const menu      = document.getElementById('mobile-menu');
        const icon      = document.getElementById('hamburger-icon');
        const mobileLinks = document.querySelectorAll('.mobile-link');

        function closeMenu() {
            menu.classList.remove('open');
            icon.className = 'fa-solid fa-bars text-base';
        }

        if (hamburger) {
            hamburger.addEventListener('click', function () {
                const isOpen = menu.classList.contains('open');
                if (isOpen) {
                    closeMenu();
                } else {
                    menu.classList.add('open');
                    icon.className = 'fa-solid fa-xmark text-base';
                }
            });
        }

        mobileLinks.forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    const offset = 90;
                    window.scrollTo({ top: target.offsetTop - offset, behavior: 'smooth' });
                }
            });
        });
    })();
    </script>
</body>
</html>
