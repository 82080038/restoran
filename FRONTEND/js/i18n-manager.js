/**
 * F&B Management System i18n Manager
 * Handles multi-language support: loading translations, switching languages,
 * and translating UI elements via data-i18n attributes
 */
class I18nManager {
    constructor() {
        this.currentLang = localStorage.getItem('fnb_lang') || 'id';
        this.translations = {};
        this.languages = [];
        this.loaded = false;
        this.fallbackLang = 'en';

        this.init();
    }

    async init() {
        await this.loadLanguages();
        await this.loadTranslations(this.currentLang);
        this.applyTranslations();
        this.renderLanguageSwitcher();
        this.loaded = true;
    }

    /**
     * Load available languages from API
     */
    async loadLanguages() {
        try {
            const apiBase = Config.api.baseURL;
            const resp = await fetch(`${apiBase}/languages`);
            const data = await resp.json();
            if (data.success) {
                this.languages = data.data || [];
            }
        } catch (e) {
            console.warn('Failed to load languages, using defaults');
            this.languages = [
                { language_code: 'en', language_name: 'English', native_name: 'English', flag_icon: '🇬🇧' },
                { language_code: 'id', language_name: 'Indonesian', native_name: 'Bahasa Indonesia', flag_icon: '🇮🇩' }
            ];
        }
    }

    /**
     * Load translations for a language
     */
    async loadTranslations(lang) {
        if (this.translations[lang]) return;

        try {
            const apiBase = Config.api.baseURL;
            const resp = await fetch(`${apiBase}/languages/${lang}/translations`);
            const data = await resp.json();
            if (data.success) {
                this.translations[lang] = data.data.translations || {};
            }
        } catch (e) {
            console.warn(`Failed to load translations for ${lang}`);
            this.translations[lang] = {};
        }

        // Load fallback if different
        if (lang !== this.fallbackLang && !this.translations[this.fallbackLang]) {
            try {
                const apiBase = Config.api.baseURL;
                const resp = await fetch(`${apiBase}/languages/${this.fallbackLang}/translations`);
                const data = await resp.json();
                if (data.success) {
                    this.translations[this.fallbackLang] = data.data.translations || {};
                }
            } catch (e) {
                this.translations[this.fallbackLang] = {};
            }
        }
    }

    /**
     * Translate a key
     */
    t(key, fallback = null) {
        const val = this.translations[this.currentLang]?.[key]
            ?? this.translations[this.fallbackLang]?.[key]
            ?? fallback
            ?? key;
        return val;
    }

    /**
     * Apply translations to all [data-i18n] elements
     */
    applyTranslations() {
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            const translated = this.t(key);
            if (translated && translated !== key) {
                el.textContent = translated;
            }
        });

        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            const translated = this.t(key);
            if (translated && translated !== key) {
                el.setAttribute('placeholder', translated);
            }
        });

        document.querySelectorAll('[data-i18n-title]').forEach(el => {
            const key = el.getAttribute('data-i18n-title');
            const translated = this.t(key);
            if (translated && translated !== key) {
                el.setAttribute('title', translated);
            }
        });

        document.documentElement.setAttribute('lang', this.currentLang);
    }

    /**
     * Switch language
     */
    async switchLanguage(langCode) {
        if (this.currentLang === langCode) return;

        this.currentLang = langCode;
        localStorage.setItem('fnb_lang', langCode);

        await this.loadTranslations(langCode);
        this.applyTranslations();
        this.updateSwitcherActive();

        // Save preference to backend if logged in
        const token = localStorage.getItem('authToken');
        if (token) {
            try {
                const apiBase = Config.api.baseURL;
                await fetch(`${apiBase}/languages/preference`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ language_code: langCode })
                });
            } catch (e) {
                // Silent fail - preference saved locally
            }
        }

        // Dispatch event for other components
        window.dispatchEvent(new CustomEvent('languageChanged', { detail: { lang: langCode } }));
    }

    /**
     * Render language switcher dropdown
     */
    renderLanguageSwitcher() {
        let switcher = document.getElementById('lang-switcher');
        if (!switcher) {
            // Create switcher if not exists
            switcher = document.createElement('div');
            switcher.id = 'lang-switcher';
            switcher.className = 'lang-switcher';
            switcher.innerHTML = this.buildSwitcherHTML();

            // Try to insert into navbar or header
            const navbar = document.querySelector('.navbar-right, .header-right, .topbar-right');
            if (navbar) {
                navbar.appendChild(switcher);
            } else {
                // Append to body with fixed positioning
                document.body.appendChild(switcher);
            }
        } else {
            switcher.innerHTML = this.buildSwitcherHTML();
        }

        this.attachSwitcherEvents();
    }

    buildSwitcherHTML() {
        const current = this.languages.find(l => l.language_code === this.currentLang);
        const flag = current?.flag_icon || '🌐';
        const name = current?.native_name || current?.language_name || this.currentLang;

        const options = this.languages.map(l => `
            <div class="lang-option ${l.language_code === this.currentLang ? 'active' : ''}" data-lang="${l.language_code}">
                <span class="lang-flag">${l.flag_icon || '🌐'}</span>
                <span class="lang-name">${l.native_name || l.language_name}</span>
            </div>
        `).join('');

        return `
            <div class="lang-switcher-btn" onclick="window.i18nManager.toggleDropdown()">
                <span class="lang-flag">${flag}</span>
                <span class="lang-code">${name}</span>
                <i class="fas fa-chevron-down" style="font-size:10px"></i>
            </div>
            <div class="lang-dropdown" id="lang-dropdown" style="display:none">
                ${options}
            </div>
        `;
    }

    attachSwitcherEvents() {
        document.querySelectorAll('.lang-option').forEach(opt => {
            opt.addEventListener('click', (e) => {
                const lang = e.currentTarget.getAttribute('data-lang');
                this.switchLanguage(lang);
                this.toggleDropdown();
            });
        });
    }

    updateSwitcherActive() {
        document.querySelectorAll('.lang-option').forEach(opt => {
            const lang = opt.getAttribute('data-lang');
            opt.classList.toggle('active', lang === this.currentLang);
        });

        const current = this.languages.find(l => l.language_code === this.currentLang);
        const btn = document.querySelector('.lang-switcher-btn');
        if (btn && current) {
            btn.querySelector('.lang-flag').textContent = current.flag_icon || '🌐';
            btn.querySelector('.lang-code').textContent = current.native_name || current.language_name;
        }
    }

    toggleDropdown() {
        const dd = document.getElementById('lang-dropdown');
        if (dd) {
            dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
        }
    }
}

// Initialize global i18n manager
window.i18nManager = new I18nManager();

// Helper function for inline translations
window.t = (key, fallback) => window.i18nManager.t(key, fallback);
