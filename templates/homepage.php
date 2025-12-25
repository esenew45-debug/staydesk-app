<?php
// Check if user is logged in for conditional button display
$homepage_is_logged_in = is_user_logged_in();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --sapphire: #1e3a5f;
            --sapphire-light: #2d5a8a;
            --sapphire-glow: rgba(30, 58, 95, 0.4);
            --accent: #6bb3ff;
            --accent-light: #8ec5ff;
            --bg-dark: #0f1419;
            --bg-darker: #080b0e;
            --glass-bg: rgba(30, 58, 95, 0.1);
            --glass-border: rgba(107, 179, 255, 0.15);
            --text-primary: #e2e8f0;
            --text-secondary: #94a3b8;
            --font-display: 'Nunito', -apple-system, sans-serif;
            --font-body: 'Nunito', -apple-system, sans-serif;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-darker);
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--sapphire), var(--sapphire-light));
            border-radius: 3px;
        }
        
        body {
            font-family: var(--font-body);
            background: var(--bg-dark);
            color: var(--text-primary);
            overflow-x: hidden;
            font-size: 0.85rem;
        }
        
        a { text-decoration: none; }
        
        /* Hide header spacer on homepage since hero handles its own spacing */
        .header-spacer { display: none; }
        
        /* All elements visible by default - no scroll animations */
        
        /* Icon animations */
        .icon-animate {
            transition: all 0.3s ease;
        }
        
        .icon-animate:hover {
            filter: drop-shadow(0 0 8px var(--accent));
            transform: scale(1.1);
        }
        
        /* ============================================
           HERO SECTION
           ============================================ */
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-dark) 0%, #0d1520 50%, var(--bg-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 140px 20px 80px; /* Increased padding to prevent header overlap */
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 30%, rgba(30, 58, 95, 0.08) 0%, transparent 50%);
            animation: heroGlow 20s ease-in-out infinite;
        }
        
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 70% 70%, rgba(107, 179, 255, 0.04) 0%, transparent 50%);
            animation: heroGlow 25s ease-in-out infinite reverse;
        }
        
        @keyframes heroGlow {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.5; }
            50% { transform: scale(1.1) rotate(3deg); opacity: 0.8; }
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            color: var(--accent);
            margin-bottom: 24px;
            animation: fadeIn 0.8s ease-out 0.2s both;
        }
        
        .hero-content h1 {
            font-family: var(--font-display);
            font-size: clamp(2rem, 4.5vw, 3.2rem);
            font-weight: 600;
            margin-bottom: 20px;
            line-height: 1.2;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 50%, var(--accent-light) 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: textShimmer 6s ease-in-out infinite, fadeIn 0.8s ease-out 0.3s both;
            letter-spacing: -0.5px;
        }
        
        @keyframes textShimmer {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .hero-content p {
            font-size: 0.95rem;
            margin-bottom: 32px;
            color: var(--text-secondary);
            line-height: 1.7;
            max-width: 550px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeIn 0.8s ease-out 0.4s both;
        }
        
        .hero-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeIn 0.8s ease-out 0.5s both;
        }
        
        .btn {
            padding: 10px 22px;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn svg {
            width: 14px;
            height: 14px;
            transition: all 0.3s ease;
        }
        
        .btn:hover svg {
            transform: translateX(2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            color: #ffffff;
            box-shadow: 0 4px 15px var(--sapphire-glow);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px var(--sapphire-glow);
        }
        
        .btn-secondary {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            color: var(--accent);
            border: 1px solid var(--glass-border);
        }
        
        .btn-secondary:hover {
            background: rgba(30, 58, 95, 0.2);
            transform: translateY(-2px);
        }
        
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid var(--glass-border);
            animation: fadeIn 0.8s ease-out 0.6s both;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-family: var(--font-display);
            font-size: 1.8rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-label {
            font-size: 0.7rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }
        
        /* ============================================
           WHY CHOOSE SECTION
           ============================================ */
        .why-section {
            padding: 120px 20px;
            background: var(--bg-dark);
            position: relative;
            z-index: 2;
        }
        
        .why-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--glass-border), transparent);
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: var(--glass-bg);
            border-radius: 16px;
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--accent);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        
        .section-tag svg {
            width: 12px;
            height: 12px;
        }
        
        .section-title {
            font-family: var(--font-display);
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            font-weight: 600;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }
        
        .section-subtitle {
            font-size: 0.9rem;
            color: var(--text-secondary);
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .why-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        
        /* Glassmorphism Cards */
        .why-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 24px;
            border-radius: 14px;
            border: 1px solid var(--glass-border);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .why-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--sapphire), var(--accent));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        
        .why-card:hover::before {
            transform: scaleX(1);
        }
        
        .why-card:hover {
            transform: translateY(-6px);
            border-color: rgba(107, 179, 255, 0.3);
            box-shadow: 0 15px 40px rgba(30, 58, 95, 0.3);
        }
        
        .why-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--sapphire), var(--sapphire-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }
        
        .why-icon svg {
            width: 20px;
            height: 20px;
            color: var(--accent);
        }
        
        .why-card h3 {
            font-family: var(--font-display);
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .why-card p {
            font-size: 0.8rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        /* ============================================
           FEATURES SECTION
           ============================================ */
        .features-section {
            padding: 100px 20px;
            background: linear-gradient(180deg, #0d1520 0%, var(--bg-dark) 100%);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }
        
        .feature-card {
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            background: rgba(30, 58, 95, 0.15);
            border-color: rgba(107, 179, 255, 0.2);
            transform: translateY(-3px);
        }
        
        .feature-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--sapphire), var(--sapphire-light));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }
        
        .feature-icon svg {
            width: 18px;
            height: 18px;
            color: var(--accent);
        }
        
        .feature-card h3 {
            font-family: var(--font-display);
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 8px;
        }
        
        .feature-card p {
            font-size: 0.75rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        /* ============================================
           PRICING PREVIEW
           ============================================ */
        .pricing-preview {
            padding: 100px 20px;
            background: var(--bg-dark);
            position: relative;
        }
        
        .pricing-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--glass-border), transparent);
        }
        
        .pricing-card {
            max-width: 380px;
            margin: 0 auto;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 32px;
            border: 1px solid var(--glass-border);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .pricing-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--sapphire), var(--accent), var(--sapphire));
        }
        
        .pricing-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            color: #ffffff;
            font-size: 0.6rem;
            font-weight: 700;
            border-radius: 16px;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }
        
        .pricing-badge svg {
            width: 10px;
            height: 10px;
        }
        
        .pricing-price {
            margin-bottom: 20px;
        }
        
        .pricing-price .amount {
            font-family: var(--font-display);
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .pricing-price .period {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        
        .pricing-features {
            list-style: none;
            text-align: left;
            margin-bottom: 24px;
        }
        
        .pricing-features li {
            padding: 8px 0;
            border-bottom: 1px solid var(--glass-border);
            color: var(--text-secondary);
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .pricing-features li svg {
            width: 14px;
            height: 14px;
            color: var(--accent);
        }
        
        /* ============================================
           CTA SECTION
           ============================================ */
        .cta-section {
            padding: 100px 20px;
            background: linear-gradient(135deg, #0d1520 0%, var(--bg-dark) 50%, #0d1520 100%);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(30, 58, 95, 0.15) 0%, transparent 70%);
            transform: translate(-50%, -50%);
        }
        
        .cta-content {
            position: relative;
            z-index: 2;
            max-width: 550px;
            margin: 0 auto;
        }
        
        .cta-content h2 {
            font-family: var(--font-display);
            font-size: clamp(1.5rem, 3vw, 2.2rem);
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }
        
        .cta-content p {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 28px;
            line-height: 1.6;
        }
        
        /* ============================================
           ANIMATIONS
           ============================================ */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 768px) {
            .hero-section {
                padding: 100px 20px 50px;
                padding-top: 100px;
            }
            
            .hero-stats {
                flex-wrap: wrap;
                gap: 24px;
            }
            
            .stat-item {
                flex: 0 0 40%;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 250px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include STAYDESK_PLUGIN_DIR . 'templates/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-badge">
                <svg data-feather="star" width="12" height="12"></svg>
                Trusted by Hotels Across Nigeria
            </div>
            <h1>Transform Your Hotel Operations with Smart Technology</h1>
            <p>StayDesk by BendlessTech is the all-in-one hotel management platform designed specifically for Nigerian hotels. Streamline bookings, automate guest communication, and grow your revenue.</p>
            <div class="hero-buttons">
                <?php if ($homepage_is_logged_in): ?>
                    <a href="<?php echo esc_url(home_url('/staydesk-bookings')); ?>" class="btn btn-primary">
                        <svg data-feather="calendar"></svg>
                        View Bookings
                    </a>
                    <a href="<?php echo esc_url(home_url('/staydesk-dashboard')); ?>" class="btn btn-secondary">
                        <svg data-feather="grid"></svg>
                        Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/staydesk-signup')); ?>" class="btn btn-primary">
                        <svg data-feather="user-plus"></svg>
                        Create Account
                    </a>
                    <a href="<?php echo esc_url(home_url('/staydesk-pricing')); ?>" class="btn btn-secondary">
                        <svg data-feather="tag"></svg>
                        View Pricing
                    </a>
                <?php endif; ?>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-value">500+</div>
                    <div class="stat-label">Hotels Served</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">50K+</div>
                    <div class="stat-label">Bookings Processed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">99.9%</div>
                    <div class="stat-label">Uptime</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Why Choose Section -->
    <section class="why-section">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">
                    <svg data-feather="award"></svg>
                    Why BendlessTech
                </div>
                <h2 class="section-title">Built for Nigerian Hotels</h2>
                <p class="section-subtitle">We understand the unique challenges of running a hotel in Nigeria. That's why StayDesk is designed with local needs in mind.</p>
            </div>
            
            <div class="why-grid">
                <div class="why-card">
                    <div class="why-icon">
                        <svg data-feather="credit-card"></svg>
                    </div>
                    <h3>Local Payment Integration</h3>
                    <p>Seamless Paystack integration for accepting payments in Nigerian Naira. No foreign exchange hassles.</p>
                </div>
                
                <div class="why-card">
                    <div class="why-icon">
                        <svg data-feather="message-circle"></svg>
                    </div>
                    <h3>Bilingual AI Chatbot</h3>
                    <p>Our AI assistant speaks both English and Pidgin, connecting naturally with your Nigerian guests 24/7.</p>
                </div>
                
                <div class="why-card">
                    <div class="why-icon">
                        <svg data-feather="smartphone"></svg>
                    </div>
                    <h3>WhatsApp Integration</h3>
                    <p>Send booking confirmations, reminders, and updates directly to guests via WhatsApp.</p>
                </div>
                
                <div class="why-card">
                    <div class="why-icon">
                        <svg data-feather="zap"></svg>
                    </div>
                    <h3>Optimized for Nigeria</h3>
                    <p>Works smoothly even on slower internet connections. Lightweight design ensures fast loading.</p>
                </div>
                
                <div class="why-card">
                    <div class="why-icon">
                        <svg data-feather="shield"></svg>
                    </div>
                    <h3>Reliable & Secure</h3>
                    <p>Your data is protected with enterprise-grade security. Built on robust infrastructure for 99.9% uptime.</p>
                </div>
                
                <div class="why-card">
                    <div class="why-icon">
                        <svg data-feather="headphones"></svg>
                    </div>
                    <h3>Local Support Team</h3>
                    <p>Our support team is based in Nigeria, understanding your needs and available during your hours.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">
                    <svg data-feather="layers"></svg>
                    Features
                </div>
                <h2 class="section-title">Everything You Need to Succeed</h2>
                <p class="section-subtitle">Powerful tools designed to make hotel management effortless</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg data-feather="calendar"></svg>
                    </div>
                    <h3>Smart Booking System</h3>
                    <p>Manage reservations, check-ins, and check-outs from one intuitive dashboard.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg data-feather="home"></svg>
                    </div>
                    <h3>Room Management</h3>
                    <p>Track room availability, set dynamic pricing, and manage housekeeping status.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg data-feather="trending-up"></svg>
                    </div>
                    <h3>Revenue Tracking</h3>
                    <p>Real-time analytics on revenue, occupancy rates, and booking trends.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg data-feather="bell"></svg>
                    </div>
                    <h3>Auto Notifications</h3>
                    <p>Automated email and WhatsApp notifications keep guests informed.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg data-feather="rotate-ccw"></svg>
                    </div>
                    <h3>Easy Refunds</h3>
                    <p>Process cancellations and refunds with just a few clicks.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg data-feather="bar-chart-2"></svg>
                    </div>
                    <h3>Detailed Reports</h3>
                    <p>Generate comprehensive reports for better business decisions.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Pricing Preview -->
    <section class="pricing-preview">
        <div class="container">
            <div class="section-header">
                <div class="section-tag">
                    <svg data-feather="tag"></svg>
                    Pricing
                </div>
                <h2 class="section-title">Simple, Transparent Pricing</h2>
                <p class="section-subtitle">Start growing your hotel business today</p>
            </div>
            
            <div class="pricing-card">
                <div class="pricing-badge">
                    <svg data-feather="star"></svg>
                    MOST POPULAR
                </div>
                <div class="pricing-price">
                    <span class="amount">₦15,000</span>
                    <span class="period">/month</span>
                </div>
                <ul class="pricing-features">
                    <li><svg data-feather="check"></svg> Unlimited room listings</li>
                    <li><svg data-feather="check"></svg> Unlimited bookings</li>
                    <li><svg data-feather="check"></svg> AI Chatbot (English & Pidgin)</li>
                    <li><svg data-feather="check"></svg> Paystack payment integration</li>
                    <li><svg data-feather="check"></svg> WhatsApp notifications</li>
                    <li><svg data-feather="check"></svg> Analytics dashboard</li>
                    <li><svg data-feather="check"></svg> Priority support</li>
                </ul>
                <a href="<?php echo esc_url(home_url('/staydesk-signup')); ?>" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Get Started
                    <svg data-feather="arrow-right"></svg>
                </a>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2>Ready to Transform Your Hotel?</h2>
            <p>Join hundreds of Nigerian hotels already using StayDesk to streamline their operations and delight their guests.</p>
            <div class="hero-buttons">
                <a href="<?php echo esc_url(home_url('/staydesk-signup')); ?>" class="btn btn-primary">
                    Start Your Free Trial
                    <svg data-feather="arrow-right"></svg>
                </a>
                <a href="https://wa.me/2347120018023" class="btn btn-secondary" target="_blank">
                    <svg data-feather="message-circle"></svg>
                    Chat With Us
                </a>
            </div>
        </div>
    </section>
    
    <?php include STAYDESK_PLUGIN_DIR . 'templates/footer.php'; ?>
    
    <script>
        // Initialize Feather Icons with fallback check
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>