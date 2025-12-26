<?php
/**
 * Settings Page Template
 */

if (!is_user_logged_in() || !Staydesk_Roles::can('manage_settings')) {
    echo '<p>Please <a href="' . esc_url(home_url('/staydesk-login')) . '">login</a> with admin privileges to access this page.</p>';
    return;
}

// Get current settings
global $wpdb;
$settings_table = $wpdb->prefix . 'staydesk_settings';

$test_mode = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $settings_table WHERE setting_key = %s", 'paystack_test_mode')) ?: 'yes';
$test_public_key = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $settings_table WHERE setting_key = %s", 'paystack_test_public_key')) ?: '';
$test_secret_key = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $settings_table WHERE setting_key = %s", 'paystack_test_secret_key')) ?: '';
$live_public_key = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $settings_table WHERE setting_key = %s", 'paystack_live_public_key')) ?: '';
$live_secret_key = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $settings_table WHERE setting_key = %s", 'paystack_live_secret_key')) ?: '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    check_admin_referer('staydesk_settings_nonce');
    
    $settings_to_save = array(
        'paystack_test_mode' => sanitize_text_field($_POST['test_mode']),
        'paystack_test_public_key' => sanitize_text_field($_POST['test_public_key']),
        'paystack_test_secret_key' => sanitize_text_field($_POST['test_secret_key']),
        'paystack_live_public_key' => sanitize_text_field($_POST['live_public_key']),
        'paystack_live_secret_key' => sanitize_text_field($_POST['live_secret_key'])
    );
    
    foreach ($settings_to_save as $key => $value) {
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $settings_table WHERE setting_key = %s", $key));
        
        if ($existing) {
            $wpdb->update($settings_table, array('setting_value' => $value), array('setting_key' => $key));
        } else {
            $wpdb->insert($settings_table, array('setting_key' => $key, 'setting_value' => $value));
        }
    }
    
    echo '<div class="success-message">Settings saved successfully!</div>';
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
        
        body {
            font-family: var(--font-body);
            background: var(--bg-dark);
            color: var(--text-primary);
            font-size: 14px;
            line-height: 1.6;
        }
        
        .settings-container {
            max-width: 700px;
            margin: 30px auto;
            padding: 24px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 14px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }
        
        h1 {
            font-family: var(--font-display);
            font-size: 1.4rem;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 24px;
        }
        
        h2 {
            font-family: var(--font-display);
            font-size: 1.1rem;
            color: var(--accent);
            margin: 24px 0 16px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            background: rgba(30, 58, 95, 0.3);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.85rem;
            font-family: var(--font-body);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(107, 179, 255, 0.2);
        }
        
        .mode-toggle {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .mode-option {
            flex: 1;
            padding: 12px;
            background: rgba(30, 58, 95, 0.2);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .mode-option.active {
            background: rgba(30, 58, 95, 0.4);
            border-color: var(--accent);
            color: var(--text-primary);
        }
        
        .mode-option input[type="radio"] {
            margin-right: 6px;
        }
        
        .btn-primary {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 3px 12px var(--sapphire-glow);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary svg {
            width: 16px;
            height: 16px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 18px var(--sapphire-glow);
        }
        
        .btn-secondary {
            padding: 10px 20px;
            background: rgba(30, 58, 95, 0.3);
            color: var(--accent);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-secondary svg {
            width: 16px;
            height: 16px;
        }
        
        .btn-secondary:hover {
            background: rgba(30, 58, 95, 0.5);
        }
        
        .success-message {
            padding: 12px 16px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 8px;
            color: #34d399;
            margin-bottom: 16px;
            font-size: 0.85rem;
        }
        
        .info-box {
            padding: 14px;
            background: rgba(30, 58, 95, 0.2);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.8rem;
            color: var(--text-secondary);
            line-height: 1.7;
        }
        
        .info-box strong {
            color: var(--accent);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 16px;
            color: var(--accent);
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .back-link svg {
            width: 14px;
            height: 14px;
        }
        
        .back-link:hover {
            color: var(--accent-light);
        }
    </style>
</head>
<body>

<div class="settings-container">
    <a href="<?php echo esc_url(home_url('/staydesk-admin-dashboard')); ?>" class="back-link">
        <svg data-feather="arrow-left"></svg>
        Back to Dashboard
    </a>
    
    <h1>StayDesk Settings</h1>
    
    <form method="POST" action="">
        <?php wp_nonce_field('staydesk_settings_nonce'); ?>
        
        <h2>Paystack Configuration</h2>
        
        <div class="info-box">
            <strong>Test Mode:</strong> Use test keys for development and testing<br>
            <strong>Live Mode:</strong> Use live keys for production payments<br>
            <small>Get your API keys from your Paystack Dashboard at https://dashboard.paystack.com</small>
        </div>
        
        <div class="mode-toggle">
            <label class="mode-option <?php echo $test_mode === 'yes' ? 'active' : ''; ?>">
                <input type="radio" name="test_mode" value="yes" <?php checked($test_mode, 'yes'); ?>>
                Test Mode
            </label>
            <label class="mode-option <?php echo $test_mode === 'no' ? 'active' : ''; ?>">
                <input type="radio" name="test_mode" value="no" <?php checked($test_mode, 'no'); ?>>
                Live Mode
            </label>
        </div>
        
        <h2>Test Keys (for development)</h2>
        
        <div class="form-group">
            <label for="test_public_key">Test Public Key</label>
            <input type="text" id="test_public_key" name="test_public_key" value="<?php echo esc_attr($test_public_key); ?>" placeholder="pk_test_...">
        </div>
        
        <div class="form-group">
            <label for="test_secret_key">Test Secret Key</label>
            <input type="password" id="test_secret_key" name="test_secret_key" value="<?php echo esc_attr($test_secret_key); ?>" placeholder="sk_test_...">
        </div>
        
        <h2>Live Keys (for production)</h2>
        
        <div class="form-group">
            <label for="live_public_key">Live Public Key</label>
            <input type="text" id="live_public_key" name="live_public_key" value="<?php echo esc_attr($live_public_key); ?>" placeholder="pk_live_...">
        </div>
        
        <div class="form-group">
            <label for="live_secret_key">Live Secret Key</label>
            <input type="password" id="live_secret_key" name="live_secret_key" value="<?php echo esc_attr($live_secret_key); ?>" placeholder="sk_live_...">
        </div>
        
        <div style="margin-top: 24px;">
            <button type="submit" name="save_settings" class="btn-primary">
                <svg data-feather="save"></svg>
                Save Settings
            </button>
            <button type="button" class="btn-secondary" onclick="testPaystackConnection()">
                <svg data-feather="zap"></svg>
                Test Connection
            </button>
        </div>
    </form>
</div>

<script>
// Initialize Feather Icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

function testPaystackConnection() {
    alert('Testing Paystack connection... (Feature coming soon)');
}

// Toggle mode styling
document.querySelectorAll('.mode-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.mode-option').forEach(opt => opt.classList.remove('active'));
        this.closest('.mode-option').classList.add('active');
    });
});
</script>