function undoSingle(key) {
    const form = document.getElementById('undoSingleForm');
    if (form) {
        form.action = '/admin/settings/preferences/undo/' + key;
        form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit preferences form when a color is selected
    const colorPickers = document.querySelectorAll('#preferencesForm input[type="color"]');
    colorPickers.forEach(picker => {
        picker.addEventListener('change', function() {
            const form = document.getElementById('preferencesForm');
            if (form) form.submit();
        });
    });
});

function applyTheme(colors) {
    if(colors.primary_color) document.getElementById('primary_color').value = colors.primary_color;
    if(colors.sidebar_bg) document.getElementById('sidebar_bg').value = colors.sidebar_bg;
    if(colors.navbar_bg) document.getElementById('navbar_bg').value = colors.navbar_bg;
    if(colors.sidebar_text) document.getElementById('sidebar_text').value = colors.sidebar_text;
    if(colors.navbar_text) document.getElementById('navbar_text').value = colors.navbar_text;
    if(colors.site_name_color) document.getElementById('site_name_color').value = colors.site_name_color;
    if(colors.user_name_color) document.getElementById('user_name_color').value = colors.user_name_color;
    if(colors.sidebar_separator) document.getElementById('sidebar_separator').value = colors.sidebar_separator;
    if(colors.menu_title_color) document.getElementById('menu_title_color').value = colors.menu_title_color;
    if(colors.site_logo) {
        document.getElementById('applied_logo').value = colors.site_logo;
        // Update preview immediately if possible
        const logoPreview = document.getElementById('logoPreview');
        if (logoPreview) {
            // Note: We use a simple path here, server reload will fix the absolute path if needed
            logoPreview.src = '/storage/' + colors.site_logo;
        }
    } else {
        document.getElementById('applied_logo').value = "";
        const logoPreview = document.getElementById('logoPreview');
        if (logoPreview) {
            logoPreview.src = '/img/HelpTK--C.png';
        }
    }
    
    // Auto-submit the form to apply changes immediately
    const form = document.getElementById('preferencesForm');
    if (form) form.submit();
}

function deleteTheme(id) {
    if(confirm('Are you sure you want to delete this design?')) {
        const form = document.getElementById('deleteThemeForm');
        if (form) {
            form.action = '/admin/settings/themes/delete/' + id;
            form.submit();
        }
    }
}

function submitThemeForm() {
    // copy values from main form to hidden form
    const ids = ['primary_color', 'sidebar_bg', 'navbar_bg', 'sidebar_text', 'navbar_text', 'site_name_color', 'user_name_color', 'sidebar_separator', 'menu_title_color'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        const hiddenEl = document.getElementById('hidden_' + id);
        if (el && hiddenEl) {
            hiddenEl.value = el.value;
        }
    });
    
    const form = document.getElementById('saveThemeForm');
    if (form) {
        const currentLogo = document.getElementById('current_logo_path');
        const hiddenLogo = document.getElementById('hidden_site_logo');
        if (currentLogo && hiddenLogo) {
            hiddenLogo.value = currentLogo.value;
        }
        form.submit();
    }
}

function switchTab(tabId, element) {
    // Remove active class from all nav items
    document.querySelectorAll('.settings-nav-item').forEach(item => {
        item.classList.remove('active');
    });

    // Add active class to clicked item
    if (element) {
        element.classList.add('active');
    }

    // Hide all tab panes
    document.querySelectorAll('.settings-tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });

    // Show target tab pane
    const targetTab = document.getElementById('tab-' + tabId);
    if (targetTab) {
        targetTab.classList.add('active');
    }

    // Save to session storage
    sessionStorage.setItem('settingsActiveTab', tabId);
}

// Keep active tab on validation failure or reload
document.addEventListener('DOMContentLoaded', function () {
    // Restore active tab from session storage
    const savedTab = sessionStorage.getItem('settingsActiveTab');
    if (savedTab) {
        const tabElement = document.querySelector(`[onclick="switchTab('${savedTab}', this)"]`);
        if (tabElement) {
            switchTab(savedTab, tabElement);
        }
    }

    if (window.SettingsConfig && window.SettingsConfig.hasPasswordErrors) {
        // If password errors exist, switch to security tab
        const securityTab = document.querySelector('[onclick="switchTab(\'security\', this)"]');
        if (securityTab) switchTab('security', securityTab);
    }

    // Avatar Image Preview
    const avatarInput = document.getElementById('avatarInput');
    if (avatarInput) {
        avatarInput.addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const previewContainer = document.getElementById('avatarPreviewContainer');
                    const initial = document.getElementById('avatarInitial');

                    if (previewContainer) {
                        previewContainer.style.backgroundImage = `url(${e.target.result})`;
                        previewContainer.style.backgroundSize = 'cover';
                        previewContainer.style.backgroundPosition = 'center';
                        previewContainer.style.color = 'transparent';
                    }

                    if (initial) initial.style.display = 'none';

                    const removeInput = document.getElementById('removeAvatarInput');
                    if (removeInput) removeInput.value = '0';
                    
                    const removeBtn = document.getElementById('avatarRemoveBtn');
                    if (removeBtn) removeBtn.style.display = 'block';
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    // Logo Image Preview
    const logoInput = document.getElementById('site_logo');
    if (logoInput) {
        logoInput.addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const logoPreview = document.getElementById('logoPreview');
                    if (logoPreview) {
                        logoPreview.src = e.target.result;
                    }
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }
});
