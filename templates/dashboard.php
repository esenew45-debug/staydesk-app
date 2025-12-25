<?php
if (!is_user_logged_in()) {
    wp_redirect(home_url('/staydesk-login'));
    exit;
}

global $wpdb;
$user_id = get_current_user_id();
$table_hotels = $wpdb->prefix . 'staydesk_hotels';
$hotel = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_hotels WHERE user_id = %d",
    $user_id
));

if (!$hotel) {
    echo '<p>Hotel profile not found.</p>';
    return;
}

$dashboard_data = Staydesk_Dashboard::get_dashboard_data($hotel->id);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
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
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --font-display: 'Nunito', -apple-system, sans-serif;
            --font-body: 'Nunito', -apple-system, sans-serif;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
        
        /* Remove all underlines from links */
        a {
            text-decoration: none !important;
        }
        
        body {
            font-family: var(--font-body);
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
        }
        
        /* Dashboard Layout with Sidebar */
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .dashboard-sidebar {
            width: 240px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--glass-border);
            padding: 20px 0;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Collapsed sidebar state */
        .dashboard-sidebar.collapsed {
            width: 70px;
        }
        
        .dashboard-sidebar.collapsed .sidebar-logo h2,
        .dashboard-sidebar.collapsed .nav-section-title,
        .dashboard-sidebar.collapsed .nav-item-text {
            opacity: 0;
            visibility: hidden;
            width: 0;
            overflow: hidden;
        }
        
        .dashboard-sidebar.collapsed .sidebar-logo {
            padding: 0 10px 20px;
            justify-content: center;
        }
        
        .dashboard-sidebar.collapsed .nav-item {
            justify-content: center;
            padding: 12px;
        }
        
        .dashboard-sidebar.collapsed .nav-item svg {
            margin: 0;
        }
        
        .sidebar-logo {
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--glass-border);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-logo-icon {
            width: 32px;
            height: 32px;
            min-width: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px var(--sapphire-glow);
        }
        
        .sidebar-logo-icon svg {
            width: 18px;
            height: 18px;
            color: var(--accent);
        }
        
        .sidebar-logo h2 {
            font-family: var(--font-display);
            font-size: 1.3rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            white-space: nowrap;
            transition: all 0.3s ease;
        }
        
        /* Collapse Toggle Button */
        .sidebar-collapse-btn {
            position: absolute;
            top: 70px;
            right: -12px;
            width: 24px;
            height: 24px;
            background: var(--sapphire);
            border: 1px solid var(--glass-border);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 101;
            transition: all 0.3s ease;
        }
        
        .sidebar-collapse-btn svg {
            width: 14px;
            height: 14px;
            color: var(--accent);
            transition: transform 0.3s ease;
        }
        
        .sidebar-collapse-btn:hover {
            background: var(--sapphire-light);
            transform: scale(1.1);
        }
        
        .dashboard-sidebar.collapsed .sidebar-collapse-btn svg {
            transform: rotate(180deg);
        }
        
        .sidebar-nav {
            padding: 0 12px;
        }
        
        .dashboard-sidebar.collapsed .sidebar-nav {
            padding: 0 8px;
        }
        
        .nav-section {
            margin-bottom: 20px;
        }
        
        .nav-section-title {
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 8px;
            margin-bottom: 8px;
            white-space: nowrap;
            transition: all 0.3s ease;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--text-secondary);
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 4px;
        }
        
        .nav-item svg {
            width: 16px;
            height: 16px;
            min-width: 16px;
            transition: all 0.3s ease;
        }
        
        .nav-item-text {
            white-space: nowrap;
            transition: all 0.3s ease;
        }
        
        .nav-item:hover, .nav-item.active {
            background: rgba(30, 58, 95, 0.3);
            color: var(--accent);
        }
        
        .nav-item:hover svg, .nav-item.active svg {
            filter: drop-shadow(0 0 4px var(--accent));
        }
        
        .nav-item.active {
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            color: #ffffff;
        }
        
        /* Main Content */
        .dashboard-main {
            flex: 1;
            margin-left: 240px;
            padding: 24px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .dashboard-main.sidebar-collapsed {
            margin-left: 70px;
        }
        
        /* Dashboard Header */
        .dashboard-header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 18px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            border: 1px solid var(--glass-border);
        }
        
        .header-left h1 {
            font-family: var(--font-display);
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 4px;
            font-weight: 600;
            font-size: 1.3rem;
        }
        
        .header-left p {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }
        
        .header-right {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        
        .real-time-clock {
            font-family: var(--font-display);
            font-size: 1.2rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .btn-logout {
            padding: 8px 16px;
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            cursor: pointer;
            font-family: var(--font-body);
            font-weight: 600;
            font-size: 0.75rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-logout svg {
            width: 14px;
            height: 14px;
        }
        
        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.15);
            transform: translateY(-1px);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 18px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
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
        
        .stat-card:hover::before {
            transform: scaleX(1);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(30, 58, 95, 0.3);
            border-color: rgba(107, 179, 255, 0.25);
        }
        
        .stat-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--sapphire), var(--sapphire-light));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }
        
        .stat-icon svg {
            width: 18px;
            height: 18px;
            color: var(--accent);
        }
        
        .stat-value {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 4px;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        /* Dashboard Sections */
        .dashboard-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .section-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .section-card::before {
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
        
        .section-card:hover::before {
            transform: scaleX(1);
        }
        
        .section-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(30, 58, 95, 0.3);
            border-color: rgba(107, 179, 255, 0.25);
        }
        
        .section-card h2 {
            font-family: var(--font-display);
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section-card h2 svg {
            width: 18px;
            height: 18px;
            color: var(--accent);
        }
        
        .section-card p {
            color: var(--text-secondary);
            margin-bottom: 18px;
            line-height: 1.6;
            font-size: 0.8rem;
        }
        
        .btn-section {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            color: #ffffff;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-family: var(--font-body);
            font-weight: 600;
            font-size: 0.75rem;
            box-shadow: 0 2px 10px var(--sapphire-glow);
        }
        
        .btn-section svg {
            width: 14px;
            height: 14px;
            transition: transform 0.3s ease;
        }
        
        .btn-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px var(--sapphire-glow);
        }
        
        .btn-section:hover svg {
            transform: translateX(2px);
        }
        
        /* Subscription Alert */
        .subscription-alert {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .subscription-alert svg {
            width: 18px;
            height: 18px;
        }
        
        .subscription-alert.expired {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }
        
        .subscription-alert.active {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }
        
        .subscription-alert a {
            color: inherit;
            text-decoration: underline !important;
        }
        
        /* Mobile Toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 200;
            width: 40px;
            height: 40px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }
        
        .mobile-toggle svg {
            width: 20px;
            height: 20px;
            color: var(--accent);
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
        
        @media (max-width: 768px) {
            .dashboard-sidebar {
                transform: translateX(-100%);
            }
            
            .dashboard-sidebar.active {
                transform: translateX(0);
            }
            
            .dashboard-main {
                margin-left: 0;
                padding: 16px;
                padding-top: 70px;
            }
            
            .mobile-toggle {
                display: flex;
            }
            
            .dashboard-header {
                flex-direction: column;
                text-align: center;
            }
            
            .header-right {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Mobile Toggle -->
        <button class="mobile-toggle" id="mobile-toggle" aria-label="Toggle sidebar menu" aria-expanded="false" aria-controls="dashboard-sidebar">
            <svg data-feather="menu"></svg>
        </button>
        
        <!-- Sidebar -->
        <aside class="dashboard-sidebar" id="dashboard-sidebar">
            <!-- Collapse Button with inline SVG arrow -->
            <button class="sidebar-collapse-btn" id="sidebar-collapse-btn" aria-label="Toggle sidebar">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <svg data-feather="home"></svg>
                </div>
                <h2>StayDesk</h2>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="<?php echo esc_url(home_url('/staydesk-dashboard')); ?>" class="nav-item active">
                        <svg data-feather="grid"></svg>
                        <span class="nav-item-text">Dashboard</span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/staydesk-bookings')); ?>" class="nav-item">
                        <svg data-feather="calendar"></svg>
                        <span class="nav-item-text">Bookings</span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/staydesk-rooms')); ?>" class="nav-item">
                        <svg data-feather="home"></svg>
                        <span class="nav-item-text">Rooms</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Finance</div>
                    <a href="#" class="nav-item">
                        <svg data-feather="credit-card"></svg>
                        <span class="nav-item-text">Payments</span>
                    </a>
                    <a href="#" class="nav-item">
                        <svg data-feather="rotate-ccw"></svg>
                        <span class="nav-item-text">Refunds</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Communication</div>
                    <a href="#" class="nav-item">
                        <svg data-feather="message-circle"></svg>
                        <span class="nav-item-text">Enquiries</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <a href="<?php echo esc_url(home_url('/staydesk-profile')); ?>" class="nav-item">
                        <svg data-feather="user"></svg>
                        <span class="nav-item-text">Profile</span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/staydesk-pricing')); ?>" class="nav-item">
                        <svg data-feather="star"></svg>
                        <span class="nav-item-text">Subscription</span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-item">
                        <svg data-feather="arrow-left"></svg>
                        <span class="nav-item-text">Back to Home</span>
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="dashboard-main">
            <div class="dashboard-header">
                <div class="header-left">
                    <h1>Welcome, <?php echo esc_html($hotel->hotel_name); ?>!</h1>
                    <p>Here's what's happening with your hotel today</p>
                </div>
                <div class="header-right">
                    <div class="real-time-clock" id="clock"></div>
                    <button class="btn-logout" id="logout-btn">
                        <svg data-feather="log-out"></svg>
                        Logout
                    </button>
                </div>
            </div>
            
            <?php if ($hotel->subscription_status === 'expired'): ?>
                <div class="subscription-alert expired">
                    <svg data-feather="alert-triangle"></svg>
                    Your subscription has expired. Please <a href="<?php echo home_url('/staydesk-pricing'); ?>">renew your subscription</a> to continue using StayDesk.
                </div>
            <?php elseif ($hotel->subscription_status === 'active'): ?>
                <div class="subscription-alert active">
                    <svg data-feather="check-circle"></svg>
                    Active until <?php echo date('F j, Y', strtotime($hotel->subscription_expiry)); ?>
                </div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg data-feather="calendar"></svg>
                    </div>
                    <div class="stat-value"><?php echo number_format($dashboard_data['total_bookings']); ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg data-feather="clock"></svg>
                    </div>
                    <div class="stat-value"><?php echo number_format($dashboard_data['pending_bookings']); ?></div>
                    <div class="stat-label">Pending Bookings</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg data-feather="trending-up"></svg>
                    </div>
                    <div class="stat-value">₦<?php echo number_format($dashboard_data['total_revenue'], 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg data-feather="home"></svg>
                    </div>
                    <div class="stat-value"><?php echo $dashboard_data['available_rooms']; ?>/<?php echo $dashboard_data['total_rooms']; ?></div>
                    <div class="stat-label">Available Rooms</div>
                </div>
            </div>
            
            <div class="dashboard-sections">
                <div class="section-card">
                    <h2><svg data-feather="calendar"></svg> Bookings Management</h2>
                    <p>View and manage all your hotel bookings, check-ins, and check-outs.</p>
                    <a href="<?php echo home_url('/staydesk-bookings'); ?>" class="btn-section">
                        Manage Bookings
                        <svg data-feather="arrow-right"></svg>
                    </a>
                </div>
                
                <div class="section-card">
                    <h2><svg data-feather="home"></svg> Rooms Management</h2>
                    <p>Add, edit, or remove rooms. Set pricing and availability.</p>
                    <a href="<?php echo home_url('/staydesk-rooms'); ?>" class="btn-section">
                        Manage Rooms
                        <svg data-feather="arrow-right"></svg>
                    </a>
                </div>
                
                <div class="section-card">
                    <h2><svg data-feather="credit-card"></svg> Payment Verification</h2>
                    <p>Verify payments and track transactions for all bookings.</p>
                    <a href="#" class="btn-section">
                        Verify Payments
                        <svg data-feather="arrow-right"></svg>
                    </a>
                </div>
                
                <div class="section-card">
                    <h2><svg data-feather="rotate-ccw"></svg> Refund Management</h2>
                    <p>Process refund requests and manage cancellations.</p>
                    <a href="#" class="btn-section">
                        Manage Refunds
                        <svg data-feather="arrow-right"></svg>
                    </a>
                </div>
                
                <div class="section-card">
                    <h2><svg data-feather="message-circle"></svg> Guest Enquiries</h2>
                    <p>View and respond to guest messages from the chatbot.</p>
                    <a href="#" class="btn-section">
                        View Enquiries (<?php echo $dashboard_data['enquiries_count']; ?>)
                        <svg data-feather="arrow-right"></svg>
                    </a>
                </div>
                
                <div class="section-card">
                    <h2><svg data-feather="settings"></svg> Profile & Settings</h2>
                    <p>Update your hotel profile, account details, and preferences.</p>
                    <a href="<?php echo home_url('/staydesk-profile'); ?>" class="btn-section">
                        Edit Profile
                        <svg data-feather="arrow-right"></svg>
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <!-- WhatsApp Support Widget -->
    <div id="whatsapp-support-widget" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
        <style>
            #whatsapp-support-widget .whatsapp-toggle {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
                border: none;
                cursor: pointer;
                box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                text-decoration: none;
                color: #FFFFFF;
            }
            
            #whatsapp-support-widget .whatsapp-toggle svg {
                width: 20px;
                height: 20px;
            }
            
            #whatsapp-support-widget .whatsapp-toggle:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(37, 211, 102, 0.6);
            }
            
            #whatsapp-support-widget .whatsapp-tooltip {
                position: absolute;
                bottom: 10px;
                right: 55px;
                background: var(--glass-bg);
                backdrop-filter: blur(10px);
                color: var(--text-primary);
                padding: 6px 10px;
                border-radius: 6px;
                font-size: 0.7rem;
                white-space: nowrap;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s;
                border: 1px solid var(--glass-border);
            }
            
            #whatsapp-support-widget:hover .whatsapp-tooltip {
                opacity: 1;
            }
        </style>
        <span class="whatsapp-tooltip">Contact Support</span>
        <a href="https://wa.me/2348012345678?text=Hello,%20I%20need%20help%20with%20StayDesk" 
           target="_blank" 
           class="whatsapp-toggle" 
           title="WhatsApp Support">
            <svg data-feather="message-circle"></svg>
        </a>
    </div>
    
    <script>
        // Initialize Feather icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
        
        // Real-time clock
        function updateClock() {
            var now = new Date();
            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');
            var seconds = now.getSeconds().toString().padStart(2, '0');
            document.getElementById('clock').textContent = hours + ':' + minutes + ':' + seconds;
        }
        
        setInterval(updateClock, 1000);
        updateClock();
        
        // Mobile sidebar toggle with null checks and ARIA updates
        var mobileToggle = document.getElementById('mobile-toggle');
        var dashboardSidebar = document.getElementById('dashboard-sidebar');
        var dashboardMain = document.querySelector('.dashboard-main');
        
        if (mobileToggle && dashboardSidebar) {
            mobileToggle.addEventListener('click', function() {
                dashboardSidebar.classList.toggle('active');
                var isExpanded = dashboardSidebar.classList.contains('active');
                mobileToggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
            });
        }
        
        // Sidebar collapse toggle (for desktop)
        var collapseBtn = document.getElementById('sidebar-collapse-btn');
        
        if (collapseBtn && dashboardSidebar && dashboardMain) {
            // Restore collapsed state from localStorage
            var isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                dashboardSidebar.classList.add('collapsed');
                dashboardMain.classList.add('sidebar-collapsed');
            }
            
            collapseBtn.addEventListener('click', function() {
                dashboardSidebar.classList.toggle('collapsed');
                dashboardMain.classList.toggle('sidebar-collapsed');
                
                // Save state to localStorage
                var nowCollapsed = dashboardSidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', nowCollapsed ? 'true' : 'false');
                
                // Re-initialize feather icons after transition
                setTimeout(function() {
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                }, 300);
            });
        }
        
        // Logout
        jQuery(document).ready(function($) {
            $('#logout-btn').on('click', function() {
                $.ajax({
                    url: staydesk_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'staydesk_logout',
                        nonce: staydesk_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.data.redirect;
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>