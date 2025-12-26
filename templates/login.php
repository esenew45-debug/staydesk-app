<?php
// Prevent caching to ensure fresh nonces
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
<style>
        :root {
            --sapphire: #1e3a5f;
            --sapphire-light: #2d5a8a;
            --sapphire-glow: rgba(30, 58, 95, 0.4);
            --accent: #6bb3ff;
            --accent-light: #8ec5ff;
            --bg-dark: #0f1419;
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
            background: #080b0e;
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--sapphire), var(--sapphire-light));
            border-radius: 3px;
        }

        /* Scroll Progress Bar */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--sapphire), var(--accent), var(--sapphire-light));
            background-size: 200% 100%;
            z-index: 99999;
            transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            animation: gradient-slide 3s linear infinite;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: var(--bg-dark);
            font-family: var(--font-body);
        }
        
        /* Remove underlines from all links */
        a {
            text-decoration: none !important;
        }
        
        .staydesk-login {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--bg-dark) 0%, #0d1520 50%, var(--bg-dark) 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .staydesk-login::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(30, 58, 95, 0.08) 0%, transparent 70%);
            animation: pulse 8s ease-in-out infinite;
        }
        
        /* Glassmorphism Login Container */
        .login-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            max-width: 380px;
            width: 100%;
            animation: slideIn 0.5s ease-out;
            border: 1px solid var(--glass-border);
            position: relative;
            z-index: 1;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 24px;
        }
        
        .login-header h1 {
            font-family: var(--font-display);
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 50%, var(--accent-light) 100%);
            background-size: 200% 100%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient-shift 4s ease-in-out infinite;
            font-size: 1.4rem;
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: -0.3px;
        }
        
        .login-header p {
            color: var(--text-secondary);
            font-size: 0.8rem;
            line-height: 1.5;
        }
        
        /* Role Selection Tabs */
        .role-tabs {
            display: flex;
            gap: 6px;
            margin-bottom: 20px;
            background: rgba(0, 0, 0, 0.2);
            padding: 4px;
            border-radius: 10px;
        }
        
        .role-tab {
            flex: 1;
            padding: 8px 12px;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-family: var(--font-body);
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .role-tab svg {
            width: 14px;
            height: 14px;
            transition: all 0.3s ease;
        }
        
        .role-tab.active {
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            color: #ffffff;
            box-shadow: 0 2px 8px var(--sapphire-glow);
        }
        
        .role-tab.active svg {
            filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.5));
        }
        
        .role-tab:hover:not(.active) {
            background: rgba(30, 58, 95, 0.2);
            color: var(--accent);
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
            color: var(--text-primary);
            font-weight: 500;
            letter-spacing: 0.2px;
            font-size: 0.75rem;
        }
        
        .form-group label svg {
            width: 12px;
            height: 12px;
            color: var(--accent);
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--accent);
            transition: all 0.3s ease;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle svg {
            width: 16px;
            height: 16px;
        }
        
        .password-toggle:hover {
            color: var(--accent-light);
            filter: drop-shadow(0 0 4px var(--accent));
        }
        
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            font-family: var(--font-body);
            font-size: 0.8rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            color: var(--text-primary);
        }
        
        .form-group input::placeholder {
            color: #64748b;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(107, 179, 255, 0.15);
            background: rgba(30, 58, 95, 0.15);
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .remember-me input {
            width: auto;
            margin-right: 8px;
            accent-color: var(--accent);
        }
        
        .remember-me label {
            color: var(--text-secondary);
            margin-bottom: 0;
            font-size: 0.75rem;
        }
        
        .forgot-password {
            text-align: right;
            margin-top: 6px;
            margin-bottom: 16px;
        }
        
        .forgot-password a {
            color: var(--accent);
            text-decoration: none !important;
            font-size: 0.75rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .forgot-password a svg {
            width: 12px;
            height: 12px;
        }
        
        .forgot-password a:hover {
            color: var(--accent-light);
        }
        
        .btn-login {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-family: var(--font-body);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 0.4px;
            box-shadow: 0 3px 12px var(--sapphire-glow);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-login svg {
            width: 14px;
            height: 14px;
            transition: transform 0.3s ease;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px var(--sapphire-glow);
        }
        
        .btn-login:hover svg {
            transform: translateX(2px);
        }
        
        .btn-login:disabled {
            background: linear-gradient(135deg, #334155 0%, #475569 100%);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: var(--text-secondary);
            font-size: 0.8rem;
        }
        
        .signup-link a {
            color: var(--accent);
            text-decoration: none !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .signup-link a:hover {
            color: var(--accent-light);
        }
        
        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 16px;
            display: none;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .alert svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        /* ============================================
           ANIMATIONS
           ============================================ */
        
        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        @keyframes gradient-slide {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.5; }
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
        
        @media (max-width: 768px) {
            .login-container {
                padding: 20px;
            }
        }
    </style>
    
    <!-- Scroll Progress Bar -->
    <div class="scroll-progress"></div>
    
    <script>
        // Scroll Progress Bar
        window.addEventListener('scroll', function() {
            var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            var scrolled = (winScroll / height) * 100;
            document.querySelector('.scroll-progress').style.width = scrolled + '%';
        });
    </script>
    
<div class="staydesk-login">
        <div class="login-container">
            <div class="login-header">
                <h1>Hotel Login</h1>
                <p>Access your hotel management dashboard</p>
            </div>
            
            <?php if (isset($_GET['verified']) && $_GET['verified'] == '1'): ?>
                <div class="alert alert-success" style="display: flex;">
                    <svg data-feather="check-circle"></svg>
                    Email verified successfully! You can now login.
                </div>
            <?php endif; ?>
            
            <div id="login-alert" class="alert" style="display: none;"></div>
            
            <!-- Role Selection Tabs -->
            <div class="role-tabs">
                <button type="button" class="role-tab active" data-role="admin">
                    <svg data-feather="briefcase"></svg>
                    Hotel Manager
                </button>
                <button type="button" class="role-tab" data-role="staff">
                    <svg data-feather="user"></svg>
                    Hotel Staff
                </button>
            </div>
            
            <form id="staydesk-login-form">
                <input type="hidden" id="staydesk_nonce_field" value="<?php echo esc_attr(wp_create_nonce('staydesk_nonce')); ?>">
                <input type="hidden" id="user_role" name="user_role" value="admin">
                <div class="form-group">
                    <label for="email">
                        <svg data-feather="mail"></svg>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <svg data-feather="lock"></svg>
                        Password
                    </label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <span class="password-toggle" id="password-toggle">
                            <svg data-feather="eye"></svg>
                        </span>
                    </div>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <div class="forgot-password">
                    <a href="<?php echo home_url('/staydesk-forgot-password'); ?>">
                        <svg data-feather="help-circle"></svg>
                        Forgot Password?
                    </a>
                </div>
                
                <button type="submit" class="btn-login" id="login-btn">
                    <span>Login</span>
                    <svg data-feather="arrow-right"></svg>
                </button>
                
                <div class="signup-link">
                    Don't have an account? <a href="<?php echo home_url('/staydesk-signup'); ?>">Sign up here</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Initialize Feather icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
        
        // Password toggle
        document.getElementById('password-toggle').addEventListener('click', function() {
            var field = document.getElementById('password');
            var icon = this.querySelector('svg');
            if (field.type === 'password') {
                field.type = 'text';
                // Use textContent for the icon indicator (safe, no HTML injection)
                this.setAttribute('data-visible', 'true');
            } else {
                field.type = 'password';
                this.setAttribute('data-visible', 'false');
            }
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
        
        (function() {
            // Wait for jQuery to be available
            var checkJQuery = setInterval(function() {
                if (typeof jQuery !== 'undefined') {
                    clearInterval(checkJQuery);
                    initLoginForm();
                }
            }, 100);
            
            function initLoginForm() {
                jQuery(document).ready(function($) {
                    console.log('StayDesk Login Form Initialized');
                    console.log('AJAX URL:', '<?php echo admin_url('admin-ajax.php'); ?>');
                    
                    // Handle role tab switching
                    $('.role-tab').on('click', function() {
                        $('.role-tab').removeClass('active');
                        $(this).addClass('active');
                        $('#user_role').val($(this).data('role'));
                    });
                    
                    $('#staydesk-login-form').on('submit', function(e) {
                        e.preventDefault();
                        console.log('Login form submitted');
                        
                        var $btn = $('#login-btn');
                        var $alert = $('#login-alert');
                        
                        $btn.prop('disabled', true).html('<span>Logging in...</span><svg data-feather="loader" class="spin"></svg>');
                        feather.replace();
                        $alert.hide();
                        
                        var nonceValue = $('#staydesk_nonce_field').val();
                        var ajaxData = {
                            action: 'staydesk_login',
                            nonce: nonceValue,
                            nonce_field: nonceValue,
                            email: $('#email').val(),
                            password: $('#password').val(),
                            remember: $('#remember').is(':checked'),
                            user_role: $('#user_role').val()
                        };
                        
                        console.log('Sending login AJAX request');
                        
                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'POST',
                            data: ajaxData,
                            success: function(response) {
                                console.log('Login AJAX Success:', response);
                                if (response.success) {
                                    $alert.removeClass('alert-error').addClass('alert-success')
                                          .html('<svg data-feather="check-circle"></svg>' + response.data.message).show();
                                    feather.replace();
                                    
                                    setTimeout(function() {
                                        window.location.href = response.data.redirect;
                                    }, 600);
                                } else {
                                    $alert.removeClass('alert-success').addClass('alert-error')
                                          .html('<svg data-feather="alert-circle"></svg>' + (response.data.message || 'An error occurred. Please try again.')).show();
                                    feather.replace();
                                    $btn.prop('disabled', false).html('<span>Login</span><svg data-feather="arrow-right"></svg>');
                                    feather.replace();
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Login AJAX Error:', {
                                    status: status,
                                    error: error,
                                    responseText: xhr.responseText,
                                    response: xhr.responseJSON
                                });
                                
                                var errorMessage = 'An error occurred. Please try again.';
                                
                                try {
                                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                        errorMessage = xhr.responseJSON.data.message;
                                    } else if (xhr.responseText) {
                                        errorMessage = 'Server error. Please check console for details.';
                                    }
                                } catch (e) {
                                    console.error('Error parsing response:', e);
                                }
                                
                                $alert.removeClass('alert-success').addClass('alert-error')
                                      .html('<svg data-feather="alert-circle"></svg>' + errorMessage).show();
                                feather.replace();
                                $btn.prop('disabled', false).html('<span>Login</span><svg data-feather="arrow-right"></svg>');
                                feather.replace();
                            }
                        });
                    });
                });
            }
        })();
    </script>
    
    <style>
        .spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</body>
</html>