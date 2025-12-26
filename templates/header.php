<?php
/**
 * StayDesk Header Component
 * Sleek glassmorphism header with sapphire theme
 */

// Check if user is logged in
$is_logged_in = is_user_logged_in();
$current_user = $is_logged_in ? wp_get_current_user() : null;
?>

<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/feather-icons"></script>

<style>
/* ============================================
   HEADER STYLES - Sapphire Glassmorphism
   ============================================ */
.staydesk-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 9999;
    background: rgba(15, 20, 25, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(107, 179, 255, 0.15);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
}

.staydesk-header.scrolled {
    background: rgba(15, 20, 25, 0.95);
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 60px;
}

/* Logo */
.header-logo {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.header-logo-svg {
    width: 150px;
    height: 44px;
    transition: all 0.3s ease;
}

.header-logo:hover .header-logo-svg {
    transform: scale(1.05);
    filter: drop-shadow(0 0 8px rgba(107, 179, 255, 0.4));
}

/* Navigation */
.header-nav {
    display: flex;
    align-items: center;
    gap: 6px;
}

.header-nav-link {
    padding: 6px 14px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.75rem;
    font-weight: 500;
    color: #94a3b8;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.header-nav-link svg {
    width: 14px;
    height: 14px;
    transition: all 0.3s ease;
}

.header-nav-link::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #1e3a5f, #6bb3ff);
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.header-nav-link:hover {
    color: #6bb3ff;
}

.header-nav-link:hover svg {
    filter: drop-shadow(0 0 4px #6bb3ff);
}

.header-nav-link:hover::before {
    width: 50%;
}

/* Auth Buttons */
.header-btn {
    padding: 6px 14px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    letter-spacing: 0.3px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.header-btn svg {
    width: 12px;
    height: 12px;
    transition: all 0.3s ease;
}

.header-btn-outline {
    background: rgba(30, 58, 95, 0.2);
    backdrop-filter: blur(10px);
    color: #6bb3ff;
    border: 1px solid rgba(107, 179, 255, 0.3);
}

.header-btn-outline:hover {
    background: rgba(30, 58, 95, 0.3);
    border-color: #6bb3ff;
    color: #8ec5ff;
    transform: translateY(-1px);
}

.header-btn-outline:hover svg {
    filter: drop-shadow(0 0 4px #6bb3ff);
}

.header-btn-primary {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 100%);
    color: #ffffff;
    box-shadow: 0 2px 10px rgba(30, 58, 95, 0.3);
}

.header-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(30, 58, 95, 0.4);
}

.header-btn-primary:hover svg {
    transform: translateX(2px);
}

.header-btn-logout {
    background: rgba(239, 68, 68, 0.1);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.header-btn-logout:hover {
    background: rgba(239, 68, 68, 0.15);
    border-color: #ef4444;
    transform: translateY(-1px);
}

/* User Info */
.header-user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 4px 12px;
    background: rgba(30, 58, 95, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 8px;
    border: 1px solid rgba(107, 179, 255, 0.15);
}

.header-user-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Nunito', sans-serif;
    font-size: 0.75rem;
    font-weight: 600;
    color: #ffffff;
}

.header-user-name {
    font-family: 'Nunito', sans-serif;
    font-size: 0.75rem;
    font-weight: 500;
    color: #e2e8f0;
}

/* Hamburger Menu */
.hamburger-btn {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
    width: 28px;
    height: 28px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 4px;
    z-index: 10001;
}

.hamburger-line {
    width: 100%;
    height: 2px;
    background: #6bb3ff;
    border-radius: 2px;
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    transform-origin: center;
}

.hamburger-btn.active .hamburger-line:nth-child(1) {
    transform: rotate(45deg) translate(4px, 4px);
}

.hamburger-btn.active .hamburger-line:nth-child(2) {
    opacity: 0;
    transform: scaleX(0);
}

.hamburger-btn.active .hamburger-line:nth-child(3) {
    transform: rotate(-45deg) translate(4px, -4px);
}

/* Mobile Menu */
.mobile-menu {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 20, 25, 0.98);
    backdrop-filter: blur(20px);
    z-index: 10000;
    padding: 80px 24px 30px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-y: auto;
}

.mobile-menu.active {
    opacity: 1;
    visibility: visible;
}

/* Mobile Menu Close Button */
.mobile-menu-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(30, 58, 95, 0.3);
    border: 1px solid rgba(107, 179, 255, 0.3);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 10001;
}

.mobile-menu-close svg {
    width: 24px;
    height: 24px;
    color: #6bb3ff;
    transition: all 0.3s ease;
}

.mobile-menu-close:hover {
    background: rgba(30, 58, 95, 0.5);
    border-color: #6bb3ff;
    transform: rotate(90deg);
}

.mobile-menu-close:hover svg {
    filter: drop-shadow(0 0 6px #6bb3ff);
}

.mobile-menu-nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 24px;
}

