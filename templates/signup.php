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
            --font-display: 'Nunito', -apple-system, sans-serif;
            --font-body: 'Nunito', -apple-system, sans-serif;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: var(--bg-dark);
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
        
        .staydesk-signup {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-dark);
            font-family: var(--font-body);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .staydesk-signup::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(30, 58, 95, 0.15) 0%, transparent 70%);
            animation: pulse 8s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.5; }
        }
        
        .signup-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            max-width: 480px;
            width: 100%;
            animation: slideIn 0.6s ease-out;
            border: 1px solid var(--glass-border);
            position: relative;
            z-index: 1;
        }
        
        .signup-header {
            text-align: center;
            margin-bottom: 28px;
        }
        
        .signup-header h1 {
            font-family: var(--font-display);
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.5rem;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .signup-header p {
            color: var(--text-secondary);
            font-size: 0.85rem;
            line-height: 1.5;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 14px;
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
            width: 18px;
            height: 18px;
        }
        
        .password-toggle:hover {
            color: var(--accent-light);
            transform: translateY(-50%) scale(1.1);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
            background: rgba(30, 58, 95, 0.3);
            color: var(--text-primary);
            font-family: var(--font-body);
        }
        
        .form-group input::placeholder {
            color: var(--text-secondary);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(107, 179, 255, 0.2);
        }
        
        .btn-signup {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px var(--sapphire-glow);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-signup svg {
            width: 16px;
            height: 16px;
            transition: transform 0.3s ease;
        }
        
        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--sapphire-glow);
        }
        
        .btn-signup:hover svg {
            transform: translateX(2px);
        }
        
        .btn-signup:disabled {
            background: rgba(30, 58, 95, 0.3);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .login-link {
            text-align: center;
            margin-top: 24px;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        
        .login-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .login-link a:hover {
            color: var(--accent-light);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            font-size: 0.8rem;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
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
            .signup-container {
                padding: 24px;
            }
        }
    </style>
    
<div class="staydesk-signup">
        <div class="signup-container">
            <div class="signup-header">
                <h1>Hotel Registration</h1>
                <p>Create your hotel account to get started</p>
            </div>
            
            <div id="signup-alert" class="alert"></div>
            
            <form id="staydesk-signup-form">
                <div class="form-group">
                    <label for="hotel_name">Hotel Name</label>
                    <input type="text" id="hotel_name" name="hotel_name" required placeholder="Enter your hotel name">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="08012345678">
                </div>
                
                <div class="form-group">
                    <label for="website_url">Hotel Website URL (Optional)</label>
                    <input type="url" id="website_url" name="website_url" placeholder="https://www.yourhotel.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required minlength="8" placeholder="Enter password">
                        <span class="password-toggle" onclick="togglePassword('password', this)">
                            <svg data-feather="eye" class="eye-icon"></svg>
                            <svg data-feather="eye-off" class="eye-off-icon" style="display:none"></svg>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="Confirm password">
                        <span class="password-toggle" onclick="togglePassword('confirm_password', this)">
                            <svg data-feather="eye" class="eye-icon"></svg>
                            <svg data-feather="eye-off" class="eye-off-icon" style="display:none"></svg>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn-signup" id="signup-btn">
                    Create Account
                    <svg data-feather="arrow-right"></svg>
                </button>
                
                <div class="login-link">
                    Already have an account? <a href="<?php echo esc_url(home_url('/staydesk-login')); ?>">Login here</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Initialize Feather Icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
        
        function togglePassword(fieldId, toggleSpan) {
            var field = document.getElementById(fieldId);
            var eyeIcon = toggleSpan.querySelector('.eye-icon');
            var eyeOffIcon = toggleSpan.querySelector('.eye-off-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                if (eyeIcon) eyeIcon.style.display = 'none';
                if (eyeOffIcon) eyeOffIcon.style.display = 'inline';
            } else {
                field.type = 'password';
                if (eyeIcon) eyeIcon.style.display = 'inline';
                if (eyeOffIcon) eyeOffIcon.style.display = 'none';
            }
        }
        
        (function() {
            // Wait for jQuery to be available
            var checkJQuery = setInterval(function() {
                if (typeof jQuery !== 'undefined') {
                    clearInterval(checkJQuery);
                    initSignupForm();
                }
            }, 100);
            
            function initSignupForm() {
                jQuery(document).ready(function($) {
                    console.log('StayDesk Signup Form Initialized');
                    console.log('AJAX URL:', '<?php echo admin_url('admin-ajax.php'); ?>');
                    
                    // Test AJAX connection first
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'staydesk_test'
                        },
                        success: function(response) {
                            console.log('AJAX Test Success:', response);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Test Failed:', {
                                status: status,
                                error: error,
                                statusCode: xhr.status,
                                responseText: xhr.responseText
                            });
                        }
                    });
                    
                    $('#staydesk-signup-form').on('submit', function(e) {
                        e.preventDefault();
                        console.log('Form submitted');
                        
                        var $btn = $('#signup-btn');
                        var $alert = $('#signup-alert');
                        var password = $('#password').val();
                        var confirmPassword = $('#confirm_password').val();
                        
                        // Validate passwords match
                        if (password !== confirmPassword) {
                            $alert.removeClass('alert-success').addClass('alert-error')
                                  .text('Passwords do not match.').show();
                            return;
                        }
                        
                        $btn.prop('disabled', true).text('Creating account...');
                        $alert.hide();
                        
                        var ajaxData = {
                            action: 'staydesk_signup',
                            nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>',
                            hotel_name: $('#hotel_name').val(),
                            email: $('#email').val(),
                            phone: $('#phone').val(),
                            website_url: $('#website_url').val(),
                            password: password
                        };
                        
                        console.log('Sending AJAX request with data:', ajaxData);
                        
                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'POST',
                            data: ajaxData,
                            success: function(response) {
                                console.log('AJAX Success:', response);
                                if (response.success) {
                                    $alert.removeClass('alert-error').addClass('alert-success')
                                          .text(response.data.message).show();
                                    
                                    setTimeout(function() {
                                        window.location.href = response.data.redirect;
                                    }, 800);
                                } else {
                                    $alert.removeClass('alert-success').addClass('alert-error')
                                          .text(response.data.message || 'An error occurred. Please try again.').show();
                                    $btn.prop('disabled', false).text('Create Account');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', {
                                    status: status,
                                    error: error,
                                    responseText: xhr.responseText,
                                    response: xhr.responseJSON
                                });
                                
                                var errorMessage = 'An error occurred. Please try again.';
                                
                                // Try to parse error response
                                try {
                                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                                        errorMessage = xhr.responseJSON.data.message;
                                    } else if (xhr.responseText) {
                                        // Try to extract error from HTML response
                                        var match = xhr.responseText.match(/<body[^>]*>(.*?)<\/body>/is);
                                        if (match) {
                                            errorMessage = 'Server error. Please check console for details.';
                                        }
                                    }
                                } catch (e) {
                                    console.error('Error parsing response:', e);
                                }
                                
                                $alert.removeClass('alert-success').addClass('alert-error')
                                      .text(errorMessage).show();
                                $btn.prop('disabled', false).text('Create Account');
                            }
                        });
                    });
                });
            }
        })();
    </script>