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
        
        .staydesk-pricing {
            font-family: var(--font-body);
            background: var(--bg-dark);
            padding: 60px 20px;
            min-height: 100vh;
        }
        
        .pricing-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .pricing-header h1 {
            font-family: var(--font-display);
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            font-weight: 600;
        }
        
        .pricing-header p {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .pricing-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 30px;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .pricing-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            padding: 32px 28px;
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .pricing-card::before {
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
        
        .pricing-card:hover::before {
            transform: scaleX(1);
        }
        
        .pricing-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 50px rgba(30, 58, 95, 0.4);
            border-color: rgba(107, 179, 255, 0.3);
        }
        
        .pricing-card.popular {
            border: 2px solid var(--accent);
            box-shadow: 0 8px 40px rgba(107, 179, 255, 0.2);
        }
        
        .popular-badge {
            position: absolute;
            top: 20px;
            right: -35px;
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            color: #ffffff;
            padding: 6px 45px;
            transform: rotate(45deg);
            font-weight: 700;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 10px var(--sapphire-glow);
        }
        
        .plan-name {
            font-family: var(--font-display);
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--text-primary);
        }
        
        .plan-price {
            font-family: var(--font-display);
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
        }
        
        .plan-price small {
            font-size: 0.9rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .discount-info {
            background: rgba(30, 58, 95, 0.3);
            color: var(--accent);
            padding: 12px 16px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid var(--glass-border);
            text-align: center;
        }
        
        .plan-features {
            list-style: none;
            padding: 0;
            margin: 28px 0;
        }
        
        .plan-features li {
            padding: 10px 0;
            border-bottom: 1px solid rgba(107, 179, 255, 0.1);
            color: var(--text-primary);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .plan-features li svg {
            width: 16px;
            height: 16px;
            color: var(--accent);
        }
        
        .btn-subscribe {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--sapphire) 0%, var(--sapphire-light) 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
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
        
        .btn-subscribe svg {
            width: 16px;
            height: 16px;
        }
        
        .btn-subscribe:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px var(--sapphire-glow);
        }
        
        .btn-subscribe:hover svg {
            transform: translateX(2px);
        }
        
        @media (max-width: 768px) {
            .pricing-header h1 {
                font-size: 1.5rem;
            }
            
            .pricing-cards {
                grid-template-columns: 1fr;
            }
            
            .pricing-card {
                padding: 28px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="staydesk-pricing">
        <div class="pricing-header">
            <h1>Choose Your Plan</h1>
            <p>Affordable pricing for hotels of all sizes</p>
        </div>
        
        <div class="pricing-cards">
            <div class="pricing-card">
                <div class="plan-name">Monthly Plan</div>
                <div class="plan-price">
                    ₦49,900
                    <small>/month</small>
                </div>
                
                <ul class="plan-features">
                    <li><svg data-feather="check"></svg> Unlimited bookings</li>
                    <li><svg data-feather="check"></svg> Room management</li>
                    <li><svg data-feather="check"></svg> Payment integration</li>
                    <li><svg data-feather="check"></svg> Customised Chatbot (bilingual)</li>
                    <li><svg data-feather="check"></svg> Email notifications</li>
                    <li><svg data-feather="check"></svg> WhatsApp integration</li>
                    <li><svg data-feather="check"></svg> Analytics dashboard</li>
                    <li><svg data-feather="check"></svg> 24/7 support</li>
                </ul>
                
                <button class="btn-subscribe" data-plan="monthly">
                    Subscribe Now
                    <svg data-feather="arrow-right"></svg>
                </button>
            </div>
            
            <div class="pricing-card popular">
                <div class="popular-badge">BEST VALUE</div>
                <div class="plan-name">Yearly Plan</div>
                <div class="plan-price">
                    ₦598,800
                    <small>/year</small>
                </div>
                
                <div class="discount-info">
                    First 10 hotels get 10% OFF!<br>
                    Save ₦59,880 annually
                </div>
                
                <ul class="plan-features">
                    <li><svg data-feather="check"></svg> Everything in Monthly Plan</li>
                    <li><svg data-feather="check"></svg> Priority support</li>
                    <li><svg data-feather="check"></svg> Custom branding</li>
                    <li><svg data-feather="check"></svg> Advanced analytics</li>
                    <li><svg data-feather="check"></svg> API access</li>
                    <li><svg data-feather="check"></svg> Dedicated account manager</li>
                    <li><svg data-feather="check"></svg> Free updates</li>
                    <li><svg data-feather="check"></svg> Training sessions</li>
                </ul>
                
                <button class="btn-subscribe popular" data-plan="yearly">
                    Subscribe Now
                    <svg data-feather="arrow-right"></svg>
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Initialize Feather Icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
        
        <?php wp_enqueue_script('jquery'); ?>
        jQuery(document).ready(function($) {
            $('.btn-subscribe').on('click', function() {
                var plan = $(this).data('plan');
                var $btn = $(this);
                
                // Check if user is logged in - redirect to signup with plan info
                <?php if (!is_user_logged_in()): ?>
                    // Store the selected plan in localStorage for use after signup
                    localStorage.setItem('staydesk_selected_plan', plan);
                    // Redirect to signup page
                    window.location.href = '<?php echo home_url('/staydesk-signup'); ?>?redirect=subscription&plan=' + plan;
                    return;
                <?php endif; ?>
                
                $btn.prop('disabled', true).text('Processing...');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'staydesk_subscribe',
                        nonce: '<?php echo wp_create_nonce('staydesk_nonce'); ?>',
                        plan_type: plan
                    },
                    success: function(response) {
                        console.log('Subscribe response:', response);
                        if (response.success) {
                            // Redirect to Paystack payment page
                            if (response.data && response.data.authorization_url) {
                                window.location.href = response.data.authorization_url;
                            } else {
                                alert('Payment initialization failed.');
                                $btn.prop('disabled', false).text('Subscribe Now');
                            }
                        } else {
                            alert(response.data.message || 'Failed to initialize payment.');
                            $btn.prop('disabled', false).text('Subscribe Now');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Subscribe error:', error);
                        alert('Failed to initialize payment. Please try again.');
                        $btn.prop('disabled', false).text('Subscribe Now');
                    }
                });
            });
        });
    </script>
</body>
</html>