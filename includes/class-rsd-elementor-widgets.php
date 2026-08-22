<?php
/**
 * RED SEA DIGITAL — Luxury Coastal Editorial Elementor Widgets Suite
 * Fully Bilingual (AR & EN) using Clean Anti-AI Light Luxury Design Tokens.
 */

if (!defined('ABSPATH')) exit;
if (!class_exists('\Elementor\Widget_Base')) return;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

function rsd_is_arabic() {
    if (is_rtl()) return true;
    if (function_exists('pll_current_language') && pll_current_language() === 'ar') return true;
    if (isset($_GET['lang']) && $_GET['lang'] === 'ar') return true;
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    if (strpos($uri, '/ar') !== false || strpos($uri, 'ar-') !== false || strpos($uri, 'الرئيسية') !== false) return true;
    if (function_exists('get_locale') && strpos(get_locale(), 'ar') !== false) return true;
    return false;
}

// 1. HERO WIDGET (Warm Coastal Editorial Light Canvas)
class RSD_Elementor_Hero_Widget extends Widget_Base {
    public function get_name() { return 'rsd_hero'; }
    public function get_title() { return esc_html__('RSD — Editorial Hero', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-banner'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Hero Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        $is_ar = rsd_is_arabic();
        $img_url = plugins_url('assets/hero-v1.webp', dirname(__FILE__));
        
        $badge = $is_ar ? '✦ أنظمة المبيعات والحجز المباشر ✦' : '✦ DIRECT SALES & BOOKING ENGINES ✦';
        $h1 = $is_ar ? 'امتلك نظام مبيعات وحجز مباشر..<br><span class="rsd-editorial-highlight">واستلم أرباحك كاملة بدون عمولات</span>' : 'Build Direct Sales & Booking Engines.<br><span class="rsd-editorial-highlight">Keep 100% of Your Revenue.</span>';
        $sub = $is_ar ? 'تخلص من عمولات الوسطاء ومنصات الحجز المرهقة. ربط مباشر بحسابك البنكي، واستقبال للمدفوعات، ورد آلي على العملاء عبر الواتساب على مدار 24 ساعة.' : 'Zero middleman commissions. Direct guest payments to your bank account with automated 24/7 WhatsApp AI response.';
        $btn_roi = $is_ar ? 'حاسبة توفير الأرباح ↓' : 'Calculate Retained Revenue ↓';
        $btn_chat = $is_ar ? 'تحدث مع المستشار التقني 💬' : 'Consult With Our Engineer 💬';
        ?>
        <section class="rsd-editorial-hero">
            <div class="rsd-editorial-container">
                <div class="rsd-editorial-badge"><span><?php echo esc_html($badge); ?></span></div>
                <h1 class="rsd-editorial-h1"><?php echo wp_kses_post($h1); ?></h1>
                <p class="rsd-editorial-sub"><?php echo esc_html($sub); ?></p>
                <div class="rsd-editorial-cta-wrap">
                    <button onclick="var el=document.getElementById('rsd-roi-section');if(el){el.scrollIntoView({behavior:'smooth'});}else{window.toggleRsdChatWidget(event);}" class="rsd-btn-primary">
                        <?php echo esc_html($btn_roi); ?>
                    </button>
                    <button onclick="window.toggleRsdChatWidget(event)" class="rsd-btn-secondary">
                        <?php echo esc_html($btn_chat); ?>
                    </button>
                </div>
                <div class="rsd-editorial-hero-media">
                    <img src="<?php echo esc_url($img_url); ?>" alt="Direct Booking Engine" class="rsd-editorial-mockup" loading="eager" width="1020" height="640" />
                </div>
            </div>
        </section>
        <?php
    }
}

// 2. TRUST BAR WIDGET (Clean Ecosystem Marquee)
class RSD_Elementor_Trust_Bar_Widget extends Widget_Base {
    public function get_name() { return 'rsd_trust_bar'; }
    public function get_title() { return esc_html__('RSD — Ecosystem Integrations', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-carousel'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Trust Bar Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        $is_ar = rsd_is_arabic();
        $badge = $is_ar ? 'تكاملات رسمية مع بوابات الدفع العالمية وأنظمة الإدارة السحابية' : 'GLOBAL PAYMENT GATEWAYS & CLOUD PMS INTEGRATIONS';
        ?>
        <section class="rsd-editorial-trust-sec">
            <div class="rsd-trust-header-label"><?php echo esc_html($badge); ?></div>
            <div class="rsd-integrations-marquee-wrapper">
                <div class="rsd-integrations-marquee-track">
                    <div class="rsd-editorial-chip"><span class="chip-icon"></span> Apple Pay</div>
                    <div class="rsd-editorial-chip"><span class="chip-icon">💳</span> Visa / Mastercard</div>
                    <div class="rsd-editorial-chip"><span class="chip-icon">💬</span> WhatsApp Cloud API</div>
                    <div class="rsd-editorial-chip"><span class="chip-icon">🏨</span> 2-Way PMS Channel Sync</div>
                    <div class="rsd-editorial-chip"><span class="chip-icon">🔒</span> Stripe 3D-Secure 2.0</div>
                    <div class="rsd-editorial-chip"><span class="chip-icon">⚡</span> Sub-Second Checkout</div>
                    <!-- Seamless duplicate -->
                    <div class="rsd-editorial-chip"><span class="chip-icon"></span> Apple Pay</div>
                    <div class="rsd-editorial-chip"><span class="chip-icon">💳</span> Visa / Mastercard</div>
                    <div class="rsd-editorial-chip"><span class="chip-icon">💬</span> WhatsApp Cloud API</div>
                    <div class="rsd-editorial-chip"><span class="chip-icon">🏨</span> 2-Way PMS Channel Sync</div>
                    <div class="rsd-editorial-chip"><span class="chip-icon">🔒</span> Stripe 3D-Secure 2.0</div>
                    <div class="rsd-editorial-chip"><span class="chip-icon">⚡</span> Sub-Second Checkout</div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 3. SECTORS WE SERVE (4 Cards Grid)
class RSD_Elementor_ROI_Calculator_Widget extends Widget_Base {
    public function get_name() { return 'rsd_roi_calc'; }
    public function get_title() { return esc_html__('RSD — Sectors & Financial ROI', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-gallery-grid'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Sectors Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        $is_ar = rsd_is_arabic();
        $badge = $is_ar ? 'القطاعات والحلول المخصصة' : 'SECTORS & BESPOKE SOLUTIONS';
        $title = $is_ar ? 'أنظمة مهندسة خصيصاً لتنمية أعمالك المباشرة' : 'Architected Specifically for Your Direct Business Growth';
        $sub = $is_ar ? 'حلول عملية متكاملة تلبي طبيعة كل نشاط لزيادة المبيعات والتحصيل الفوري بدون وسيط.' : 'Tailored high-converting infrastructure built to maximize direct revenue across high-value sectors.';
        ?>
        <section class="rsd-editorial-sec" id="rsd-sectors-section">
            <div class="rsd-editorial-container">
                <div class="rsd-section-heading-wrap">
                    <div class="rsd-editorial-badge"><span><?php echo esc_html($badge); ?></span></div>
                    <h2 class="rsd-editorial-h2"><?php echo esc_html($title); ?></h2>
                    <p class="rsd-editorial-sub"><?php echo esc_html($sub); ?></p>
                </div>
                
                <!-- 4 Cards Grid -->
                <div class="rsd-sectors-grid">
                    <!-- Card 1 -->
                    <div class="rsd-sector-card">
                        <div class="rsd-sector-icon">🛍️</div>
                        <h3 class="rsd-sector-title"><?php echo $is_ar ? 'المتاجر الإلكترونية الفاخرة' : 'High-Converting E-Commerce'; ?></h3>
                        <p class="rsd-sector-desc"><?php echo $is_ar ? 'بيع مباشر سريع، دفع فوري بـ Apple Pay والبطاقات، ومتابعة آلية لحالة الشحن وتأكيد الطلبات عبر الواتساب.' : 'Sub-second checkout with Apple Pay, multi-currency processing, and automated WhatsApp shipment notifications.'; ?></p>
                    </div>
                    <!-- Card 2 -->
                    <div class="rsd-sector-card">
                        <div class="rsd-sector-icon">🏨</div>
                        <h3 class="rsd-sector-title"><?php echo $is_ar ? 'الفنادق والأنشطة السياحية' : 'Hotels & Tourism Operators'; ?></h3>
                        <p class="rsd-sector-desc"><?php echo $is_ar ? 'محرك حجز غرف ورحلات فوري متصل بالـ PMS، يحميك من عمولات منصات الحجز التي تصل إلى 20%.' : 'Zero-commission direct room & excursion booking engine with 2-way PMS sync eliminating double bookings.'; ?></p>
                    </div>
                    <!-- Card 3 -->
                    <div class="rsd-sector-card">
                        <div class="rsd-sector-icon">💼</div>
                        <h3 class="rsd-sector-title"><?php echo $is_ar ? 'الشركات والمكاتب الاستشارية' : 'Consultancies & Corporate Firms'; ?></h3>
                        <p class="rsd-sector-desc"><?php echo $is_ar ? 'استقبال طلبات العملاء، حجز الاستشارات المدفوعة، وتوليد عروض الأسعار وفواتير العقود بصورة تلقائية.' : 'Automated inbound qualification, paid client appointments, and instant contractual quote generation.'; ?></p>
                    </div>
                    <!-- Card 4 -->
                    <div class="rsd-sector-card">
                        <div class="rsd-sector-icon">🩺</div>
                        <h3 class="rsd-sector-title"><?php echo $is_ar ? 'المراكز التدريبية والعيادات' : 'Clinics & Training Academies'; ?></h3>
                        <p class="rsd-sector-desc"><?php echo $is_ar ? 'تنظيم المواعيد والجلسات، تأكيد الحجوزات المسبقة، وإرسال تنبيهات المواعيد للعملاء عبر الواتساب لتقليل الإلغاء.' : 'Online session scheduling, deposit collection, and automated WhatsApp appointment reminders.'; ?></p>
                    </div>
                </div>

                <!-- ROI Calculator Box (Clean Light Cards) -->
                <div class="rsd-roi-wrapper" id="rsd-roi-section" style="margin-top: 60px;">
                    <div class="rsd-roi-light-card">
                        <div class="rsd-roi-card-header">
                            <span class="rsd-card-heading"><?php echo $is_ar ? 'حاسبة توفير العمولات' : 'Direct Revenue Retention Simulator'; ?></span>
                            <span class="rsd-pill-clean"><?php echo $is_ar ? 'حساب فوري' : 'Live Calculator'; ?></span>
                        </div>
                        <div class="rsd-slider-group">
                            <div class="rsd-slider-label"><span><?php echo $is_ar ? 'عدد الوحدات / الغرف / العمليات اليومية' : 'Rooms / Daily Transactions'; ?></span><strong id="valRooms" class="rsd-stat-highlight">60</strong></div>
                            <input type="range" id="rangeRooms" min="5" max="300" value="60" oninput="calcRoi()" class="rsd-clean-slider" />
                        </div>
                        <div class="rsd-slider-group">
                            <div class="rsd-slider-label"><span><?php echo $is_ar ? 'متوسط قيمة العملية / الليلة (USD)' : 'Average Order / Rate (USD)'; ?></span><strong id="valAdr" class="rsd-stat-highlight">$120</strong></div>
                            <input type="range" id="rangeAdr" min="30" max="1000" step="10" value="120" oninput="calcRoi()" class="rsd-clean-slider" />
                        </div>
                        <div class="rsd-slider-group">
                            <div class="rsd-slider-label"><span><?php echo $is_ar ? 'نسبة عمولة الوسيط الحالية' : 'Current Middleman Commission %'; ?></span><strong id="valComm" class="rsd-stat-highlight">18%</strong></div>
                            <input type="range" id="rangeComm" min="10" max="30" value="18" oninput="calcRoi()" class="rsd-clean-slider" />
                        </div>
                    </div>

                    <div class="rsd-roi-light-card rsd-roi-result-card">
                        <div class="rsd-roi-card-header">
                            <span class="rsd-card-heading"><?php echo $is_ar ? 'الأرباح المستردة لصالحك' : 'Estimated Direct Savings'; ?></span>
                            <span class="rsd-pill-clean-green"><?php echo $is_ar ? 'عمولة 0%' : '0% Commission'; ?></span>
                        </div>
                        <div class="rsd-result-block">
                            <div class="rsd-result-sub"><?php echo $is_ar ? 'إجمالي الوفورات السنوية المستردة' : 'ANNUAL REVENUE RETAINED'; ?></div>
                            <div class="rsd-result-value" id="outAnnual">$132,451 USD</div>
                            <div class="rsd-result-note" id="outMonthly"><?php echo $is_ar ? '+ 11,038 دولار شهرياً مستردة في حسابك' : '+ $11,038 USD / mo retained'; ?></div>
                        </div>
                        <button onclick="window.toggleRsdChatWidget(event)" class="rsd-btn-primary" style="width: 100%; margin-top: 20px;">
                            <?php echo $is_ar ? 'احصل على تحليل مالي مخصص لمشروعك →' : 'Request A Custom Architecture Audit →'; ?>
                        </button>
                    </div>
                </div>

            </div>
        </section>
        <script>
        function calcRoi() {
            var r = parseInt(document.getElementById('rangeRooms').value) || 60;
            var a = parseInt(document.getElementById('rangeAdr').value) || 120;
            var c = parseInt(document.getElementById('rangeComm').value) || 18;
            
            document.getElementById('valRooms').innerText = r;
            document.getElementById('valAdr').innerText = '$' + a;
            document.getElementById('valComm').innerText = c + '%';

            var total = r * a * 365 * 0.70 * 0.40;
            var saved = Math.round(total * (c / 100));
            var monthly = Math.round(saved / 12);

            document.getElementById('outAnnual').innerText = '$' + saved.toLocaleString() + ' USD';
            var isAr = <?php echo $is_ar ? 'true' : 'false'; ?>;
            if (isAr) {
                document.getElementById('outMonthly').innerText = '+ ' + monthly.toLocaleString() + ' دولار شهرياً مستردة في حسابك';
            } else {
                document.getElementById('outMonthly').innerText = '+ $' + monthly.toLocaleString() + ' USD / mo retained';
            }
        }
        </script>
        <?php
    }
}

// 4. HOW WE WORK (3 Simple Steps)
class RSD_Elementor_Roadmap_Widget extends Widget_Base {
    public function get_name() { return 'rsd_roadmap'; }
    public function get_title() { return esc_html__('RSD — 3 Simple Steps', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-flow'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Steps Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        $is_ar = rsd_is_arabic();
        $badge = $is_ar ? 'خطوات العمل الواضحة' : 'HOW WE WORK';
        $title = $is_ar ? '3 خطوات بسيطة لامتلاك نظامك المستقل' : '3 Simple Steps to Full Direct Revenue Ownership';
        $sub = $is_ar ? 'منهجية هندسية واضحة تنقلك من الاعتماد على الوسطاء إلى الاستقلال المالي الكامل خلال 7 إلى 14 يوماً.' : 'A battle-tested 3-stage protocol delivering your turnkey infrastructure in 7 to 14 days.';
        ?>
        <section class="rsd-editorial-sec rsd-steps-sec">
            <div class="rsd-editorial-container">
                <div class="rsd-section-heading-wrap">
                    <div class="rsd-editorial-badge"><span><?php echo esc_html($badge); ?></span></div>
                    <h2 class="rsd-editorial-h2"><?php echo esc_html($title); ?></h2>
                    <p class="rsd-editorial-sub"><?php echo esc_html($sub); ?></p>
                </div>

                <div class="rsd-steps-grid">
                    <div class="rsd-step-card">
                        <div class="rsd-step-number">01</div>
                        <h3 class="rsd-step-title"><?php echo $is_ar ? 'تحليل نشاطك وفحص العمولات المهدرة' : 'Business Audit & Commission Leakage Review'; ?></h3>
                        <p class="rsd-step-desc"><?php echo $is_ar ? 'ندرس هيكل مبيعاتك الحالي، نحدد نسب العمولات التي تذهب للمنصات الوسيطة، ونرسم خطة التحول المباشر.' : 'Comprehensive audit of your current channels, transaction flows, and middleman fee leakages.'; ?></p>
                    </div>
                    <div class="rsd-step-card">
                        <div class="rsd-step-number">02</div>
                        <h3 class="rsd-step-title"><?php echo $is_ar ? 'التصميم والربط التقني لبوابات الدفع والواتساب' : 'Bespoke Engineering & Payment Integration'; ?></h3>
                        <p class="rsd-step-desc"><?php echo $is_ar ? 'نبني واجهة سريعة وخاصة بهويتك، نربط بوابات الدفع المباشرة بحسابك، ونفعل مساعد الواتساب الذكي للرد الفوري.' : 'Custom fast-loading UI connected to your bank/Stripe with 24/7 automated WhatsApp AI concierge.'; ?></p>
                    </div>
                    <div class="rsd-step-card">
                        <div class="rsd-step-number">03</div>
                        <h3 class="rsd-step-title"><?php echo $is_ar ? 'التشغيل وزيادة الأرباح على مدار 24 ساعة' : 'Turnkey Launch & 24/7 Autonomous Revenue'; ?></h3>
                        <p class="rsd-step-desc"><?php echo $is_ar ? 'إطلاق النظام بالكامل، تدريب فريقك على لوحة التحكم، وبدء استقبال الحجوزات والمبيعات مع ضمان الأداء.' : 'Full system activation, team handover, and autonomous 24/7 direct customer checkout with guarantee.'; ?></p>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 5. TESTIMONIALS WIDGET (Warm Editorial Cards)
class RSD_Elementor_Testimonials_Widget extends Widget_Base {
    public function get_name() { return 'rsd_testimonials'; }
    public function get_title() { return esc_html__('RSD — Client Trust', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-testimonial-carousel'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Testimonials Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        $is_ar = rsd_is_arabic();
        $badge = $is_ar ? 'تجارب العملاء والنتائج الواقعية' : 'CLIENT EXPERIENCES & TRUST';
        $title = $is_ar ? 'ماذا يقول شركاؤنا عن التحول المباشر؟' : 'What Our Partners Say About Direct Ownership';
        $sub = $is_ar ? 'نتائج موثقة من منشآت سياحية ومتاجر إلكترونية استعادت كامل أرباحها وبيانات عملائها.' : 'Documented outcomes from hospitality and e-commerce leaders who reclaimed 100% of their revenue.';
        ?>
        <section class="rsd-editorial-sec rsd-trust-editorial-sec">
            <div class="rsd-editorial-container">
                <div class="rsd-section-heading-wrap">
                    <div class="rsd-editorial-badge"><span><?php echo esc_html($badge); ?></span></div>
                    <h2 class="rsd-editorial-h2"><?php echo esc_html($title); ?></h2>
                    <p class="rsd-editorial-sub"><?php echo esc_html($sub); ?></p>
                </div>

                <div class="rsd-testimonials-editorial-grid">
                    <div class="rsd-t-editorial-card">
                        <div class="rsd-t-stars">★★★★★</div>
                        <p class="rsd-t-text"><?php echo $is_ar ? '"الانتقال لنظام الحجز المباشر من Red Sea Digital وفّر علينا أكثر من 22% من العمولات التي كنا ندفعها شهرياً للمنصات."' : '"Transitioning to Red Sea Digital direct booking engine saved us over 22% in monthly OTA commissions."'; ?></p>
                        <div class="rsd-t-user-info">
                            <div class="rsd-t-avatar-box">KM</div>
                            <div>
                                <h4 class="rsd-t-name"><?php echo $is_ar ? 'كابتن كريم منصور' : 'Capt. Karim Mansour'; ?></h4>
                                <span class="rsd-t-role"><?php echo $is_ar ? 'المدير التنفيذي — Red Sea Diving' : 'Managing Director — Red Sea Diving'; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="rsd-t-editorial-card">
                        <div class="rsd-t-stars">★★★★★</div>
                        <p class="rsd-t-text"><?php echo $is_ar ? '"مساعد الواتساب الذكي يستقبل استفسارات العملاء ويؤكد الدفع حتى في أوقات متأخرة من الليل دون أي تدخل بشري."' : '"The WhatsApp AI concierge captures inquiries instantly and confirms checkout even at midnight."'; ?></p>
                        <div class="rsd-t-user-info">
                            <div class="rsd-t-avatar-box">OS</div>
                            <div>
                                <h4 class="rsd-t-name"><?php echo $is_ar ? 'عمر سليمان' : 'Omar Soliman'; ?></h4>
                                <span class="rsd-t-role"><?php echo $is_ar ? 'مدير العمليات — Desert Oasis' : 'Operations Lead — Desert Oasis'; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="rsd-t-editorial-card">
                        <div class="rsd-t-stars">★★★★★</div>
                        <p class="rsd-t-text"><?php echo $is_ar ? '"الربط المباشر مع نظام إدارة الغرف (PMS) منع حدوث أي تعارض في المواعيد وسرّع من تجربة النزلاء."' : '"2-Way PMS synchronization eliminated double bookings and saved dozens of manual admin hours."'; ?></p>
                        <div class="rsd-t-user-info">
                            <div class="rsd-t-avatar-box">TA</div>
                            <div>
                                <h4 class="rsd-t-name"><?php echo $is_ar ? 'طارق السيد' : 'Tarek Al-Sayed'; ?></h4>
                                <span class="rsd-t-role"><?php echo $is_ar ? 'المدير العام — Coral View Luxury' : 'General Manager — Coral View Luxury'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 6. MATRIX WIDGET (Direct vs Third-Party Comparison Table)
class RSD_Elementor_Matrix_Widget extends Widget_Base {
    public function get_name() { return 'rsd_matrix'; }
    public function get_title() { return esc_html__('RSD — Real Comparison Table', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-table'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Matrix Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        $is_ar = rsd_is_arabic();
        $badge = $is_ar ? 'المقارنة الواقعية' : 'THE REAL DIFFERENCE';
        $title = $is_ar ? 'الفارق الحقيقي بين النظام المباشر والمنصات الوسيطة' : 'The Real Difference in Revenue Ownership';
        ?>
        <section class="rsd-editorial-sec rsd-matrix-editorial-sec">
            <div class="rsd-editorial-container">
                <div class="rsd-section-heading-wrap">
                    <div class="rsd-editorial-badge"><span><?php echo esc_html($badge); ?></span></div>
                    <h2 class="rsd-editorial-h2"><?php echo esc_html($title); ?></h2>
                </div>

                <div class="rsd-matrix-editorial-card">
                    <!-- Legacy Column -->
                    <div class="rsd-matrix-side side-legacy">
                        <div class="rsd-matrix-header-side">
                            <span class="rsd-badge-status-red"><?php echo $is_ar ? 'المنصات الوسيطة (الوضع التقليدي)' : 'Third-Party Platforms (Old Way)'; ?></span>
                        </div>
                        <ul class="rsd-matrix-bullet-list">
                            <li class="bullet-cross"><strong><?php echo $is_ar ? 'عمولات مهدرة من 15% إلى 30%:' : '15% to 30% Recurring Commissions:'; ?></strong> <?php echo $is_ar ? 'استقطاع مستمر من أرباح كل حجز أو عملية بيع لصالح المنصة.' : 'Continuous deduction from your revenue on every single transaction.'; ?></li>
                            <li class="bullet-cross"><strong><?php echo $is_ar ? 'فقدان بيانات العملاء:' : 'Guest Data Held Hostage:'; ?></strong> <?php echo $is_ar ? 'المنصة تحتفظ بأرقام وإيميلات عملائك لإعادة استهدافهم لمنافسيك.' : 'Zero ownership of client contact details or remarketing history.'; ?></li>
                            <li class="bullet-cross"><strong><?php echo $is_ar ? 'تأخر استلام الأموال:' : 'Delayed Payouts:'; ?></strong> <?php echo $is_ar ? 'احتجاز أموالك لأسابيع مع رسوم تحويل ومعالجة إضافية.' : 'Revenue held for weeks with recurring settlement fees.'; ?></li>
                        </ul>
                    </div>

                    <!-- Direct Column -->
                    <div class="rsd-matrix-side side-direct">
                        <div class="rsd-matrix-header-side">
                            <span class="rsd-badge-status-green"><?php echo $is_ar ? 'نظام Red Sea Digital المباشر' : 'Direct Booking Engine (Red Sea Digital)'; ?></span>
                        </div>
                        <ul class="rsd-matrix-bullet-list">
                            <li class="bullet-check"><strong><?php echo $is_ar ? 'عمولة 0% على كافة العمليات:' : '0% Middleman Commission:'; ?></strong> <?php echo $is_ar ? 'تستلم 100% من أرباحك مباشرة في حسابك البنكي بدون أي وسيط.' : 'Keep 100% of your earnings deposited directly into your corporate bank.'; ?></li>
                            <li class="bullet-check"><strong><?php echo $is_ar ? 'ملكية كاملة ومطلقة لبيانات العملاء:' : '100% Customer Data Ownership:'; ?></strong> <?php echo $is_ar ? 'قاعدة بيانات مستقلة تمكنك من إعادة استهداف عملائك وبناء ولائهم مجاناً.' : 'Independent CRM empowering direct remarketing and long-term brand loyalty.'; ?></li>
                            <li class="bullet-check"><strong><?php echo $is_ar ? 'تحصيل مالي فوري وآلي 24/7:' : 'Instant Multi-Currency Payouts:'; ?></strong> <?php echo $is_ar ? 'دفع سريع بـ Apple Pay والبطاقات البنكية مع تأكيد فوري عبر الواتساب.' : 'Instant frictionless mobile checkout with real-time WhatsApp confirmations.'; ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 7. FAQS ACCORDION (Clean High-Contrast Accordion)
class RSD_Elementor_Cal_Booking_Widget extends Widget_Base {
    public function get_name() { return 'rsd_cal_booking'; }
    public function get_title() { return esc_html__('RSD — FAQs Accordion', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-accordion'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'FAQs Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        $is_ar = rsd_is_arabic();
        $badge = $is_ar ? 'الأسئلة الشائعة والإجابات التقنية' : 'FREQUENTLY ASKED QUESTIONS';
        $title = $is_ar ? 'كل ما تحتاج معرفته عن استقلالية نظامك' : 'Everything You Need to Know About Direct Architecture';
        ?>
        <section class="rsd-editorial-sec rsd-faq-editorial-sec">
            <div class="rsd-editorial-container">
                <div class="rsd-section-heading-wrap">
                    <div class="rsd-editorial-badge"><span><?php echo esc_html($badge); ?></span></div>
                    <h2 class="rsd-editorial-h2"><?php echo esc_html($title); ?></h2>
                </div>

                <div class="rsd-faq-clean-list">
                    <!-- Q1 -->
                    <div class="rsd-faq-card active">
                        <div class="rsd-faq-q-row" onclick="this.parentElement.classList.toggle('active')">
                            <span><?php echo $is_ar ? 'هل النظام سهل الإدارة لفريقي دون خبرة برمجية سابقة؟' : 'Is the system easy to manage without prior technical experience?'; ?></span>
                            <span class="rsd-faq-toggle-icon">+</span>
                        </div>
                        <div class="rsd-faq-a-row">
                            <?php echo $is_ar ? 'نعم تماماً. يتم تسليم لوحة تحكم عربية بالكامل وسهلة الاستخدام تتيح لك تعديل الأسعار، مراجعة الحجوزات، وسحب التقارير المالية بضغطة زر واحدة مع تدريب عملي كامل لفريقك.' : 'Yes. We deliver an intuitive visual dashboard allowing you to adjust rates, view reservations, and download financial reports with zero coding required.'; ?>
                        </div>
                    </div>

                    <!-- Q2 -->
                    <div class="rsd-faq-card">
                        <div class="rsd-faq-q-row" onclick="this.parentElement.classList.toggle('active')">
                            <span><?php echo $is_ar ? 'كيف تصل أموال المبيعات والحجوزات إلى حسابي البنكي؟' : 'How are customer payments transferred to my bank account?'; ?></span>
                            <span class="rsd-faq-toggle-icon">+</span>
                        </div>
                        <div class="rsd-faq-a-row">
                            <?php echo $is_ar ? 'يتم ربط بوابات الدفع الرسمية (Stripe، Apple Pay، أو بواباتك المحلية) مباشرة بحسابك البنكي التجاري، وتصلك أموال العملاء فورياً دون أن تمر عبر طرف ثالث.' : '100% of guest payments are routed directly to your corporate bank account via integrated gateways (Stripe, Apple Pay, Visa, Mastercard) with zero middleman holding periods.'; ?>
                        </div>
                    </div>

                    <!-- Q3 -->
                    <div class="rsd-faq-card">
                        <div class="rsd-faq-q-row" onclick="this.parentElement.classList.toggle('active')">
                            <span><?php echo $is_ar ? 'كيف يستجيب مساعد الواتساب الذكي للعملاء على مدار 24 ساعة؟' : 'How does the WhatsApp AI assistant respond to inquiries 24/7?'; ?></span>
                            <span class="rsd-faq-toggle-icon">+</span>
                        </div>
                        <div class="rsd-faq-a-row">
                            <?php echo $is_ar ? 'يتم تدريب الذكاء الاصطناعي على كافة تفاصيل نشاطك وأسعارك وخدماتك عبر WhatsApp Business Cloud API الرسمي، ليرد بلهجة احترافية دقيقة، يرسل روابط الحجز والدفع، ويؤكد العمليات فوراً.' : 'Trained on your business catalog via the official WhatsApp Business Cloud API, the assistant answers queries, sends checkout links, and confirms bookings with human-grade precision.'; ?>
                        </div>
                    </div>

                    <!-- Q4 -->
                    <div class="rsd-faq-card">
                        <div class="rsd-faq-q-row" onclick="this.parentElement.classList.toggle('active')">
                            <span><?php echo $is_ar ? 'كم يستغرق تجهيز النظام وإطلاقه للعمل؟' : 'How long does turnkey implementation and launch take?'; ?></span>
                            <span class="rsd-faq-toggle-icon">+</span>
                        </div>
                        <div class="rsd-faq-a-row">
                            <?php echo $is_ar ? 'يتم إنجاز النظام بالكامل وتفعيله خلال 7 إلى 14 يوم عمل، شاملاً التصميم المخصص، ربط بوابات الدفع، وإعداد وبرمجة مساعد الواتساب.' : 'Our engineering protocol delivers your entire custom infrastructure turnkey in 7 to 14 business days, fully tested and ready for live transactions.'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 8. FINAL CTA WIDGET (Warm Clean CTA & Guarantee)
class RSD_Elementor_Final_CTA_Widget extends Widget_Base {
    public function get_name() { return 'rsd_final_cta'; }
    public function get_title() { return esc_html__('RSD — Final Editorial CTA', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-call-to-action'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'CTA Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        $is_ar = rsd_is_arabic();
        $guarantee = $is_ar ? '🛡️ ضمان استرداد كامل خلال 30 يوماً في حال عدم مطابقة سرعة وأداء النظام لتطلعاتك.' : '🛡️ 30-Day Performance Guarantee: 100% full refund if speed & architecture do not meet your exact requirements.';
        $title = $is_ar ? 'ابدأ في امتلاك مبيعاتك وأرباحك المباشرة اليوم' : 'Start Reclaiming Your Direct Revenue Today';
        $sub = $is_ar ? 'تواصل مع فريقنا الهندسي لمناقشة متطلبات نشاطك واستعراض دراسة الجدوى المناسبة لك.' : 'Consult with our engineering team to review your custom direct sales and booking architecture.';
        $btn = $is_ar ? 'احجز استشارتك المجانية الآن 🚀' : 'Book Your Free Consultation 🚀';
        ?>
        <section class="rsd-editorial-cta-sec">
            <div class="rsd-editorial-container" style="text-align: center;">
                <div class="rsd-guarantee-pill-clean"><?php echo esc_html($guarantee); ?></div>
                <h2 class="rsd-editorial-cta-h2"><?php echo esc_html($title); ?></h2>
                <p class="rsd-editorial-cta-sub"><?php echo esc_html($sub); ?></p>
                <button onclick="window.toggleRsdChatWidget(event)" class="rsd-btn-primary rsd-btn-large">
                    <?php echo esc_html($btn); ?>
                </button>
            </div>
        </section>
        <?php
    }
}
