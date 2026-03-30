<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-copyright">
            <p>&copy; 2026 AssamDonBoscoUniversity2026_Sain_Andrew. All rights reserved.</p>
        </div>
    </div>
</footer>

<style>
/* Footer Styles */
.site-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--card-bg);
    border-top: 1px solid var(--border-color);
    padding: 12px 20px;
    z-index: 100;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
}

.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    flex-wrap: wrap;
    gap: 15px;
}

.footer-copyright {
    color: var(--text-secondary, #b4b4b4);
    font-size: 14px;
    text-align: center;
    flex: 1;
}

/* Dark theme adjustments */
.dark-theme .site-footer {
    background: var(--card-bg);
    border-top-color: var(--border-color);
}

.dark-theme .footer-copyright {
    color: var(--text-secondary);
}


/* Mobile responsive */
@media (max-width: 768px) {
    .footer-content {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
       
    .footer-copyright {
        font-size: 12px;
    }
    
    .site-footer {
        padding: 10px 15px;
    }
}

/* Adjust main content to account for fixed footer */
body {
    padding-bottom: 60px;
}

@media (max-width: 768px) {
    body {
        padding-bottom: 70px;
    }
}
</style>
