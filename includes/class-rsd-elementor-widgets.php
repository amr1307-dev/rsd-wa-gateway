<?php
/**
 * RED SEA DIGITAL — Custom Elementor Dynamic Widgets Suite
 */

if (!defined('ABSPATH')) exit;
if (!class_exists('\Elementor\Widget_Base')) return;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Repeater;

// 1. HERO WIDGET
class RSD_Elementor_Hero_Widget extends Widget_Base {
    public function get_name() { return 'rsd_hero'; }
    public function get_title() { return esc_html__('RSD — Luxury SaaS Hero', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-banner'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => esc_html__('Hero Content', 'redsea-ai-engine'),
            'tab' => Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control('badge_text', ['label' => 'Badge', 'type' => Controls_Manager::TEXT, 'default' => '✦ Direct Booking']);
        $this->add_control('title', ['label' => 'Title (HTML)', 'type' => Controls_Manager::TEXTAREA, 'default' => 'Build Direct Booking Engines.<br><span class="rsd-saas-gradient-text">Own 100% of Your Revenue.</span>']);
        $this->add_control('subtitle', ['label' => 'Subtitle', 'type' => Controls_Manager::TEXTAREA, 'default' => 'Zero middleman commissions. Direct guest payments and automated 24/7 AI response on WhatsApp.']);
        $this->add_control('image', ['label' => 'Mockup Image', 'type' => Controls_Manager::MEDIA, 'default' => ['url' => plugins_url('assets/hero-v1.webp', dirname(__FILE__)) ]]);
        $this->add_control('primary_btn', ['label' => 'Primary Button', 'type' => Controls_Manager::TEXT, 'default' => 'Consult With Us →']);
        $this->add_control('secondary_btn', ['label' => 'Secondary Button', 'type' => Controls_Manager::TEXT, 'default' => 'View Live Showcase ✦']);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $img_url = !empty($settings['image']['url']) ? $settings['image']['url'] : plugins_url('assets/hero-v1.webp', dirname(__FILE__));
        ?>
        <section class="rsd-saas-hero">
            <div class="rsd-saas-hero-container">
                <div class="rsd-saas-pill"><span><?php echo esc_html($settings['badge_text']); ?></span></div>
                <h1 class="rsd-saas-h1"><?php echo wp_kses_post($settings['title']); ?></h1>
                <p class="rsd-saas-subtext"><?php echo esc_html($settings['subtitle']); ?></p>
                <div class="rsd-hero-showcase-wrapper">
                    <img src="<?php echo esc_url($img_url); ?>" alt="Direct Booking Engine" class="rsd-hero-master-img" loading="eager" width="1000" height="650" />
                </div>
                <div class="rsd-saas-cta-group">
                    <button onclick="var el=document.getElementById('rsd-booking-calendar');if(el){el.scrollIntoView({behavior:'smooth'});}else{window.toggleRsdChatWidget(event);}" class="shiny-cta">
                        <span><?php echo esc_html($settings['primary_btn']); ?></span>
                    </button>
                    <a href="https://redseadigital.pro/work/" class="rsd-btn-showcase"><?php echo esc_html($settings['secondary_btn']); ?></a>
                </div>
            </div>
        </section>
        <?php
    }
}

// 2. TRUST BAR WIDGET
class RSD_Elementor_Trust_Bar_Widget extends Widget_Base {
    public function get_name() { return 'rsd_trust_bar'; }
    public function get_title() { return esc_html__('RSD — Integrations Marquee', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-carousel'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Trust Bar Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->add_control('badge_text', ['label' => 'Badge', 'type' => Controls_Manager::TEXT, 'default' => '✦ GLOBAL HOSPITALITY & FINTECH ECOSYSTEM INTEGRATIONS']);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <section class="rsd-hero-trust-bar">
            <div class="rsd-trust-bar-header">
                <span class="rsd-trust-badge"><?php echo esc_html($settings['badge_text']); ?></span>
            </div>
            <div class="rsd-trust-wrapper">
                <div class="rsd-trust-track">
                    <div class="rsd-trust-chip"><span class="rsd-trust-icon"></span> Apple Pay</div>
                    <div class="rsd-trust-chip"><span class="rsd-trust-icon">💳</span> Visa / Mastercard</div>
                    <div class="rsd-trust-chip"><span class="rsd-trust-icon">💬</span> WhatsApp Cloud API</div>
                    <div class="rsd-trust-chip"><span class="rsd-trust-icon">🏨</span> 2-Way PMS Channel Sync</div>
                    <div class="rsd-trust-chip"><span class="rsd-trust-icon">🔒</span> Stripe 3D-Secure 2.0</div>
                    <div class="rsd-trust-chip"><span class="rsd-trust-icon">⚡</span> Sub-Second Checkout</div>
                    <!-- Duplicate for infinite drift -->
                    <div class="rsd-trust-chip"><span class="rsd-trust-icon"></span> Apple Pay</div>
                    <div class="rsd-trust-chip"><span class="rsd-trust-icon">💳</span> Visa / Mastercard</div>
                    <div class="rsd-trust-chip"><span class="rsd-trust-icon">💬</span> WhatsApp Cloud API</div>
                    <div class="rsd-trust-chip"><span class="rsd-trust-icon">🏨</span> 2-Way PMS Channel Sync</div>
                    <div class="rsd-trust-chip"><span class="rsd-trust-icon">🔒</span> Stripe 3D-Secure 2.0</div>
                    <div class="rsd-trust-chip"><span class="rsd-trust-icon">⚡</span> Sub-Second Checkout</div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 3. ROI CALCULATOR WIDGET
class RSD_Elementor_ROI_Calculator_Widget extends Widget_Base {
    public function get_name() { return 'rsd_roi_calc'; }
    public function get_title() { return esc_html__('RSD — ROI Financial Calculator', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-calculator'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Calculator Settings', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->add_control('pill', ['label' => 'Pill', 'type' => Controls_Manager::TEXT, 'default' => '✦ FINANCIAL ENGINE AUDIT']);
        $this->add_control('title', ['label' => 'Title', 'type' => Controls_Manager::TEXT, 'default' => 'Calculate Your Direct Revenue Growth']);
        $this->add_control('subtitle', ['label' => 'Subtitle', 'type' => Controls_Manager::TEXTAREA, 'default' => 'Simulate the exact profit retained by shifting 40% of your bookings from OTA intermediaries.']);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <section class="rsd-roi-section" id="rsd-roi-engine">
            <div class="rsd-roi-container">
                <div style="text-align:center;max-width:700px;margin:0 auto 44px auto;">
                    <div class="rsd-roi-pill"><?php echo esc_html($settings['pill']); ?></div>
                    <h2 class="rsd-roi-title"><?php echo esc_html($settings['title']); ?></h2>
                    <p class="rsd-roi-subtitle"><?php echo esc_html($settings['subtitle']); ?></p>
                </div>
                <div class="rsd-roi-calculator-wrap">
                    <div class="rsd-roi-box">
                        <div class="rsd-roi-box-header">
                            <span class="rsd-roi-box-title">ROI Calculator Inputs</span>
                            <span class="rsd-roi-box-badge">● Live Dynamic</span>
                        </div>
                        <div class="rsd-roi-field">
                            <div class="rsd-field-header"><span>Total Hotel Rooms / Slots</span><span class="rsd-val-highlight" id="valRooms">60</span></div>
                            <input type="range" id="rangeRooms" min="5" max="300" value="60" oninput="calculateRsdRoi()" class="rsd-slider">
                        </div>
                        <div class="rsd-roi-field">
                            <div class="rsd-field-header"><span>Average Daily Rate (ADR)</span><span class="rsd-val-highlight" id="valAdr">$120</span></div>
                            <input type="range" id="rangeAdr" min="30" max="1000" step="10" value="120" oninput="calculateRsdRoi()" class="rsd-slider">
                        </div>
                        <div class="rsd-roi-field">
                            <div class="rsd-field-header"><span>Current OTA Commission %</span><span class="rsd-val-highlight" id="valCommission">18%</span></div>
                            <input type="range" id="rangeCommission" min="10" max="30" value="18" oninput="calculateRsdRoi()" class="rsd-slider">
                        </div>
                        <div class="rsd-roi-field">
                            <div class="rsd-field-header"><span>Target Direct Bookings Transition</span><span class="rsd-val-highlight" id="valDirect">40%</span></div>
                            <input type="range" id="rangeDirect" min="10" max="100" value="40" oninput="calculateRsdRoi()" class="rsd-slider">
                        </div>
                    </div>
                    <div class="rsd-roi-box output-box">
                        <div class="rsd-roi-box-header">
                            <span class="rsd-roi-box-title">Calculated Financial Output</span>
                            <span class="rsd-roi-box-badge">0% Commission</span>
                        </div>
                        <div class="rsd-output-group">
                            <div class="rsd-output-label">ESTIMATED ANNUAL REVENUE SAVED</div>
                            <div class="rsd-output-value" id="outAnnualSavings">$132,451 USD</div>
                            <div class="rsd-output-sub" id="outMonthlySavings">+ $11,038 USD / mo retained</div>
                        </div>
                        <div class="rsd-output-group">
                            <div class="rsd-output-label">DIRECT BOOKING GROWTH & SHARE</div>
                            <div class="rsd-output-value-green" id="outDirectGrowth">+40.0% Direct Share</div>
                            <div class="rsd-output-sub">100% ownership of guest records & direct rebooking</div>
                        </div>
                        <button onclick="var el=document.getElementById('rsd-booking-calendar');if(el){el.scrollIntoView({behavior:'smooth'});}else{window.toggleRsdChatWidget(event);}" class="rsd-output-btn">
                            Request A Custom Architecture Quote →
                        </button>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 4. ROADMAP WIDGET
class RSD_Elementor_Roadmap_Widget extends Widget_Base {
    public function get_name() { return 'rsd_roadmap'; }
    public function get_title() { return esc_html__('RSD — 4-Step Roadmap', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-flow'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Roadmap Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->add_control('pill', ['label' => 'Pill', 'type' => Controls_Manager::TEXT, 'default' => '✦ 4-STEP ARCHITECTURAL PROTOCOL']);
        $this->add_control('title', ['label' => 'Title', 'type' => Controls_Manager::TEXT, 'default' => 'Turnkey Roadmap to Full Direct Revenue']);
        $this->add_control('subtitle', ['label' => 'Subtitle', 'type' => Controls_Manager::TEXTAREA, 'default' => 'A battle-tested engineering protocol delivering your direct booking infrastructure turnkey in 7 to 14 days.']);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <section class="rsd-protocol-sec">
            <div class="rsd-protocol-container">
                <div style="text-align:center;max-width:680px;margin:0 auto 40px auto;">
                    <div class="rsd-roi-pill"><?php echo esc_html($settings['pill']); ?></div>
                    <h2 class="rsd-roi-title"><?php echo esc_html($settings['title']); ?></h2>
                    <p class="rsd-roi-subtitle"><?php echo esc_html($settings['subtitle']); ?></p>
                </div>
                <div class="rsd-protocol-grid">
                    <div class="rsd-protocol-card">
                        <div>
                            <div class="rsd-protocol-num">01</div>
                            <h3 class="rsd-protocol-title">Revenue & OTA Gap Audit</h3>
                            <p class="rsd-protocol-desc">Deep analysis of your current reservation mix, OTA commission leakages, and target direct share.</p>
                        </div>
                    </div>
                    <div class="rsd-protocol-card">
                        <div>
                            <div class="rsd-protocol-num">02</div>
                            <h3 class="rsd-protocol-title">Bespoke UX & Engine Design</h3>
                            <p class="rsd-protocol-desc">Crafting a high-conversion 3D booking engine tailored to your brand, optimized for sub-second checkout.</p>
                        </div>
                    </div>
                    <div class="rsd-protocol-card">
                        <div>
                            <div class="rsd-protocol-num">03</div>
                            <h3 class="rsd-protocol-title">2-Way PMS Channel Sync</h3>
                            <p class="rsd-protocol-desc">Direct two-way synchronization with your PMS preventing double bookings across all channels.</p>
                        </div>
                    </div>
                    <div class="rsd-protocol-card">
                        <div>
                            <div class="rsd-protocol-num">04</div>
                            <h3 class="rsd-protocol-title">AI Concierge & Launch</h3>
                            <p class="rsd-protocol-desc">Enabling autonomous 24/7 WhatsApp AI concierge to capture leads, confirm payments, and upsell amenities.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 5. TESTIMONIALS WIDGET
class RSD_Elementor_Testimonials_Widget extends Widget_Base {
    public function get_name() { return 'rsd_testimonials'; }
    public function get_title() { return esc_html__('RSD — Testimonials Marquee', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-testimonial-carousel'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Testimonials Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->add_control('badge', ['label' => 'Badge', 'type' => Controls_Manager::TEXT, 'default' => '✦ VERIFIED CLIENT RESULTS']);
        $this->add_control('title', ['label' => 'Title', 'type' => Controls_Manager::TEXT, 'default' => 'Trusted by Elite Hospitality Operators']);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <section class="rsd-saas-sec rsd-trust-sec">
            <div class="rsd-saas-container">
                <div style="text-align:center;max-width:700px;margin:0 auto 36px auto;">
                    <div class="rsd-roi-pill"><?php echo esc_html($settings['badge']); ?></div>
                    <h2 class="rsd-saas-title"><?php echo esc_html($settings['title']); ?></h2>
                </div>
                <div class="rsd-marquee-mask-wrap">
                    <div class="rsd-marquee-3col-grid">
                        <div class="rsd-t-track track-1">
                            <div class="rsd-t-card"><div class="rsd-t-stars">★★★★★</div><p class="rsd-t-quote">"Transitioning to Red Sea Digital direct booking engine completely freed us from 22% OTA commissions."</p><div class="rsd-t-author"><div class="rsd-t-avatar-box">KM</div><div><h4 class="rsd-t-name">Capt. Karim Mansour</h4><span class="rsd-t-role">Managing Director — Red Sea Diving</span></div></div></div>
                        </div>
                        <div class="rsd-t-track track-2">
                            <div class="rsd-t-card"><div class="rsd-t-stars">★★★★★</div><p class="rsd-t-quote">"Real-time two-way PMS channel sync saved hundreds of manual hours and eliminated double bookings."</p><div class="rsd-t-author"><div class="rsd-t-avatar-box">TA</div><div><h4 class="rsd-t-name">Tarek Al-Sayed</h4><span class="rsd-t-role">General Manager — Coral View Luxury Resort</span></div></div></div>
                        </div>
                        <div class="rsd-t-track track-3">
                            <div class="rsd-t-card"><div class="rsd-t-stars">★★★★★</div><p class="rsd-t-quote">"Multilingual voice and chat responses gave our European guests a 5-star concierge experience."</p><div class="rsd-t-author"><div class="rsd-t-avatar-box">ER</div><div><h4 class="rsd-t-name">Elena Rostova</h4><span class="rsd-t-role">Guest Experience Lead — Riviera Boutique</span></div></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 6. MATRIX WIDGET
class RSD_Elementor_Matrix_Widget extends Widget_Base {
    public function get_name() { return 'rsd_matrix'; }
    public function get_title() { return esc_html__('RSD — Comparison Matrix', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-table'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Matrix Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->add_control('title', ['label' => 'Title', 'type' => Controls_Manager::TEXT, 'default' => 'Traditional Middlemen vs Red Sea Digital Engine']);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <section class="rsd-saas-sec rsd-matrix-sec">
            <div class="rsd-saas-container">
                <h2 class="rsd-saas-title" style="text-align:center;margin-bottom:40px;"><?php echo esc_html($settings['title']); ?></h2>
                <div class="rsd-unified-matrix-card">
                    <div class="rsd-matrix-col col-old">
                        <div class="rsd-col-header">
                            <span class="rsd-col-badge badge-old">Legacy Method</span>
                            <h3 class="rsd-col-title">Third-Party Platforms (OTA)</h3>
                        </div>
                        <ul class="rsd-matrix-list">
                            <li class="rsd-matrix-item item-cross"><strong>15% to 30% Recurring Fees:</strong> Heavy continuous commissions on every single guest reservation.</li>
                            <li class="rsd-matrix-item item-cross"><strong>Guest Data Held Hostage:</strong> Zero ownership of direct email addresses, phone numbers, or guest history.</li>
                            <li class="rsd-matrix-item item-cross"><strong>Delayed Payouts:</strong> Revenue withheld for weeks with high dispute processing costs.</li>
                        </ul>
                    </div>
                    <div class="rsd-matrix-col col-new">
                        <div class="rsd-col-header">
                            <span class="rsd-col-badge badge-new">The Red Sea Digital Standard</span>
                            <h3 class="rsd-col-title">Direct Booking Architecture</h3>
                        </div>
                        <ul class="rsd-matrix-list">
                            <li class="rsd-matrix-item item-check"><strong>0% Commission Leaks:</strong> Keep 100% of room profits directly in your own bank account.</li>
                            <li class="rsd-matrix-item item-check"><strong>100% Guest Data Ownership:</strong> Independent CRM database empowering direct re-marketing & loyalty.</li>
                            <li class="rsd-matrix-item item-check"><strong>Instant Payouts:</strong> Direct multi-currency deposits into your Stripe/Bank with Apple Pay.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 7. CAL BOOKING WIDGET
class RSD_Elementor_Cal_Booking_Widget extends Widget_Base {
    public function get_name() { return 'rsd_cal_booking'; }
    public function get_title() { return esc_html__('RSD — Cal.com Live Scheduler', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-calendar'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Calendar Settings', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->add_control('pill', ['label' => 'Pill', 'type' => Controls_Manager::TEXT, 'default' => '✦ LIVE STRATEGY CONSULTATION']);
        $this->add_control('title', ['label' => 'Title', 'type' => Controls_Manager::TEXT, 'default' => 'Schedule Your 15-Minute Strategy Call']);
        $this->add_control('subtitle', ['label' => 'Subtitle', 'type' => Controls_Manager::TEXTAREA, 'default' => '15-minute direct strategy consultation with our technical architects to engineer your direct booking engine.']);
        $this->add_control('cal_link', ['label' => 'Cal.com Link', 'type' => Controls_Manager::TEXT, 'default' => 'edu-me-pkl28r/15min']);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $cal_link = !empty($settings['cal_link']) ? $settings['cal_link'] : 'edu-me-pkl28r/15min';
        ?>
        <section class="rsd-cal-booking-section" id="rsd-booking-calendar" style="background:#030712;padding:90px 20px;position:relative;">
            <div class="rsd-cal-container" style="max-width:1120px;margin:0 auto;position:relative;z-index:2;">
                <div style="text-align:center;margin-bottom:36px;">
                    <div class="rsd-roi-pill" style="display:inline-block;padding:6px 18px;background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.3);border-radius:9999px;color:#38BDF8;font-size:0.8rem;font-weight:700;margin-bottom:14px;">
                        <?php echo esc_html($settings['pill']); ?>
                    </div>
                    <h2 class="rsd-dark-h2" style="font-size:clamp(1.8rem, 3.5vw, 2.6rem);font-weight:800;color:#FFFFFF;margin:0 0 12px 0;">
                        <?php echo esc_html($settings['title']); ?>
                    </h2>
                    <p class="rsd-dark-subtext" style="color:#94A3B8;font-size:1.05rem;max-width:680px;margin:0 auto;line-height:1.65;">
                        <?php echo esc_html($settings['subtitle']); ?>
                    </p>
                </div>
                <div class="rsd-cal-frame-wrapper" style="background:#FFFFFF;border-radius:24px;border:1.5px solid rgba(255,255,255,0.15);box-shadow:0 30px 70px -15px rgba(0,0,0,0.6);overflow:hidden;min-height:680px;position:relative;">
                    <div style="width:100%;height:100%;min-height:680px;overflow:auto" id="my-cal-inline-15min"></div>
                    <script type="text/javascript">
                    (function (C, A, L) { let p = function (a, ar) { a.q.push(ar); }; let d = C.document; C.Cal = C.Cal || function () { let cal = C.Cal; let ar = arguments; if (!cal.loaded) { cal.ns = {}; cal.q = cal.q || []; d.head.appendChild(d.createElement("script")).src = A; cal.loaded = true; } if (ar[0] === L) { const api = function () { p(api, arguments); }; const namespace = ar[1]; api.q = api.q || []; if(typeof namespace === "string"){cal.ns[namespace] = cal.ns[namespace] || api;p(cal.ns[namespace], ar);p(cal, ["initNamespace", namespace]);} else p(cal, ar); return;} p(cal, ar); }; })(window, "https://app.cal.com/embed/embed.js", "init");
                    Cal("init", "15min", {origin:"https://app.cal.com"});
                    Cal.config = Cal.config || {};
                    Cal.config.forwardQueryParams = true;
                    Cal.ns["15min"]("inline", {
                        elementOrSelector:"#my-cal-inline-15min",
                        config: {"layout":"month_view","useSlotsViewOnSmallScreen":"true"},
                        calLink: "<?php echo esc_js($cal_link); ?>",
                    });
                    Cal.ns["15min"]("ui", {"hideEventTypeDetails":false,"layout":"month_view"});
                    </script>
                </div>
            </div>
        </section>
        <?php
    }
}

// 8. FINAL CTA WIDGET
class RSD_Elementor_Final_CTA_Widget extends Widget_Base {
    public function get_name() { return 'rsd_final_cta'; }
    public function get_title() { return esc_html__('RSD — Final CTA & Guarantee', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-call-to-action'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'CTA Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->add_control('guarantee', ['label' => 'Guarantee', 'type' => Controls_Manager::TEXT, 'default' => '🛡️ 30-Day Money-Back Guarantee: If you are not satisfied with speed & performance, receive a 100% full refund.']);
        $this->add_control('title', ['label' => 'Title', 'type' => Controls_Manager::TEXT, 'default' => 'Start Reclaiming Your Direct Revenue Today']);
        $this->add_control('subtitle', ['label' => 'Subtitle', 'type' => Controls_Manager::TEXTAREA, 'default' => 'Consult with our team to review your direct booking architecture.']);
        $this->add_control('btn_text', ['label' => 'Button', 'type' => Controls_Manager::TEXT, 'default' => 'Book Free Consultation 🚀']);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
        <section class="rsd-saas-dark-sec rsd-saas-cta-sec">
            <div class="rsd-saas-dark-container" style="text-align:center;">
                <div class="rsd-guarantee-pill"><?php echo esc_html($settings['guarantee']); ?></div>
                <h2 class="rsd-dark-h2" style="margin-top:24px;"><?php echo esc_html($settings['title']); ?></h2>
                <p class="rsd-dark-subtext" style="margin-bottom:32px;"><?php echo esc_html($settings['subtitle']); ?></p>
                <button onclick="var el=document.getElementById('rsd-booking-calendar');if(el){el.scrollIntoView({behavior:'smooth'});}else{window.toggleRsdChatWidget(event);}" class="rsd-saas-btn-primary" style="font-size:1.15rem; padding:18px 44px;">
                    <?php echo esc_html($settings['btn_text']); ?>
                </button>
            </div>
        </section>
        <?php
    }
}