.mobile-menu-link {
    padding: 14px 16px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.9rem;
    font-weight: 500;
    color: #94a3b8;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    background: rgba(30, 58, 95, 0.1);
    border: 1px solid rgba(107, 179, 255, 0.1);
    transform: translateX(-20px);
    opacity: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.mobile-menu-link svg {
    width: 18px;
    height: 18px;
}

.mobile-menu.active .mobile-menu-link {
    transform: translateX(0);
    opacity: 1;
}

.mobile-menu.active .mobile-menu-link:nth-child(1) { transition-delay: 0.1s; }
.mobile-menu.active .mobile-menu-link:nth-child(2) { transition-delay: 0.15s; }
.mobile-menu.active .mobile-menu-link:nth-child(3) { transition-delay: 0.2s; }
.mobile-menu.active .mobile-menu-link:nth-child(4) { transition-delay: 0.25s; }

.mobile-menu-link:hover {
    color: #6bb3ff;
    background: rgba(30, 58, 95, 0.2);
    border-color: rgba(107, 179, 255, 0.2);
}

.mobile-menu-link:hover svg {
    filter: drop-shadow(0 0 4px #6bb3ff);
}

.mobile-menu-auth {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding-top: 16px;
    border-top: 1px solid rgba(107, 179, 255, 0.1);
}

.mobile-btn {
    padding: 12px 20px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
    text-align: center;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s ease;
    transform: translateY(20px);
    opacity: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.mobile-btn svg {
    width: 16px;
    height: 16px;
}

.mobile-menu.active .mobile-btn {
    transform: translateY(0);
    opacity: 1;
}

.mobile-menu.active .mobile-btn:nth-child(1) { transition-delay: 0.3s; }
.mobile-menu.active .mobile-btn:nth-child(2) { transition-delay: 0.35s; }

.mobile-btn-outline {
    background: rgba(30, 58, 95, 0.2);
    color: #6bb3ff;
    border: 1px solid rgba(107, 179, 255, 0.3);
}

.mobile-btn-primary {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 100%);
    color: #ffffff;
    border: none;
}

.mobile-btn-logout {
    background: rgba(239, 68, 68, 0.1);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .header-nav {
        display: none;
    }
    
    .hamburger-btn {
        display: flex;
    }
    
    .mobile-menu {
        display: block;
    }
    
    .header-container {
        height: 55px;
    }
    
    .header-logo-svg {
        width: 130px;
        height: 38px;
    }
}
</style>

<header class="staydesk-header" id="staydesk-header">
    <div class="header-container">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="header-logo" title="StayDesk">
            <svg class="header-logo-svg" width="150" height="44" viewBox="0 0 150 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Logo Icon - Stylized Hotel/Booking Calendar -->
                <defs>
                    <linearGradient id="headerLogoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#1e3a5f"/>
                        <stop offset="100%" stop-color="#2d5a8a"/>
                    </linearGradient>
                    <linearGradient id="headerAccentGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#6bb3ff"/>
                        <stop offset="100%" stop-color="#8ec5ff"/>
                    </linearGradient>
                </defs>
                
                <!-- Background rounded square -->
                <rect x="2" y="6" width="36" height="36" rx="8" fill="url(#headerLogoGradient)" stroke="rgba(107,179,255,0.3)" stroke-width="1"/>
                
                <!-- Building/Hotel icon -->
                <path d="M12 32V18C12 16.8954 12.8954 16 14 16H28C29.1046 16 30 16.8954 30 18V32C30 33.1046 29.1046 34 28 34H14C12.8954 34 12 33.1046 12 32Z" stroke="#6bb3ff" stroke-width="1.5" fill="none"/>
                
                <!-- Top bar (calendar header) -->
                <path d="M12 21H30" stroke="#6bb3ff" stroke-width="1.5"/>
                
                <!-- Calendar pins -->
                <path d="M17 16V12" stroke="#6bb3ff" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M25 16V12" stroke="#6bb3ff" stroke-width="1.5" stroke-linecap="round"/>
                
                <!-- Room indicators (dots) -->
                <circle cx="17" cy="26" r="2" fill="#6bb3ff"/>
                <circle cx="21" cy="26" r="2" fill="#8ec5ff"/>
                <circle cx="25" cy="26" r="2" fill="#6bb3ff"/>
                
                <!-- Text: StayDesk -->
                <text x="46" y="30" font-family="'Nunito', sans-serif" font-size="18" font-weight="700" fill="#e2e8f0">Stay<tspan fill="#6bb3ff">Desk</tspan></text>
            </svg>
        </a>
        
        <nav class="header-nav">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="header-nav-link">
                <svg data-feather="home"></svg>
                Home
            </a>
            <a href="<?php echo esc_url(home_url('/staydesk-pricing')); ?>" class="header-nav-link">
                <svg data-feather="tag"></svg>
                Pricing
            </a>
            
            <?php if ($is_logged_in): ?>
                <a href="<?php echo esc_url(home_url('/staydesk-dashboard')); ?>" class="header-nav-link">
                    <svg data-feather="grid"></svg>
                    Dashboard
                </a>
                <div class="header-user-info">
                    <div class="header-user-avatar">
                        <?php echo strtoupper(substr($current_user->display_name, 0, 1)); ?>
                    </div>
                    <span class="header-user-name"><?php echo esc_html($current_user->display_name); ?></span>
                </div>
                <button class="header-btn header-btn-logout" id="header-logout-btn">
                    <svg data-feather="log-out"></svg>
                    Logout
                </button>
            <?php else: ?>
                <a href="<?php echo esc_url(home_url('/staydesk-login')); ?>" class="header-btn header-btn-outline">
                    <svg data-feather="log-in"></svg>
                    Login
                </a>
                <a href="<?php echo esc_url(home_url('/staydesk-signup')); ?>" class="header-btn header-btn-primary">
                    Create Account
                    <svg data-feather="arrow-right"></svg>
                </a>
            <?php endif; ?>
        </nav>
        
        <button class="hamburger-btn" id="hamburger-btn" aria-label="Toggle menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobile-menu">
    <button class="mobile-menu-close" id="mobile-menu-close" aria-label="Close menu">
        <svg data-feather="x"></svg>
    </button>
    <nav class="mobile-menu-nav">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="mobile-menu-link">
            <svg data-feather="home"></svg>
            Home
        </a>
        <a href="<?php echo esc_url(home_url('/staydesk-pricing')); ?>" class="mobile-menu-link">
            <svg data-feather="tag"></svg>
            Pricing
        </a>
        <?php if ($is_logged_in): ?>
            <a href="<?php echo esc_url(home_url('/staydesk-dashboard')); ?>" class="mobile-menu-link">
                <svg data-feather="grid"></svg>
                Dashboard
            </a>
            <a href="<?php echo esc_url(home_url('/staydesk-profile')); ?>" class="mobile-menu-link">
                <svg data-feather="user"></svg>
                Profile
            </a>
        <?php endif; ?>
    </nav>
    
    <div class="mobile-menu-auth">
        <?php if ($is_logged_in): ?>
            <button class="mobile-btn mobile-btn-logout" id="mobile-logout-btn">
                <svg data-feather="log-out"></svg>
                Logout
            </button>
        <?php else: ?>
            <a href="<?php echo esc_url(home_url('/staydesk-login')); ?>" class="mobile-btn mobile-btn-outline">
                <svg data-feather="log-in"></svg>
                Login
            </a>
            <a href="<?php echo esc_url(home_url('/staydesk-signup')); ?>" class="mobile-btn mobile-btn-primary">
                <svg data-feather="user-plus"></svg>
                Create Account
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Header spacing for pages - hero section has its own spacing -->
<div class="header-spacer" style="height: 60px;"></div>

<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        const header = document.getElementById('staydesk-header');
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const headerLogoutBtn = document.getElementById('header-logout-btn');
        const mobileLogoutBtn = document.getElementById('mobile-logout-btn');
        
        // Function to close mobile menu
        function closeMobileMenu() {
            hamburgerBtn.classList.remove('active');
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Header scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Hamburger menu toggle
        if (hamburgerBtn && mobileMenu) {
            hamburgerBtn.addEventListener('click', function() {
                hamburgerBtn.classList.toggle('active');
                mobileMenu.classList.toggle('active');
                document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
                // Re-initialize icons for mobile menu
                if (typeof feather !== 'undefined') {
                    setTimeout(() => feather.replace(), 100);
                }
            });
            
            // Close button click handler
            if (mobileMenuClose) {
                mobileMenuClose.addEventListener('click', closeMobileMenu);
            }
            
            const mobileLinks = mobileMenu.querySelectorAll('a');
            mobileLinks.forEach(function(link) {
                link.addEventListener('click', closeMobileMenu);
            });
        }
        
        // Logout functionality
        function handleLogout() {
            if (typeof jQuery !== 'undefined') {
                jQuery.ajax({
                    url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                    type: 'POST',
                    data: {
                        action: 'staydesk_logout',
                        nonce: '<?php echo esc_js(wp_create_nonce('staydesk_nonce')); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.data.redirect;
                        } else {
                            window.location.href = '<?php echo esc_url(home_url('/staydesk-login')); ?>';
                        }
                    },
                    error: function() {
                        window.location.href = '<?php echo esc_url(home_url('/staydesk-login')); ?>';
                    }
                });
            } else {
                window.location.href = '<?php echo esc_url(home_url('/staydesk-login')); ?>';
            }
        }
        
        if (headerLogoutBtn) {
            headerLogoutBtn.addEventListener('click', handleLogout);
        }
        
        if (mobileLogoutBtn) {
            mobileLogoutBtn.addEventListener('click', handleLogout);
        }
    });
})();
</script>
