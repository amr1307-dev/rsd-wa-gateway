<?php
/**
 * RED SEA DIGITAL — Custom Elementor Dynamic Widgets Suite
 * Perfectly matching the Master Reference Luxury SaaS Architecture Design.
 */

if (!defined('ABSPATH')) exit;
if (!class_exists('\Elementor\Widget_Base')) return;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

// 1. HERO WIDGET (Light with subtle ambient gradient)
class RSD_Elementor_Hero_Widget extends Widget_Base {
    public function get_name() { return 'rsd_hero'; }
    public function get_title() { return esc_html__('RSD — Luxury SaaS Hero', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-banner'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Hero Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->add_control('badge_text', ['label' => 'Badge', 'type' => Controls_Manager::TEXT, 'default' => '✦ DIRECT BOOKING ENGINE SUITE ✦']);
        $this->add_control('title', ['label' => 'Title (HTML)', 'type' => Controls_Manager::TEXTAREA, 'default' => 'Build Direct Booking Engines.<br><span class="rsd-saas-gradient-text">Own 100% of Your Revenue.</span>']);
        $this->add_control('subtitle', ['label' => 'Subtitle', 'type' => Controls_Manager::TEXTAREA, 'default' => 'Zero middleman commissions. Direct guest payments and automated 24/7 AI response on WhatsApp.']);
        $this->add_control('image', ['label' => 'Mockup Image', 'type' => Controls_Manager::MEDIA, 'default' => ['url' => plugins_url('assets/hero-v1.webp', dirname(__FILE__)) ]]);
        $this->add_control('primary_btn', ['label' => 'Primary Button', 'type' => Controls_Manager::TEXT, 'default' => 'Consult With Us →']);
        $this->add_control('secondary_btn', ['label' => 'Secondary Button', 'type' => Controls_Manager::TEXT, 'default' => 'View Live Showcase ✦']);
        $this->end_controls_section();
    }

    protected function render() {
        $img_url = plugins_url('assets/hero-v1.webp', dirname(__FILE__));
        ?>
        <section class="rsd-saas-hero">
            <div class="rsd-hero-ambient-glow"></div>
            <div class="rsd-saas-hero-container">
                <div class="rsd-saas-pill"><span>✦ DIRECT BOOKING ENGINE SUITE ✦</span></div>
                <h1 class="rsd-saas-h1">Build Direct Booking Engines.<br><span class="rsd-saas-gradient-text">Own 100% of Your Revenue.</span></h1>
                <p class="rsd-saas-subtext">Zero middleman commissions. Direct guest payments and automated 24/7 AI response on WhatsApp.</p>
                <div class="rsd-hero-showcase-wrapper">
                    <img src="<?php echo esc_url($img_url); ?>" alt="Direct Booking Engine" class="rsd-hero-master-img" loading="eager" width="1000" height="650" />
                </div>
                <div class="rsd-saas-cta-group">
                    <button onclick="window.toggleRsdChatWidget(event)" class="shiny-cta">
                        <span>Consult With Us →</span>
                    </button>
                    <a href="https://redseadigital.pro/work/" class="rsd-btn-showcase">View Live Showcase ✦</a>
                </div>
            </div>
        </section>
        <?php
    }
}

// 2. TRUST BAR WIDGET (Dark Sleek Marquee)
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
        ?>
        <section class="rsd-marquee-section">
            <div class="rsd-marquee-header">
                <span class="rsd-marquee-badge">✦ GLOBAL HOSPITALITY & FINTECH ECOSYSTEM INTEGRATIONS</span>
            </div>
            <div class="rsd-marquee-wrapper">
                <div class="rsd-marquee-track">
                    <div class="rsd-marquee-item"><span class="rsd-marquee-icon"></span> Apple Pay</div>
                    <div class="rsd-marquee-item"><span class="rsd-marquee-icon">💳</span> Visa / Mastercard</div>
                    <div class="rsd-marquee-item"><span class="rsd-marquee-icon">💬</span> WhatsApp Cloud API</div>
                    <div class="rsd-marquee-item"><span class="rsd-marquee-icon">🏨</span> 2-Way PMS Channel Sync</div>
                    <div class="rsd-marquee-item"><span class="rsd-marquee-icon">🔒</span> Stripe 3D-Secure 2.0</div>
                    <div class="rsd-marquee-item"><span class="rsd-marquee-icon">⚡</span> Sub-Second Checkout</div>
                    <!-- Duplicate for infinite drift -->
                    <div class="rsd-marquee-item"><span class="rsd-marquee-icon"></span> Apple Pay</div>
                    <div class="rsd-marquee-item"><span class="rsd-marquee-icon">💳</span> Visa / Mastercard</div>
                    <div class="rsd-marquee-item"><span class="rsd-marquee-icon">💬</span> WhatsApp Cloud API</div>
                    <div class="rsd-marquee-item"><span class="rsd-marquee-icon">🏨</span> 2-Way PMS Channel Sync</div>
                    <div class="rsd-marquee-item"><span class="rsd-marquee-icon">🔒</span> Stripe 3D-Secure 2.0</div>
                    <div class="rsd-marquee-item"><span class="rsd-marquee-icon">⚡</span> Sub-Second Checkout</div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 3. INTEGRATED SYSTEM & ROI CALCULATOR WIDGET (Dark + 3 Modular Cards)
class RSD_Elementor_ROI_Calculator_Widget extends Widget_Base {
    public function get_name() { return 'rsd_roi_calc'; }
    public function get_title() { return esc_html__('RSD — ROI Financial Calculator & Systems', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-calculator'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Calculator Settings', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        ?>
        <section class="rsd-roi-section" id="rsd-roi-engine">
            <div class="rsd-roi-ambient-glow"></div>
            <div class="rsd-roi-container">
                <div style="text-align:center;max-width:720px;margin:0 auto 44px auto;">
                    <div class="rsd-roi-pill">✦ POWERFUL REVENUE ACCELERATOR ✦</div>
                    <h2 class="rsd-roi-title">Integrated System Serving Your Business</h2>
                    <p class="rsd-roi-subtitle">Simulate the exact revenue retained by shifting 40% of your reservations to direct booking.</p>
                </div>
                
                <!-- 2-Column Calculator -->
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
                        <button onclick="window.toggleRsdChatWidget(event)" class="rsd-output-btn">
                            Request A Custom Architecture Quote →
                        </button>
                    </div>
                </div>

                <!-- 3 Modular Cards Below Calculator -->
                <div style="text-align:center;margin:60px 0 28px 0;">
                    <h3 class="rsd-modular-title" style="color:#FFFFFF;font-size:1.45rem;font-weight:800;">Modular Solutions Without Restrictions</h3>
                </div>
                <div class="rsd-modular-grid">
                    <div class="rsd-modular-card">
                        <div class="rsd-modular-num">01</div>
                        <h4 class="rsd-modular-h4">Bespoke Direct Booking Engine</h4>
                        <p class="rsd-modular-desc">Direct 0% commission guest booking engine tailored to your hotel brand.</p>
                    </div>
                    <div class="rsd-modular-card">
                        <div class="rsd-modular-num">02</div>
                        <h4 class="rsd-modular-h4">2-Way PMS Channel Sync</h4>
                        <p class="rsd-modular-desc">Automated calendar & rates synchronization directly with your PMS without double bookings.</p>
                    </div>
                    <div class="rsd-modular-card">
                        <div class="rsd-modular-num">03</div>
                        <h4 class="rsd-modular-h4">Multilingual 24/7 AI WhatsApp Concierge</h4>
                        <p class="rsd-modular-desc">AI guest concierge answering queries, processing payments, and upselling amenities 24/7.</p>
                    </div>
                </div>

            </div>
        </section>
        <script>
        function calculateRsdRoi() {
            var rooms = parseInt(document.getElementById('rangeRooms').value) || 60;
            var adr = parseInt(document.getElementById('rangeAdr').value) || 120;
            var comm = parseInt(document.getElementById('rangeCommission').value) || 18;
            var direct = parseInt(document.getElementById('rangeDirect').value) || 40;

            document.getElementById('valRooms').innerText = rooms;
            document.getElementById('valAdr').innerText = '$' + adr;
            document.getElementById('valCommission').innerText = comm + '%';
            document.getElementById('valDirect').innerText = direct + '%';

            var totalGrossAnnual = rooms * adr * 365 * 0.70;
            var shiftedGross = totalGrossAnnual * (direct / 100);
            var annualSaved = Math.round(shiftedGross * (comm / 100));
            var monthlySaved = Math.round(annualSaved / 12);

            document.getElementById('outAnnualSavings').innerText = '$' + annualSaved.toLocaleString() + ' USD';
            document.getElementById('outMonthlySavings').innerText = '+ $' + monthlySaved.toLocaleString() + ' USD / mo retained';
            document.getElementById('outDirectGrowth').innerText = '+' + direct.toFixed(1) + '% Direct Share';
        }
        </script>
        <?php
    }
}

// 4. ROADMAP WIDGET (Dark 4-Step Grid)
class RSD_Elementor_Roadmap_Widget extends Widget_Base {
    public function get_name() { return 'rsd_roadmap'; }
    public function get_title() { return esc_html__('RSD — 4-Step Roadmap', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-flow'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Roadmap Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        ?>
        <section class="rsd-protocol-sec">
            <div class="rsd-protocol-container">
                <div style="text-align:center;max-width:680px;margin:0 auto 40px auto;">
                    <div class="rsd-roi-pill">✦ 4-STEP ARCHITECTURAL PROTOCOL ✦</div>
                    <h2 class="rsd-roi-title">Turnkey Roadmap to Full Direct Revenue</h2>
                    <p class="rsd-roi-subtitle">A battle-tested engineering protocol delivering your direct booking infrastructure turnkey in 7 to 14 days.</p>
                </div>
                <div class="rsd-protocol-grid">
                    <div class="rsd-protocol-card">
                        <div class="rsd-protocol-num">01</div>
                        <h3 class="rsd-protocol-title">Revenue & OTA Gap Audit</h3>
                        <p class="rsd-protocol-desc">Deep analysis of your current reservation mix, OTA commission leakages, and target direct share.</p>
                    </div>
                    <div class="rsd-protocol-card">
                        <div class="rsd-protocol-num">02</div>
                        <h3 class="rsd-protocol-title">Bespoke UX & Engine Design</h3>
                        <p class="rsd-protocol-desc">Crafting a high-conversion 3D booking engine tailored to your brand, optimized for sub-second checkout.</p>
                    </div>
                    <div class="rsd-protocol-card">
                        <div class="rsd-protocol-num">03</div>
                        <h3 class="rsd-protocol-title">2-Way PMS Channel Sync</h3>
                        <p class="rsd-protocol-desc">Direct two-way synchronization with your PMS preventing double bookings across all channels.</p>
                    </div>
                    <div class="rsd-protocol-card">
                        <div class="rsd-protocol-num">04</div>
                        <h3 class="rsd-protocol-title">AI Concierge & Launch</h3>
                        <p class="rsd-protocol-desc">Enabling autonomous 24/7 WhatsApp AI concierge to capture leads, confirm payments, and upsell amenities.</p>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 5. TESTIMONIALS WIDGET (Clean Light Background)
class RSD_Elementor_Testimonials_Widget extends Widget_Base {
    public function get_name() { return 'rsd_testimonials'; }
    public function get_title() { return esc_html__('RSD — Testimonials Grid', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-testimonial-carousel'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Testimonials Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        ?>
        <section class="rsd-saas-sec rsd-trust-sec" style="background:#F8FAFC;padding:90px 20px;">
            <div class="rsd-saas-container">
                <div style="text-align:center;max-width:700px;margin:0 auto 44px auto;">
                    <div class="rsd-roi-pill" style="background:#EFF6FF;color:#2563EB;border-color:rgba(37,99,235,0.25);">✦ TESTIMONIALS & TRUST ✦</div>
                    <h2 class="rsd-saas-title" style="color:#0F172A;font-size:clamp(2rem, 3.5vw, 2.7rem);font-weight:800;">What Our Users Say</h2>
                    <p style="color:#64748B;font-size:1.05rem;margin-top:10px;">Real results from luxury boutique hotels and resorts.</p>
                </div>
                
                <div class="rsd-testimonials-grid">
                    <!-- Col 1 -->
                    <div style="display:flex;flex-direction:column;gap:20px;">
                        <div class="rsd-t-card">
                            <div class="rsd-t-stars">★★★★★</div>
                            <p class="rsd-t-quote">"Transitioning to Red Sea Digital direct booking engine completely freed us from 22% OTA commissions."</p>
                            <div class="rsd-t-author">
                                <div class="rsd-t-avatar-box">KM</div>
                                <div><h4 class="rsd-t-name">Capt. Karim Mansour</h4><span class="rsd-t-role">Managing Director — Red Sea Diving</span></div>
                            </div>
                        </div>
                        <div class="rsd-t-card">
                            <div class="rsd-t-stars">★★★★★</div>
                            <p class="rsd-t-quote">"The WhatsApp AI concierge captures inquiries instantly even at midnight. Conversions jumped significantly."</p>
                            <div class="rsd-t-author">
                                <div class="rsd-t-avatar-box">OS</div>
                                <div><h4 class="rsd-t-name">Omar Soliman</h4><span class="rsd-t-role">Operations Lead — Desert Oasis</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Col 2 -->
                    <div style="display:flex;flex-direction:column;gap:20px;">
                        <div class="rsd-t-card">
                            <div class="rsd-t-stars">★★★★★</div>
                            <p class="rsd-t-quote">"Real-time two-way PMS channel sync saved hundreds of manual hours and eliminated double bookings."</p>
                            <div class="rsd-t-author">
                                <div class="rsd-t-avatar-box">TA</div>
                                <div><h4 class="rsd-t-name">Tarek Al-Sayed</h4><span class="rsd-t-role">General Manager — Coral View Luxury</span></div>
                            </div>
                        </div>
                        <div class="rsd-t-card">
                            <div class="rsd-t-stars">★★★★★</div>
                            <p class="rsd-t-quote">"Fast loading times, flawless mobile checkout with Apple Pay. Guests love booking directly with us."</p>
                            <div class="rsd-t-author">
                                <div class="rsd-t-avatar-box">MK</div>
                                <div><h4 class="rsd-t-name">Mona Khalil</h4><span class="rsd-t-role">Commercial Director — Blue Bay</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Col 3 -->
                    <div style="display:flex;flex-direction:column;gap:20px;">
                        <div class="rsd-t-card">
                            <div class="rsd-t-stars">★★★★★</div>
                            <p class="rsd-t-quote">"Multilingual voice and chat responses gave our European guests a 5-star concierge experience."</p>
                            <div class="rsd-t-author">
                                <div class="rsd-t-avatar-box">ER</div>
                                <div><h4 class="rsd-t-name">Elena Rostova</h4><span class="rsd-t-role">Guest Experience Lead — Riviera Boutique</span></div>
                            </div>
                        </div>
                        <div class="rsd-t-card">
                            <div class="rsd-t-stars">★★★★★</div>
                            <p class="rsd-t-quote">"We recouped our investment within the first 60 days purely from retained OTA commission fees."</p>
                            <div class="rsd-t-author">
                                <div class="rsd-t-avatar-box">AH</div>
                                <div><h4 class="rsd-t-name">Ahmed Hegazy</h4><span class="rsd-t-role">Owner — Sinai Luxury Eco-Lodge</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 6. MATRIX WIDGET (Comparison Matrix)
class RSD_Elementor_Matrix_Widget extends Widget_Base {
    public function get_name() { return 'rsd_matrix'; }
    public function get_title() { return esc_html__('RSD — Comparison Matrix', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-table'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Matrix Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        ?>
        <section class="rsd-saas-sec rsd-matrix-sec">
            <div class="rsd-saas-container">
                <h2 class="rsd-saas-title" style="text-align:center;margin-bottom:44px;color:#0F172A;font-size:clamp(1.9rem, 3.5vw, 2.6rem);font-weight:800;">The Real Difference In Revenue Ownership</h2>
                <div class="rsd-unified-matrix-card">
                    <div class="rsd-matrix-col col-old">
                        <div class="rsd-col-header">
                            <span class="rsd-col-badge badge-old">Legacy Method</span>
                            <h3 class="rsd-col-title" style="color:#0F172A;">✕ Third-Party Platforms (Old Way)</h3>
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
                            <h3 class="rsd-col-title" style="color:#FFFFFF;">★ Direct Booking Engine (Red Sea Digital)</h3>
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

// 7. CAL BOOKING WIDGET (Render Direct Architecture FAQs matching reference design)
class RSD_Elementor_Cal_Booking_Widget extends Widget_Base {
    public function get_name() { return 'rsd_cal_booking'; }
    public function get_title() { return esc_html__('RSD — Direct Architecture FAQs', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-accordion'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'FAQ Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        ?>
        <section class="rsd-faq-sec">
            <div class="rsd-faq-container">
                <div style="text-align:center;max-width:680px;margin:0 auto 36px auto;">
                    <div class="rsd-roi-pill">✦ DIRECT ARCHITECTURE FAQS ✦</div>
                    <h2 class="rsd-roi-title">Direct Architecture FAQs</h2>
                    <p class="rsd-roi-subtitle">Find answers to common questions about direct booking engine integration.</p>
                </div>
                
                <div class="rsd-faq-list">
                    <div class="rsd-faq-item active">
                        <div class="rsd-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>How fast is the turnkey setup delivered?</span>
                            <span class="rsd-faq-icon">+</span>
                        </div>
                        <div class="rsd-faq-answer" style="max-height: 200px; padding: 0 24px 20px 24px;">
                            Our battle-tested protocol delivers your complete direct booking architecture, payment gateway, and WhatsApp AI concierge in 7 to 14 business days.
                        </div>
                    </div>
                    
                    <div class="rsd-faq-item">
                        <div class="rsd-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>Does this integrate with our current PMS system?</span>
                            <span class="rsd-faq-icon">+</span>
                        </div>
                        <div class="rsd-faq-answer">
                            Yes. We build two-way iCal / API PMS channel synchronization ensuring room inventory, rates, and bookings update in real-time across all channels without double bookings.
                        </div>
                    </div>

                    <div class="rsd-faq-item">
                        <div class="rsd-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>Where do guest booking payments go?</span>
                            <span class="rsd-faq-icon">+</span>
                        </div>
                        <div class="rsd-faq-answer">
                            100% of guest payments land directly in your own corporate bank account via integrated gateways (Stripe, Apple Pay, Visa, MasterCard) with 0% middleman commission.
                        </div>
                    </div>

                    <div class="rsd-faq-item">
                        <div class="rsd-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>What languages does the AI WhatsApp Concierge support?</span>
                            <span class="rsd-faq-icon">+</span>
                        </div>
                        <div class="rsd-faq-answer">
                            The AI concierge natively speaks English, Arabic, German, Russian, and Italian, providing 24/7 autonomous guest inquiries, room recommendations, and instant booking confirmation.
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
    }
}

// 8. FINAL CTA WIDGET (Dark Guarantee & CTA)
class RSD_Elementor_Final_CTA_Widget extends Widget_Base {
    public function get_name() { return 'rsd_final_cta'; }
    public function get_title() { return esc_html__('RSD — Final CTA & Guarantee', 'redsea-ai-engine'); }
    public function get_icon() { return 'eicon-call-to-action'; }
    public function get_categories() { return ['redsea-digital-suite']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', ['label' => 'CTA Content', 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->end_controls_section();
    }

    protected function render() {
        ?>
        <section class="rsd-saas-dark-sec rsd-saas-cta-sec">
            <div class="rsd-saas-dark-container" style="text-align:center;">
                <div class="rsd-guarantee-pill">🛡️ 30-Day Money-Back Guarantee: If you are not satisfied with speed & performance, receive a 100% full refund.</div>
                <h2 class="rsd-dark-h2" style="margin-top:24px;">Start Reclaiming Your Direct Revenue Today</h2>
                <p class="rsd-dark-subtext" style="margin-bottom:32px;">Consult with our team to review your direct booking architecture.</p>
                <button onclick="window.toggleRsdChatWidget(event)" class="rsd-saas-btn-primary" style="font-size:1.15rem; padding:18px 44px;">
                    Book Free Consultation 🚀
                </button>
            </div>
        </section>
        <?php
    }
}
