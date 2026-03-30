<header class="site-header">
    <div class="header-content">
        <div class="header-left">
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <img src="assets/images/ADBU_logo.png" alt="Schedule Planner" class="logo-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="logo-fallback">
                <i class="fas fa-calendar-alt"></i>
                <span>Schedule Planner</span>
            </div>
            <div class="university-label">Assam Don Bosco University</div>
        </div>
        <div class="header-right">
            <?php if (is_logged_in()): ?>
                <!-- User menu for logged in users -->
                <div class="user-menu">
                    <div class="user-avatar">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode(get_logged_in_user()['username']); ?>&background=3498db&color=fff&size=32" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars(get_logged_in_user()['username']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="user-dropdown">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item theme-toggle-item">
                            <i class="fas fa-moon"></i> <span class="theme-text">Dark Theme</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Only theme toggle for authentication pages -->
                <div class="auth-theme-toggle">
                    <a href="#" class="dropdown-item theme-toggle-item">
                        <i class="fas fa-moon"></i> <span class="theme-text">Dark Theme</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<style>
/* Header Styles */
.site-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 65px;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 100%;
    width: 100%;
    padding: 0 20px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.university-label {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin-left: 8px;
    white-space: nowrap;
}

.logo-image {
    height: 55px;
    width: auto;
    border-radius: 6px;
}

.logo-fallback {
    display: none;
    align-items: center;
    gap: 10px;
    font-size: 19px;
    font-weight: 600;
    color: #333;
}

.logo-fallback i {
    color: #3498db;
    font-size: 40px;
}

.header-right {
    display: flex;
    align-items: center;
}

/* Auth Theme Toggle */
.auth-theme-toggle {
    display: flex;
    align-items: center;
}

.auth-theme-toggle .dropdown-item {
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 16px;
    color: #374151;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s ease;
}

.auth-theme-toggle .dropdown-item:hover {
    background-color: #e5e7eb;
}

/* User Menu */
.user-menu {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.user-menu:hover {
    background-color: #f3f4f6;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #e5e7eb;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-name {
    font-weight: 600;
    color: #374151;
    font-size: 15px;
}

.user-info i {
    color: #6b7280;
    font-size: 12px;
    transition: transform 0.3s ease;
}

.user-menu:hover .user-info i {
    transform: rotate(180deg);
}

.user-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 180px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    margin-top: 8px;
}

.user-menu:hover .user-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #374151;
    text-decoration: none;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.dropdown-item:hover {
    background-color: #f3f4f6;
}

.dropdown-item.logout {
    color: #ef4444;
}

.dropdown-item.logout:hover {
    background-color: #fef2f2;
}

.dropdown-divider {
    height: 1px;
    background-color: #e5e7eb;
    margin: 4px 0;
}

.theme-toggle-item {
    cursor: pointer;
}

/* Dark Theme Styles */
.dark-theme .site-header {
    background: #1f2937;
    border-bottom-color: #374151;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.dark-theme .logo-fallback {
    color: #f9fafb;
}

.dark-theme .university-label {
    color: #f9fafb;
}

.dark-theme .auth-theme-toggle .dropdown-item {
    background: #374151;
    border-color: #4b5563;
    color: #f9fafb;
}

.dark-theme .auth-theme-toggle .dropdown-item:hover {
    background-color: #4b5563;
}

.dark-theme .user-menu:hover {
    background-color: #374151;
}

.dark-theme .user-name {
    color: #f9fafb;
}

.dark-theme .user-info i {
    color: #9ca3af;
}

.dark-theme .user-dropdown {
    background: #374151;
    border-color: #4b5563;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

.dark-theme .dropdown-item {
    color: #f9fafb;
}

.dark-theme .dropdown-item:hover {
    background-color: #4b5563;
}

.dark-theme .dropdown-item.logout {
    color: #f87171;
}

.dark-theme .dropdown-item.logout:hover {
    background-color: #7f1d1d;
}

.dark-theme .dropdown-divider {
    background-color: #4b5563;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-content {
        padding: 0 15px;
    }
    
    .header-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .logo-image {
        height: 35px;
    }
    
    .logo-fallback {
        font-size: 17px;
    }
    
    .logo-fallback i {
        font-size: 35px;
    }
    
    .university-label {
        font-size: 14px;
        margin-left: 5px;
        display: none; /* Hide on very small screens */
    }
    
    .user-info {
        display: none;
    }
    
    .user-dropdown {
        right: -10px;
    }
    
    .user-menu {
        padding: 8px;
    }
    
    /* Show mobile menu toggle */
    .mobile-menu-toggle {
        display: block;
    }
    
    /* Adjust university label for medium screens */
    @media (max-width: 600px) {
        .university-label {
            display: none;
        }
    }
    
    @media (min-width: 601px) and (max-width: 768px) {
        .university-label {
            display: block;
            font-size: 12px;
        }
    }
}

@media (max-width: 480px) {
    .auth-theme-toggle .dropdown-item {
        padding: 6px 12px;
        font-size: 13px;
    }
}

/* Adjust body for fixed header */
body {
    padding-top: 65px;
}

@media (max-width: 768px) {
    body {
        padding-top: 65px;
    }
}
</style>

<script>
// Theme toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.classList.toggle('dark-theme', savedTheme === 'dark');
    
    // Update theme toggle text
    updateThemeToggleText();
    
    // Theme toggle for both auth and user menu
    const themeToggleItems = document.querySelectorAll('.theme-toggle-item');
    themeToggleItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('dark-theme');
            const currentTheme = document.body.classList.contains('dark-theme') ? 'dark' : 'light';
            localStorage.setItem('theme', currentTheme);
            updateThemeToggleText();
        });
    });
    
    function updateThemeToggleText() {
        const themeTexts = document.querySelectorAll('.theme-text');
        const isDark = document.body.classList.contains('dark-theme');
        
        themeTexts.forEach(function(themeText) {
            themeText.textContent = isDark ? 'Light Theme' : 'Dark Theme';
        });
        
        const icons = document.querySelectorAll('.theme-toggle-item i');
        icons.forEach(function(icon) {
            if (isDark) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        });
    }
    
    // Mobile menu toggle
    function initMobileMenu() {
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (mobileMenuToggle && sidebar) {
            // Remove existing listeners to prevent duplicates
            mobileMenuToggle.replaceWith(mobileMenuToggle.cloneNode(true));
            const newToggle = document.getElementById('mobileMenuToggle');
            
            newToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                sidebar.classList.toggle('active');
            });
            
            // Close sidebar when clicking outside
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768 && 
                    !sidebar.contains(e.target) && 
                    !newToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            });
            
            console.log('Mobile menu initialized successfully');
        } else {
            console.log('Mobile menu elements not found, retrying...');
            // Retry after a short delay
            setTimeout(initMobileMenu, 100);
        }
    }
    
    // Initialize mobile menu
    initMobileMenu();
});
</script>
