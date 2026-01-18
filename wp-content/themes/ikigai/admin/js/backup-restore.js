document.addEventListener('DOMContentLoaded', function() {
    
    // Tab Switching Logic
    const tabs = document.querySelectorAll('.ikg-backup-tab-btn');
    const contents = document.querySelectorAll('.ikg-backup-tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all
            tabs.forEach(t => t.classList.remove('ikg-backup-active'));
            contents.forEach(c => c.classList.remove('ikg-backup-active'));

            // Add active to clicked
            tab.classList.add('ikg-backup-active');
            const targetId = tab.getAttribute('data-tab');
            document.getElementById(targetId).classList.add('ikg-backup-active');
        });
    });

    // Handle Backup Generation
    // We bind a click event to the buttons to maybe show a loading state
    const backupButtons = document.querySelectorAll('button[name="create_backup"]');
    backupButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Optional: Change text to loading...
            const originalText = this.innerText;
            this.innerText = '⏳ Generando...';
            this.style.opacity = '0.7';
            // Form submission continues naturally
        });
    });

    // Log Auto Scroll
    const logContainer = document.querySelector('.ikg-backup-log-container');
    if (logContainer) {
        logContainer.scrollTop = logContainer.scrollHeight;
    }
    
    // Validate File Upload
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0].name;
            if (!fileName.endsWith('.zip')) {
                alert('Por favor selecciona un archivo .zip válido');
                this.value = '';
            }
        });
    }

});
