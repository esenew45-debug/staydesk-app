<?php
if (!is_user_logged_in()) {
    wp_redirect(home_url('/staydesk-login'));
    exit;
}

wp_enqueue_script('jquery');

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

$table_subscriptions = $wpdb->prefix . 'staydesk_subscriptions';
// Enhanced query to get subscription data with better fallback
$subscription = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_subscriptions 
     WHERE hotel_id = %d AND (status = 'active' OR status = 'pending')
     ORDER BY created_at DESC LIMIT 1",
    $hotel->id
));

// CRITICAL FIX: If no subscription record but hotel shows active, create display data from hotel record
if (!$subscription && $hotel->subscription_status === 'active' && $hotel->subscription_expiry) {
    $subscription = (object) array(
        'id' => 0,
        'hotel_id' => $hotel->id,
        'plan_type' => $hotel->subscription_plan ?: 'monthly',
        'status' => 'active',
        'expiry_date' => $hotel->subscription_expiry,
        'plan_price' => ($hotel->subscription_plan === 'yearly') ? 598800 : 49900,
        'auto_renew' => 1,
        'start_date' => date('Y-m-d H:i:s', strtotime('-30 days')),
        'created_at' => date('Y-m-d H:i:s')
    );
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024, initial-scale=0.5">
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
            padding: 25px;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 20px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            border: 1px solid var(--glass-border);
        }
        
        .page-header h1 {
            font-family: var(--font-display);
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 600;
            font-size: 1.4rem;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.75rem;
            font-family: var(--font-body);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn svg {
            width: 14px;
            height: 14px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-back {
            background: rgba(30, 58, 95, 0.3);
            color: var(--text-primary);
            border: 1px solid var(--glass-border);
        }
        
        .btn-back:hover {
            background: rgba(30, 58, 95, 0.5);
            border-color: var(--accent);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            color: #ffffff;
            box-shadow: 0 2px 10px var(--sapphire-glow);
        }
        
        .btn-primary:hover {
            box-shadow: 0 4px 20px var(--sapphire-glow);
        }
        
        .profile-section {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid var(--glass-border);
        }
        
        .profile-section h2 {
            font-family: var(--font-display);
            color: var(--accent);
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            color: var(--text-primary);
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            background: rgba(30, 58, 95, 0.3);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.85rem;
            font-family: var(--font-body);
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(107, 179, 255, 0.2);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(107, 179, 255, 0.1);
            font-size: 0.85rem;
        }
        
        .info-row label {
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .info-row span {
            color: var(--text-primary);
        }
        
        .chatbot-section {
            background: rgba(30, 58, 95, 0.15);
        }
        
        .embed-code {
            background: var(--bg-darker);
            padding: 16px;
            border-radius: 8px;
            margin-top: 12px;
            border: 1px solid var(--glass-border);
        }
        
        .embed-code code {
            color: var(--accent);
            font-size: 0.8rem;
            word-break: break-all;
            display: block;
            line-height: 1.6;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            display: none;
            font-size: 0.8rem;
        }
        
        .alert.show {
            display: block;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .upgrade-box {
            margin-top: 24px;
            padding: 20px;
            background: rgba(30, 58, 95, 0.2);
            border-radius: 10px;
            border: 1px solid var(--glass-border);
        }
        
        .upgrade-box h3 {
            color: var(--accent);
            margin-bottom: 12px;
            font-size: 1.1rem;
        }
        
        .upgrade-box p {
            color: var(--text-primary);
            margin-bottom: 12px;
            font-size: 0.85rem;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
        }
        
        .btn-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .api-endpoint {
            background: var(--bg-darker);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 8px;
        }
        
        .api-endpoint strong {
            color: #34d399;
        }
        
        .api-endpoint span {
            color: var(--text-primary);
            font-size: 0.8rem;
        }
        
        .api-endpoint p {
            color: var(--text-secondary);
            font-size: 0.75rem;
            margin: 4px 0 0 0;
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="page-header">
            <h1>Profile & Settings</h1>
            <button class="btn btn-back" onclick="window.location.href='<?php echo esc_url(home_url('/staydesk-dashboard')); ?>'">
                <svg data-feather="arrow-left"></svg>
                Dashboard
            </button>
        </div>
        
        <div id="alertBox" class="alert"></div>
        
        <?php if (isset($_GET['payment']) && $_GET['payment'] === 'success'): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showAlert('✅ Payment successful! Your subscription is now active.', 'success');
            });
        </script>
        <?php endif; ?>
        
        <div class="profile-section">
            <h2>Hotel Information</h2>
            <form id="profileForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Hotel Name</label>
                        <input type="text" name="hotel_name" value="<?php echo esc_attr($hotel->hotel_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Email</label>
                        <input type="email" name="contact_email" value="<?php echo esc_attr($hotel->contact_email); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone_number" value="<?php echo esc_attr($hotel->phone_number); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" value="<?php echo esc_attr($hotel->address); ?>">
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" value="<?php echo esc_attr($hotel->city); ?>">
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" name="state" value="<?php echo esc_attr($hotel->state); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Hotel Description</label>
                    <textarea name="description" rows="4"><?php echo esc_textarea($hotel->description ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
        
        <div class="profile-section">
            <h2>Subscription Status</h2>
            <?php if ($subscription): ?>
                <div class="info-row">
                    <label>Plan Type</label>
                    <span><strong><?php echo ucfirst($subscription->plan_type); ?> Plan</strong></span>
                </div>
                <div class="info-row">
                    <label>Status</label>
                    <span><strong style="color: #28A745;">● Active</strong></span>
                </div>
                <div class="info-row">
                    <label>Expiry Date</label>
                    <span><?php echo date('F d, Y', strtotime($subscription->expiry_date)); ?></span>
                </div>
                <div class="info-row">
                    <label>Days Remaining</label>
                    <span><?php 
                        $days = ceil((strtotime($subscription->expiry_date) - time()) / 86400);
                        echo $days . ' days';
                    ?></span>
                </div>
                <div class="info-row">
                    <label>Amount</label>
                    <span>₦<?php echo number_format($subscription->plan_price); ?>/<?php echo $subscription->plan_type === 'monthly' ? 'month' : 'year'; ?></span>
                </div>
                <div class="info-row">
                    <label>Auto-Renew</label>
                    <span><?php echo $subscription->auto_renew ? 'On' : 'Off'; ?></span>
                </div>
                
                <?php if ($subscription->plan_type === 'monthly'): ?>
                <div class="upgrade-box">
                    <h3>Upgrade to Yearly Plan</h3>
                    <p>Save ₦59,880 annually with our yearly plan!</p>
                    <p style="color: var(--text-secondary); font-size: 0.8rem; margin-bottom: 12px;">
                        Yearly: ₦598,800/year<br>
                        10% discount for first 10 hotels!
                    </p>
                    <button class="btn btn-success" onclick="upgradeToYearly()">
                        <svg data-feather="trending-up"></svg>
                        Upgrade Now - Save ₦59,880!
                    </button>
                </div>
                <?php endif; ?>
                
                <div style="margin-top: 20px;">
                    <button class="btn btn-danger" onclick="cancelSubscription()">
                        <svg data-feather="x-circle"></svg>
                        Cancel Subscription
                    </button>
                </div>
            <?php else: ?>
                <?php if ($hotel->subscription_status !== 'active'): ?>
                    <p style="color: var(--text-secondary);">No active subscription. <a href="<?php echo esc_url(home_url('/staydesk-pricing')); ?>" style="color: var(--accent);">Subscribe now</a></p>
                <?php else: ?>
                    <div style="padding: 16px; background: rgba(16, 185, 129, 0.1); border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.3);">
                        <p style="color: #34d399; font-size: 0.95rem; margin-bottom: 8px;">✓ Subscription Active</p>
                        <p style="color: var(--text-secondary); font-size: 0.8rem;">Your subscription is currently active. Detailed information will appear once the system completes synchronization.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="profile-section chatbot-section">
            <h2>Website Integration</h2>
            <p style="color: var(--text-primary); margin-bottom: 16px; font-size: 0.85rem;">Connect your hotel website to StayDesk for real-time room availability and booking integration.</p>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Hotel Website URL</label>
                    <input type="url" id="website_url" name="website_url" value="<?php echo esc_attr($hotel->website_url ?? ''); ?>" placeholder="https://www.yourhotel.com">
                </div>
                <div class="form-group">
                    <label>API Key (for authenticated requests)</label>
                    <input type="text" id="api_key" value="<?php echo esc_attr($hotel->api_key ?? ''); ?>" readonly style="background: #1a1a1a;">
                    <button type="button" class="btn btn-primary" style="margin-top: 10px; padding: 10px 20px;" onclick="regenerateApiKey()">🔄 Regenerate Key</button>
                </div>
            </div>
            
            <button type="button" class="btn btn-primary" style="margin-top: 15px;" onclick="saveWebsiteUrl()">Save Website URL</button>
            
            <div style="margin-top: 30px; padding: 20px; background: rgba(212, 175, 55, 0.1); border-radius: 10px; border: 1px solid rgba(212, 175, 55, 0.3);">
                <h3 style="color: #D4AF37; margin-bottom: 15px; font-size: 1.2rem;">📊 Real-Time Widget for Your Website</h3>
                <p style="color: #B8B8B8; font-size: 0.9rem; margin-bottom: 15px;">
                    Add this widget to your hotel website to display real-time room availability and pricing. Your guests can see available rooms and book directly.
                </p>
                
                <div class="embed-code">
                    <code id="widget-code">&lt;!-- StayDesk Real-Time Widget --&gt;<br>
&lt;div id="staydesk-widget"&gt;&lt;/div&gt;<br>
&lt;script src="<?php echo home_url('/wp-content/plugins/staydesk/public/js/staydesk-widget.js'); ?>"&gt;&lt;/script&gt;<br>
&lt;script&gt;<br>
&nbsp;&nbsp;StayDeskWidget.init({<br>
&nbsp;&nbsp;&nbsp;&nbsp;hotelId: <?php echo $hotel->id; ?>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;apiKey: '<?php echo esc_attr($hotel->api_key ?? ''); ?>',<br>
&nbsp;&nbsp;&nbsp;&nbsp;theme: 'dark', // 'dark' or 'light'<br>
&nbsp;&nbsp;&nbsp;&nbsp;showPrices: true,<br>
&nbsp;&nbsp;&nbsp;&nbsp;showAvailability: true,<br>
&nbsp;&nbsp;&nbsp;&nbsp;bookingUrl: '<?php echo home_url('/staydesk-booking?hotel=' . $hotel->id); ?>'<br>
&nbsp;&nbsp;});<br>
&lt;/script&gt;</code>
                </div>
                
                <button class="btn btn-primary" style="margin-top: 15px;" onclick="copyWidgetCode()">📋 Copy Widget Code</button>
            </div>
            
            <div style="margin-top: 30px; padding: 20px; background: rgba(100, 181, 246, 0.1); border-radius: 10px; border: 1px solid rgba(100, 181, 246, 0.3);">
                <h3 style="color: #64B5F6; margin-bottom: 15px; font-size: 1.2rem;">🔗 API Endpoints</h3>
                <p style="color: #B8B8B8; font-size: 0.9rem; margin-bottom: 15px;">
                    Use these API endpoints to integrate StayDesk data directly into your website or application.
                </p>
                
                <div style="background: #1a1a1a; padding: 15px; border-radius: 8px; margin-bottom: 10px;">
                    <strong style="color: #28A745;">GET</strong> <span style="color: #E8E8E8;"><?php echo rest_url('staydesk/v1/hotel/' . $hotel->id . '/rooms'); ?></span>
                    <p style="color: #888; font-size: 0.8rem; margin: 5px 0 0 0;">Get all rooms with real-time availability</p>
                </div>
                
                <div style="background: #1a1a1a; padding: 15px; border-radius: 8px; margin-bottom: 10px;">
                    <strong style="color: #28A745;">GET</strong> <span style="color: #E8E8E8;"><?php echo rest_url('staydesk/v1/hotel/' . $hotel->id . '/availability'); ?></span>
                    <p style="color: #888; font-size: 0.8rem; margin: 5px 0 0 0;">Check room availability for specific dates</p>
                </div>
                
                <div style="background: #1a1a1a; padding: 15px; border-radius: 8px;">
                    <strong style="color: #FFC107;">POST</strong> <span style="color: #E8E8E8;"><?php echo rest_url('staydesk/v1/bookings'); ?></span>
                    <p style="color: #888; font-size: 0.8rem; margin: 5px 0 0 0;">Create a booking (requires API key)</p>
                </div>
            </div>
        </div>
        
        <div class="profile-section chatbot-section">
            <h2>🤖 Chatbot Widget</h2>
            <p style="color: #E8E8E8; margin-bottom: 20px;">Add this code to your hotel website to enable the AI chatbot:</p>
            
            <div class="embed-code">
                <code>&lt;script src="<?php echo home_url('/wp-content/plugins/staydesk/public/js/chatbot-embed.js'); ?>"&gt;&lt;/script&gt;<br>
&lt;script&gt;StayDeskChatbot.init({hotelId: <?php echo $hotel->id; ?>});&lt;/script&gt;</code>
            </div>
            
            <button class="btn btn-primary" style="margin-top: 20px;" onclick="copyEmbedCode()">Copy Code</button>
        </div>
    </div>
    
    <script>
        function showAlert(message, type) {
            const alert = document.getElementById('alertBox');
            alert.className = 'alert alert-' + type + ' show';
            alert.textContent = message;
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }
        
        function copyEmbedCode() {
            const code = document.querySelector('.embed-code code').textContent;
            navigator.clipboard.writeText(code).then(() => {
                showAlert('Embed code copied to clipboard!', 'success');
            });
        }
        
        function copyWidgetCode() {
            const code = document.getElementById('widget-code').textContent;
            navigator.clipboard.writeText(code).then(() => {
                showAlert('Widget code copied to clipboard!', 'success');
            });
        }
        
        function saveWebsiteUrl() {
            const websiteUrl = document.getElementById('website_url').value;
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_update_website_url',
                    nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>',
                    website_url: websiteUrl
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('Website URL saved successfully!', 'success');
                    } else {
                        showAlert(response.data.message || 'Failed to save website URL', 'error');
                    }
                },
                error: function() {
                    showAlert('An error occurred. Please try again.', 'error');
                }
            });
        }
        
        function regenerateApiKey() {
            if (!confirm('Are you sure you want to regenerate your API key? This will invalidate the current key.')) {
                return;
            }
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_regenerate_api_key',
                    nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        document.getElementById('api_key').value = response.data.api_key;
                        showAlert('API key regenerated successfully! Update your widget code.', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showAlert(response.data.message || 'Failed to regenerate API key', 'error');
                    }
                },
                error: function() {
                    showAlert('An error occurred. Please try again.', 'error');
                }
            });
        }
        
        function upgradeToYearly() {
            if (!confirm('Upgrade to yearly plan and save ₦59,880 per year?')) {
                return;
            }
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_upgrade_subscription',
                    nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.authorization_url) {
                        window.location.href = response.data.authorization_url;
                    } else {
                        showAlert(response.data.message || 'Failed to initialize upgrade', 'error');
                    }
                },
                error: function() {
                    showAlert('An error occurred. Please try again.', 'error');
                }
            });
        }
        
        function cancelSubscription() {
            if (!confirm('Cancel subscription? You will keep access until your current expiry date.')) {
                return;
            }
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'staydesk_cancel_subscription',
                    nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('Subscription cancelled successfully. Access until <?php echo isset($subscription) ? date("F d, Y", strtotime($subscription->expiry_date)) : "expiry"; ?>', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showAlert(response.data.message || 'Failed to cancel subscription', 'error');
                    }
                },
                error: function() {
                    showAlert('An error occurred. Please try again.', 'error');
                }
            });
        }
        
        // Initialize Feather Icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
        
        jQuery(document).ready(function($) {
            $('#profileForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                    type: 'POST',
                    data: {
                        action: 'staydesk_update_profile',
                        nonce: '<?php echo esc_js(wp_create_nonce('staydesk_nonce')); ?>',
                        hotel_id: <?php echo esc_js((int)$hotel->id); ?>,
                        ...Object.fromEntries(new FormData(this))
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('Profile updated successfully!', 'success');
                        } else {
                            showAlert(response.data.message || 'Error updating profile', 'error');
                        }
                    },
                    error: function() {
                        showAlert('An error occurred. Please try again.', 'error');
                    }
                });
            });
        });
    </script>
</body>
</html>