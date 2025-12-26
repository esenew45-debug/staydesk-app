<?php
/**
 * StayDesk Footer Component
 * Sleek glassmorphism footer with sapphire theme
 */
?>

<style>
/* ============================================
   FOOTER STYLES - Sapphire Glassmorphism
   ============================================ */
.staydesk-footer {
    background: linear-gradient(180deg, #0f1419 0%, #080b0e 100%);
    border-top: 1px solid rgba(107, 179, 255, 0.1);
    padding: 50px 0 24px;
    font-family: 'Nunito', -apple-system, sans-serif;
}

.footer-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 20px;
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 40px;
    margin-bottom: 40px;
}

/* Footer Brand */
.footer-brand {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.footer-logo-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.footer-logo-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(30, 58, 95, 0.4);
}

.footer-logo-icon svg {
    width: 20px;
    height: 20px;
    color: #6bb3ff;
}

.footer-logo {
    font-family: 'Nunito', -apple-system, sans-serif;
    font-size: 1.4rem;
    font-weight: 600;
    background: linear-gradient(135deg, #e2e8f0 0%, #6bb3ff 50%, #8ec5ff 100%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: footerShimmer 3s ease-in-out infinite;
}

@keyframes footerShimmer {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.footer-description {
    font-size: 0.8rem;
    color: #94a3b8;
    line-height: 1.7;
    max-width: 280px;
}

.footer-social {
    display: flex;
    gap: 10px;
    margin-top: 6px;
}

.social-link {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(30, 58, 95, 0.2);
    border: 1px solid rgba(107, 179, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link svg {
    width: 14px;
    height: 14px;
    color: #6bb3ff;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: rgba(30, 58, 95, 0.3);
    border-color: #6bb3ff;
    transform: translateY(-2px);
}

.social-link:hover svg {
    filter: drop-shadow(0 0 4px #6bb3ff);
}

/* Footer Links */
.footer-column h4 {
    font-family: 'Inter', sans-serif;
    font-size: 0.8rem;
    font-weight: 600;
    color: #e2e8f0;
    margin-bottom: 16px;
    letter-spacing: 0.3px;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.footer-links a {
    color: #94a3b8;
    text-decoration: none;
    font-size: 0.75rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.footer-links a svg {
    width: 12px;
    height: 12px;
    opacity: 0;
    transition: all 0.3s ease;
}

.footer-links a:hover {
    color: #6bb3ff;
    transform: translateX(4px);
}

.footer-links a:hover svg {
    opacity: 1;
    filter: drop-shadow(0 0 2px #6bb3ff);
}

/* Footer Contact */
.footer-contact-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 12px;
    color: #94a3b8;
    font-size: 0.75rem;
}

.footer-contact-icon svg {
    width: 14px;
    height: 14px;
    color: #6bb3ff;
    margin-top: 2px;
}

.footer-contact-text {
    line-height: 1.5;
}

.footer-contact-text a {
    color: #94a3b8;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-contact-text a:hover {
    color: #6bb3ff;
}

/* Footer Bottom */
.footer-bottom {
    padding-top: 24px;
    border-top: 1px solid rgba(107, 179, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.footer-copyright {
    font-size: 0.7rem;
    color: #64748b;
}

.footer-copyright a {
    color: #6bb3ff;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.footer-copyright a:hover {
    color: #8ec5ff;
}

.footer-bottom-links {
    display: flex;
    gap: 20px;
}

.footer-bottom-links a {
    font-size: 0.7rem;
    color: #64748b;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: #6bb3ff;
}

/* Made with love */
.footer-made-with {
    font-size: 0.65rem;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 4px;
}

.footer-made-with .heart {
    color: #ef4444;
    animation: heartbeat 1.5s ease-in-out infinite;
}

@keyframes heartbeat {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Responsive */
@media (max-width: 900px) {
    .footer-grid {
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }
}

@media (max-width: 600px) {
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 28px;
    }
    
    .footer-bottom {
        flex-direction: column;
        text-align: center;
    }
    
    .footer-bottom-links {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>

<footer class="staydesk-footer">
    <div class="footer-container">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-brand">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo-wrapper" style="text-decoration: none;">
                    <svg class="footer-logo-svg" width="160" height="44" viewBox="0 0 160 44" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Logo Icon -->
                        <defs>
                            <linearGradient id="footerLogoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#1e3a5f"/>
                                <stop offset="100%" stop-color="#2d5a8a"/>
                            </linearGradient>
                        </defs>
                        
                        <!-- Background rounded square -->
                        <rect x="2" y="4" width="38" height="38" rx="8" fill="url(#footerLogoGradient)" stroke="rgba(107,179,255,0.3)" stroke-width="1"/>
                        
                        <!-- Building/Hotel icon -->
                        <path d="M12 32V18C12 16.8954 12.8954 16 14 16H28C29.1046 16 30 16.8954 30 18V32C30 33.1046 29.1046 34 28 34H14C12.8954 34 12 33.1046 12 32Z" stroke="#6bb3ff" stroke-width="1.5" fill="none"/>
                        
                        <!-- Top bar -->
                        <path d="M12 21H30" stroke="#6bb3ff" stroke-width="1.5"/>
                        
                        <!-- Calendar pins -->
                        <path d="M17 16V11" stroke="#6bb3ff" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M25 16V11" stroke="#6bb3ff" stroke-width="1.5" stroke-linecap="round"/>
                        
                        <!-- Room indicators -->
                        <circle cx="17" cy="26" r="2" fill="#6bb3ff"/>
                        <circle cx="21" cy="26" r="2" fill="#8ec5ff"/>
                        <circle cx="25" cy="26" r="2" fill="#6bb3ff"/>
                        
                        <!-- Text -->
                        <text x="48" y="30" font-family="'Nunito', sans-serif" font-size="18" font-weight="700" fill="#e2e8f0">Stay<tspan fill="#6bb3ff">Desk</tspan></text>
                    </svg>
                </a>
                <p class="footer-description">
                    The ultimate hotel management platform by BendlessTech. Empowering hotels across Nigeria with smart booking solutions.
                </p>
                <div class="footer-social">
                    <a href="https://twitter.com/bendlesstech" class="social-link" target="_blank" rel="noopener" aria-label="Twitter">
                        <svg data-feather="twitter"></svg>
                    </a>
                    <a href="https://www.linkedin.com/company/bendlesstech" class="social-link" target="_blank" rel="noopener" aria-label="LinkedIn">
                        <svg data-feather="linkedin"></svg>
                    </a>
                    <a href="https://wa.me/2347120018023" class="social-link" target="_blank" rel="noopener" aria-label="WhatsApp">
                        <svg data-feather="message-circle"></svg>
                    </a>
                    <a href="mailto:reach@bendlesstech.com" class="social-link" target="_blank" rel="noopener" aria-label="Email">
                        <svg data-feather="mail"></svg>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>"><svg data-feather="chevron-right"></svg>Home</a></li>
                    <li><a href="<?php echo esc_url(home_url('/staydesk-pricing')); ?>"><svg data-feather="chevron-right"></svg>Pricing</a></li>
                    <li><a href="<?php echo esc_url(home_url('/staydesk-login')); ?>"><svg data-feather="chevron-right"></svg>Login</a></li>
                    <li><a href="<?php echo esc_url(home_url('/staydesk-signup')); ?>"><svg data-feather="chevron-right"></svg>Sign Up</a></li>
                </ul>
            </div>
            
            <!-- Features -->
            <div class="footer-column">
                <h4>Features</h4>
                <ul class="footer-links">
                    <li><a href="#"><svg data-feather="chevron-right"></svg>Booking Management</a></li>
                    <li><a href="#"><svg data-feather="chevron-right"></svg>Payment Integration</a></li>
                    <li><a href="#"><svg data-feather="chevron-right"></svg>AI Chatbot</a></li>
                    <li><a href="#"><svg data-feather="chevron-right"></svg>Analytics</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div class="footer-column">
                <h4>Contact Us</h4>
                <div class="footer-contact-item">
                    <span class="footer-contact-icon"><svg data-feather="mail"></svg></span>
                    <div class="footer-contact-text">
                        <a href="mailto:reach@bendlesstech.com">reach@bendlesstech.com</a>
                    </div>
                </div>
                <div class="footer-contact-item">
                    <span class="footer-contact-icon"><svg data-feather="phone"></svg></span>
                    <div class="footer-contact-text">
                        <a href="https://wa.me/2347120018023">+234 712 001 8023</a>
                    </div>
                </div>
                <div class="footer-contact-item">
                    <span class="footer-contact-icon"><svg data-feather="map-pin"></svg></span>
                    <div class="footer-contact-text">
                        Lagos, Nigeria
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p class="footer-copyright">
                © <?php echo date('Y'); ?> <a href="https://bendlesstech.com" target="_blank">BendlessTech</a>. All rights reserved.
            </p>
            
            <div class="footer-bottom-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Support</a>
            </div>
            
            <div class="footer-made-with">
                Made with <span class="heart">❤️</span> in Nigeria
            </div>
        </div>
    </div>
</footer>

<script>
// Initialize Feather icons for footer
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>
