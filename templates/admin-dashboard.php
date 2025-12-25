<?php
if (!is_user_logged_in()) {
    wp_redirect(home_url('/staydesk-login'));
    exit;
}

global $wpdb;
$admin_data = Staydesk_Admin::get_admin_data();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024, initial-scale=0.5">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            min-height: 100vh;
            padding: 25px;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            padding: 25px;
            border-radius: 18px;
            margin-bottom: 30px;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(212, 175, 55, 0.3);
            animation: slideIn 0.6s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        h1 {
            background: linear-gradient(90deg, #FFD700 0%, #4FC3F7 25%, #FFD700 50%, #64B5F6 75%, #FFD700 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient-shift 4s ease-in-out infinite;
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 10px 0;
        }
        
        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .subtitle {
            color: #B8B8B8;
            font-size: 14px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(212, 175, 55, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.6s ease-out backwards;
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(212, 175, 55, 0.3);
            border-color: rgba(212, 175, 55, 0.5);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #D4AF37 0%, #FFD700 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #A0A0A0;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(212, 175, 55, 0.2);
            animation: fadeInUp 0.6s ease-out backwards;
        }
        
        .action-card:nth-child(1) { animation-delay: 0.5s; }
        .action-card:nth-child(2) { animation-delay: 0.6s; }
        .action-card:nth-child(3) { animation-delay: 0.7s; }
        
        .action-card h3 {
            color: #FFD700;
            font-size: 1.2rem;
            margin-bottom: 12px;
        }
        
        .action-card p {
            color: #B8B8B8;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .btn {
            padding: 8px 18px;
            background: linear-gradient(135deg, #D4AF37 0%, #FFD700 100%);
            color: #000;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 0.5px;
            box-shadow: 0 3px 12px rgba(212, 175, 55, 0.3);
            position: relative;
            overflow: hidden;
            display: inline-block;
            text-decoration: none;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn span {
            position: relative;
            display: inline-block;
            animation: buttonTextFloat 2s ease-in-out infinite;
        }
        
        @keyframes buttonTextFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-2px); }
        }
        
        .btn:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.5);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #4FC3F7 0%, #64B5F6 100%);
            box-shadow: 0 3px 12px rgba(79, 195, 247, 0.3);
        }
        
        .btn-secondary:hover {
            box-shadow: 0 6px 20px rgba(79, 195, 247, 0.5);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>🏨 Admin Dashboard</h1>
            <p class="subtitle">BendlessTech platform administration and analytics</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🏨</div>
                <div class="stat-value"><?php echo number_format($admin_data['total_hotels']); ?></div>
                <div class="stat-label">Total Hotels</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?php echo number_format($admin_data['active_subscriptions']); ?></div>
                <div class="stat-label">Active Subscriptions</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-value"><?php echo number_format($admin_data['total_bookings']); ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-value">₦<?php echo number_format($admin_data['total_revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        
        <div class="actions-grid">
            <div class="action-card">
                <h3>🏨 Manage Hotels</h3>
                <p>View and manage all registered hotels on the platform. Monitor subscriptions and performance.</p>
                <a href="<?php echo home_url('/staydesk-hotels'); ?>" class="btn">
                    <span>View Hotels</span>
                </a>
            </div>
            
            <div class="action-card">
                <h3>📊 View Analytics</h3>
                <p>Access detailed analytics and reports for platform performance and revenue tracking.</p>
                <a href="<?php echo home_url('/staydesk-analytics'); ?>" class="btn btn-secondary">
                    <span>View Analytics</span>
                </a>
            </div>
            
            <div class="action-card">
                <h3>👥 User Management</h3>
                <p>Manage user roles, permissions, and access control across the platform.</p>
                <a href="<?php echo home_url('/staydesk-users'); ?>" class="btn">
                    <span>Manage Users</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>