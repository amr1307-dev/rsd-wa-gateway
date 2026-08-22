<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend Pristine Layout & Nuclear Centering CSS Partial
 * Complete responsive layout, desktop symmetrical grid, glassmorphism, animations, SaaS hero, and Bento grid styling.
 */

$is_ar = (strpos($_SERVER['REQUEST_URI'], '/ar') !== false);
        ?>
        <style id="rsd-pristine-uncompressed-layout-css">
            /* ========================================================= */
            /* 1. DESKTOP HEADER SYMMETRICAL GRID (MIN-WIDTH 992PX)      */
            /* ========================================================= */
            @media (min-width: 992px) {
                .rsd-header {
                    background: rgba(251, 251, 249, 0.94) !important;
                    backdrop-filter: blur(20px) !important;
                    -webkit-backdrop-filter: blur(20px) !important;
                    border-bottom: 1px solid rgba(17, 17, 17, 0.06) !important;
                    position: sticky !important;
                    top: 0 !important;
                    z-index: 99999 !important;
                    width: 100% !important;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02) !important;
                }

                .rsd-header-container {
                    display: grid !important;
                    grid-template-columns: 300px 1fr 300px !important;
                    align-items: center !important;
                    max-width: 1360px !important;
                    margin: 0 auto !important;
                    padding: 16px 40px !important;
                    box-sizing: border-box !important;
                    width: 100% !important;
                    <?php echo $is_ar ? "direction: rtl !important;" : "direction: ltr !important;"; ?>
                }

                .rsd-logo-link {
                    grid-column: 1 !important;
                    justify-self: start !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .rsd-logo-img {
                    max-height: 38px !important;
                    width: auto !important;
                    object-fit: contain !important;
                }

                .rsd-desktop-nav {
                    grid-column: 2 !important;
                    justify-self: center !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    gap: 36px !important;
                    margin: 0 auto !important;
                }

                .rsd-desktop-nav .rsd-nav-link {
                    font-family: Inter, system-ui, sans-serif !important;
                    font-size: 0.9rem !important;
                    font-weight: 600 !important;
                    letter-spacing: 0.3px !important;
                    color: #0F172A !important;
                    text-decoration: none !important;
                    transition: color 0.25s ease, opacity 0.25s ease !important;
                    opacity: 0.82;
                    white-space: nowrap !important;
                }

                <?php if ($is_ar): ?>
                .rsd-desktop-nav .rsd-nav-link {
                    font-family: 'Cairo', 'Tajawal', sans-serif !important;
                    font-size: 0.95rem !important;
                    font-weight: 700 !important;
                }
                <?php endif; ?>

                .rsd-desktop-nav .rsd-nav-link:hover {
                    opacity: 1 !important;
                    color: #2563EB !important;
                }

                .rsd-header-right {
                    grid-column: 3 !important;
                    justify-self: end !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: flex-end !important;
                    gap: 16px !important;
                    margin: 0 !important;
                }

                .rsd-header-btn {
                    background: #FFFFFF !important;
                    color: #ffffff !important;
                    padding: 10px 24px !important;
                    border-radius: 30px !important;
                    font-size: 0.85rem !important;
                    font-weight: 700 !important;
                    text-decoration: none !important;
                    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
                    box-shadow: 0 4px 12px rgba(17, 17, 17, 0.1) !important;
                    white-space: nowrap !important;
                }

                .rsd-header-btn:hover {
                    background: #2563EB !important;
                    color: #0F172A !important;
                    transform: translateY(-2px) !important;
                    box-shadow: 0 6px 18px rgba(197, 160, 89, 0.35) !important;
                }

                .rsd-sleek-lang-toggle {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    gap: 4px !important;
                    font-family: Inter, system-ui, sans-serif !important;
                    font-size: 0.82rem !important;
                    font-weight: 700 !important;
                    margin: 0 !important;
                    background: rgba(17, 17, 17, 0.04) !important;
                    padding: 6px 14px !important;
                    border-radius: 20px !important;
                    border: 1px solid rgba(197, 160, 89, 0.35) !important;
                }
            }

            /* ========================================================= */
            /* 2. MOBILE HEADER LAYOUT (MAX-WIDTH 991PX)                 */
            /* ========================================================= */
            @media (max-width: 991px) {
                .rsd-header {
                    background: #FBFBF9 !important;
                    border-bottom: 1px solid rgba(17, 17, 17, 0.08) !important;
                    position: sticky !important;
                    top: 0 !important;
                    z-index: 99999 !important;
                }

                .rsd-header-container {
                    display: block !important;
                    width: 100% !important;
                    box-sizing: border-box !important;
                    position: relative !important;
                    min-height: 64px !important;
                    direction: ltr !important;
                    padding: 0 16px !important;
                }

                .rsd-logo-link {
                    position: absolute !important;
                    left: 16px !important;
                    right: auto !important;
                    top: 50% !important;
                    transform: translateY(-50%) !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    max-width: 130px !important;
                    z-index: 99999 !important;
                }

                .rsd-logo-img {
                    max-height: 34px !important;
                    width: auto !important;
                    object-fit: contain !important;
                }

                .rsd-desktop-nav {
                    display: none !important;
                }

                .rsd-header-right {
                    position: absolute !important;
                    right: 16px !important;
                    left: auto !important;
                    top: 50% !important;
                    transform: translateY(-50%) !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: flex-end !important;
                    gap: 8px !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    z-index: 99999 !important;
                    direction: ltr !important;
                }

                .rsd-header-right .rsd-header-btn {
                    display: none !important;
                }

                button.rsd-mobile-toggle {
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    font-size: 1.3rem !important;
                    background: #ffffff !important;
                    color: #0F172A !important;
                    border: 1px solid #cbd5e1 !important;
                    border-radius: 8px !important;
                    padding: 6px 12px !important;
                    cursor: pointer !important;
                    margin: 0 !important;
                    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
                    height: 38px !important;
                    line-height: 1 !important;
                }
            }

            /* ========================================================= */
            /* 3. PRISTINE UNCOMPRESSED CONTAINERS & SECTIONS (EN & AR)  */
            /* ========================================================= */
            .rsd-sec {
                width: 100% !important;
                padding: 80px 0 !important;
                box-sizing: border-box !important;
            }

            .rsd-container {
                width: 100% !important;
                max-width: 1200px !important;
                margin: 0 auto !important;
                padding: 0 24px !important;
                box-sizing: border-box !important;
            }

            /* Portfolio Cards: Natural Uncompressed Layout */
            .rsd-portfolio-card {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                gap: 48px !important;
                width: 100% !important;
                margin-bottom: 48px !important;
                background: #ffffff !important;
                border-radius: 24px !important;
                padding: 40px !important;
                border: 1px solid rgba(17, 17, 17, 0.06) !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
                box-sizing: border-box !important;
            }

            .rsd-portfolio-card > div {
                flex: 1 !important;
                width: 50% !important;
                box-sizing: border-box !important;
            }

            .rsd-portfolio-img-wrap img,
            .rsd-portfolio-card img {
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                object-fit: cover !important;
                border-radius: 16px !important;
                display: block !important;
            }

            @media (max-width: 768px) {
                .rsd-sec {
                    padding: 48px 0 !important;
                }

                .rsd-container {
                    padding: 0 20px !important;
                }

                .rsd-portfolio-card,
                .rsd-portfolio-card.reverse {
                    flex-direction: column !important;
                    padding: 24px 20px !important;
                    gap: 24px !important;
                }

                .rsd-portfolio-card > div {
                    width: 100% !important;
                    flex: none !important;
                }
            }

            /* Clean Language Toggle Pill Styling */
            .rsd-sleek-lang-toggle {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 6px !important;
                font-family: Inter, system-ui, sans-serif !important;
                font-size: 0.85rem !important;
                font-weight: 700 !important;
                letter-spacing: 0.5px !important;
                margin: 0 8px !important;
                vertical-align: middle !important;
                background: rgba(17, 17, 17, 0.05);
                padding: 5px 14px;
                border-radius: 20px;
                border: 1px solid rgba(197, 160, 89, 0.3);
            }
            .rsd-sleek-lang-toggle a {
                color: #646460 !important;
                text-decoration: none !important;
                transition: color 0.2s ease !important;
                padding: 2px 6px !important;
            }
            .rsd-sleek-lang-toggle a:hover, .rsd-sleek-lang-toggle a.active {
                color: #2563EB !important;
                font-weight: 900 !important;
            }

            /* Global Hero Centering Rules */
            .rsd-hero-sec,
            .rsd-hero-content {
                text-align: center !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                width: 100% !important;
                max-width: 1200px !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }
            .rsd-hero-tag, .rsd-hero-title, .rsd-hero-sub {
                text-align: center !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }
            .rsd-hero-actions {
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                gap: 16px !important;
                margin: 24px auto !important;
            }
        
            /* ========================================================= */
            /* 100% FULL WIDTH ZERO SIDE PADDING MOBILE SECTIONS & CARDS */
            /* ========================================================= */
            @media (max-width: 768px) {
                html, body, #page, #content, .site-content, .entry-content, article,
                                .rsd-ar-master-wrapper {
                    overflow-x: hidden !important;
                    width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                /* All Mobile Sections & Containers: 100% FULL WIDTH, ZERO SIDE PADDING */
                .rsd-sec,
                .rsd-container,
                .elementor-section,
                .elementor-container,
                .elementor-column,
                .elementor-widget-wrap,
                .rsd-portfolio-container,
                .rsd-cs-container,
                .rsd-case-study-hero {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                    float: none !important;
                }

                /* All Mobile Cards: 100% FULL WIDTH EDGE TO EDGE */
                .rsd-portfolio-card,
                .rsd-portfolio-card.reverse,
                .rsd-card,
                .rsd-service-card,
                .rsd-work-card,
                .rsd-process-step,
                .rsd-capability-card,
                .rsd-testimonial-card,
                .rsd-comparison-card,
                .rsd-cs-card,
                .rsd-metric-box {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    border-radius: 0 !important; /* Edge to edge full screen width */
                    padding-left: 16px !important;
                    padding-right: 16px !important;
                }

                .rsd-portfolio-card > div,
                .rsd-portfolio-card.reverse > div,
                .rsd-portfolio-img-wrap {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                }

                .rsd-portfolio-img-wrap img,
                .rsd-portfolio-card img {
                    width: 100% !important;
                    max-width: 100% !important;
                    height: auto !important;
                    border-radius: 0 !important;
                }
            }

        
            /* ========================================================= */
            /* 5PX BREATHING SIDE PADDING FOR MOBILE CONTAINERS & CARDS */
            /* ========================================================= */
            @media (max-width: 768px) {
                html, body, #page, #content, .site-content, .entry-content, article,
                                .rsd-ar-master-wrapper {
                    overflow-x: hidden !important;
                    width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                /* Mobile Containers: 5px Breathing Side Padding */
                .rsd-sec,
                .rsd-container,
                .elementor-section,
                .elementor-container,
                .elementor-column,
                .elementor-widget-wrap,
                .rsd-portfolio-container,
                .rsd-cs-container,
                .rsd-case-study-hero {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    padding-left: 5px !important;
                    padding-right: 5px !important;
                    float: none !important;
                }

                /* Mobile Cards: 12px Internal Padding & 12px Radius for Breathing Comfort */
                .rsd-portfolio-card,
                .rsd-portfolio-card.reverse,
                .rsd-card,
                .rsd-service-card,
                .rsd-work-card,
                .rsd-process-step,
                .rsd-capability-card,
                .rsd-testimonial-card,
                .rsd-comparison-card,
                .rsd-cs-card,
                .rsd-metric-box {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    border-radius: 12px !important;
                    padding-left: 12px !important;
                    padding-right: 12px !important;
                }

                .rsd-portfolio-card > div,
                .rsd-portfolio-card.reverse > div,
                .rsd-portfolio-img-wrap {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                }

                .rsd-portfolio-img-wrap img,
                .rsd-portfolio-card img {
                    width: 100% !important;
                    max-width: 100% !important;
                    height: auto !important;
                    border-radius: 10px !important;
                }
            }

        /* ========================================================= */
            /* UNIVERSAL MOBILE CONTAINER & CARD LAYOUT FIX (ALL PAGES) */
            /* ========================================================= */
            @media (max-width: 900px) {
                html, body, #page, #content, .site-content, .entry-content, article {
                    overflow-x: hidden !important;
                    width: 100% !important;
                    max-width: 100vw !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    box-sizing: border-box !important;
                }

                .rsd-sec,
                .rsd-container,
                .rsd-portfolio-container,
                .rsd-cs-container,
                .rsd-case-study-hero,
                .elementor-section,
                .elementor-container,
                .elementor-column,
                .elementor-widget-wrap {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    padding-left: 16px !important;
                    padding-right: 16px !important;
                    float: none !important;
                }

                .rsd-portfolio-card,
                .rsd-portfolio-card.reverse,
                .rsd-card,
                .rsd-cs-card,
                .rsd-service-card,
                .rsd-work-card,
                .rsd-process-step,
                .rsd-capability-card,
                .rsd-testimonial-card,
                .rsd-comparison-card {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    border-radius: 20px !important;
                    padding: 24px 18px !important;
                    flex-direction: column !important;
                    gap: 20px !important;
                }

                .rsd-portfolio-card > div,
                .rsd-portfolio-card.reverse > div,
                .rsd-portfolio-img-wrap {
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                    flex: none !important;
                }

                .rsd-portfolio-img-wrap img,
                .rsd-portfolio-card img,
                .rsd-cs-card img {
                    width: 100% !important;
                    max-width: 100% !important;
                    height: auto !important;
                    border-radius: 14px !important;
                    object-fit: cover !important;
                }
            }
        /* ========================================================= */
            /* ELIMINATE HOMEPAGE HERO TOP BLANK WHITESPACE GAP           */
            /* ========================================================= */
            .rsd-hero-sec,
            .rsd-sec:first-of-type,
            body.page-id-12 .rsd-sec:first-of-type,
            body.page-id-163 .rsd-sec:first-of-type,
            body.home .rsd-sec:first-of-type,
            .entry-content > .rsd-sec:first-child {
                padding-top: 16px !important;
                margin-top: 0 !important;
            }

            @media (max-width: 900px) {
                #rsd-header-spacer {
                    height: 84px !important;
                }
                .rsd-hero-sec,
                .rsd-sec:first-of-type,
                body.page-id-12 .rsd-sec:first-of-type,
                body.page-id-163 .rsd-sec:first-of-type,
                body.home .rsd-sec:first-of-type,
                .entry-content > .rsd-sec:first-child {
                    padding-top: 12px !important;
                    margin-top: 0 !important;
                }
            }
        /* ========================================================= */
            /* BULLETPROOF MATHEMATICAL NO-GAP HERO RULE                  */
            /* Fixed Header = 84px | Top Section Padding = 104px          */
            /* Net Gap = Exactly 20px Below Universal Header!             */
            /* ========================================================= */
            #rsd-header-spacer {
                display: none !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .entry-content header.rsd-header,
            .rsd-hero-center-master > header.rsd-header,
            .rsd-ar-master-wrapper header.rsd-header,
            .rsd-ar-centered-wrapper > header.rsd-header,
            .entry-content .rsd-header,
            header.rsd-header:not(#rsdUniversalHeader) {
                display: none !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body.home .entry-content,
            body.page-id-12 .entry-content,
            body.page-id-163 .entry-content,
            .site-main,
            #content {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }

            .rsd-hero-center-master,
            .rsd-ar-master-wrapper,
            .rsd-ar-centered-wrapper,
            .rsd-hero-sec,
            .rsd-sec:first-of-type,
            body.page-id-12 .rsd-hero-center-master,
            body.page-id-163 .rsd-ar-master-wrapper,
            body.home .rsd-hero-center-master,
            body.home .rsd-ar-master-wrapper,
            .entry-content > div:first-child,
            .entry-content > section:first-child {
                padding-top: 104px !important;
                margin-top: 0 !important;
            }

            @media (max-width: 900px) {
                .rsd-hero-center-master,
                .rsd-ar-master-wrapper,
                .rsd-ar-centered-wrapper,
                .rsd-hero-sec,
                .rsd-sec:first-of-type,
                body.page-id-12 .rsd-hero-center-master,
                body.page-id-163 .rsd-ar-master-wrapper,
                body.home .rsd-hero-center-master,
                body.home .rsd-ar-master-wrapper,
                .entry-content > div:first-child,
                .entry-content > section:first-child {
                    padding-top: 96px !important;
                    margin-top: 0 !important;
                }
            }
        /* ========================================================= */
            /* WPAUTOP JUNK & HERO TOP GAP ABSOLUTE ELIMINATION           */
            /* ========================================================= */
            .entry-content > p:empty,
            .entry-content > p:has(script),
            .entry-content > p:has(style),
            .entry-content > p:has(iframe),
            body.home .entry-content > p:first-child,
            body.page-id-12 .entry-content > p:first-child,
            body.page-id-163 .entry-content > p:first-child {
                display: none !important;
                margin: 0 !important;
                padding: 0 !important;
                height: 0 !important;
                line-height: 0 !important;
            }

            .rsd-hero-sec,
            section.rsd-hero-sec,
            .rsd-hero-center-master,
            .rsd-ar-master-wrapper {
                padding-top: 100px !important;
                margin-top: 0 !important;
            }

            @media (max-width: 900px) {
                .rsd-hero-sec,
                section.rsd-hero-sec,
                .rsd-hero-center-master,
                .rsd-ar-master-wrapper {
                    padding-top: 92px !important;
                    margin-top: 0 !important;
                }
            }
        /* ========================================================= */
            /* ABSOLUTE MATHEMATICAL HERO GAP FIX (HEADER=84px, PADDING=92px) */
            /* ========================================================= */
            .entry-content > p:first-child {
                display: none !important;
                margin: 0 !important;
                padding: 0 !important;
                height: 0 !important;
            }

            body.home section.rsd-hero-sec,
            body.page-id-12 section.rsd-hero-sec,
            body.page-id-163 section.rsd-hero-sec,
            .rsd-hero-sec,
            section.rsd-hero-sec {
                padding-top: 92px !important;
                margin-top: 0 !important;
            }

            @media (max-width: 900px) {
                body.home section.rsd-hero-sec,
                body.page-id-12 section.rsd-hero-sec,
                body.page-id-163 section.rsd-hero-sec,
                .rsd-hero-sec,
                section.rsd-hero-sec {
                    padding-top: 86px !important;
                    margin-top: 0 !important;
                }
            }
        /* ========================================================= */
            /* ABSOLUTE PARAGRAPH DESTRUCTION FOR ZERO TOP GAP           */
            /* ========================================================= */
            p:has(section),
            p:has(script),
            p:has(style),
            .rsd-hero-center-master > p,
            .rsd-ar-master-wrapper > p,
            .entry-content > p:first-child {
                display: none !important;
                margin: 0 !important;
                padding: 0 !important;
                height: 0 !important;
                line-height: 0 !important;
                font-size: 0 !important;
            }

            body.home section.rsd-hero-sec,
            body.page-id-12 section.rsd-hero-sec,
            body.page-id-163 section.rsd-hero-sec,
            .rsd-hero-sec,
            section.rsd-hero-sec {
                padding-top: 90px !important;
                margin-top: 0 !important;
            }

            @media (max-width: 900px) {
                body.home section.rsd-hero-sec,
                body.page-id-12 section.rsd-hero-sec,
                body.page-id-163 section.rsd-hero-sec,
                .rsd-hero-sec,
                section.rsd-hero-sec {
                    padding-top: 86px !important;
                    margin-top: 0 !important;
                }
            }
        /* ========================================================= */
            /* NUCLEAR ZERO-GAP HERO ALIGNMENT (HEADER 84px -> TAG 16px)   */
            /* ========================================================= */
            .rsd-hero-sec,
            section.rsd-hero-sec,
            .rsd-hero-center-master,
            .rsd-ar-master-wrapper,
            .rsd-ar-centered-wrapper {
                padding-top: 84px !important;
                margin-top: 0 !important;
            }

            .rsd-hero-sec .rsd-container,
            .rsd-hero-sec .rsd-hero-content,
            .rsd-hero-sec .rsd-hero-tag,
            .rsd-hero-content > *:first-child,
            .rsd-hero-tag,
            span.rsd-hero-tag {
                margin-top: 0 !important;
                padding-top: 16px !important;
            }

            @media (max-width: 900px) {
                .rsd-hero-sec,
                section.rsd-hero-sec,
                .rsd-hero-center-master,
                .rsd-ar-master-wrapper {
                    padding-top: 84px !important;
                    margin-top: 0 !important;
                }
                .rsd-hero-sec .rsd-container,
                .rsd-hero-sec .rsd-hero-content,
                .rsd-hero-sec .rsd-hero-tag,
                .rsd-hero-content > *:first-child,
                .rsd-hero-tag,
                span.rsd-hero-tag {
                    margin-top: 0 !important;
                    padding-top: 12px !important;
                }
            }
        /* ========================================================= */
            /* ABSOLUTE COLLAPSE OF TOP ELEMENTOR TEXT-EDITOR WIDGET     */
            /* ========================================================= */
            .elementor-widget-text-editor:has(#rsd-english-hero-center-override),
            .elementor-widget-text-editor:has(#rsd-absolute-hero-center-override),
            .elementor-widget-text-editor:has(#rsd-hero-center-override),
            .elementor-widget-text-editor:has(#rsd-eng-hero-center-override),
            .elementor-widget-text-editor:has(.rsd-header),
            .elementor-widget-text-editor:has(.rsd-side-drawer),
            .elementor-widget-text-editor:first-child,
            .elementor-widget-text-editor:has(style) {
                display: none !important;
                height: 0 !important;
                max-height: 0 !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                position: absolute !important;
                overflow: hidden !important;
                visibility: hidden !important;
                pointer-events: none !important;
            }

            .elementor-top-section:first-child,
            .elementor-section:first-child,
            .rsd-hero-sec,
            section.rsd-hero-sec,
            .rsd-hero-center-master,
            .rsd-ar-master-wrapper {
                padding-top: 84px !important;
                margin-top: 0 !important;
            }

            .rsd-hero-sec .rsd-container,
            .rsd-hero-sec .rsd-hero-content,
            .rsd-hero-sec .rsd-hero-tag,
            .rsd-hero-tag,
            span.rsd-hero-tag {
                margin-top: 0 !important;
                padding-top: 12px !important;
            }

            @media (max-width: 900px) {
                .elementor-top-section:first-child,
                .elementor-section:first-child,
                .rsd-hero-sec,
                section.rsd-hero-sec,
                .rsd-hero-center-master,
                .rsd-ar-master-wrapper {
                    padding-top: 84px !important;
                    margin-top: 0 !important;
                }
                .rsd-hero-sec .rsd-container,
                .rsd-hero-sec .rsd-hero-content,
                .rsd-hero-sec .rsd-hero-tag,
                .rsd-hero-tag,
                span.rsd-hero-tag {
                    margin-top: 0 !important;
                    padding-top: 8px !important;
                }
            }
        /* ========================================================= */
            /* ABSOLUTE CLEAN MATHEMATICAL HERO ALIGNMENT                 */
            /* ========================================================= */
            .rsd-hero-sec,
            section.rsd-hero-sec,
            .rsd-hero-center-master,
            .rsd-ar-master-wrapper {
                margin-top: 0 !important;
                padding-top: 84px !important;
            }

            .rsd-hero-sec .rsd-container,
            .rsd-hero-sec .rsd-hero-content,
            .rsd-hero-sec .rsd-hero-tag,
            .rsd-hero-tag,
            span.rsd-hero-tag {
                margin-top: 0 !important;
                padding-top: 14px !important;
            }

            @media (max-width: 900px) {
                .rsd-hero-sec,
                section.rsd-hero-sec,
                .rsd-hero-center-master,
                .rsd-ar-master-wrapper {
                    margin-top: 0 !important;
                    padding-top: 84px !important;
                }
                .rsd-hero-sec .rsd-container,
                .rsd-hero-sec .rsd-hero-content,
                .rsd-hero-sec .rsd-hero-tag,
                .rsd-hero-tag,
                span.rsd-hero-tag {
                    margin-top: 0 !important;
                    padding-top: 10px !important;
                }
            }
        /* ========================================================= */
            /* ABSOLUTE ZERO-SPACER MATHEMATICAL HERO ALIGNMENT          */
            /* Fixed Header = 84px | Hero Section Top Padding = 96px      */
            /* Net Gap = Exactly 12px Below Universal Header!             */
            /* ========================================================= */
            #rsd-header-spacer {
                display: none !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .rsd-ar-master-wrapper,
            .rsd-ar-centered-wrapper,
            .rsd-hero-center-master,
            .rsd-hero-sec,
            section.rsd-hero-sec,
            .elementor-top-section:first-child,
            .elementor-section:first-child {
                margin-top: 0 !important;
                padding-top: 96px !important;
            }

            .rsd-hero-sec .rsd-container,
            .rsd-hero-sec .rsd-hero-content,
            .rsd-hero-sec .rsd-hero-tag,
            .rsd-hero-tag,
            span.rsd-hero-tag {
                margin-top: 0 !important;
                padding-top: 0 !important;
            }

            @media (max-width: 900px) {
                .rsd-ar-master-wrapper,
                .rsd-ar-centered-wrapper,
                .rsd-hero-center-master,
                .rsd-hero-sec,
                section.rsd-hero-sec,
                .elementor-top-section:first-child,
                .elementor-section:first-child {
                    margin-top: 0 !important;
                    padding-top: 90px !important;
                }
                .rsd-hero-sec .rsd-container,
                .rsd-hero-sec .rsd-hero-content,
                .rsd-hero-sec .rsd-hero-tag,
                .rsd-hero-tag,
                span.rsd-hero-tag {
                    margin-top: 0 !important;
                    padding-top: 0 !important;
                }
            }
        /* ========================================================= */
            /* ABSOLUTE ZERO-GAP MATHEMATICAL HERO FIX (84px HEADER MATCH) */
            /* ========================================================= */
            .rsd-ar-master-wrapper,
            .rsd-ar-centered-wrapper,
            .rsd-hero-center-master,
            body.page-id-163 .entry-content,
            body.page-id-12 .entry-content,
            body.home .entry-content,
            .site-main,
            #content {
                margin-top: 0 !important;
                padding-top: 0 !important;
                border-top: none !important;
            }

            .rsd-ar-centered-wrapper > p,
            .rsd-ar-master-wrapper > p,
            .rsd-hero-center-master > p,
            .entry-content > p:first-child,
            .entry-content > p:nth-child(2),
            p:has(section) {
                display: none !important;
                margin: 0 !important;
                padding: 0 !important;
                height: 0 !important;
                line-height: 0 !important;
                font-size: 0 !important;
            }

            body.page-id-163 section.rsd-hero-sec,
            body.page-id-12 section.rsd-hero-sec,
            body.home section.rsd-hero-sec,
            .rsd-hero-sec,
            section.rsd-hero-sec,
            .elementor-top-section:first-child,
            .elementor-section:first-child {
                padding-top: 84px !important;
                margin-top: 0 !important;
            }

            .rsd-hero-sec .rsd-container,
            .rsd-hero-sec .rsd-hero-content,
            .rsd-hero-sec .rsd-hero-tag,
            .rsd-hero-tag,
            span.rsd-hero-tag {
                margin-top: 0 !important;
                padding-top: 10px !important;
            }

            @media (max-width: 900px) {
                body.page-id-163 section.rsd-hero-sec,
                body.page-id-12 section.rsd-hero-sec,
                body.home section.rsd-hero-sec,
                .rsd-hero-sec,
                section.rsd-hero-sec {
                    padding-top: 84px !important;
                    margin-top: 0 !important;
                }
                .rsd-hero-sec .rsd-container,
                .rsd-hero-sec .rsd-hero-content,
                .rsd-hero-sec .rsd-hero-tag,
                .rsd-hero-tag,
                span.rsd-hero-tag {
                    margin-top: 0 !important;
                    padding-top: 8px !important;
                }
            }
        /* ========================================================= */
            /* COMPLETE LUXURY HOMEPAGE STYLING RESTORATION SYSTEM       */
            /* ========================================================= */
            :root {
                --bg-alabaster: #FBFBF9;
                --text-dark: #0F172A;
                --text-body: #4A4A48;
                --text-muted: #71716A;
                --border-stone: #E5E5E0;
                --border-light: #F0F0EC;
                --accent-gold: #2563EB;
                --card-bg: #FFFFFF;
                --shadow-subtle: 0 10px 30px rgba(0, 0, 0, 0.03);
                --shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.06);
                --transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .rsd-container {
                max-width: 1280px !important;
                margin: 0 auto !important;
                padding: 0 24px !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .rsd-hero-title {
                font-family: 'Playfair Display', Georgia, serif !important;
                font-size: clamp(2.2rem, 5vw, 3.8rem) !important;
                font-weight: 700 !important;
                line-height: 1.18 !important;
                color: #0F172A !important;
                margin: 0 0 20px 0 !important;
                max-width: 960px !important;
            }

            .rsd-hero-sub {
                font-size: 1.12rem !important;
                color: #4A4A48 !important;
                max-width: 820px !important;
                margin-bottom: 32px !important;
                line-height: 1.7 !important;
            }

            .rsd-hero-actions {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                gap: 16px !important;
                margin-bottom: 36px !important;
                flex-wrap: wrap !important;
            }

            .rsd-btn-black,
            a.rsd-btn-black {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                height: 50px !important;
                padding: 0 32px !important;
                background: #FFFFFF !important;
                color: #FBFBF9 !important;
                border-radius: 9999px !important;
                text-decoration: none !important;
                font-weight: 700 !important;
                font-size: 0.92rem !important;
                letter-spacing: 0.04em !important;
                transition: all 0.25s ease !important;
                box-shadow: 0 4px 14px rgba(0,0,0,0.08) !important;
            }
            .rsd-btn-black:hover,
            a.rsd-btn-black:hover {
                background: #2563EB !important;
                color: #0F172A !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 8px 24px rgba(197, 160, 89, 0.35) !important;
            }

            .rsd-btn-outline-main,
            a.rsd-btn-outline-main {
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                height: 50px !important;
                padding: 0 32px !important;
                background: #FFFFFF !important;
                color: #0F172A !important;
                border: 1px solid #E5E5E0 !important;
                border-radius: 9999px !important;
                text-decoration: none !important;
                font-weight: 600 !important;
                font-size: 0.92rem !important;
                transition: all 0.25s ease !important;
            }
            .rsd-btn-outline-main:hover,
            a.rsd-btn-outline-main:hover {
                border-color: #0F172A !important;
                transform: translateY(-2px) !important;
            }

            .rsd-metrics-grid {
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 24px !important;
                margin-top: 36px !important;
                width: 100% !important;
            }
            .rsd-metric-card {
                background: #FFFFFF !important;
                padding: 28px 24px !important;
                border-radius: 16px !important;
                border: 1px solid #E5E5E0 !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
                transition: all 0.3s ease !important;
                text-align: center !important;
            }
            .rsd-metric-card:hover {
                transform: translateY(-3px) !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06) !important;
                border-color: #2563EB !important;
            }
            .rsd-metric-num {
                font-size: 2.2rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 6px !important;
            }
            .rsd-metric-label {
                font-size: 0.88rem !important;
                color: #4A4A48 !important;
                font-weight: 500 !important;
            }

            .rsd-sec {
                padding: 90px 0 !important;
                border-bottom: 1px solid #E5E5E0 !important;
            }
            .rsd-sec-title {
                font-family: 'Playfair Display', Georgia, serif !important;
                font-size: clamp(1.8rem, 3.5vw, 2.5rem) !important;
                font-weight: 600 !important;
                margin-bottom: 44px !important;
                color: #0F172A !important;
            }
            .rsd-sec-tag {
                font-size: 0.78rem !important;
                font-weight: 700 !important;
                letter-spacing: 0.12em !important;
                text-transform: uppercase !important;
                color: #2563EB !important;
                margin-bottom: 12px !important;
                display: block !important;
            }
            .rsd-portfolio-card {
                background: #FFFFFF !important;
                border-radius: 24px !important;
                border: 1px solid #E5E5E0 !important;
                overflow: hidden !important;
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                align-items: center !important;
                gap: 48px !important;
                padding: 44px !important;
                margin-bottom: 48px !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
                transition: all 0.3s ease !important;
            }
            .rsd-portfolio-card:hover {
                transform: translateY(-4px) !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06) !important;
                border-color: #2563EB !important;
            }
            .rsd-portfolio-img-wrap {
                border-radius: 16px !important;
                overflow: hidden !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .rsd-portfolio-img-wrap img {
                width: 100% !important;
                height: auto !important;
                display: block !important;
                object-fit: contain !important;
                border-radius: 12px !important;
            }

            .rsd-process-grid {
                display: grid !important;
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 24px !important;
            }
            .rsd-process-card {
                background: #FFFFFF !important;
                padding: 32px 24px !important;
                border-radius: 16px !important;
                border: 1px solid #E5E5E0 !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
                transition: all 0.3s ease !important;
            }
            .rsd-process-card:hover {
                transform: translateY(-3px) !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06) !important;
                border-color: #2563EB !important;
            }
            .rsd-process-step {
                font-size: 0.78rem !important;
                font-weight: 700 !important;
                letter-spacing: 0.1em !important;
                color: #2563EB !important;
                text-transform: uppercase !important;
                margin-bottom: 12px !important;
            }
            .rsd-process-title {
                font-size: 1.15rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 10px !important;
            }
            .rsd-process-desc {
                font-size: 0.9rem !important;
                color: #4A4A48 !important;
                line-height: 1.6 !important;
            }

            .rsd-systems-grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 24px !important;
            }
            .rsd-system-card {
                background: #FFFFFF !important;
                padding: 36px !important;
                border-radius: 20px !important;
                border: 1px solid #E5E5E0 !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
                transition: all 0.3s ease !important;
            }
            .rsd-system-card.featured {
                background: #F4F4F0 !important;
                border-color: #D5D5CF !important;
            }
            .rsd-system-card:hover {
                transform: translateY(-3px) !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06) !important;
            }
            .rsd-system-tag {
                font-size: 0.75rem !important;
                font-weight: 700 !important;
                letter-spacing: 0.1em !important;
                color: #646460 !important;
                text-transform: uppercase !important;
                margin-bottom: 12px !important;
            }
            .rsd-system-title {
                font-size: 1.35rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 12px !important;
            }
            .rsd-system-desc {
                font-size: 0.95rem !important;
                color: #4A4A48 !important;
                line-height: 1.65 !important;
            }

            .rsd-comparison-grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 32px !important;
            }
            .rsd-comp-card {
                background: #FFFFFF !important;
                padding: 40px !important;
                border-radius: 20px !important;
                border: 1px solid #E5E5E0 !important;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
            }
            .rsd-comp-card.highlight {
                border: 2px solid #0F172A !important;
                background: #FFFFFF !important;
            }
            .rsd-comp-card.muted {
                background: #F4F4F0 !important;
                border: 1px solid #E5E5E0 !important;
            }
            .rsd-comp-title {
                font-size: 1.4rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 24px !important;
            }
            .rsd-comp-list {
                list-style: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .rsd-comp-item {
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
                margin-bottom: 16px !important;
                font-size: 0.95rem !important;
                color: #4A4A48 !important;
            }
            .rsd-icon-check { color: #16A34A !important; font-weight: 700 !important; }
            .rsd-icon-cross { color: #DC2626 !important; font-weight: 700 !important; }

            @media (max-width: 992px) {
                .rsd-portfolio-card { grid-template-columns: 1fr !important; padding: 28px !important; gap: 24px !important; }
                .rsd-process-grid { grid-template-columns: repeat(2, 1fr) !important; }
                .rsd-systems-grid, .rsd-comparison-grid { grid-template-columns: 1fr !important; }
            }

            @media (max-width: 768px) {
                .rsd-container { padding: 0 16px !important; }
                .rsd-metrics-grid { grid-template-columns: 1fr !important; }
                .rsd-process-grid { grid-template-columns: 1fr !important; }
                .rsd-hero-actions { flex-direction: column !important; gap: 12px !important; }
                .rsd-btn-black, .rsd-btn-outline-main { width: 100% !important; justify-content: center !important; }
                .rsd-sec { padding: 54px 0 !important; }
            }
        /* ========================================================= */
            /* ABSOLUTE OVERRIDE FOR ELEMENTOR NATIVE BUTTON WIDGETS     */
            /* ========================================================= */
            .elementor-widget-button .elementor-button,
            .elementor-button-wrapper a.elementor-button,
            a.elementor-button,
            .rsd-btn-black,
            a.rsd-btn-black {
                background-color: #0F172A !important;
                color: #FBFBF9 !important;
                border-radius: 9999px !important;
                padding: 14px 32px !important;
                font-weight: 700 !important;
                font-size: 0.92rem !important;
                letter-spacing: 0.04em !important;
                transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08) !important;
                border: none !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-decoration: none !important;
            }

            .elementor-widget-button .elementor-button:hover,
            .elementor-button-wrapper a.elementor-button:hover,
            a.elementor-button:hover,
            .rsd-btn-black:hover,
            a.rsd-btn-black:hover {
                background-color: #2563EB !important;
                color: #0F172A !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 8px 24px rgba(197, 160, 89, 0.35) !important;
            }

            .rsd-btn-outline-main,
            a.rsd-btn-outline-main {
                background-color: #FFFFFF !important;
                color: #0F172A !important;
                border: 1px solid #E5E5E0 !important;
                border-radius: 9999px !important;
                padding: 14px 32px !important;
                font-weight: 600 !important;
                font-size: 0.92rem !important;
                transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-decoration: none !important;
            }

            .rsd-btn-outline-main:hover,
            a.rsd-btn-outline-main:hover {
                border-color: #0F172A !important;
                transform: translateY(-2px) !important;
            }

            .elementor-widget-heading .elementor-heading-title {
                color: inherit;
            }
        /* ========================================================= */
            /* $10,000+ AWWWARDS QUIET LUXURY STUDIO POLISHING & RESPONSIVE */
            /* ========================================================= */
            .rsd-luxury-portfolio-card,
            .rsd-metric-card,
            .rsd-step-card,
            .rsd-capability-card,
            .rsd-comparison-card-pro,
            .rsd-cta-banner-container {
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: 0 16px 40px -10px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02) !important;
            }

            .rsd-luxury-portfolio-card:hover,
            .rsd-metric-card:hover,
            .rsd-step-card:hover,
            .rsd-capability-card:hover,
            .rsd-comparison-card-pro:hover {
                transform: translateY(-8px) !important;
                box-shadow: 0 32px 72px -16px rgba(197, 160, 89, 0.22), 0 0 1px rgba(197, 160, 89, 0.4) !important;
                border-color: rgba(197, 160, 89, 0.5) !important;
            }

            .rsd-luxury-portfolio-card img {
                transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: 0 12px 32px rgba(0, 0, 0, 0.06) !important;
            }

            .rsd-luxury-portfolio-card:hover img {
                transform: scale(1.025) !important;
            }

            /* Responsive Mobile & Tablet Breakdown */
            @media (max-width: 991px) {
                .elementor-section-wrap .e-con,
                .elementor-element .e-con,
                .rsd-luxury-portfolio-card,
                .rsd-hero-sec,
                .rsd-cta-banner-container {
                    flex-direction: column !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    padding-left: 16px !important;
                    padding-right: 16px !important;
                }

                .rsd-luxury-portfolio-card > .e-con,
                .rsd-luxury-portfolio-card > div {
                    width: 100% !important;
                    max-width: 100% !important;
                }

                .rsd-luxury-portfolio-card {
                    padding: 24px 20px !important;
                    gap: 24px !important;
                }
            }
        /* ========================================================= */
            /* RED SEA DIGITAL — SINGLE SOURCE OF TRUTH QUIET LUXURY CSS  */
            /* ========================================================= */
            :root {
                --rsd-primary: #0F172A;
                --rsd-bg: #FBFBF9;
                --rsd-card: #F4F4F0;
                --rsd-hairline: #E5E5E0;
                --rsd-accent: #2563EB;
            }

            body, html {
                background-color: var(--rsd-bg) !important;
                color: var(--rsd-primary) !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }

            /* Clean Editorial Hairline Cards */
            .rsd-editorial-case-study,
            .rsd-metric-card,
            .rsd-step-card,
            .rsd-capability-card {
                background-color: var(--rsd-card) !important;
                border: 1px solid var(--rsd-hairline) !important;
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: none !important;
            }

            /* Controlled Max 4px Hover Elevation (NO GOLD GLOW, NO NEON) */
            .rsd-editorial-case-study:hover,
            .rsd-metric-card:hover,
            .rsd-step-card:hover,
            .rsd-capability-card:hover {
                transform: translateY(-4px) !important;
                border-color: #2563EB !important;
                box-shadow: 0 12px 32px rgba(0, 0, 0, 0.04) !important;
            }

            /* Restrained Dark Obsidian Pill Buttons */
            .elementor-widget-button .elementor-button,
            a.elementor-button,
            .rsd-btn-black,
            a.rsd-btn-black {
                background-color: #0F172A !important;
                color: #FBFBF9 !important;
                border-radius: 9999px !important;
                padding: 14px 32px !important;
                font-weight: 700 !important;
                font-size: 0.92rem !important;
                letter-spacing: 0.04em !important;
                transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: none !important;
                border: none !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-decoration: none !important;
            }

            .elementor-widget-button .elementor-button:hover,
            a.elementor-button:hover,
            .rsd-btn-black:hover,
            a.rsd-btn-black:hover {
                background-color: #2563EB !important;
                color: #0F172A !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 14px rgba(197, 160, 89, 0.25) !important;
            }

            .rsd-btn-outline-main,
            a.rsd-btn-outline-main {
                background-color: #FFFFFF !important;
                color: #0F172A !important;
                border: 1px solid #E5E5E0 !important;
                border-radius: 9999px !important;
                padding: 14px 32px !important;
                font-weight: 600 !important;
                font-size: 0.92rem !important;
                transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }

            .rsd-btn-outline-main:hover,
            a.rsd-btn-outline-main:hover {
                border-color: #0F172A !important;
                transform: translateY(-2px) !important;
            }

            /* Responsive Media Queries Across 360px, 390px, 430px, 768px, 834px, 1024px, 1280px, 1440px, 1920px */
            @media (max-width: 991px) {
                .rsd-editorial-case-study {
                    flex-direction: column !important;
                    padding: 28px 20px !important;
                    gap: 24px !important;
                }
                .rsd-editorial-case-study > div {
                    width: 100% !important;
                }
            }

            @media (max-width: 480px) {
                h1.elementor-heading-title {
                    font-size: 2.1rem !important;
                    line-height: 1.25 !important;
                }
                h2.elementor-heading-title {
                    font-size: 1.75rem !important;
                }
                .rsd-btn-black, .rsd-btn-outline-main {
                    width: 100% !important;
                }
            }
        /* ========================================================= */
            /* 15-POINT BRUTALLY OBJECTIVE ART-DIRECTION OVERHAUL CSS     */
            /* ========================================================= */
            :root {
                --rsd-primary: #0F172A;
                --rsd-bg: #FBFBF9;
                --rsd-card: #F4F4F0;
                --rsd-hairline: #E5E5E0;
                --rsd-accent: #2563EB;
            }

            body, html {
                background-color: var(--rsd-bg) !important;
                color: var(--rsd-primary) !important;
            }

            /* Weakness 1: H1 Fluid Scale & Architectural Line-Height */
            .elementor-widget-heading h1.elementor-heading-title {
                font-size: clamp(3.2rem, 6.5vw, 5.4rem) !important;
                line-height: 1.08 !important;
                letter-spacing: -0.03em !important;
                font-weight: 500 !important;
            }

            .elementor-widget-heading h2.elementor-heading-title {
                font-size: clamp(2.4rem, 4.5vw, 3.8rem) !important;
                line-height: 1.15 !important;
                letter-spacing: -0.02em !important;
            }

            /* Weakness 6: Clean Pill Buttons (No ASCII Brackets) */
            .elementor-widget-button .elementor-button,
            a.elementor-button,
            .rsd-btn-black,
            a.rsd-btn-black {
                background-color: #0F172A !important;
                color: #FBFBF9 !important;
                border-radius: 9999px !important;
                padding: 14px 32px !important;
                font-weight: 700 !important;
                font-size: 0.92rem !important;
                letter-spacing: 0.04em !important;
                transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
                box-shadow: none !important;
                border: none !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-decoration: none !important;
            }

            .elementor-widget-button .elementor-button:hover,
            a.elementor-button:hover,
            .rsd-btn-black:hover,
            a.rsd-btn-black:hover {
                background-color: #2563EB !important;
                color: #0F172A !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 14px rgba(197, 160, 89, 0.25) !important;
            }

            /* Weakness 12: Quiet Minimal Chatbot Widget (44px Circle) */
            #rsd-chat-toggle,
            .rsd-chat-badge-trigger {
                width: 44px !important;
                height: 44px !important;
                border-radius: 50% !important;
                background-color: #0F172A !important;
                color: #FBFBF9 !important;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            /* Weakness 14: Native Arabic RTL Typography */
            .rtl h1.elementor-heading-title,
            .rtl h2.elementor-heading-title {
                font-family: 'Cairo', sans-serif !important;
                line-height: 1.35 !important;
                letter-spacing: 0 !important;
            }

            /* Responsive Media Queries Across 360px to 1920px */
            @media (max-width: 991px) {
                .elementor-element .e-con {
                    flex-direction: column !important;
                    width: 100% !important;
                    padding-left: 16px !important;
                    padding-right: 16px !important;
                }
            }
        /* ========================================================= */
            /* 12 TARGETED ART-DIRECTION REFINEMENTS CSS                 */
            /* ========================================================= */
            @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap');

            :root {
                --rsd-serif: 'Cormorant Garamond', 'Playfair Display', serif;
                --rsd-sans: 'Inter', system-ui, -apple-system, sans-serif;
            }

            .elementor-widget-heading h1.elementor-heading-title,
            .elementor-widget-heading h2.elementor-heading-title,
            .elementor-widget-heading h3.elementor-heading-title {
                font-family: var(--rsd-serif) !important;
            }

            /* Refinement 3: Mobile Body Readability (16-18px body, 10-11px eyebrows) */
            @media (max-width: 768px) {
                body, p, .elementor-text-editor, .elementor-text-editor p {
                    font-size: 16px !important;
                    line-height: 1.7 !important;
                }
                .rsd-eyebrow, .elementor-widget-heading span.elementor-heading-title {
                    font-size: 11px !important;
                    letter-spacing: 0.2em !important;
                }
                .rsd-methodology-row {
                    flex-direction: column !important;
                }
            }

            /* Footer Styling */
            .rsd-master-footer {
                position: relative;
                background-color: #0F172A;
                color: #FBFBF9;
                padding: 100px 48px 40px 48px;
                overflow: hidden;
            }
            .rsd-footer-watermark {
                position: absolute;
                bottom: -20px;
                left: 50%;
                transform: translateX(-50%);
                font-size: 120px;
                font-weight: 800;
                color: transparent;
                -webkit-text-stroke: 1px rgba(229, 229, 224, 0.08);
                white-space: nowrap;
                pointer-events: none;
                user-select: none;
            }
            .rsd-footer-inner {
                max-width: 1200px;
                margin: 0 auto;
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 48px;
                position: relative;
                z-index: 2;
            }
            .rsd-footer-col h3 {
                font-family: var(--rsd-serif);
                font-size: 1.6rem;
                color: #FBFBF9;
                margin-bottom: 12px;
            }
            .rsd-footer-desc {
                color: #A0A09A;
                font-size: 0.95rem;
                line-height: 1.65;
                max-width: 320px;
            }
            .rsd-footer-col h4 {
                font-size: 0.75rem;
                letter-spacing: 0.2em;
                color: #2563EB;
                margin-bottom: 20px;
            }
            .rsd-footer-col ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .rsd-footer-col ul li {
                margin-bottom: 10px;
            }
            .rsd-footer-col ul li a {
                color: #A0A09A;
                text-decoration: none;
                font-size: 0.92rem;
                transition: color 0.2s ease;
            }
            .rsd-footer-col ul li a:hover {
                color: #FBFBF9;
            }
            .rsd-footer-bottom {
                max-width: 1200px;
                margin: 60px auto 0 auto;
                padding-top: 24px;
                border-top: 1px solid rgba(225, 225, 220, 0.12);
                text-align: center;
                color: #646460;
                font-size: 0.85rem;
                position: relative;
                z-index: 2;
            }
        /* ========================================================= */
            /* ULTIMATE RESPONSIVE LUXURY FOOTER CSS                     */
            /* ========================================================= */
            .rsd-master-footer {
                position: relative;
                background-color: #0F172A !important;
                color: #FBFBF9 !important;
                padding: 100px 32px 40px 32px !important;
                overflow: hidden !important;
                border-top: 1px solid #E5E5E0 !important;
                font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
            }

            .rsd-footer-watermark {
                position: absolute;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                font-family: 'Cormorant Garamond', serif !important;
                font-size: clamp(3.2rem, 10.5vw, 9rem) !important;
                font-weight: 800 !important;
                color: transparent !important;
                -webkit-text-stroke: 1px rgba(229, 229, 224, 0.07) !important;
                white-space: nowrap !important;
                pointer-events: none !important;
                user-select: none !important;
                letter-spacing: 0.08em !important;
            }

            .rsd-footer-inner {
                max-width: 1240px;
                margin: 0 auto;
                display: grid;
                grid-template-columns: 2fr 1fr 1.2fr 1fr;
                gap: 48px;
                position: relative;
                z-index: 2;
            }

            .rsd-footer-logo {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 1.75rem !important;
                font-weight: 600 !important;
                color: #FBFBF9 !important;
                margin-bottom: 14px !important;
                letter-spacing: -0.02em !important;
            }

            .rsd-footer-desc {
                color: #A0A09A !important;
                font-size: 0.95rem !important;
                line-height: 1.7 !important;
                max-width: 340px !important;
                margin-bottom: 20px !important;
            }

            .rsd-footer-status {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                background: rgba(255, 255, 255, 0.04);
                padding: 6px 14px;
                border-radius: 9999px;
                border: 1px solid rgba(229, 229, 224, 0.12);
            }

            .rsd-status-dot {
                width: 8px;
                height: 8px;
                background-color: #16A34A;
                border-radius: 50%;
                box-shadow: 0 0 8px rgba(22, 163, 74, 0.6);
            }

            .rsd-status-text {
                color: #D4D4CE;
                font-size: 0.82rem;
                font-weight: 500;
            }

            .rsd-footer-heading {
                font-size: 0.75rem !important;
                font-weight: 800 !important;
                letter-spacing: 0.25em !important;
                color: #2563EB !important;
                margin-bottom: 22px !important;
                text-transform: uppercase !important;
            }

            .rsd-footer-links {
                list-style: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .rsd-footer-links li {
                margin-bottom: 12px !important;
            }

            .rsd-footer-links li a,
            .rsd-footer-email,
            .rsd-footer-wa {
                color: #A0A09A !important;
                text-decoration: none !important;
                font-size: 0.92rem !important;
                transition: all 0.2s ease !important;
            }

            .rsd-footer-links li a:hover,
            .rsd-footer-email:hover,
            .rsd-footer-wa:hover {
                color: #FBFBF9 !important;
            }

            .rsd-footer-email {
                color: #2563EB !important;
                font-weight: 500 !important;
            }

            .rsd-footer-info {
                color: #D4D4CE !important;
                font-size: 0.9rem !important;
                line-height: 1.6 !important;
                margin: 0 !important;
            }

            .rsd-footer-text-muted {
                color: #888882 !important;
            }

            .rsd-footer-bottom {
                max-width: 1240px;
                margin: 72px auto 0 auto !important;
                padding-top: 28px !important;
                border-top: 1px solid rgba(229, 229, 224, 0.12) !important;
                position: relative;
                z-index: 2;
            }

            .rsd-footer-bottom-inner {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 16px;
                color: #646460;
                font-size: 0.85rem;
            }

            /* Responsive Media Queries Across 360px to 1024px */
            @media (max-width: 1024px) {
                .rsd-footer-inner {
                    grid-template-columns: 1fr 1fr;
                    gap: 40px;
                }
                .rsd-master-footer {
                    padding: 80px 24px 36px 24px !important;
                }
            }

            @media (max-width: 640px) {
                .rsd-footer-inner {
                    grid-template-columns: 1fr !important;
                    gap: 36px !important;
                }
                .rsd-master-footer {
                    padding: 60px 20px 30px 20px !important;
                }
                .rsd-footer-bottom-inner {
                    flex-direction: column !important;
                    text-align: center !important;
                }
                .rsd-footer-logo {
                    font-size: 1.5rem !important;
                }
            }
        /* ========================================================= */
            /* 3D LIQUID GLASS CARDS FOR METHODOLOGY SECTION             */
            /* ========================================================= */
            .rsd-methodology-row {
                perspective: 1200px !important;
            }

            .rsd-liquid-glass-card {
                position: relative !important;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.85) 0%, rgba(244, 244, 240, 0.65) 100%) !important;
                backdrop-filter: blur(20px) saturate(190%) !important;
                -webkit-backdrop-filter: blur(20px) saturate(190%) !important;
                border: 1px solid rgba(255, 255, 255, 0.95) !important;
                border-bottom: 1px solid rgba(229, 229, 224, 0.85) !important;
                border-radius: 24px !important;
                padding: 36px 28px !important;
                box-shadow: 
                    inset 0 1px 2px rgba(255, 255, 255, 1),
                    inset 0 -1px 1px rgba(0, 0, 0, 0.03),
                    0 16px 36px -10px rgba(0, 0, 0, 0.06),
                    0 4px 14px rgba(0, 0, 0, 0.03) !important;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
                transform-style: preserve-3d !important;
                overflow: hidden !important;
            }

            .rsd-liquid-glass-card::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: -100% !important;
                width: 200% !important;
                height: 100% !important;
                background: linear-gradient(
                    90deg, 
                    transparent 0%, 
                    rgba(255, 255, 255, 0.5) 50%, 
                    transparent 100%
                ) !important;
                transform: skewX(-25deg) !important;
                transition: left 0.75s ease !important;
                pointer-events: none !important;
            }

            .rsd-liquid-glass-card:hover::before {
                left: 100% !important;
            }

            .rsd-liquid-glass-card:hover {
                transform: translateY(-10px) rotateX(4deg) scale(1.02) !important;
                box-shadow: 
                    inset 0 1px 3px rgba(255, 255, 255, 1),
                    0 24px 48px -12px rgba(197, 160, 89, 0.22),
                    0 12px 24px -6px rgba(0, 0, 0, 0.08) !important;
                border-color: rgba(197, 160, 89, 0.5) !important;
            }

            .rsd-step-badge-3d {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 2.5rem !important;
                font-weight: 800 !important;
                color: #2563EB !important;
                line-height: 1 !important;
                margin-bottom: 16px !important;
                display: inline-block !important;
                text-shadow: 0 2px 6px rgba(197, 160, 89, 0.2) !important;
            }

            .rsd-step-title-3d {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 1.35rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 12px !important;
                line-height: 1.3 !important;
            }

            .rsd-step-desc-3d {
                color: #4A4A48 !important;
                font-size: 1.02rem !important;
                line-height: 1.65 !important;
                margin: 0 !important;
            }

            @media (max-width: 991px) {
                .rsd-methodology-row {
                    display: grid !important;
                    grid-template-columns: 1fr 1fr !important;
                    gap: 24px !important;
                }
            }

            @media (max-width: 640px) {
                .rsd-methodology-row {
                    grid-template-columns: 1fr !important;
                    gap: 20px !important;
                }
                .rsd-liquid-glass-card {
                    padding: 28px 20px !important;
                }
            }
        /* ========================================================= */
            /* 3D LIQUID GLASS CARDS FOR POSITIONING MANIFESTO SECTION  */
            /* ========================================================= */
            .rsd-manifesto-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 36px !important;
                perspective: 1200px !important;
            }

            .rsd-liquid-glass-manifesto-card {
                position: relative !important;
                border-radius: 28px !important;
                padding: 44px 36px !important;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
                transform-style: preserve-3d !important;
                overflow: hidden !important;
            }

            .rsd-liquid-glass-manifesto-card::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: -100% !important;
                width: 200% !important;
                height: 100% !important;
                background: linear-gradient(
                    90deg, 
                    transparent 0%, 
                    rgba(255, 255, 255, 0.5) 50%, 
                    transparent 100%
                ) !important;
                transform: skewX(-25deg) !important;
                transition: left 0.75s ease !important;
                pointer-events: none !important;
            }

            .rsd-liquid-glass-manifesto-card:hover::before {
                left: 100% !important;
            }

            /* RSD Standard Card (Left) */
            .rsd-standard-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(240, 253, 244, 0.65) 100%) !important;
                backdrop-filter: blur(20px) saturate(190%) !important;
                -webkit-backdrop-filter: blur(20px) saturate(190%) !important;
                border: 1px solid rgba(22, 163, 74, 0.3) !important;
                box-shadow: 
                    inset 0 1px 2px rgba(255, 255, 255, 1),
                    0 16px 40px -10px rgba(22, 163, 74, 0.12),
                    0 4px 14px rgba(0, 0, 0, 0.03) !important;
            }

            .rsd-standard-card:hover {
                transform: translateY(-10px) rotateX(3deg) scale(1.015) !important;
                box-shadow: 
                    inset 0 1px 3px rgba(255, 255, 255, 1),
                    0 28px 56px -12px rgba(22, 163, 74, 0.22),
                    0 12px 24px -6px rgba(0, 0, 0, 0.06) !important;
                border-color: rgba(22, 163, 74, 0.55) !important;
            }

            /* Conventional Model Card (Right) */
            .rsd-conventional-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(254, 242, 242, 0.6) 100%) !important;
                backdrop-filter: blur(20px) saturate(190%) !important;
                -webkit-backdrop-filter: blur(20px) saturate(190%) !important;
                border: 1px solid rgba(220, 38, 38, 0.2) !important;
                box-shadow: 
                    inset 0 1px 2px rgba(255, 255, 255, 1),
                    0 16px 40px -10px rgba(220, 38, 38, 0.08),
                    0 4px 14px rgba(0, 0, 0, 0.03) !important;
            }

            .rsd-conventional-card:hover {
                transform: translateY(-8px) rotateX(2deg) scale(1.01) !important;
                box-shadow: 
                    inset 0 1px 3px rgba(255, 255, 255, 1),
                    0 24px 48px -12px rgba(220, 38, 38, 0.16),
                    0 12px 24px -6px rgba(0, 0, 0, 0.06) !important;
                border-color: rgba(220, 38, 38, 0.4) !important;
            }

            .rsd-manifesto-badge {
                display: inline-block !important;
                padding: 4px 12px !important;
                border-radius: 9999px !important;
                font-size: 0.72rem !important;
                font-weight: 800 !important;
                letter-spacing: 0.15em !important;
                margin-bottom: 16px !important;
                text-transform: uppercase !important;
            }

            .rsd-standard-badge {
                background: rgba(22, 163, 74, 0.12) !important;
                color: #16A34A !important;
                border: 1px solid rgba(22, 163, 74, 0.25) !important;
            }

            .rsd-conventional-badge {
                background: rgba(220, 38, 38, 0.1) !important;
                color: #DC2626 !important;
                border: 1px solid rgba(220, 38, 38, 0.2) !important;
            }

            .rsd-manifesto-title {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 1.5rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 24px !important;
                line-height: 1.3 !important;
            }

            .rsd-glass-list {
                list-style: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .rsd-glass-list-item {
                display: flex !important;
                align-items: flex-start !important;
                gap: 14px !important;
                padding: 12px 16px !important;
                border-radius: 14px !important;
                margin-bottom: 12px !important;
                background: rgba(255, 255, 255, 0.5) !important;
                border: 1px solid rgba(255, 255, 255, 0.7) !important;
                transition: all 0.25s ease !important;
            }

            .rsd-glass-list-item:hover {
                background: rgba(255, 255, 255, 0.85) !important;
                transform: translateX(4px) !important;
            }

            .rsd-glass-icon {
                font-weight: 800 !important;
                font-size: 1.1rem !important;
                line-height: 1.4 !important;
            }

            .rsd-icon-check {
                color: #16A34A !important;
            }

            .rsd-icon-cross {
                color: #DC2626 !important;
            }

            .rsd-glass-text-green {
                color: #15803D !important;
                font-weight: 600 !important;
                font-size: 1.02rem !important;
                line-height: 1.5 !important;
            }

            .rsd-glass-text-red {
                color: #B91C1C !important;
                font-weight: 500 !important;
                font-size: 1.02rem !important;
                line-height: 1.5 !important;
            }

            @media (max-width: 991px) {
                .rsd-manifesto-grid {
                    grid-template-columns: 1fr !important;
                    gap: 28px !important;
                }
                .rsd-liquid-glass-manifesto-card {
                    padding: 32px 24px !important;
                }
            }
        /* ========================================================= */
            /* ULTRA-VISIBLE 3D LIQUID GLASS CARDS (HIGH CONTRAST)       */
            /* ========================================================= */
            .rsd-methodology-row, .rsd-manifesto-grid {
                perspective: 1200px !important;
            }

            .rsd-liquid-glass-card,
            .elementor-element.rsd-liquid-glass-card,
            div.rsd-liquid-glass-card {
                position: relative !important;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(238, 238, 230, 0.8) 100%) !important;
                backdrop-filter: blur(24px) saturate(200%) !important;
                -webkit-backdrop-filter: blur(24px) saturate(200%) !important;
                border: 1.5px solid rgba(255, 255, 255, 1) !important;
                outline: 1px solid rgba(197, 160, 89, 0.25) !important;
                border-radius: 24px !important;
                padding: 36px 28px !important;
                margin: 6px !important;
                box-shadow: 
                    inset 0 2px 4px rgba(255, 255, 255, 1),
                    inset 0 -2px 4px rgba(0, 0, 0, 0.04),
                    0 20px 44px -10px rgba(0, 0, 0, 0.12),
                    0 8px 20px rgba(0, 0, 0, 0.05) !important;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
                transform-style: preserve-3d !important;
                overflow: hidden !important;
            }

            .rsd-liquid-glass-card::before,
            .elementor-element.rsd-liquid-glass-card::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: -100% !important;
                width: 200% !important;
                height: 100% !important;
                background: linear-gradient(
                    90deg, 
                    transparent 0%, 
                    rgba(255, 255, 255, 0.65) 50%, 
                    transparent 100%
                ) !important;
                transform: skewX(-25deg) !important;
                transition: left 0.75s ease !important;
                pointer-events: none !important;
            }

            .rsd-liquid-glass-card:hover,
            .elementor-element.rsd-liquid-glass-card:hover {
                transform: translateY(-12px) rotateX(4deg) scale(1.025) !important;
                box-shadow: 
                    inset 0 2px 6px rgba(255, 255, 255, 1),
                    0 30px 60px -12px rgba(197, 160, 89, 0.3),
                    0 16px 32px -6px rgba(0, 0, 0, 0.1) !important;
                border-color: rgba(197, 160, 89, 0.6) !important;
                outline-color: rgba(197, 160, 89, 0.5) !important;
            }

            .rsd-liquid-glass-card:hover::before,
            .elementor-element.rsd-liquid-glass-card:hover::before {
                left: 100% !important;
            }

            .rsd-step-badge-3d {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 2.6rem !important;
                font-weight: 800 !important;
                color: #2563EB !important;
                line-height: 1 !important;
                margin-bottom: 16px !important;
                display: inline-block !important;
                text-shadow: 0 2px 6px rgba(197, 160, 89, 0.25) !important;
            }

            .rsd-step-title-3d {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 1.4rem !important;
                font-weight: 700 !important;
                color: #0F172A !important;
                margin-bottom: 12px !important;
                line-height: 1.3 !important;
            }

            .rsd-step-desc-3d {
                color: #4A4A48 !important;
                font-size: 1.02rem !important;
                line-height: 1.65 !important;
                margin: 0 !important;
            }

            /* Ultra-Visible Manifesto Cards */
            .rsd-liquid-glass-manifesto-card,
            .elementor-element.rsd-liquid-glass-manifesto-card {
                position: relative !important;
                border-radius: 28px !important;
                padding: 44px 36px !important;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
                transform-style: preserve-3d !important;
                overflow: hidden !important;
            }

            .rsd-standard-card,
            .elementor-element.rsd-standard-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(236, 253, 243, 0.8) 100%) !important;
                backdrop-filter: blur(24px) saturate(200%) !important;
                -webkit-backdrop-filter: blur(24px) saturate(200%) !important;
                border: 1.5px solid rgba(22, 163, 74, 0.4) !important;
                box-shadow: 
                    inset 0 2px 4px rgba(255, 255, 255, 1),
                    0 20px 48px -10px rgba(22, 163, 74, 0.16),
                    0 8px 20px rgba(0, 0, 0, 0.05) !important;
            }

            .rsd-standard-card:hover,
            .elementor-element.rsd-standard-card:hover {
                transform: translateY(-12px) rotateX(3deg) scale(1.02) !important;
                box-shadow: 
                    inset 0 2px 6px rgba(255, 255, 255, 1),
                    0 32px 64px -12px rgba(22, 163, 74, 0.28),
                    0 16px 32px -6px rgba(0, 0, 0, 0.08) !important;
                border-color: rgba(22, 163, 74, 0.7) !important;
            }

            .rsd-conventional-card,
            .elementor-element.rsd-conventional-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(254, 242, 242, 0.75) 100%) !important;
                backdrop-filter: blur(24px) saturate(200%) !important;
                -webkit-backdrop-filter: blur(24px) saturate(200%) !important;
                border: 1.5px solid rgba(220, 38, 38, 0.3) !important;
                box-shadow: 
                    inset 0 2px 4px rgba(255, 255, 255, 1),
                    0 20px 48px -10px rgba(220, 38, 38, 0.12),
                    0 8px 20px rgba(0, 0, 0, 0.05) !important;
            }

            .rsd-conventional-card:hover,
            .elementor-element.rsd-conventional-card:hover {
                transform: translateY(-10px) rotateX(2deg) scale(1.015) !important;
                box-shadow: 
                    inset 0 2px 6px rgba(255, 255, 255, 1),
                    0 28px 56px -12px rgba(220, 38, 38, 0.22),
                    0 16px 32px -6px rgba(0, 0, 0, 0.08) !important;
                border-color: rgba(220, 38, 38, 0.5) !important;
            }
        /* ========================================================= */
            /* BOLD HIGH-CONTRAST 3D LIQUID GLASS CARDS (IMPOSSIBLE TO MISS) */
            /* ========================================================= */
            .rsd-methodology-row {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                gap: 20px !important;
                margin-top: 40px !important;
                perspective: 1200px !important;
            }

            .rsd-liquid-glass-card,
            .elementor-element.rsd-liquid-glass-card {
                background: #FFFFFF !important;
                border: 2px solid #2563EB !important;
                border-radius: 20px !important;
                padding: 36px 28px !important;
                box-shadow: 
                    0 20px 48px -10px rgba(0, 0, 0, 0.12),
                    0 8px 20px rgba(197, 160, 89, 0.15),
                    inset 0 1px 2px #FFFFFF !important;
                transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
                transform-style: preserve-3d !important;
                position: relative !important;
            }

            .rsd-liquid-glass-card:hover,
            .elementor-element.rsd-liquid-glass-card:hover {
                transform: translateY(-10px) rotateX(4deg) scale(1.02) !important;
                box-shadow: 
                    0 30px 60px -12px rgba(0, 0, 0, 0.18),
                    0 12px 28px rgba(197, 160, 89, 0.3) !important;
                border-color: #0F172A !important;
            }

            /* Positioning Manifesto 3D Cards */
            .rsd-manifesto-grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 32px !important;
                margin-top: 40px !important;
            }

            .rsd-standard-card,
            .elementor-element.rsd-standard-card {
                background: #FFFFFF !important;
                border: 2px solid #16A34A !important;
                border-radius: 24px !important;
                padding: 40px 32px !important;
                box-shadow: 0 20px 48px -10px rgba(22, 163, 74, 0.18) !important;
            }

            .rsd-conventional-card,
            .elementor-element.rsd-conventional-card {
                background: #FFFFFF !important;
                border: 2px solid #DC2626 !important;
                border-radius: 24px !important;
                padding: 40px 32px !important;
                box-shadow: 0 20px 48px -10px rgba(220, 38, 38, 0.15) !important;
            }
        /* ========================================================= */
            /* HERO METRICS 3D CHAMPAGNE GOLD GLASS CARDS                */
            /* ========================================================= */
            .rsd-metrics-row {
                gap: 24px !important;
                perspective: 1200px !important;
            }

            .rsd-metric-3d-card,
            .elementor-element.rsd-metric-3d-card {
                background: linear-gradient(135deg, #FFFFFF 0%, #F9F8F3 100%) !important;
                border: 2px solid #2563EB !important;
                outline: 1px solid rgba(255, 255, 255, 0.9) !important;
                border-radius: 24px !important;
                padding: 32px 24px !important;
                margin: 4px !important;
                box-shadow: 
                    inset 0 2px 4px #FFFFFF,
                    0 18px 44px -10px rgba(197, 160, 89, 0.18),
                    0 6px 16px rgba(0, 0, 0, 0.04) !important;
                transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
                transform-style: preserve-3d !important;
                position: relative !important;
                overflow: hidden !important;
            }

            .rsd-metric-3d-card::before,
            .elementor-element.rsd-metric-3d-card::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: -100% !important;
                width: 200% !important;
                height: 100% !important;
                background: linear-gradient(
                    90deg, 
                    transparent 0%, 
                    rgba(197, 160, 89, 0.15) 50%, 
                    transparent 100%
                ) !important;
                transform: skewX(-25deg) !important;
                transition: left 0.75s ease !important;
                pointer-events: none !important;
            }

            .rsd-metric-3d-card:hover,
            .elementor-element.rsd-metric-3d-card:hover {
                transform: translateY(-10px) rotateX(3deg) scale(1.025) !important;
                box-shadow: 
                    inset 0 2px 6px #FFFFFF,
                    0 28px 56px -12px rgba(197, 160, 89, 0.35),
                    0 12px 24px -6px rgba(0, 0, 0, 0.08) !important;
                border-color: #0F172A !important;
            }

            .rsd-metric-3d-card:hover::before,
            .elementor-element.rsd-metric-3d-card:hover::before {
                left: 100% !important;
            }

            .rsd-metric-3d-card h3.elementor-heading-title,
            .elementor-element.rsd-metric-3d-card h3.elementor-heading-title {
                font-family: 'Cormorant Garamond', 'Playfair Display', serif !important;
                font-size: 3.4rem !important;
                font-weight: 800 !important;
                color: #2563EB !important;
                text-align: center !important;
                line-height: 1 !important;
                margin-bottom: 12px !important;
                text-shadow: 0 3px 10px rgba(197, 160, 89, 0.25) !important;
            }

            .rsd-metric-3d-card p,
            .elementor-element.rsd-metric-3d-card p {
                text-align: center !important;
                color: #0F172A !important;
                font-size: 0.88rem !important;
                font-weight: 700 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.06em !important;
                margin: 0 !important;
                line-height: 1.4 !important;
            }
        /* ========================================================= */
            /* MASTER MOBILE RESPONSIVE UI & TYPOGRAPHY REPAIR (< 768px)  */
            /* ========================================================= */
            @media (max-width: 767px) {
                /* 1. Force 100% Full Width Column Stacking */
                .elementor-element .e-con-inner,
                .e-con.e-child,
                .e-con.e-parent {
                    flex-direction: column !important;
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box !important;
                }

                .elementor-column, 
                .elementor-element[data-element_type="container"] > .e-con-inner > .e-con {
                    width: 100% !important;
                    max-width: 100% !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                }

                /* 2. Hero Section Typography on Mobile */
                h1.elementor-heading-title,
                .elementor-widget-heading h1 {
                    font-size: 2.1rem !important;
                    line-height: 1.25 !important;
                    text-align: center !important;
                    padding: 0 4px !important;
                    word-break: normal !important;
                    hyphens: none !important;
                }

                .elementor-widget-text-editor p {
                    font-size: 1.02rem !important;
                    line-height: 1.65 !important;
                    text-align: center !important;
                }

                /* 3. Case Studies Section Headings & Text */
                h2.elementor-heading-title,
                .elementor-widget-heading h2 {
                    font-size: 1.75rem !important;
                    line-height: 1.3 !important;
                    text-align: center !important;
                }

                h4.elementor-heading-title,
                .elementor-widget-heading h4 {
                    font-size: 1.3rem !important;
                    line-height: 1.35 !important;
                    text-align: center !important;
                }

                /* 4. Fix Squished & Warped Buttons on Mobile */
                .rsd-btn-black,
                .rsd-btn-outline-main,
                .elementor-button-wrapper,
                .elementor-button {
                    display: block !important;
                    width: 100% !important;
                    max-width: 320px !important;
                    margin: 14px auto !important;
                    text-align: center !important;
                    border-radius: 9999px !important;
                    padding: 14px 24px !important;
                    white-space: normal !important;
                    box-sizing: border-box !important;
                }

                /* 5. Fix Image Alignment & Size on Mobile */
                .elementor-widget-image,
                .elementor-widget-image img {
                    width: 100% !important;
                    height: auto !important;
                    max-width: 100% !important;
                    margin: 0 auto 16px auto !important;
                    display: block !important;
                    border-radius: 14px !important;
                }

                /* 6. Card Grids Stacking on Mobile */
                .rsd-metrics-row,
                .rsd-methodology-row,
                .rsd-manifesto-grid {
                    display: flex !important;
                    flex-direction: column !important;
                    gap: 18px !important;
                    width: 100% !important;
                }

                .rsd-metric-3d-card,
                .rsd-liquid-glass-card,
                .rsd-liquid-glass-manifesto-card {
                    width: 100% !important;
                    margin: 0 0 14px 0 !important;
                    padding: 28px 20px !important;
                    box-sizing: border-box !important;
                }
            }
        /* ========================================================= */
            /* BULLETPROOF DEVICE-SPECIFIC VISIBILITY ENFORCEMENT         */
            /* ========================================================= */
            @media (min-width: 1025px) {
                .elementor-hidden-desktop,
                .elementor-element.elementor-hidden-desktop {
                    display: none !important;
                }
            }

            @media (min-width: 768px) and (max-width: 1024px) {
                .elementor-hidden-tablet,
                .elementor-element.elementor-hidden-tablet {
                    display: none !important;
                }
            }

            @media (max-width: 767px) {
                .elementor-hidden-mobile,
                .elementor-element.elementor-hidden-mobile {
                    display: none !important;
                }
            }
        /* Slightly shrink Hero H1 title font size as requested */
            .elementor-widget-heading h1.elementor-heading-title,
            .rsd-hero-title h1,
            h1.rsd-hero-h1 {
                font-size: 2.5rem !important;
                line-height: 1.25 !important;
            }
            @media (max-width: 767px) {
                .elementor-widget-heading h1.elementor-heading-title,
                .rsd-hero-title h1,
                h1.rsd-hero-h1 {
                    font-size: 1.65rem !important;
                    line-height: 1.3 !important;
                }
            }
        /* ========================================================= */
            /* SILICON VALLEY $10K+ QUIET LUXURY PORTFOLIO DESIGN SYSTEM  */
            /* ========================================================= */
            .rsd-case-study-card {
                background: rgba(255, 255, 255, 0.85) !important;
                backdrop-filter: blur(16px) saturate(180%) !important;
                -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
                border: 1px solid rgba(197, 160, 89, 0.28) !important;
                border-radius: 24px !important;
                padding: 48px !important;
                margin-bottom: 48px !important;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(197, 160, 89, 0.1) !important;
                transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1) !important;
                position: relative !important;
                overflow: hidden !important;
                will-change: transform, box-shadow !important;
            }

            .rsd-case-study-card:hover {
                transform: translateY(-8px) scale(1.008) !important;
                border-color: rgba(197, 160, 89, 0.65) !important;
                box-shadow: 0 32px 70px rgba(197, 160, 89, 0.18), 0 10px 30px rgba(0, 0, 0, 0.08) !important;
            }

            .rsd-case-study-card::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: -100% !important;
                width: 50% !important;
                height: 100% !important;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent) !important;
                transform: skewX(-25deg) !important;
                transition: left 0.8s ease-in-out !important;
                pointer-events: none !important;
            }

            .rsd-case-study-card:hover::before {
                left: 150% !important;
            }

            /* Image Mockup 3D Hover */
            .rsd-case-study-card .elementor-widget-image img {
                border-radius: 16px !important;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12) !important;
                transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }

            .rsd-case-study-card:hover .elementor-widget-image img {
                transform: scale(1.03) translateY(-4px) !important;
                box-shadow: 0 24px 55px rgba(0, 0, 0, 0.18) !important;
            }

            /* Number Badge Pill */
            .rsd-project-badge {
                display: inline-block !important;
                padding: 6px 16px !important;
                background: rgba(197, 160, 89, 0.08) !important;
                border: 1px solid rgba(197, 160, 89, 0.3) !important;
                border-radius: 9999px !important;
                color: #2563EB !important;
                font-weight: 800 !important;
                letter-spacing: 2px !important;
                font-size: 0.85rem !important;
                margin-bottom: 16px !important;
                backdrop-filter: blur(8px) !important;
            }

            /* Interactive Luxury Button Hover */
            .rsd-case-study-card .elementor-widget-button a.elementor-button,
            .rsd-btn-luxury a.elementor-button {
                background: #FFFFFF !important;
                color: #FBFBF9 !important;
                border-radius: 9999px !important;
                padding: 16px 36px !important;
                font-weight: 600 !important;
                letter-spacing: 0.5px !important;
                box-shadow: 0 10px 24px rgba(17, 17, 17, 0.18) !important;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }

            .rsd-case-study-card .elementor-widget-button a.elementor-button:hover,
            .rsd-btn-luxury a.elementor-button:hover {
                background: #2563EB !important;
                color: #0F172A !important;
                box-shadow: 0 16px 36px rgba(197, 160, 89, 0.4) !important;
                transform: translateY(-2px) !important;
            }

            @media (max-width: 767px) {
                .rsd-case-study-card {
                    padding: 24px 18px !important;
                    border-radius: 18px !important;
                    margin-bottom: 28px !important;
                }
            }
        /* MASTER ULTRA-LUXURY HERO SECTION STYLES */
        .rsd-hero-master-sec {
            position: relative !important;
            background: #FFFFFF !important;
            padding: 110px 20px 80px 20px !important;
            overflow: hidden !important;
            text-align: center !important;
            box-sizing: border-box !important;
            margin-top: 0 !important;
        }
        .rsd-hero-ambient-glow {
            position: absolute !important;
            top: 0 !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: 600px !important;
            height: 400px !important;
            background: radial-gradient(circle, rgba(197, 160, 89, 0.15) 0%, rgba(17, 17, 17, 0) 70%) !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }
        .rsd-hero-container {
            position: relative !important;
            z-index: 2 !important;
            max-width: 1050px !important;
            margin: 0 auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
        }
        .rsd-hero-eyebrow {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            color: #2563EB !important;
            font-size: 0.92rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.05em !important;
            text-transform: uppercase !important;
            margin-bottom: 24px !important;
            padding: 8px 20px !important;
            background: rgba(197, 160, 89, 0.08) !important;
            border: 1px solid rgba(197, 160, 89, 0.25) !important;
            border-radius: 30px !important;
        }
        .rsd-hero-h1 {
            color: #FFFFFF !important;
            font-size: clamp(2rem, 4.5vw, 3.4rem) !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
            margin: 0 0 24px 0 !important;
            max-width: 950px !important;
        }
        .rsd-gold-text {
            color: #2563EB !important;
        }
        .rsd-hero-subtext {
            color: #CBD5E1 !important;
            font-size: clamp(1rem, 2vw, 1.2rem) !important;
            line-height: 1.75 !important;
            margin: 0 0 40px 0 !important;
            max-width: 820px !important;
        }
        .rsd-hero-cta-group {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 16px !important;
            flex-wrap: wrap !important;
            width: 100% !important;
        }
        .rsd-hero-btn-primary {
            background: #2563EB !important;
            color: #0F172A !important;
            border: none !important;
            padding: 16px 36px !important;
            border-radius: 50px !important;
            font-weight: 800 !important;
            font-size: 1.05rem !important;
            cursor: pointer !important;
            box-shadow: 0 10px 30px rgba(197, 160, 89, 0.3) !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .rsd-hero-btn-primary:hover {
            transform: translateY(-2px) scale(1.03) !important;
            box-shadow: 0 15px 40px rgba(197, 160, 89, 0.45) !important;
        }
        .rsd-hero-btn-secondary {
            background: transparent !important;
            color: #FFFFFF !important;
            border: 1px solid #2563EB !important;
            padding: 16px 36px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 1.05rem !important;
            cursor: pointer !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .rsd-hero-btn-secondary:hover {
            background: rgba(197, 160, 89, 0.12) !important;
            transform: translateY(-2px) !important;
        }
        @media (max-width: 768px) {
            .rsd-hero-master-sec {
                padding: 75px 16px 50px 16px !important;
            }
            .rsd-hero-h1 {
                font-size: 1.8rem !important;
                line-height: 1.3 !important;
            }
            .rsd-hero-cta-group {
                flex-direction: column !important;
                gap: 12px !important;
            }
            .rsd-hero-btn-primary,
            .rsd-hero-btn-secondary {
                width: 100% !important;
                box-sizing: border-box !important;
            }
        }
        /* FRESH LIGHT HERO SECTION STYLES (NO BLACK, NO GOLD) */
        .rsd-hero-fresh-sec {
            position: relative !important;
            background: linear-gradient(180deg, #F8FAFC 0%, #FFFFFF 100%) !important;
            padding: 100px 20px 70px 20px !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
            margin-top: 0 !important;
            border-bottom: 1px solid #F1F5F9 !important;
        }
        .rsd-hero-fresh-container {
            max-width: 1180px !important;
            margin: 0 auto !important;
            display: grid !important;
            grid-template-columns: 1.2fr 0.8fr !important;
            gap: 40px !important;
            align-items: center !important;
        }
        .rsd-hero-fresh-eyebrow {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            color: #2563EB !important;
            font-size: 0.9rem !important;
            font-weight: 700 !important;
            margin-bottom: 20px !important;
            padding: 8px 18px !important;
            background: #EFF6FF !important;
            border: 1px solid #BFDBFE !important;
            border-radius: 30px !important;
        }
        .rsd-hero-fresh-h1 {
            color: #0F172A !important;
            font-size: clamp(2rem, 4vw, 3.2rem) !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
            margin: 0 0 20px 0 !important;
        }
        .rsd-vivid-text {
            background: linear-gradient(135deg, #2563EB 0%, #059669 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
        }
        .rsd-hero-fresh-subtext {
            color: #475569 !important;
            font-size: 1.1rem !important;
            line-height: 1.75 !important;
            margin: 0 0 32px 0 !important;
        }
        .rsd-hero-fresh-cta-group {
            display: flex !important;
            align-items: center !important;
            gap: 14px !important;
            flex-wrap: wrap !important;
        }
        .rsd-hero-fresh-btn-primary {
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
            color: #FFFFFF !important;
            border: none !important;
            padding: 16px 32px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
            cursor: pointer !important;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25) !important;
            transition: all 0.25s ease !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .rsd-hero-fresh-btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 14px 35px rgba(37, 99, 235, 0.35) !important;
        }
        .rsd-hero-fresh-btn-secondary {
            background: #FFFFFF !important;
            color: #0F172A !important;
            border: 1px solid #E2E8F0 !important;
            padding: 16px 32px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
            cursor: pointer !important;
            transition: all 0.25s ease !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03) !important;
        }
        .rsd-hero-fresh-btn-secondary:hover {
            background: #F8FAFC !important;
            border-color: #CBD5E1 !important;
            transform: translateY(-2px) !important;
        }

        /* Visual Revenue Showcase Card */
        .rsd-hero-visual-card {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 24px !important;
            padding: 24px !important;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.08) !important;
        }
        .rsd-visual-card-hdr {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding-bottom: 16px !important;
            border-bottom: 1px solid #F1F5F9 !important;
            margin-bottom: 20px !important;
        }
        .rsd-visual-badge {
            background: #F0FDF4 !important;
            border: 1px solid #DCFCE7 !important;
            color: #166534 !important;
            padding: 6px 14px !important;
            border-radius: 20px !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
        }
        .rsd-visual-grid {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 14px !important;
        }
        .rsd-visual-stat {
            background: #F8FAFC !important;
            border: 1px solid #F1F5F9 !important;
            padding: 16px 20px !important;
            border-radius: 16px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }
        .rsd-stat-val {
            font-size: 1.4rem !important;
            font-weight: 800 !important;
            color: #2563EB !important;
        }
        .rsd-stat-lbl {
            font-size: 0.9rem !important;
            color: #475569 !important;
            font-weight: 600 !important;
        }

        @media (max-width: 900px) {
            .rsd-hero-fresh-container {
                grid-template-columns: 1fr !important;
                gap: 30px !important;
            }
            .rsd-hero-fresh-sec {
                padding: 70px 16px 40px 16px !important;
            }
            .rsd-hero-fresh-h1 {
                font-size: 1.8rem !important;
                line-height: 1.3 !important;
            }
            .rsd-hero-fresh-cta-group {
                flex-direction: column !important;
                gap: 12px !important;
            }
            .rsd-hero-fresh-btn-primary,
            .rsd-hero-fresh-btn-secondary {
                width: 100% !important;
                box-sizing: border-box !important;
            }
        }
        /* SUPPRESS OLD HERO DUPLICATES AND ENFORCE ULTRA-CLEAN PUNCHY HERO */
        body.home section.rsd-hero-sec,
        body.page-id-12 section.rsd-hero-sec,
        body.page-id-163 section.rsd-hero-sec,
        .rsd-hero-master-sec,
        .elementor-element-7a8e9e4 {
            display: none !important;
        }

        .rsd-hero-fresh-sec {
            display: block !important;
            padding: 90px 20px 50px 20px !important;
        }
        .rsd-hero-fresh-h1 {
            font-size: clamp(2.2rem, 4vw, 3.4rem) !important;
            letter-spacing: -0.02em !important;
        }
        .rsd-hero-fresh-subtext {
            max-width: 650px !important;
            font-size: 1.1rem !important;
        }
        /* =========================================================
           GLOBAL MASTER LIGHT COLOR ENFORCER (NO BLACK, NO GOLD)
           ========================================================= */
        body,
        body.home,
        body.page,
        .site-content,
        #page,
        .entry-content,
        .elementor-page {
            background-color: #FFFFFF !important;
            color: #0F172A !important;
        }

        /* Enforce Light Containers for All Cards & Sections */
        .elementor-section,
        .elementor-container,
        .elementor-card,
        .rsd-card,
        .rsd-sec,
        .rsd-liquid-glass-card,
        .rsd-metric-3d-card,
        .rsd-luxury-portfolio-card {
            background-color: #FFFFFF !important;
            border-color: #E2E8F0 !important;
            color: #0F172A !important;
        }

        /* Headings & Text Color Enforcer */
        h1, h2, h3, h4, h5, h6,
        .elementor-heading-title {
            color: #0F172A !important;
        }
        p, span, li, a {
            color: #334155 !important;
        }
        a:hover {
            color: #2563EB !important;
        }

        /* Suppress Any Stray Black or Gold Gradients Globally */
        *[style*="background: #0F172A"],
        *[style*="#0F172A"],
        *[style*="#2563EB"],
        *[style*="#2563EB"] {
            background: #FFFFFF !important;
            color: #0F172A !important;
            border-color: #E2E8F0 !important;
        }
        /* NUCLEAR CONTRAST & LEGIBILITY ENFORCER (NO DARK CONTAINERS WITH DARK TEXT) */
        
        /* 1. Suppress Old Hero Section Containers Completely */
        body.home section:has(h1:contains("حرر فندقك")),
        body.home div:has(h1:contains("حرر فندقك")),
        body.page-id-163 section:has(h1:contains("حرر فندقك")),
        .elementor-element-7a8e9e4,
        .rsd-hero-master-sec {
            display: none !important;
            height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* 2. Fix Dark Containers (e.g. "نحن لا نبني مجرد مواقع") */
        *[class*="elementor-element"]:has(h2:contains("نحن لا نبني")),
        *[class*="elementor-element"]:has(p:contains("نحن لا نبني")),
        .rsd-sec-dark,
        section[style*="background: #000"],
        section[style*="background: #111"],
        div[style*="background: #000"],
        div[style*="background: #111"] {
            background-color: #F8FAFC !important;
            color: #0F172A !important;
            border-top: 1px solid #E2E8F0 !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }

        /* Enforce Dark High-Contrast Navy Text Everywhere */
        h1, h2, h3, h4, h5, h6,
        .elementor-heading-title,
        .entry-title {
            color: #0F172A !important;
            font-weight: 800 !important;
            text-shadow: none !important;
        }

        p, span, li, td, th {
            color: #334155 !important;
            font-weight: 500 !important;
        }

        /* 3. Fix All Dark Buttons (Replace black buttons with Electric Blue) */
        button,
        a.button,
        .button,
        a.rsd-btn-black,
        button.rsd-btn-black,
        .elementor-button {
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
            color: #FFFFFF !important;
            border: none !important;
            font-weight: 700 !important;
            border-radius: 50px !important;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25) !important;
            text-shadow: none !important;
        }

        button:hover,
        a.button:hover,
        .button:hover,
        .elementor-button:hover {
            background: #1D4ED8 !important;
            color: #FFFFFF !important;
            transform: translateY(-1px) !important;
        }

        /* 4. Fix Footer Background & Contrast */
        #rsdUniversalFooter,
        footer,
        .site-footer {
            background-color: #F8FAFC !important;
            border-top: 1px solid #E2E8F0 !important;
            color: #0F172A !important;
        }
        #rsdUniversalFooter *,
        footer *,
        .site-footer * {
            color: #475569 !important;
        }
        #rsdUniversalFooter h4,
        footer h4,
        .site-footer h4 {
            color: #0F172A !important;
            font-weight: 800 !important;
        }
        #rsdUniversalFooter a:hover,
        footer a:hover,
        .site-footer a:hover {
            color: #2563EB !important;
        }
        /* ABSOLUTE SUPPRESSION FOR THE "نبني ونطور لك" SECTION */
        section:has(*:contains("نبني ونطور لك")),
        div:has(*:contains("نبني ونطور لك")),
        section:has(*:contains("استوديو تصميم وبرمجة")),
        div:has(*:contains("استوديو تصميم وبرمجة")) {
            display: none !important;
            height: 0 !important;
            min-height: 0 !important;
            max-height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
        /* SAFE NON-DESTRUCTIVE TARGETED SECTION SUPPRESSION */
        .elementor-top-section:has(*:contains("حرر فندقك")),
        .elementor-top-section:has(*:contains("نبني ونطور")),
        .elementor-top-section:has(*:contains("استوديو تصميم")),
        .elementor-element-7a8e9e4,
        .rsd-hero-master-sec {
            display: none !important;
        }

        /* PRESERVE ALL ELEMENTOR LAYOUT GRID & FLEXBOX CONTAINERS */
        .elementor-section,
        .elementor-container,
        .elementor-column,
        .elementor-widget-wrap {
            box-sizing: border-box !important;
        }
        .elementor-container {
            display: flex !important;
            margin-right: auto !important;
            margin-left: auto !important;
            position: relative !important;
        }
        /* OUTSETA / LINEAR SUNSET MESH MASTER REDESIGN STYLES */
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Readex+Pro:wght@400;600;700;800&display=swap');

        .rsd-outseta-master-wrap {
            font-family: 'Readex Pro', 'Cairo', system-ui, sans-serif !important;
            background: #FFFFFF !important;
            color: #0B0F19 !important;
            width: 100% !important;
            overflow-x: hidden !important;
        }

        /* SECTION 1: SUNSET MESH GLOW HERO */
        .rsd-outseta-hero {
            position: relative !important;
            background: radial-gradient(circle at 50% 0%, #FFD1B3 0%, #FEE2E2 35%, #F5F3FF 70%, #FFFFFF 100%) !important;
            padding: 90px 20px 70px 20px !important;
            text-align: center !important;
        }
        .rsd-outseta-hero-container {
            max-width: 1180px !important;
            margin: 0 auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
        }
        .rsd-outseta-pill {
            background: rgba(255, 255, 255, 0.9) !important;
            border: 1px solid rgba(255, 94, 126, 0.3) !important;
            color: #FF5E7E !important;
            padding: 8px 20px !important;
            border-radius: 30px !important;
            font-size: 0.88rem !important;
            font-weight: 700 !important;
            margin-bottom: 24px !important;
            box-shadow: 0 4px 15px rgba(255, 94, 126, 0.1) !important;
            backdrop-filter: blur(10px) !important;
        }
        .rsd-outseta-h1 {
            color: #0B0F19 !important;
            font-size: clamp(2.2rem, 4.5vw, 3.6rem) !important;
            font-weight: 800 !important;
            line-height: 1.25 !important;
            margin: 0 0 24px 0 !important;
            max-width: 950px !important;
        }
        .rsd-gradient-text {
            background: linear-gradient(135deg, #FF5E7E 0%, #6366F1 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
        }
        .rsd-outseta-subtext {
            color: #4B5563 !important;
            font-size: 1.15rem !important;
            line-height: 1.75 !important;
            margin: 0 0 36px 0 !important;
            max-width: 780px !important;
        }
        .rsd-outseta-cta-group {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 16px !important;
            flex-wrap: wrap !important;
            margin-bottom: 60px !important;
        }
        .rsd-outseta-btn-primary {
            background: #0B0F19 !important;
            color: #FFFFFF !important;
            border: none !important;
            padding: 16px 36px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 1.05rem !important;
            cursor: pointer !important;
            box-shadow: 0 10px 25px rgba(11, 15, 25, 0.25) !important;
            transition: all 0.25s ease !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .rsd-outseta-btn-primary:hover {
            transform: translateY(-2px) scale(1.02) !important;
            box-shadow: 0 14px 35px rgba(11, 15, 25, 0.35) !important;
        }
        .rsd-outseta-btn-secondary {
            background: rgba(255, 255, 255, 0.85) !important;
            color: #1F2937 !important;
            border: 1px solid #E5E7EB !important;
            padding: 16px 36px !important;
            border-radius: 50px !important;
            font-weight: 700 !important;
            font-size: 1.05rem !important;
            cursor: pointer !important;
            transition: all 0.25s ease !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            backdrop-filter: blur(10px) !important;
        }
        .rsd-outseta-btn-secondary:hover {
            background: #FFFFFF !important;
            border-color: #CBD5E1 !important;
            transform: translateY(-2px) !important;
        }

        /* 3-CARD SHOWCASE STRIP */
        .rsd-showcase-strip {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 24px !important;
            width: 100% !important;
            max-width: 1100px !important;
        }
        .rsd-showcase-card {
            background: #FFFFFF !important;
            border: 1px solid rgba(229, 231, 235, 0.8) !important;
            border-radius: 20px !important;
            overflow: hidden !important;
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.08) !important;
            transition: transform 0.25s ease !important;
            text-align: right !important;
        }
        .rsd-showcase-card:hover {
            transform: translateY(-4px) !important;
        }
        .rsd-showcase-img-wrap {
            height: 180px !important;
            overflow: hidden !important;
            background: #F9FAFB !important;
        }
        .rsd-showcase-img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }
        .rsd-showcase-body {
            padding: 20px !important;
        }
        .rsd-showcase-body h4 {
            margin: 0 0 6px 0 !important;
            color: #0B0F19 !important;
            font-size: 1.15rem !important;
            font-weight: 800 !important;
        }
        .rsd-showcase-body p {
            margin: 0 !important;
            color: #6B7280 !important;
            font-size: 0.9rem !important;
        }

        /* SECTION 2: AUTHENTIC FEEDBACK STRIP */
        .rsd-outseta-sec {
            padding: 80px 20px !important;
        }
        .rsd-sec-container {
            max-width: 1180px !important;
            margin: 0 auto !important;
        }
        .rsd-sec-title {
            text-align: center !important;
            font-size: clamp(1.8rem, 3.5vw, 2.5rem) !important;
            font-weight: 800 !important;
            color: #0B0F19 !important;
            margin-bottom: 40px !important;
        }
        .rsd-feedback-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 24px !important;
        }
        .rsd-feedback-card {
            background: #FFFFFF !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 20px !important;
            padding: 28px !important;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
        }
        .rsd-quote-text {
            color: #374151 !important;
            font-size: 1rem !important;
            line-height: 1.7 !important;
            margin-bottom: 20px !important;
        }
        .rsd-author-info strong {
            display: block !important;
            color: #0B0F19 !important;
            font-size: 0.95rem !important;
        }
        .rsd-author-info span {
            color: #6B7280 !important;
            font-size: 0.85rem !important;
        }

        /* SECTION 3 & 6: DEEP MIDNIGHT SLATE BREAKOUT CONTAINERS */
        .rsd-dark-breakout-sec {
            background: #0B0F19 !important;
            padding: 90px 20px !important;
            color: #FFFFFF !important;
            position: relative !important;
            overflow: hidden !important;
        }
        .rsd-dark-container {
            max-width: 1050px !important;
            margin: 0 auto !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .rsd-dark-badge {
            display: inline-block !important;
            color: #FF5E7E !important;
            background: rgba(255, 94, 126, 0.12) !important;
            border: 1px solid rgba(255, 94, 126, 0.3) !important;
            padding: 6px 16px !important;
            border-radius: 20px !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            margin-bottom: 20px !important;
        }
        .rsd-dark-h2 {
            color: #FFFFFF !important;
            font-size: clamp(2rem, 4vw, 3rem) !important;
            font-weight: 800 !important;
            margin-bottom: 20px !important;
        }
        .rsd-dark-subtext {
            color: #9CA3AF !important;
            font-size: 1.1rem !important;
            max-width: 700px !important;
            margin-bottom: 40px !important;
        }

        .rsd-dark-mockup-wrap {
            background: #131B2E !important;
            border: 1px solid rgba(255, 94, 126, 0.25) !important;
            border-radius: 20px !important;
            overflow: hidden !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
        }
        .rsd-mockup-hdr {
            background: #0F172A !important;
            padding: 14px 20px !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .rsd-dot {
            width: 10px !important;
            height: 10px !important;
            border-radius: 50% !important;
        }
        .rsd-dot.red { background: #EF4444 !important; }
        .rsd-dot.yellow { background: #F59E0B !important; }
        .rsd-dot.green { background: #10B981 !important; }
        .rsd-mockup-title {
            color: #94A3B8 !important;
            font-size: 0.85rem !important;
            margin-right: auto !important;
        }
        .rsd-mockup-content {
            padding: 30px 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-around !important;
            flex-wrap: wrap !important;
            gap: 20px !important;
        }
        .rsd-flow-step {
            display: flex !important;
            align-items: center !important;
            gap: 14px !important;
            background: rgba(255, 255, 255, 0.04) !important;
            padding: 16px 24px !important;
            border-radius: 14px !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .rsd-step-num {
            color: #FF5E7E !important;
            font-size: 1.4rem !important;
            font-weight: 800 !important;
        }
        .rsd-flow-arrow {
            color: #6366F1 !important;
            font-size: 1.5rem !important;
        }

        /* SECTION 4: BENTO GRID MODULAR PRICING */
        .rsd-bento-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)) !important;
            gap: 20px !important;
            margin-bottom: 30px !important;
        }
        .rsd-bento-card {
            background: #FFFFFF !important;
            border: 1px solid #E5E7EB !important;
            border-radius: 20px !important;
            padding: 28px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04) !important;
            transition: transform 0.25s ease !important;
        }
        .rsd-bento-card:hover {
            transform: translateY(-4px) !important;
            border-color: #6366F1 !important;
        }
        .rsd-bento-price {
            font-size: 2rem !important;
            font-weight: 800 !important;
            color: #6366F1 !important;
            margin-bottom: 12px !important;
        }
        .rsd-bento-card h3 {
            color: #0B0F19 !important;
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            margin: 0 0 10px 0 !important;
        }
        .rsd-bento-card p {
            color: #6B7280 !important;
            font-size: 0.92rem !important;
            margin: 0 !important;
            line-height: 1.6 !important;
        }

        .rsd-bundle-card {
            background: linear-gradient(135deg, #FFF5F7 0%, #EEF2FF 100%) !important;
            border: 2px solid #FF5E7E !important;
            border-radius: 24px !important;
            padding: 32px !important;
            position: relative !important;
        }
        .rsd-bundle-badge {
            background: #FF5E7E !important;
            color: #FFFFFF !important;
            padding: 6px 16px !important;
            border-radius: 20px !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            display: inline-block !important;
            margin-bottom: 16px !important;
        }
        .rsd-bundle-content {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 20px !important;
        }

        /* SECTION 5: PRACTICAL COMPARISON */
        .rsd-comp-grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 24px !important;
        }
        .rsd-comp-card {
            border-radius: 20px !important;
            padding: 32px !important;
        }
        .rsd-before-card {
            background: #FEF2F2 !important;
            border: 1px solid #FCA5A5 !important;
        }
        .rsd-after-card {
            background: #ECFDF5 !important;
            border: 2px solid #10B981 !important;
        }
        .rsd-comp-list {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 14px !important;
        }
        .rsd-comp-list li {
            font-size: 0.98rem !important;
            color: #1F2937 !important;
            font-weight: 600 !important;
        }

        /* GUARANTEE BOX */
        .rsd-guarantee-box {
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #F9FAFB !important;
            padding: 16px 28px !important;
            border-radius: 30px !important;
            display: inline-block !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
            max-width: 800px !important;
        }

        @media (max-width: 900px) {
            .rsd-comp-grid { grid-template-columns: 1fr !important; }
            .rsd-outseta-hero { padding: 70px 16px 50px 16px !important; }
            .rsd-outseta-sec { padding: 60px 16px !important; }
            .rsd-dark-breakout-sec { padding: 60px 16px !important; }
        }
        /* AWARD-WINNING HIGH-CONVERTING SAAS LAYOUT STYLES (PURE HTML/CSS - NO BROKEN IMAGES) */
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Readex+Pro:wght@400;600;700;800&display=swap');

        .rsd-award-saas-wrap {
            font-family: 'Readex Pro', 'Cairo', system-ui, sans-serif !important;
            background: #FFFFFF !important;
            color: #0F172A !important;
            width: 100% !important;
            overflow-x: hidden !important;
        }

        /* SECTION 1: MESH GLOW HERO */
        /* ==========================================================================
           REFINED LUXURY SHINY BUTTON (Zero Artifacts, 100% Crisp White Text)
           ========================================================================== */
        @property --gradient-angle {
            syntax: "<angle>";
            initial-value: 0deg;
            inherits: false;
        }

        .shiny-cta {
            --shiny-cta-bg: #09090B;
            --shiny-cta-fg: #FFFFFF;
            --shiny-cta-highlight: #3B82F6;
            --shiny-cta-shine: #93C5FD;
            --animation: gradient-angle 3.5s linear infinite;
            
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 52px !important;
            padding: 0 32px !important;
            font-family: 'Plus Jakarta Sans', 'Inter', 'Cairo', -apple-system, sans-serif !important;
            font-size: 1rem !important;
            font-weight: 700 !important;
            letter-spacing: -0.01em !important;
            color: #FFFFFF !important;
            border-radius: 9999px !important;
            cursor: pointer !important;
            text-decoration: none !important;
            border: 1px solid transparent !important;
            background: linear-gradient(var(--shiny-cta-bg), var(--shiny-cta-bg)) padding-box,
                conic-gradient(
                    from var(--gradient-angle),
                    transparent 0%,
                    var(--shiny-cta-highlight) 10%,
                    var(--shiny-cta-shine) 20%,
                    var(--shiny-cta-highlight) 30%,
                    transparent 40%,
                    transparent 100%
                ) border-box !important;
            box-shadow: 0 4px 20px -2px rgba(59, 130, 246, 0.45), 0 2px 6px rgba(0, 0, 0, 0.2) !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            animation: var(--animation) !important;
            overflow: hidden !important;
        }

        .shiny-cta span {
            position: relative !important;
            z-index: 2 !important;
            color: #FFFFFF !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5) !important;
        }

        .shiny-cta::after {
            content: "" !important;
            position: absolute !important;
            inset: 0 !important;
            background: radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.35), transparent 70%) !important;
            opacity: 0 !important;
            transition: opacity 0.3s ease !important;
            pointer-events: none !important;
            border-radius: inherit !important;
        }

        .shiny-cta:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 28px -2px rgba(59, 130, 246, 0.65), 0 4px 12px rgba(0, 0, 0, 0.3) !important;
            border-color: rgba(147, 197, 253, 0.6) !important;
        }

        .shiny-cta:hover::after {
            opacity: 1 !important;
        }

        .shiny-cta:active {
            transform: translateY(0px) scale(0.98) !important;
        }

        @keyframes gradient-angle {
            to { --gradient-angle: 360deg; }
        }

        .rsd-btn-showcase {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 52px !important;
            padding: 0 28px !important;
            font-family: 'Plus Jakarta Sans', 'Inter', 'Cairo', -apple-system, sans-serif !important;
            font-size: 0.98rem !important;
            font-weight: 700 !important;
            border-radius: 9999px !important;
            background: #FFFFFF !important;
            color: #09090B !important;
            border: 1px solid #E2E8F0 !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important;
            text-decoration: none !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            cursor: pointer !important;
        }
        .rsd-btn-showcase:hover {
            background: #F8FAFC !important;
            border-color: #CBD5E1 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08) !important;
            color: #09090B !important;
        }

        /* ==========================================================================
           LUXURY DARK ROI CALCULATOR & MODULAR SOLUTIONS SECTION
           ========================================================================== */
        .rsd-roi-section {
            background: #09090B !important;
            color: #FAFAFA !important;
            padding: 90px 20px 80px 20px !important;
            position: relative !important;
            overflow: hidden !important;
            border-top: 1px solid #1E293B !important;
            border-bottom: 1px solid #1E293B !important;
        }
        .rsd-roi-ambient-glow {
            position: absolute !important;
            top: 20% !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: 800px !important;
            height: 400px !important;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, rgba(139, 92, 246, 0.08) 50%, transparent 70%) !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }
        .rsd-roi-container {
            max-width: 1100px !important;
            margin: 0 auto !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .rsd-roi-pill {
            background: rgba(30, 41, 59, 0.8) !important;
            border: 1px solid rgba(59, 130, 246, 0.3) !important;
            color: #93C5FD !important;
            padding: 6px 18px !important;
            border-radius: 9999px !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            display: inline-block !important;
            margin-bottom: 16px !important;
            backdrop-filter: blur(8px) !important;
        }
        .rsd-roi-title {
            font-size: clamp(2rem, 3.5vw, 2.8rem) !important;
            font-weight: 800 !important;
            letter-spacing: -0.03em !important;
            color: #FFFFFF !important;
            margin: 0 0 12px 0 !important;
            line-height: 1.2 !important;
            text-align: center !important;
        }
        .rsd-roi-subtitle {
            font-size: 1.05rem !important;
            color: #94A3B8 !important;
            max-width: 640px !important;
            margin: 0 auto 50px auto !important;
            line-height: 1.6 !important;
            text-align: center !important;
        }

        /* Calculator Grid */
        .rsd-roi-grid {
            display: grid !important;
            grid-template-columns: 1.15fr 1fr !important;
            gap: 28px !important;
            margin-bottom: 70px !important;
            align-items: stretch !important;
        }
        @media (max-width: 860px) {
            .rsd-roi-grid { grid-template-columns: 1fr !important; }
        }

        .rsd-roi-card {
            background: rgba(18, 18, 24, 0.85) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 20px !important;
            padding: 32px !important;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5) !important;
            backdrop-filter: blur(16px) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            text-align: left !important;
        }
        [dir="rtl"] .rsd-roi-card {
            text-align: right !important;
        }

        .rsd-roi-card-header {
            font-size: 1.25rem !important;
            font-weight: 800 !important;
            color: #FFFFFF !important;
            margin-bottom: 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        /* Sliders Group */
        .rsd-slider-group {
            margin-bottom: 22px !important;
        }
        .rsd-slider-label-row {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 10px !important;
        }
        .rsd-slider-name {
            font-size: 0.92rem !important;
            font-weight: 600 !important;
            color: #CBD5E1 !important;
        }
        .rsd-slider-val {
            font-size: 1.05rem !important;
            font-weight: 800 !important;
            color: #38BDF8 !important;
            background: rgba(56, 189, 248, 0.1) !important;
            padding: 2px 10px !important;
            border-radius: 6px !important;
        }
        .rsd-range-input {
            width: 100% !important;
            height: 6px !important;
            border-radius: 5px !important;
            background: #27272A !important;
            outline: none !important;
            -webkit-appearance: none !important;
            cursor: pointer !important;
        }
        .rsd-range-input::-webkit-slider-thumb {
            -webkit-appearance: none !important;
            width: 20px !important;
            height: 20px !important;
            border-radius: 50% !important;
            background: #FFFFFF !important;
            border: 3px solid #3B82F6 !important;
            cursor: pointer !important;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.8) !important;
            transition: transform 0.15s ease !important;
        }
        .rsd-range-input::-webkit-slider-thumb:hover {
            transform: scale(1.2) !important;
        }

        /* Output Card Results */
        .rsd-output-block {
            margin-bottom: 24px !important;
        }
        .rsd-output-label {
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            color: #94A3B8 !important;
            margin-bottom: 6px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
        }
        .rsd-output-val-large {
            font-size: clamp(2rem, 3.2vw, 2.7rem) !important;
            font-weight: 800 !important;
            letter-spacing: -0.03em !important;
            background: linear-gradient(135deg, #38BDF8 0%, #818CF8 50%, #C084FC 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            line-height: 1.1 !important;
            margin-bottom: 4px !important;
        }
        .rsd-output-subtext {
            font-size: 0.9rem !important;
            color: #34D399 !important;
            font-weight: 700 !important;
        }

        .rsd-output-btn {
            background: #FFFFFF !important;
            color: #09090B !important;
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            padding: 14px 24px !important;
            border-radius: 9999px !important;
            border: none !important;
            cursor: pointer !important;
            width: 100% !important;
            text-align: center !important;
            box-shadow: 0 4px 20px rgba(255, 255, 255, 0.15) !important;
            transition: all 0.2s ease !important;
            display: inline-block !important;
            text-decoration: none !important;
            box-sizing: border-box !important;
        }
        .rsd-output-btn:hover {
            background: #F1F5F9 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.25) !important;
        }

        /* Bottom Modular Bento Cards */
        .rsd-modular-title {
            font-size: 1.5rem !important;
            font-weight: 800 !important;
            color: #FFFFFF !important;
            text-align: center !important;
            margin-bottom: 30px !important;
            letter-spacing: -0.02em !important;
        }
        .rsd-modular-grid {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 20px !important;
        }
        @media (max-width: 860px) {
            .rsd-modular-grid { grid-template-columns: 1fr !important; }
        }

        .rsd-modular-card {
            background: rgba(18, 18, 24, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 16px !important;
            padding: 24px !important;
            transition: all 0.25s ease !important;
            text-align: left !important;
        }
        [dir="rtl"] .rsd-modular-card {
            text-align: right !important;
        }
        .rsd-modular-card:hover {
            border-color: rgba(59, 130, 246, 0.4) !important;
            transform: translateY(-4px) !important;
            background: rgba(24, 24, 32, 0.8) !important;
        }
        .rsd-modular-price-badge {
            font-size: 1.1rem !important;
            font-weight: 800 !important;
            color: #38BDF8 !important;
            background: rgba(56, 189, 248, 0.1) !important;
            padding: 4px 12px !important;
            border-radius: 8px !important;
            display: inline-block !important;
            margin-bottom: 12px !important;
        }
        .rsd-modular-card-h4 {
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            color: #FFFFFF !important;
            margin: 0 0 8px 0 !important;
        }
        .rsd-modular-card-p {
            font-size: 0.88rem !important;
            color: #94A3B8 !important;
            line-height: 1.5 !important;
            margin: 0 !important;
        }

        .rsd-saas-hero {
            position: relative !important;
            background: radial-gradient(circle at 50% -10%, #FFD6A5 0%, #FFB4D6 25%, #DDD6FE 50%, #F8FAFC 80%, #FFFFFF 100%) !important;
            padding: 120px 20px 70px 20px !important;
            text-align: center !important;
            overflow: hidden !important;
        }
        .rsd-saas-hero-container {
            max-width: 1100px !important;
            margin: 0 auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
        }
        .rsd-saas-pill {
            background: rgba(255, 255, 255, 0.95) !important;
            border: 1px solid rgba(99, 102, 241, 0.2) !important;
            color: #4F46E5 !important;
            padding: 6px 18px !important;
            border-radius: 9999px !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            margin-bottom: 18px !important;
            box-shadow: 0 2px 10px rgba(99, 102, 241, 0.08) !important;
            backdrop-filter: blur(10px) !important;
            display: inline-block !important;
        }
        .rsd-saas-h1 {
            color: #0F172A !important;
            font-size: clamp(2rem, 3.8vw, 3.2rem) !important;
            font-weight: 800 !important;
            line-height: 1.2 !important;
            margin: 0 auto 14px auto !important;
            max-width: 880px !important;
            letter-spacing: -0.03em !important;
        }
        .rsd-saas-gradient-text {
            background: linear-gradient(135deg, #09090B 30%, #3B82F6 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
        }
        .rsd-saas-subtext {
            color: #475569 !important;
            font-size: 1.05rem !important;
            line-height: 1.6 !important;
            margin: 0 auto 24px auto !important;
            max-width: 680px !important;
        }

        /* 3D Visual Centerpiece & Floating UI Cards Layout */
        .rsd-hero-showcase-wrapper {
            position: relative !important;
            width: 100% !important;
            max-width: 860px !important;
            margin: 20px auto 0 auto !important;
        }
        .rsd-laptop-frame {
            position: relative !important;
            width: 100% !important;
            background: #09090B !important;
            border: 12px solid #1E293B !important;
            border-radius: 18px 18px 0 0 !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.25) !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .rsd-laptop-camera {
            width: 6px !important;
            height: 6px !important;
            background: #475569 !important;
            border-radius: 50% !important;
            margin: 4px auto 8px auto !important;
        }
        .rsd-laptop-base {
            width: 106% !important;
            height: 14px !important;
            margin-left: -3% !important;
            background: linear-gradient(180deg, #E2E8F0, #94A3B8) !important;
            border-radius: 0 0 18px 18px !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25) !important;
        }
        .rsd-laptop-screen-content {
            width: 100% !important;
            height: 380px !important;
            background: #FFFFFF !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: stretch !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
            text-align: left !important;
            direction: ltr !important;
            color: #0F172A !important;
            font-size: 13px !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        .rsd-laptop-sidebar {
            width: 165px !important;
            flex-shrink: 0 !important;
            background: #F8FAFC !important;
            border-right: 1px solid #E2E8F0 !important;
            padding: 14px 10px !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 6px !important;
            box-sizing: border-box !important;
        }
        .rsd-laptop-main {
            flex: 1 !important;
            min-width: 0 !important;
            padding: 16px 20px !important;
            background: #FFFFFF !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 12px !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }

        /* Floating UI Card 1: Notification */
        .rsd-float-notification {
            position: absolute !important;
            top: -15px !important;
            right: -20px !important;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            border-radius: 12px !important;
            padding: 10px 14px !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12) !important;
            z-index: 20 !important;
            direction: ltr !important;
            text-align: left !important;
            animation: floatSlow 4s ease-in-out infinite !important;
        }

        /* Floating UI Card 2: Confirmation Modal */
        .rsd-float-confirmation {
            position: absolute !important;
            top: 70px !important;
            right: -15px !important;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(16px) !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 16px !important;
            padding: 16px !important;
            width: 230px !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.14) !important;
            z-index: 25 !important;
            direction: ltr !important;
            text-align: left !important;
            animation: floatSlow 5s ease-in-out infinite 0.5s !important;
        }

        /* Floating UI Card 3: WhatsApp Mobile Mockup */
        .rsd-float-whatsapp {
            position: absolute !important;
            bottom: -25px !important;
            right: -30px !important;
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 12px 14px !important;
            width: 275px !important;
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.18) !important;
            z-index: 30 !important;
            direction: ltr !important;
            text-align: left !important;
            animation: floatSlow 4.5s ease-in-out infinite 1s !important;
        }

        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        .rsd-saas-cta-group {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 16px !important;
            flex-wrap: wrap !important;
            margin-top: 36px !important;
            margin-bottom: 10px !important;
            z-index: 40 !important;
            position: relative !important;
        }

        @media (max-width: 900px) {
            .rsd-float-notification, .rsd-float-confirmation { display: none !important; }
            .rsd-float-whatsapp { right: 10px !important; bottom: -20px !important; width: 250px !important; }
            .rsd-laptop-sidebar { display: none !important; }
            .rsd-laptop-screen-content { height: 280px !important; }
        }

        .rsd-saas-sec { padding: 80px 20px !important; }
        .rsd-saas-container { max-width: 1180px !important; margin: 0 auto !important; }
        .rsd-saas-title {
            text-align: center !important;
            font-size: clamp(1.8rem, 3.5vw, 2.5rem) !important;
            font-weight: 800 !important;
            color: #0F172A !important;
            margin-bottom: 45px !important;
        }
        .rsd-trust-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)) !important;
            gap: 24px !important;
        }
        .rsd-trust-card {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 28px !important;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04) !important;
        }
        .rsd-stars { font-size: 1rem !important; margin-bottom: 14px !important; }
        .rsd-trust-quote { color: #334155 !important; font-size: 0.98rem !important; line-height: 1.7 !important; margin-bottom: 20px !important; }
        .rsd-trust-author strong { display: block !important; color: #0F172A !important; font-size: 0.95rem !important; }
        .rsd-trust-author span { color: #64748B !important; font-size: 0.85rem !important; }

        /* SECTION 3: DEEP SLATE SAAS DASHBOARD TERMINAL */
        .rsd-saas-dark-sec {
            background: #0B0F19 !important;
            padding: 95px 20px !important;
            color: #FFFFFF !important;
            position: relative !important;
        }
        .rsd-saas-dark-container { max-width: 1080px !important; margin: 0 auto !important; text-align: center !important; }
        .rsd-dark-pill {
            display: inline-block !important;
            color: #A855F7 !important;
            background: rgba(168, 85, 247, 0.12) !important;
            border: 1px solid rgba(168, 85, 247, 0.3) !important;
            padding: 6px 18px !important;
            border-radius: 20px !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            margin-bottom: 20px !important;
        }
        .rsd-dark-h2 { color: #FFFFFF !important; font-size: clamp(2rem, 4vw, 3rem) !important; font-weight: 800 !important; margin-bottom: 20px !important; }
        .rsd-dark-subtext { color: #94A3B8 !important; font-size: 1.1rem !important; max-width: 720px !important; margin: 0 auto 45px auto !important; }

        .rsd-saas-terminal-box {
            background: #111827 !important;
            border: 1px solid rgba(168, 85, 247, 0.3) !important;
            border-radius: 20px !important;
            overflow: hidden !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
            text-align: right !important;
        }
        .rsd-terminal-hdr {
            background: #0F172A !important;
            padding: 14px 20px !important;
            display: flex !items: center !important;
            align-items: center !important;
            gap: 12px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .rsd-mac-dots { display: flex !important; gap: 6px !important; }
        .rsd-mac-dots .dot { width: 10px !important; height: 10px !important; border-radius: 50% !important; }
        .rsd-mac-dots .red { background: #EF4444 !important; }
        .rsd-mac-dots .yellow { background: #F59E0B !important; }
        .rsd-mac-dots .green { background: #10B981 !important; }
        .rsd-terminal-title { color: #94A3B8 !important; font-size: 0.85rem !important; }
        .rsd-live-pulse { margin-right: auto !important; color: #10B981 !important; font-size: 0.78rem !important; font-weight: 700 !important; }

        .rsd-terminal-body {
            padding: 30px 24px !important;
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 24px !important;
        }
        .rsd-term-pane {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
            padding: 20px !important;
            border-radius: 14px !important;
        }
        .rsd-term-pane h5 { margin: 0 0 14px 0 !important; color: #E2E8F0 !important; font-size: 0.95rem !important; }
        .rsd-feed-item {
            background: rgba(255, 255, 255, 0.04) !important;
            padding: 10px 14px !important;
            border-radius: 10px !important;
            margin-bottom: 10px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            font-size: 0.85rem !important;
        }
        .rsd-feed-item strong { color: #10B981 !important; }
        .rsd-wa-notice { background: rgba(16, 185, 129, 0.1) !important; border: 1px solid rgba(16, 185, 129, 0.2) !important; padding: 14px !important; border-radius: 12px !important; text-align: right !important; }
        .rsd-wa-notice p { margin: 6px 0 0 0 !important; color: #94A3B8 !important; font-size: 0.82rem !important; }

        /* SECTION 4: RICH BENTO GRID */
        .rsd-saas-bento-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)) !important;
            gap: 20px !important;
            margin-bottom: 30px !important;
        }
        .rsd-saas-bento-card {
            background: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 28px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
        }
        .rsd-bento-badge { font-size: 2rem !important; font-weight: 800 !important; color: #4F46E5 !important; margin-bottom: 12px !important; }
        .rsd-saas-bento-card h3 { color: #0F172A !important; font-size: 1.15rem !important; font-weight: 800 !important; margin: 0 0 10px 0 !important; }
        .rsd-saas-bento-card p { color: #64748B !important; font-size: 0.92rem !important; margin: 0 0 20px 0 !important; line-height: 1.6 !important; }
        .rsd-mini-widget {
            background: #F8FAFC !important;
            border: 1px solid #E2E8F0 !important;
            padding: 10px 14px !important;
            border-radius: 12px !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            color: #334155 !important;
        }

        .rsd-saas-bundle-card {
            background: linear-gradient(135deg, #EEF2FF 0%, #F5F3FF 100%) !important;
            border: 2px solid #6366F1 !important;
            border-radius: 24px !important;
            padding: 32px !important;
        }
        .rsd-bundle-pill {
            background: #6366F1 !important;
            color: #FFFFFF !important;
            padding: 6px 16px !important;
            border-radius: 20px !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            display: inline-block !important;
            margin-bottom: 16px !important;
        }
        .rsd-bundle-flex { display: flex !important; align-items: center !important; justify-content: space-between !important; flex-wrap: wrap !important; gap: 20px !important; }

        /* SECTION 5: UNIFIED MODERN COMPARISON MATRIX */
        .rsd-unified-matrix-card {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            border-radius: 24px !important;
            overflow: hidden !important;
            border: 1px solid #E2E8F0 !important;
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.06) !important;
        }
        .rsd-matrix-side { padding: 35px !important; }
        .rsd-matrix-side.old { background: #F8FAFC !important; }
        .rsd-matrix-side.new { background: #0F172A !important; color: #FFFFFF !important; }
        .rsd-matrix-side.new h3 { color: #FFFFFF !important; }
        .rsd-matrix-side.new .rsd-matrix-list li { color: #E2E8F0 !important; }
        .rsd-matrix-list { list-style: none !important; padding: 0 !important; margin: 20px 0 0 0 !important; display: flex !important; flex-direction: column !important; gap: 14px !important; }
        .rsd-matrix-list li { font-size: 0.98rem !important; font-weight: 600 !important; color: #334155 !important; }

        .rsd-guarantee-pill {
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #F8FAFC !important;
            padding: 16px 28px !important;
            border-radius: 30px !important;
            display: inline-block !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
            max-width: 800px !important;
        }

        @media (max-width: 900px) {
            .rsd-unified-matrix-card { grid-template-columns: 1fr !important; }
            .rsd-terminal-body { grid-template-columns: 1fr !important; }
            .rsd-saas-hero { padding: 105px 16px 50px 16px !important; }
            .rsd-saas-sec { padding: 60px 16px !important; }
            .rsd-saas-dark-sec { padding: 60px 16px !important; }
        }
        /* 3-COLUMN INFINITE VERTICAL MARQUEE TESTIMONIALS STYLES */
        .rsd-marquee-mask-wrap {
            position: relative !important;
            display: flex !important;
            justify-content: center !important;
            gap: 24px !important;
            max-height: 700px !important;
            overflow: hidden !important;
            -webkit-mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent) !important;
            mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent) !important;
            padding: 10px 0 !important;
        }

        .rsd-marquee-col {
            flex: 1 !important;
            max-width: 360px !important;
            width: 100% !important;
        }

        @media (max-width: 768px) {
            .rsd-marquee-col.col-2, .rsd-marquee-col.col-3 { display: none !important; }
        }
        @media (min-width: 769px) and (max-width: 1024px) {
            .rsd-marquee-col.col-3 { display: none !important; }
        }

        .rsd-marquee-track {
            display: flex !important;
            flex-direction: column !important;
            gap: 24px !important;
            padding-bottom: 24px !important;
        }

        .track-1 {
            animation: rsd-marquee-vert 20s linear infinite !important;
        }
        .track-2 {
            animation: rsd-marquee-vert 26s linear infinite !important;
        }
        .track-3 {
            animation: rsd-marquee-vert 22s linear infinite !important;
        }

        .rsd-marquee-mask-wrap:hover .rsd-marquee-track {
            animation-play-state: paused !important;
        }

        @keyframes rsd-marquee-vert {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }

        .rsd-t-card {
            background: #FFFFFF !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            border-radius: 24px !important;
            padding: 30px !important;
            box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.08) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            text-align: right !important;
            transition: transform 0.25s ease !important;
        }
        .rsd-t-card:hover {
            transform: translateY(-2px) !important;
            border-color: rgba(99, 102, 241, 0.4) !important;
        }

        .rsd-t-text {
            color: #334155 !important;
            font-size: 0.98rem !important;
            line-height: 1.75 !important;
            margin: 0 0 20px 0 !important;
            font-weight: 500 !important;
        }

        .rsd-t-user {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
        }

        .rsd-t-avatar {
            width: 44px !important;
            height: 44px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            border: 2px solid #EEF2FF !important;
        }

        .rsd-t-name {
            display: block !important;
            color: #0F172A !important;
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            line-height: 1.3 !important;
        }

        .rsd-t-role {
            display: block !important;
            color: #64748B !important;
            font-size: 0.82rem !important;
            opacity: 0.85 !important;
        }
        /* 3D MOTION GRAPHICS VIDEO LOOPS STYLES */
        .rsd-3d-hero-video-box {
            position: relative !important;
            width: 100% !important;
            max-width: 1140px !important;
            margin: 0 auto 35px auto !important;
            border-radius: 28px !important;
            overflow: hidden !important;
            box-shadow: 0 30px 60px -20px rgba(99, 102, 241, 0.25), 0 0 0 1px rgba(226, 232, 240, 0.8) !important;
            background: #0B0F19 !important;
        }

        .rsd-3d-loop-video {
            width: 100% !important;
            height: auto !important;
            display: block !important;
            object-fit: cover !important;
            border-radius: 28px !important;
            aspect-ratio: 16 / 9 !important;
        }

        .rsd-3d-video-overlay-badge {
            position: absolute !important;
            top: 20px !important;
            right: 20px !important;
            background: rgba(11, 15, 25, 0.75) !important;
            backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #FFFFFF !important;
            padding: 8px 18px !important;
            border-radius: 9999px !important;
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            z-index: 5 !important;
        }

        .rsd-3d-terminal-video-wrap {
            width: 100% !important;
            max-width: 960px !important;
            margin: 0 auto 30px auto !important;
            border-radius: 24px !important;
            overflow: hidden !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5) !important;
        }

        .rsd-3d-term-video {
            width: 100% !important;
            display: block !important;
            aspect-ratio: 16 / 9 !important;
            object-fit: cover !important;
        }

        .rsd-bento-3d-video-wrap {
            width: 100% !important;
            border-radius: 16px !important;
            overflow: hidden !important;
            margin-bottom: 16px !important;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
        }

        .rsd-bento-mini-video {
            width: 100% !important;
            display: block !important;
            aspect-ratio: 16 / 9 !important;
            object-fit: cover !important;
            border-radius: 16px !important;
        }
        /* MASTER COMMERCIAL VIDEO LIGHTBOX & TRIGGER STYLES */
        .rsd-video-trigger-wrap {
            display: flex !important;
            justify-content: center !important;
            margin: 18px 0 35px 0 !important;
        }

        .rsd-video-lightbox-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1.5px solid #C5A059 !important;
            color: #1E293B !important;
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            padding: 12px 26px !important;
            border-radius: 9999px !important;
            cursor: pointer !important;
            box-shadow: 0 10px 25px -5px rgba(197, 160, 89, 0.25) !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            outline: none !important;
        }

        .rsd-video-lightbox-btn:hover {
            transform: translateY(-2px) scale(1.03) !important;
            background: #FFFFFF !important;
            box-shadow: 0 15px 35px -5px rgba(197, 160, 89, 0.4) !important;
            border-color: #D4AF37 !important;
            color: #0F172A !important;
        }

        .rsd-play-icon-glow {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 24px !important;
            height: 24px !important;
            background: linear-gradient(135deg, #C5A059, #D4AF37) !important;
            color: #FFFFFF !important;
            border-radius: 50% !important;
            font-size: 10px !important;
            padding-left: 2px !important;
            box-shadow: 0 0 12px rgba(197, 160, 89, 0.6) !important;
        }

        .rsd-video-modal-backdrop {
            display: none;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(11, 15, 25, 0.85) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            z-index: 999999 !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 20px !important;
            opacity: 0;
            transition: opacity 0.3s ease !important;
        }

        .rsd-video-modal-backdrop.active {
            display: flex !important;
            opacity: 1 !important;
        }

        .rsd-video-modal-content {
            position: relative !important;
            width: 100% !important;
            max-width: 900px !important;
            background: #0B0F19 !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 24px !important;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(197, 160, 89, 0.3) !important;
            overflow: hidden !important;
            transform: scale(0.95);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }

        .rsd-video-modal-backdrop.active .rsd-video-modal-content {
            transform: scale(1) !important;
        }

        .rsd-video-modal-close {
            position: absolute !important;
            top: 14px !important;
            right: 14px !important;
            width: 36px !important;
            height: 36px !important;
            border-radius: 50% !important;
            background: rgba(255, 255, 255, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            color: #FFFFFF !important;
            font-size: 16px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
            z-index: 10 !important;
            transition: background 0.2s ease !important;
        }

        .rsd-video-modal-close:hover {
            background: rgba(239, 68, 68, 0.8) !important;
        }

        .rsd-video-frame-container {
            width: 100% !important;
            aspect-ratio: 16 / 9 !important;
            background: #000000 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .rsd-video-frame-container video {
            width: 100% !important;
            height: 100% !important;
            object-fit: contain !important;
            display: block !important;
        }
        /* 3D TITANIUM DEVICE MOCKUP FRAME STYLES */
        .rsd-titanium-device-wrapper {
            position: relative !important;
            width: 100% !important;
            max-width: 980px !important;
            margin: 35px auto 45px auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            perspective: 1200px !important;
        }

        .rsd-titanium-device-frame {
            position: relative !important;
            width: 100% !important;
            background: linear-gradient(145deg, #2D3748, #1A202C 40%, #111827 80%, #4A5568) !important;
            padding: 14px !important;
            border-radius: 36px !important;
            box-shadow: 0 35px 80px -20px rgba(15, 23, 42, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.15), 0 20px 40px -15px rgba(99, 102, 241, 0.25) !important;
            border: 2px solid rgba(203, 213, 225, 0.25) !important;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease !important;
        }

        .rsd-titanium-device-frame:hover {
            transform: translateY(-4px) scale(1.01) !important;
            box-shadow: 0 45px 95px -20px rgba(15, 23, 42, 0.55), 0 0 0 1.5px rgba(197, 160, 89, 0.4), 0 25px 50px -15px rgba(99, 102, 241, 0.35) !important;
        }

        .rsd-titanium-header-bar {
            position: absolute !important;
            top: 22px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            z-index: 10 !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }

        .rsd-titanium-notch {
            width: 90px !important;
            height: 18px !important;
            background: #000000 !important;
            border-radius: 20px !important;
            box-shadow: inset 0 0 4px rgba(255, 255, 255, 0.2) !important;
        }

        .rsd-titanium-screen-glass {
            position: relative !important;
            width: 100% !important;
            border-radius: 24px !important;
            overflow: hidden !important;
            background: #0B0F19 !important;
            aspect-ratio: 16 / 9 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .rsd-hero-live-video {
            width: 100% !important;
            height: 100% !important;
            border-radius: 24px !important;
            object-fit: cover !important;
            display: block !important;
        }

        .rsd-titanium-reflection-overlay {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            border-radius: 36px !important;
            pointer-events: none !important;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0) 45%) !important;
        }

        .rsd-titanium-badge-floating {
            margin-top: 16px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(226, 232, 240, 0.9) !important;
            padding: 6px 18px !important;
            border-radius: 9999px !important;
            font-size: 0.88rem !important;
            font-weight: 700 !important;
            color: #1E293B !important;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.12) !important;
        }

        .rsd-live-pulse-dot {
            display: inline-block !important;
            animation: rsd-pulse-glow 2s infinite ease-in-out !important;
        }

        @keyframes rsd-pulse-glow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.7; }
        }

        @media (max-width: 768px) {
            .rsd-titanium-device-frame {
                padding: 8px !important;
                border-radius: 24px !important;
            }
            .rsd-titanium-screen-glass {
                border-radius: 18px !important;
            }
            .rsd-hero-live-video {
                border-radius: 18px !important;
            }
            .rsd-titanium-notch {
                width: 60px !important;
                height: 12px !important;
            }
        }
        /* MINIMALIST HERO TITANIUM DEVICE & SILENT MOTION STYLES */
        .rsd-hero-device-wrapper {
            position: relative !important;
            width: 100% !important;
            max-width: 920px !important;
            margin: 40px auto 10px auto !important;
            display: flex !important;
            justify-content: center !important;
        }

        .rsd-hero-device-frame {
            position: relative !important;
            width: 100% !important;
            background: linear-gradient(145deg, #1E293B, #0F172A 40%, #090D16 80%, #334155) !important;
            padding: 12px !important;
            border-radius: 32px !important;
            box-shadow: 0 30px 70px -15px rgba(15, 23, 42, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.12), 0 15px 35px -10px rgba(99, 102, 241, 0.2) !important;
            border: 1.5px solid rgba(203, 213, 225, 0.2) !important;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease !important;
        }

        .rsd-hero-device-frame:hover {
            transform: translateY(-4px) scale(1.01) !important;
            box-shadow: 0 40px 85px -15px rgba(15, 23, 42, 0.5), 0 0 0 1.5px rgba(197, 160, 89, 0.35) !important;
        }

        .rsd-device-header-notch {
            position: absolute !important;
            top: 20px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            z-index: 10 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            pointer-events: none !important;
        }

        .rsd-device-speaker {
            width: 70px !important;
            height: 14px !important;
            background: #000000 !important;
            border-radius: 20px !important;
            box-shadow: inset 0 0 4px rgba(255, 255, 255, 0.2) !important;
        }

        .rsd-device-screen {
            position: relative !important;
            width: 100% !important;
            border-radius: 22px !important;
            overflow: hidden !important;
            background: #000000 !important;
            aspect-ratio: 16 / 9 !important;
        }

        .rsd-hero-yt-iframe {
            position: absolute !important;
            top: -10% !important;
            left: -10% !important;
            width: 120% !important;
            height: 120% !important;
            border: none !important;
            pointer-events: none !important;
        }

        .rsd-device-screen-overlay {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            pointer-events: none !important;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0) 40%) !important;
            border-radius: 22px !important;
        }

        @media (max-width: 768px) {
            .rsd-hero-device-frame {
                padding: 6px !important;
                border-radius: 20px !important;
            }
            .rsd-device-screen {
                border-radius: 14px !important;
            }
            .rsd-device-speaker {
                width: 50px !important;
                height: 10px !important;
            }
        }
        /* ==========================================================================
           COMPREHENSIVE MOBILE & TABLET RESPONSIVE PERFECTION
           ========================================================================== */
        @media (max-width: 768px) {
            .rsd-saas-hero {
                padding: 100px 16px 40px 16px !important;
            }
            .rsd-saas-h1 {
                font-size: 1.85rem !important;
                line-height: 1.25 !important;
                margin-bottom: 12px !important;
            }
            .rsd-saas-subtext {
                font-size: 0.95rem !important;
                line-height: 1.5 !important;
                margin-bottom: 20px !important;
            }
            .shiny-cta, .rsd-btn-showcase {
                width: 100% !important;
                max-width: 320px !important;
                height: 48px !important;
                font-size: 0.95rem !important;
            }
            .rsd-saas-cta-group {
                flex-direction: column !important;
                gap: 10px !important;
                width: 100% !important;
            }
            .rsd-roi-section {
                padding: 50px 14px 45px 14px !important;
            }
            .rsd-roi-title {
                font-size: 1.65rem !important;
                margin-bottom: 8px !important;
            }
            .rsd-roi-subtitle {
                font-size: 0.92rem !important;
                margin-bottom: 30px !important;
            }
            .rsd-roi-card {
                padding: 20px 16px !important;
                border-radius: 14px !important;
            }
            .rsd-output-val-large {
                font-size: 1.9rem !important;
            }
            .rsd-modular-title {
                font-size: 1.3rem !important;
                margin-bottom: 20px !important;
            }
            .rsd-modular-card {
                padding: 18px 16px !important;
            }
            .rsd-saas-sec {
                padding: 50px 16px !important;
            }
            .rsd-laptop-screen-content {
                height: 240px !important;
            }
            .rsd-float-notification, .rsd-float-confirmation {
                display: none !important;
            }
            .rsd-float-whatsapp {
                position: relative !important;
                bottom: auto !important;
                right: auto !important;
                margin: 16px auto 0 auto !important;
                width: 95% !important;
                box-sizing: border-box !important;
            }
        }
        /* ==========================================================================
           1. BESPOKE INFINITE MONOCHROME MARQUEE & COLOR RHYTHM
           ========================================================================== */
        .rsd-marquee-section {
            background: linear-gradient(180deg, #020617 0%, #0B1120 50%, #030712 100%) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
            padding: 42px 0 !important;
            position: relative !important;
            z-index: 5 !important;
            overflow: hidden !important;
        }
        .rsd-marquee-header {
            text-align: center !important;
            margin-bottom: 22px !important;
            padding: 0 20px !important;
        }
        .rsd-marquee-badge {
            display: inline-block !important;
            font-size: 0.76rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.14em !important;
            color: #94A3B8 !important;
            opacity: 0.9 !important;
        }
        .rsd-marquee-wrapper {
            width: 100% !important;
            overflow: hidden !important;
            position: relative !important;
            mask-image: linear-gradient(to right, transparent 0%, rgba(0,0,0,1) 12%, rgba(0,0,0,1) 88%, transparent 100%) !important;
            -webkit-mask-image: linear-gradient(to right, transparent 0%, rgba(0,0,0,1) 12%, rgba(0,0,0,1) 88%, transparent 100%) !important;
        }
        .rsd-marquee-track {
            display: flex !important;
            align-items: center !important;
            gap: 28px !important;
            width: max-content !important;
            animation: rsdMarqueeDrift 34s linear infinite !important;
        }
        .rsd-marquee-track:hover {
            animation-play-state: paused !important;
        }
        @keyframes rsdMarqueeDrift {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .rsd-marquee-item {
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
            color: #94A3B8 !important;
            font-size: 0.94rem !important;
            font-weight: 600 !important;
            letter-spacing: 0.02em !important;
            padding: 10px 20px !important;
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.07) !important;
            border-radius: 12px !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            white-space: nowrap !important;
            user-select: none !important;
        }
        .rsd-marquee-item:hover {
            color: #FFFFFF !important;
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: rgba(56, 189, 248, 0.35) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4) !important;
        }
        .rsd-marquee-icon {
            font-size: 1.05rem !important;
            opacity: 0.85 !important;
        }

        /* ==========================================================================
           2. 4-STEP BESPOKE ARCHITECTURAL PROTOCOL
           ========================================================================== */
        .rsd-protocol-sec {
            background: #09090B !important;
            padding: 90px 20px !important;
            position: relative !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .rsd-protocol-container {
            max-width: 1180px !important;
            margin: 0 auto !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .rsd-protocol-grid {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 20px !important;
            margin-top: 50px !important;
        }
        @media (max-width: 992px) {
            .rsd-protocol-grid { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (max-width: 600px) {
            .rsd-protocol-grid { grid-template-columns: 1fr !important; }
        }
        .rsd-protocol-card {
            background: rgba(18, 18, 24, 0.7) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 18px !important;
            padding: 28px 24px !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1), 0 20px 30px -10px rgba(0,0,0,0.5) !important;
            backdrop-filter: blur(12px) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            text-align: left !important;
        }
        [dir="rtl"] .rsd-protocol-card {
            text-align: right !important;
        }
        .rsd-protocol-card:hover {
            border-color: rgba(99, 102, 241, 0.5) !important;
            transform: translateY(-5px) !important;
            background: rgba(24, 24, 32, 0.9) !important;
        }
        .rsd-protocol-num {
            font-family: monospace !important;
            font-size: 1.8rem !important;
            font-weight: 800 !important;
            color: #38BDF8 !important;
            letter-spacing: -0.04em !important;
            margin-bottom: 14px !important;
        }
        .rsd-protocol-title {
            font-size: 1.15rem !important;
            font-weight: 700 !important;
            color: #FFFFFF !important;
            margin: 0 0 10px 0 !important;
            line-height: 1.3 !important;
        }
        .rsd-protocol-desc {
            font-size: 0.9rem !important;
            color: #94A3B8 !important;
            line-height: 1.6 !important;
            margin: 0 !important;
        }

        /* ==========================================================================
           3. LIQUID GLASS ACCORDION FAQ SECTION
           ========================================================================== */
        .rsd-faq-sec {
            background: #09090B !important;
            padding: 90px 20px !important;
            position: relative !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .rsd-faq-container {
            max-width: 860px !important;
            margin: 0 auto !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .rsd-faq-list {
            display: flex !important;
            flex-direction: column !important;
            gap: 14px !important;
            margin-top: 40px !important;
        }
        .rsd-faq-item {
            background: rgba(18, 18, 24, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 14px !important;
            overflow: hidden !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
            transition: all 0.25s ease !important;
        }
        .rsd-faq-item.active {
            border-color: rgba(56, 189, 248, 0.4) !important;
            background: rgba(24, 24, 32, 0.85) !important;
        }
        .rsd-faq-question {
            padding: 20px 24px !important;
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            color: #FFFFFF !important;
            cursor: pointer !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            user-select: none !important;
            text-align: left !important;
        }
        [dir="rtl"] .rsd-faq-question {
            text-align: right !important;
        }
        .rsd-faq-icon {
            font-size: 1.2rem !important;
            color: #38BDF8 !important;
            transition: transform 0.25s ease !important;
        }
        .rsd-faq-item.active .rsd-faq-icon {
            transform: rotate(45deg) !important;
        }
        .rsd-faq-answer {
            max-height: 0 !important;
            overflow: hidden !important;
            transition: max-height 0.35s cubic-bezier(0.16, 1, 0.3, 1), padding 0.35s ease !important;
            padding: 0 24px !important;
            color: #94A3B8 !important;
            font-size: 0.95rem !important;
            line-height: 1.65 !important;
            text-align: left !important;
        }
        [dir="rtl"] .rsd-faq-answer {
            text-align: right !important;
        }
        .rsd-faq-item.active .rsd-faq-answer {
            max-height: 300px !important;
            padding: 0 24px 22px 24px !important;
        }
    
        /* ==========================================================================
           1. LUXURY LIGHT-THEMED HORIZONTAL MARQUEE TRUST BAR (SINGLE ROW & LIGHT BG)
           ========================================================================== */
        .rsd-hero-trust-bar {
            background: #F8FAFC !important;
            border-top: 1px solid #E2E8F0 !important;
            border-bottom: 1px solid #E2E8F0 !important;
            padding: 24px 0 !important;
            position: relative !important;
            z-index: 10 !important;
            overflow: hidden !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02) !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .rsd-trust-bar-header {
            text-align: center !important;
            margin-bottom: 14px !important;
            padding: 0 20px !important;
        }
        .rsd-trust-badge {
            display: inline-block !important;
            font-size: 0.76rem !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.12em !important;
            color: #64748B !important;
        }
        .rsd-trust-wrapper {
            width: 100% !important;
            overflow: hidden !important;
            position: relative !important;
            mask-image: linear-gradient(to right, transparent 0%, rgba(0,0,0,1) 8%, rgba(0,0,0,1) 92%, transparent 100%) !important;
            -webkit-mask-image: linear-gradient(to right, transparent 0%, rgba(0,0,0,1) 8%, rgba(0,0,0,1) 92%, transparent 100%) !important;
        }
        .rsd-trust-track {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            gap: 16px !important;
            width: max-content !important;
            animation: rsdTrustDrift 28s linear infinite !important;
            white-space: nowrap !important;
        }
        .rsd-trust-track:hover {
            animation-play-state: paused !important;
        }
        @keyframes rsdTrustDrift {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .rsd-trust-chip {
            display: inline-flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 8px !important;
            color: #0F172A !important;
            font-size: 0.92rem !important;
            font-weight: 700 !important;
            padding: 9px 20px !important;
            background: #FFFFFF !important;
            border: 1.5px solid #E2E8F0 !important;
            border-radius: 9999px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.25s ease !important;
            white-space: nowrap !important;
            user-select: none !important;
            flex-shrink: 0 !important;
        }
        .rsd-trust-chip:hover {
            color: #2563EB !important;
            border-color: #93C5FD !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.12) !important;
        }
        .rsd-trust-icon {
            font-size: 1.1rem !important;
        }

        /* ==========================================================================
           2. AUTHENTIC 3-COLUMN VERTICAL INFINITE MARQUEE TESTIMONIALS
           ========================================================================== */
        .rsd-trust-sec {
            background: #F8FAFC !important;
            padding: 90px 20px !important;
            position: relative !important;
        }
        .rsd-marquee-mask-wrap {
            position: relative !important;
            display: flex !important;
            flex-direction: row !important;
            justify-content: center !important;
            gap: 24px !important;
            max-height: 640px !important;
            overflow: hidden !important;
            -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 10%, black 90%, transparent 100%) !important;
            mask-image: linear-gradient(to bottom, transparent 0%, black 10%, black 90%, transparent 100%) !important;
        }
        .rsd-marquee-mask-wrap:hover .rsd-t-track {
            animation-play-state: paused !important;
        }
        .rsd-marquee-col {
            flex: 1 !important;
            max-width: 380px !important;
            overflow: hidden !important;
        }
        .rsd-t-track {
            display: flex !important;
            flex-direction: column !important;
            gap: 20px !important;
        }
        .rsd-t-track.track-1 { animation: rsdVertDrift 28s linear infinite !important; }
        .rsd-t-track.track-2 { animation: rsdVertDrift 34s linear infinite !important; }
        .rsd-t-track.track-3 { animation: rsdVertDrift 24s linear infinite !important; }

        @keyframes rsdVertDrift {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }
        .rsd-t-card {
            background: #FFFFFF !important;
            border: 1.5px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 24px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            text-align: right !important;
            transition: all 0.25s ease !important;
            box-sizing: border-box !important;
        }
        .rsd-t-card:hover {
            transform: translateY(-3px) !important;
            border-color: #93C5FD !important;
            box-shadow: 0 16px 32px -5px rgba(37, 99, 235, 0.12) !important;
        }
        .rsd-t-text {
            color: #334155 !important;
            font-size: 0.95rem !important;
            line-height: 1.65 !important;
            margin: 0 0 18px 0 !important;
            font-weight: 500 !important;
        }
        .rsd-t-user {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
        }
        .rsd-t-avatar {
            width: 44px !important;
            height: 44px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            border: 2px solid #E2E8F0 !important;
        }
        .rsd-t-name {
            display: block !important;
            font-size: 0.92rem !important;
            font-weight: 800 !important;
            color: #0F172A !important;
        }
        .rsd-t-role {
            display: block !important;
            font-size: 0.78rem !important;
            color: #64748B !important;
        }

        @media (max-width: 991px) {
            .rsd-marquee-col.col-3 { display: none !important; }
        }
        @media (max-width: 640px) {
            .rsd-marquee-col.col-2 { display: none !important; }
            .rsd-marquee-mask-wrap { max-height: 520px !important; }
        }

    
        /* ==========================================================================
           3. 4-STEP ARCHITECTURAL PROTOCOL (LIGHT THEME - BREATHABLE WHITESPACE)
           ========================================================================== */
        .rsd-protocol-sec {
            background: #FFFFFF !important;
            padding: 95px 20px !important;
            position: relative !important;
            border-top: 1px solid #E2E8F0 !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }
        .rsd-protocol-container {
            max-width: 1180px !important;
            margin: 0 auto !important;
            position: relative !important;
        }
        .rsd-protocol-sec .rsd-roi-pill {
            background: #EFF6FF !important;
            border: 1px solid #BFDBFE !important;
            color: #2563EB !important;
            display: inline-block !important;
            padding: 6px 18px !important;
            border-radius: 9999px !important;
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.08em !important;
            margin-bottom: 12px !important;
        }
        .rsd-protocol-sec .rsd-roi-title {
            color: #0F172A !important;
            font-size: clamp(1.8rem, 3.5vw, 2.5rem) !important;
            font-weight: 800 !important;
            margin-bottom: 12px !important;
        }
        .rsd-protocol-sec .rsd-roi-subtitle {
            color: #64748B !important;
            font-size: 1.05rem !important;
            line-height: 1.65 !important;
            margin-bottom: 48px !important;
        }
        .rsd-protocol-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)) !important;
            gap: 24px !important;
        }
        .rsd-protocol-card {
            background: #FFFFFF !important;
            border: 1.5px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 30px 24px !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            box-sizing: border-box !important;
        }
        [dir="rtl"] .rsd-protocol-card {
            text-align: right !important;
        }
        .rsd-protocol-card:hover {
            border-color: #93C5FD !important;
            transform: translateY(-4px) !important;
            box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.12) !important;
        }
        .rsd-protocol-num {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 44px !important;
            height: 44px !important;
            border-radius: 12px !important;
            background: #EFF6FF !important;
            border: 1px solid #DBEAFE !important;
            color: #2563EB !important;
            font-family: monospace !important;
            font-size: 1.25rem !important;
            font-weight: 800 !important;
            margin-bottom: 18px !important;
        }
        .rsd-protocol-title {
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            color: #0F172A !important;
            margin: 0 0 12px 0 !important;
            line-height: 1.4 !important;
        }
        .rsd-protocol-desc {
            font-size: 0.94rem !important;
            color: #475569 !important;
            line-height: 1.65 !important;
            margin: 0 !important;
        }

        /* ==========================================================================
           4. UNIFIED COMPARISON MATRIX (LIGHT THEME)
           ========================================================================== */
        .rsd-matrix-sec {
            background: #F8FAFC !important;
            padding: 90px 20px !important;
            border-bottom: 1px solid #E2E8F0 !important;
        }
        .rsd-matrix-sec .rsd-saas-title {
            color: #0F172A !important;
            font-size: clamp(1.8rem, 3.5vw, 2.5rem) !important;
            font-weight: 800 !important;
        }

    

        /* ==========================================================================
           UPGRADED HERO SCALE & CRISP HIGH-CONTRAST HOVER SYSTEM
           ========================================================================== */
        .rsd-saas-hero {
            position: relative !important;
            background: radial-gradient(circle at 50% -20%, #FFE4D6 0%, #FED7AA 15%, #FEE2E2 35%, #F5F3FF 60%, #FFFFFF 95%) !important;
            padding: 80px 20px 70px 20px !important;
            text-align: center !important;
            overflow: hidden !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .rsd-saas-hero-container {
            max-width: 1240px !important;
            margin: 0 auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .rsd-saas-pill {
            background: rgba(255, 255, 255, 0.95) !important;
            border: 1px solid rgba(37, 99, 235, 0.2) !important;
            color: #2563EB !important;
            padding: 4px 14px !important;
            border-radius: 9999px !important;
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.04em !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin-bottom: 20px !important;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08) !important;
        }
        .rsd-saas-h1 {
            font-size: clamp(2.8rem, 5.4vw, 4.5rem) !important;
            font-weight: 800 !important;
            line-height: 1.12 !important;
            color: #0F172A !important;
            letter-spacing: -0.03em !important;
            margin: 0 0 22px 0 !important;
            max-width: 1050px !important;
        }
        .rsd-saas-subtext {
            font-size: clamp(1.1rem, 1.8vw, 1.35rem) !important;
            color: #475569 !important;
            line-height: 1.65 !important;
            max-width: 820px !important;
            margin: 0 auto 36px auto !important;
            font-weight: 500 !important;
        }
        .rsd-hero-showcase-wrapper {
            width: 100% !important;
            max-width: 1060px !important;
            margin: 0 auto 40px auto !important;
            display: flex !important;
            justify-content: center !important;
        }
        .rsd-hero-master-img {
            width: 100% !important;
            max-width: 1060px !important;
            height: auto !important;
            border-radius: 20px !important;
            filter: drop-shadow(0 25px 50px rgba(15, 23, 42, 0.15)) !important;
        }

        /* 100% HIGH-CONTRAST ROI CALCULATOR BUTTON (ZERO HOVER INVISIBILITY) */
        .rsd-output-btn {
            background: #2563EB !important;
            color: #FFFFFF !important;
            font-size: 1.02rem !important;
            font-weight: 800 !important;
            padding: 16px 28px !important;
            border-radius: 14px !important;
            border: none !important;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.5) !important;
            cursor: pointer !important;
            width: 100% !important;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            text-align: center !important;
            display: block !important;
            text-decoration: none !important;
        }
        .rsd-output-btn:hover {
            background: #1D4ED8 !important;
            color: #FFFFFF !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.7) !important;
        }

        /* FLAWLESS LIGHT CARD HOVER IN 4-STEP ROADMAP (ZERO DARK BOX ARTIFACTS) */
        .rsd-protocol-card {
            background: #FFFFFF !important;
            border: 1.5px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 30px 24px !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            box-sizing: border-box !important;
        }
        .rsd-protocol-card:hover {
            background: #FFFFFF !important;
            border-color: #2563EB !important;
            transform: translateY(-6px) !important;
            box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.12) !important;
        }
        .rsd-protocol-card:hover .rsd-protocol-title {
            color: #0F172A !important;
        }
        .rsd-protocol-card:hover .rsd-protocol-desc {
            color: #475569 !important;
        }
        .rsd-protocol-card:hover .rsd-protocol-num {
            background: #2563EB !important;
            color: #FFFFFF !important;
            border-color: #2563EB !important;
        }

    

        /* ==========================================================================
           PERFECTED RESPONSIVE HERO (CINEMATIC DESKTOP + FLAWLESS COMPACT MOBILE)
           ========================================================================== */
        .rsd-saas-hero {
            position: relative !important;
            background: radial-gradient(circle at 50% -20%, #FFE4D6 0%, #FED7AA 15%, #FEE2E2 35%, #F5F3FF 60%, #FFFFFF 95%) !important;
            padding: 75px 20px 65px 20px !important;
            text-align: center !important;
            overflow: hidden !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .rsd-saas-hero-container {
            max-width: 1240px !important;
            margin: 0 auto !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            position: relative !important;
            z-index: 2 !important;
        }
        .rsd-saas-pill {
            background: rgba(255, 255, 255, 0.95) !important;
            border: 1px solid rgba(37, 99, 235, 0.2) !important;
            color: #2563EB !important;
            padding: 4px 14px !important;
            border-radius: 9999px !important;
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.04em !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin-bottom: 18px !important;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08) !important;
        }
        .rsd-saas-h1 {
            font-size: clamp(2.4rem, 4.8vw, 4.2rem) !important;
            font-weight: 800 !important;
            line-height: 1.14 !important;
            color: #0F172A !important;
            letter-spacing: -0.03em !important;
            margin: 0 0 20px 0 !important;
            max-width: 1050px !important;
        }
        .rsd-saas-subtext {
            font-size: clamp(1.05rem, 1.6vw, 1.3rem) !important;
            color: #475569 !important;
            line-height: 1.6 !important;
            max-width: 820px !important;
            margin: 0 auto 34px auto !important;
            font-weight: 500 !important;
        }
        .rsd-hero-showcase-wrapper {
            width: 100% !important;
            max-width: 1060px !important;
            margin: 0 auto 36px auto !important;
            display: flex !important;
            justify-content: center !important;
        }
        .rsd-hero-master-img {
            width: 100% !important;
            max-width: 1060px !important;
            height: auto !important;
            border-radius: 20px !important;
            filter: drop-shadow(0 25px 50px rgba(15, 23, 42, 0.15)) !important;
        }
        .rsd-saas-cta-group {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 16px !important;
            flex-wrap: wrap !important;
        }

        /* 📱 ULTRA-REFINED MOBILE HERO (MAX-WIDTH 768PX & 640PX) */
        @media (max-width: 768px) {
            .rsd-saas-hero {
                padding: 32px 16px 28px 16px !important;
            }
            .rsd-saas-pill {
                padding: 3px 11px !important;
                font-size: 0.72rem !important;
                margin-bottom: 12px !important;
            }
            .rsd-saas-h1 {
                font-size: clamp(1.65rem, 6.2vw, 2.1rem) !important;
                line-height: 1.22 !important;
                letter-spacing: -0.02em !important;
                margin: 0 auto 12px auto !important;
                max-width: 95% !important;
            }
            .rsd-saas-subtext {
                font-size: 0.9rem !important;
                line-height: 1.45 !important;
                margin: 0 auto 16px auto !important;
                max-width: 92% !important;
                color: #475569 !important;
            }
            .rsd-hero-showcase-wrapper {
                max-width: 94% !important;
                margin: 0 auto 20px auto !important;
            }
            .rsd-hero-master-img {
                max-width: 100% !important;
                border-radius: 12px !important;
                filter: drop-shadow(0 12px 25px rgba(15, 23, 42, 0.12)) !important;
            }
            .rsd-saas-cta-group {
                flex-direction: column !important;
                gap: 10px !important;
                width: 100% !important;
                max-width: 310px !important;
                margin: 0 auto !important;
            }
            .shiny-cta {
                width: 100% !important;
                padding: 13px 20px !important;
                font-size: 0.95rem !important;
                justify-content: center !important;
            }
            .rsd-btn-showcase {
                width: 100% !important;
                padding: 11px 20px !important;
                font-size: 0.88rem !important;
                justify-content: center !important;
                text-align: center !important;
            }
        }

    

        /* ==========================================================================
           HEADER OVERLAP CLEARANCE & FLAWLESS HERO VIEWPORT ALIGNMENT
           ========================================================================== */
        .rsd-saas-hero {
            position: relative !important;
            background: radial-gradient(circle at 50% -20%, #FFE4D6 0%, #FED7AA 15%, #FEE2E2 35%, #F5F3FF 60%, #FFFFFF 95%) !important;
            padding: 105px 20px 65px 20px !important;
            text-align: center !important;
            overflow: hidden !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        @media (max-width: 768px) {
            .rsd-saas-hero {
                padding: 98px 16px 28px 16px !important; /* Full 70px header clearance + 28px breathing space */
            }
            .rsd-saas-pill {
                padding: 4px 12px !important;
                font-size: 0.74rem !important;
                margin-bottom: 12px !important;
            }
            .rsd-saas-h1 {
                font-size: clamp(1.65rem, 6.2vw, 2.1rem) !important;
                line-height: 1.22 !important;
                letter-spacing: -0.02em !important;
                margin: 0 auto 12px auto !important;
                max-width: 95% !important;
            }
            .rsd-saas-subtext {
                font-size: 0.9rem !important;
                line-height: 1.45 !important;
                margin: 0 auto 16px auto !important;
                max-width: 92% !important;
                color: #475569 !important;
            }
            .rsd-hero-showcase-wrapper {
                max-width: 94% !important;
                margin: 0 auto 20px auto !important;
            }
            .rsd-hero-master-img {
                max-width: 100% !important;
                border-radius: 12px !important;
                filter: drop-shadow(0 12px 25px rgba(15, 23, 42, 0.12)) !important;
            }
            .rsd-saas-cta-group {
                flex-direction: column !important;
                gap: 10px !important;
                width: 100% !important;
                max-width: 310px !important;
                margin: 0 auto !important;
            }
            .shiny-cta {
                width: 100% !important;
                padding: 13px 20px !important;
                font-size: 0.95rem !important;
                justify-content: center !important;
            }
            .rsd-btn-showcase {
                width: 100% !important;
                padding: 11px 20px !important;
                font-size: 0.88rem !important;
                justify-content: center !important;
                text-align: center !important;
            }
        }

    

        /* ==========================================================================
           SCROLL-DRIVEN MOTION ENGINE (FADE IN ON SCROLL DOWN & GENTLE UP DRIFT)
           ========================================================================== */
        .rsd-scroll-reveal {
            opacity: 0 !important;
            transform: translateY(28px) scale(0.985) !important;
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1) !important;
            will-change: opacity, transform !important;
        }
        .rsd-scroll-reveal.rsd-in-view {
            opacity: 1 !important;
            transform: translateY(0) scale(1) !important;
        }
        body[data-scroll-dir='up'] .rsd-scroll-reveal:not(.rsd-in-view) {
            transform: translateY(-20px) scale(0.985) !important;
        }
        .rsd-stagger-1 { transition-delay: 0.06s !important; }
        .rsd-stagger-2 { transition-delay: 0.12s !important; }
        .rsd-stagger-3 { transition-delay: 0.18s !important; }
        .rsd-stagger-4 { transition-delay: 0.24s !important; }

    
            /* ==========================================================================
               COMPREHENSIVE RESPONSIVE SUITE: ROI CALCULATOR, FOOTER & WIDGETS
               ========================================================================== */
            /* Suppression of generic theme footers */
            footer.site-footer:not(#rsd-master-footer),
            footer.hello-footer:not(#rsd-master-footer),
            .site-footer:not(#rsd-master-footer),
            #site-footer:not(#rsd-master-footer),
            .elementor-location-footer:not(#rsd-master-footer) {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            /* Master Footer 4-Column Responsive Grid */
            .rsd-master-footer-wrap,
            #rsd-master-footer {
                background: #090D1A !important;
                color: #F8FAFC !important;
                padding: 80px 24px 30px 24px !important;
                border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
                width: 100% !important;
                box-sizing: border-box !important;
                position: relative !important;
                z-index: 10 !important;
                display: block !important;
            }
            .rsd-footer-container,
            .rsd-footer-inner {
                max-width: 1240px !important;
                margin: 0 auto !important;
                display: grid !important;
                grid-template-columns: 2fr 1fr 1.3fr 1fr !important;
                gap: 40px !important;
                box-sizing: border-box !important;
            }
            @media (max-width: 992px) {
                .rsd-footer-container,
                .rsd-footer-inner {
                    grid-template-columns: 1fr 1fr !important;
                    gap: 32px !important;
                }
            }
            @media (max-width: 640px) {
                .rsd-footer-container,
                .rsd-footer-inner {
                    grid-template-columns: 1fr !important;
                    gap: 28px !important;
                }
            }
            .rsd-footer-logo-wrap img,
            .rsd-footer-logo {
                height: 48px !important;
                width: auto !important;
                max-width: 200px !important;
                object-fit: contain !important;
                margin-bottom: 14px !important;
            }
            .rsd-footer-desc {
                color: #94A3B8 !important;
                font-size: 0.92rem !important;
                line-height: 1.65 !important;
                margin: 0 0 16px 0 !important;
            }
            .rsd-footer-heading {
                font-size: 0.8rem !important;
                font-weight: 800 !important;
                letter-spacing: 0.15em !important;
                color: #38BDF8 !important;
                margin: 0 0 18px 0 !important;
                text-transform: uppercase !important;
            }
            .rsd-footer-links {
                list-style: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .rsd-footer-links li {
                margin-bottom: 10px !important;
            }
            .rsd-footer-links a {
                color: #CBD5E1 !important;
                text-decoration: none !important;
                font-size: 0.9rem !important;
                transition: color 0.2s ease !important;
            }
            .rsd-footer-links a:hover {
                color: #38BDF8 !important;
            }
            .rsd-footer-info {
                color: #CBD5E1 !important;
                font-size: 0.88rem !important;
                line-height: 1.5 !important;
                margin: 0 0 12px 0 !important;
            }
            .rsd-footer-email, .rsd-footer-wa {
                color: #38BDF8 !important;
                text-decoration: none !important;
                font-weight: 600 !important;
            }
            .rsd-footer-bottom {
                max-width: 1240px !important;
                margin: 50px auto 0 auto !important;
                padding-top: 24px !important;
                border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
                text-align: center !important;
                color: #64748B !important;
                font-size: 0.85rem !important;
            }

            /* ROI Calculator Styling */
            .rsd-roi-section {
                background: #030712 !important;
                padding: 90px 20px !important;
                position: relative !important;
            }
            .rsd-roi-container {
                max-width: 1140px !important;
                margin: 0 auto !important;
            }
            .rsd-roi-calculator-wrap,
            .rsd-roi-grid {
                display: grid !important;
                grid-template-columns: 1.15fr 1fr !important;
                gap: 28px !important;
                align-items: stretch !important;
            }
            @media (max-width: 860px) {
                .rsd-roi-calculator-wrap,
                .rsd-roi-grid {
                    grid-template-columns: 1fr !important;
                }
            }
            .rsd-roi-box,
            .rsd-roi-card {
                background: rgba(15, 23, 42, 0.75) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                border-radius: 20px !important;
                padding: 30px !important;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4) !important;
                backdrop-filter: blur(14px) !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                box-sizing: border-box !important;
            }
            .rsd-roi-box-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                margin-bottom: 22px !important;
            }
            .rsd-roi-box-title {
                font-size: 1.15rem !important;
                font-weight: 800 !important;
                color: #FFFFFF !important;
            }
            .rsd-roi-box-badge {
                font-size: 0.75rem !important;
                font-weight: 700 !important;
                padding: 4px 10px !important;
                background: rgba(56, 189, 248, 0.15) !important;
                color: #38BDF8 !important;
                border: 1px solid rgba(56, 189, 248, 0.3) !important;
                border-radius: 9999px !important;
            }
            .rsd-roi-field {
                margin-bottom: 20px !important;
            }
            .rsd-field-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                margin-bottom: 8px !important;
                color: #CBD5E1 !important;
                font-size: 0.9rem !important;
                font-weight: 600 !important;
            }
            .rsd-val-highlight {
                color: #38BDF8 !important;
                font-weight: 800 !important;
                background: rgba(56, 189, 248, 0.1) !important;
                padding: 2px 8px !important;
                border-radius: 6px !important;
                font-family: monospace !important;
                font-size: 0.95rem !important;
            }
            .rsd-slider,
            .rsd-range-input {
                width: 100% !important;
                height: 6px !important;
                background: #1E293B !important;
                border-radius: 4px !important;
                outline: none !important;
                -webkit-appearance: none !important;
                cursor: pointer !important;
            }
            .rsd-slider::-webkit-slider-thumb,
            .rsd-range-input::-webkit-slider-thumb {
                -webkit-appearance: none !important;
                width: 20px !important;
                height: 20px !important;
                border-radius: 50% !important;
                background: #FFFFFF !important;
                border: 3px solid #2563EB !important;
                cursor: pointer !important;
                box-shadow: 0 0 10px rgba(37, 99, 235, 0.8) !important;
            }
            .rsd-output-group {
                margin-bottom: 20px !important;
            }
            .rsd-output-label {
                font-size: 0.78rem !important;
                font-weight: 700 !important;
                color: #94A3B8 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.08em !important;
                margin-bottom: 4px !important;
            }
            .rsd-output-value {
                font-size: clamp(1.9rem, 3vw, 2.5rem) !important;
                font-weight: 900 !important;
                color: #38BDF8 !important;
                font-family: monospace !important;
                line-height: 1.1 !important;
            }
            .rsd-output-value-green {
                font-size: 1.4rem !important;
                font-weight: 800 !important;
                color: #10B981 !important;
            }
            .rsd-output-sub {
                font-size: 0.85rem !important;
                color: #64748B !important;
                margin-top: 4px !important;
            }
            .rsd-output-btn {
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
                color: #FFFFFF !important;
                border: none !important;
                padding: 14px 24px !important;
                border-radius: 12px !important;
                font-weight: 700 !important;
                font-size: 0.95rem !important;
                cursor: pointer !important;
                box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3) !important;
                transition: transform 0.2s ease !important;
                width: 100% !important;
                margin-top: 10px !important;
            }
            .rsd-output-btn:hover {
                transform: translateY(-2px) !important;
            }

            /* Matrix Comparison Styling */
            .rsd-matrix-sec {
                background: #030712 !important;
                padding: 90px 20px !important;
            }
            .rsd-unified-matrix-card {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 24px !important;
                max-width: 1100px !important;
                margin: 0 auto !important;
            }
            @media (max-width: 800px) {
                .rsd-unified-matrix-card { grid-template-columns: 1fr !important; }
            }
            .rsd-matrix-col {
                background: rgba(15, 23, 42, 0.7) !important;
                border: 1px solid rgba(255, 255, 255, 0.08) !important;
                border-radius: 20px !important;
                padding: 32px !important;
                backdrop-filter: blur(12px) !important;
            }
            .rsd-matrix-col.col-new {
                border-color: rgba(37, 99, 235, 0.4) !important;
                background: rgba(15, 23, 42, 0.9) !important;
                box-shadow: 0 20px 40px rgba(37, 99, 235, 0.15) !important;
            }
            .rsd-col-badge {
                display: inline-block !important;
                font-size: 0.75rem !important;
                font-weight: 700 !important;
                padding: 4px 12px !important;
                border-radius: 9999px !important;
                margin-bottom: 12px !important;
            }
            .badge-old { background: rgba(239, 68, 68, 0.15) !important; color: #F87171 !important; border: 1px solid rgba(239, 68, 68, 0.3) !important; }
            .badge-new { background: rgba(16, 185, 129, 0.15) !important; color: #34D399 !important; border: 1px solid rgba(16, 185, 129, 0.3) !important; }
            .rsd-col-title {
                color: #FFFFFF !important;
                font-size: 1.25rem !important;
                font-weight: 800 !important;
                margin: 0 0 20px 0 !important;
            }
            .rsd-matrix-list {
                list-style: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .rsd-matrix-item {
                color: #CBD5E1 !important;
                font-size: 0.92rem !important;
                line-height: 1.6 !important;
                margin-bottom: 14px !important;
                padding-left: 24px !important;
                position: relative !important;
            }
            [dir="rtl"] .rsd-matrix-item {
                padding-left: 0 !important;
                padding-right: 24px !important;
            }
            .item-cross::before {
                content: "✕" !important;
                position: absolute !important;
                left: 0 !important;
                color: #EF4444 !important;
                font-weight: 800 !important;
            }
            [dir="rtl"] .item-cross::before {
                left: auto !important;
                right: 0 !important;
            }
            .item-check::before {
                content: "✓" !important;
                position: absolute !important;
                left: 0 !important;
                color: #10B981 !important;
                font-weight: 800 !important;
            }
            [dir="rtl"] .item-check::before {
                left: auto !important;
                right: 0 !important;
            }

            /* Final Dark CTA Section */
            .rsd-saas-dark-sec {
                background: #030712 !important;
                padding: 80px 20px !important;
                text-align: center !important;
            }
            .rsd-saas-dark-container {
                max-width: 800px !important;
                margin: 0 auto !important;
            }
            .rsd-guarantee-pill {
                display: inline-block !important;
                background: rgba(16, 185, 129, 0.1) !important;
                border: 1px solid rgba(16, 185, 129, 0.3) !important;
                color: #34D399 !important;
                padding: 8px 18px !important;
                border-radius: 9999px !important;
                font-size: 0.85rem !important;
                font-weight: 700 !important;
            }
            .rsd-dark-h2 {
                color: #FFFFFF !important;
                font-size: clamp(1.8rem, 3.5vw, 2.5rem) !important;
                font-weight: 800 !important;
                margin: 20px 0 12px 0 !important;
            }
            .rsd-dark-subtext {
                color: #94A3B8 !important;
                font-size: 1rem !important;
                line-height: 1.6 !important;
                margin: 0 auto 30px auto !important;
            }
            .rsd-saas-btn-primary {
                background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
                color: #FFFFFF !important;
                border: none !important;
                padding: 16px 36px !important;
                border-radius: 50px !important;
                font-size: 1.05rem !important;
                font-weight: 700 !important;
                cursor: pointer !important;
                box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35) !important;
                transition: all 0.25s ease !important;
            }
            .rsd-saas-btn-primary:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 8px 24px rgba(37, 99, 235, 0.5) !important;
            }

        
            /* ==========================================================================
               HORIZONTAL INTEGRATIONS MARQUEE & VERTICAL TESTIMONIALS FIX
               ========================================================================== */
            .rsd-marquee-section {
                background: #030712 !important;
                padding: 40px 0 !important;
                overflow: hidden !important;
                position: relative !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .rsd-marquee-header {
                text-align: center !important;
                margin-bottom: 24px !important;
            }
            .rsd-marquee-badge {
                font-size: 0.76rem !important;
                font-weight: 800 !important;
                letter-spacing: 0.14em !important;
                color: #94A3B8 !important;
                text-transform: uppercase !important;
            }
            .rsd-integrations-marquee-wrapper {
                width: 100% !important;
                overflow: hidden !important;
                display: flex !important;
                white-space: nowrap !important;
                position: relative !important;
                -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent) !important;
                mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent) !important;
            }
            .rsd-integrations-marquee-track {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                gap: 20px !important;
                width: max-content !important;
                animation: rsd-marquee-horiz-infinite 30s linear infinite !important;
                will-change: transform !important;
            }
            .rsd-integrations-marquee-wrapper:hover .rsd-integrations-marquee-track {
                animation-play-state: paused !important;
            }
            @keyframes rsd-marquee-horiz-infinite {
                0% { transform: translateX(0); }
                100% { transform: translateX(-50%); }
            }
            .rsd-marquee-item {
                display: inline-flex !important;
                align-items: center !important;
                gap: 8px !important;
                padding: 10px 20px !important;
                background: rgba(15, 23, 42, 0.8) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                border-radius: 9999px !important;
                color: #F8FAFC !important;
                font-size: 0.92rem !important;
                font-weight: 600 !important;
                white-space: nowrap !important;
                flex-shrink: 0 !important;
            }
            .rsd-marquee-icon {
                font-size: 1.1rem !important;
            }

            /* DISTINCTIVE 3-COLUMN INFINITE VERTICAL MARQUEE TESTIMONIALS */
            .rsd-marquee-mask-wrap {
                position: relative !important;
                display: flex !important;
                justify-content: center !important;
                gap: 24px !important;
                max-height: 600px !important;
                overflow: hidden !important;
                -webkit-mask-image: linear-gradient(to bottom, transparent, black 12%, black 88%, transparent) !important;
                mask-image: linear-gradient(to bottom, transparent, black 12%, black 88%, transparent) !important;
                padding: 10px 0 !important;
                box-sizing: border-box !important;
            }
            .rsd-marquee-col {
                flex: 1 !important;
                max-width: 360px !important;
                width: 100% !important;
            }
            @media (max-width: 768px) {
                .rsd-marquee-col.col-2, .rsd-marquee-col.col-3 { display: none !important; }
            }
            @media (min-width: 769px) and (max-width: 1024px) {
                .rsd-marquee-col.col-3 { display: none !important; }
            }
            .rsd-vertical-marquee-track {
                display: flex !important;
                flex-direction: column !important;
                gap: 20px !important;
                will-change: transform !important;
            }
            .rsd-vertical-marquee-track.track-1 {
                animation: rsd-marquee-vert 22s linear infinite !important;
            }
            .rsd-vertical-marquee-track.track-2 {
                animation: rsd-marquee-vert 28s linear infinite !important;
            }
            .rsd-vertical-marquee-track.track-3 {
                animation: rsd-marquee-vert 24s linear infinite !important;
            }
            .rsd-marquee-mask-wrap:hover .rsd-vertical-marquee-track {
                animation-play-state: paused !important;
            }
            @keyframes rsd-marquee-vert {
                0% { transform: translateY(0); }
                100% { transform: translateY(-50%); }
            }

        </style>
