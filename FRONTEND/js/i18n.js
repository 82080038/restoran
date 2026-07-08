// Internationalization (i18n) for Consumer App
const translations = {
    id: {
        app: {
            title: "EBP Restaurant"
        },
        guest: "Tamu",
        login_prompt: "Login untuk akses fitur",
        nav: {
            home: "Beranda",
            search: "Cari",
            reservations: "Reservasi",
            orders: "Pesanan",
            favorites: "Favorit",
            loyalty: "Loyalitas",
            settings: "Pengaturan",
            help: "Bantuan",
            profile: "Profil"
        },
        home: {
            discover: "Temukan Restoran",
            featured: "Restoran Unggulan",
            nearby: "Restoran Terdekat",
            cuisines: "Masakan Populer"
        },
        search: {
            title: "Cari",
            placeholder: "Cari restoran, masakan, hidangan..."
        },
        filter: {
            all: "Semua",
            nearby: "Terdekat",
            top_rated: "Terbaik",
            halal: "Halal",
            delivery: "Antar",
            cuisine: "Masakan",
            price: "Rentang Harga",
            rating: "Rating",
            features: "Fitur",
            apply: "Terapkan Filter"
        },
        price: {
            cheap: "$",
            moderate: "$$",
            expensive: "$$$",
            luxury: "$$$$"
        },
        rating: {
            "4_plus": "4+ Bintang",
            "3_plus": "3+ Bintang"
        },
        feature: {
            halal: "Halal",
            delivery: "Antar",
            pickup: "Ambil Sendiri",
            reservation: "Reservasi"
        },
        reservations: {
            title: "Reservasi Saya",
            new: "+ Baru"
        },
        orders: {
            title: "Pesanan Saya"
        },
        favorites: {
            title: "Favorit Saya"
        },
        loyalty: {
            title: "Program Loyalitas"
        },
        settings: {
            title: "Pengaturan",
            language: "Bahasa",
            notifications: "Notifikasi",
            dark_mode: "Mode Gelap",
            location: "Layanan Lokasi"
        },
        help: {
            title: "Bantuan & Dukungan",
            faq: "FAQ",
            contact: "Hubungi Kami",
            chat: "Chat Dukungan",
            email: "Email Dukungan"
        },
        reservation: {
            make_title: "Buat Reservasi",
            restaurant: "Restoran",
            date: "Tanggal",
            time: "Waktu",
            party_size: "Jumlah Orang",
            special_requests: "Permintaan Khusus",
            submit: "Pesan Meja"
        },
        order: {
            title: "Buat Pesanan",
            subtotal: "Subtotal",
            delivery_fee: "Biaya Antar",
            total: "Total",
            delivery: "Antar",
            pickup: "Ambil Sendiri",
            address: "Alamat Antar",
            place: "Buat Pesanan"
        },
        review: {
            title: "Tulis Ulasan",
            rating: "Rating",
            comment: "Ulasan Anda",
            photos: "Tambah Foto",
            submit: "Kirim Ulasan"
        },
        login: {
            title: "Login",
            email: "Email",
            password: "Password",
            submit: "Login",
            no_account: "Belum punya akun?",
            signup: "Daftar"
        }
    },
    en: {
        app: {
            title: "EBP Restaurant"
        },
        guest: "Guest",
        login_prompt: "Login to access features",
        nav: {
            home: "Home",
            search: "Search",
            reservations: "Reservations",
            orders: "Orders",
            favorites: "Favorites",
            loyalty: "Loyalty",
            settings: "Settings",
            help: "Help",
            profile: "Profile"
        },
        home: {
            discover: "Discover Restaurants",
            featured: "Featured Restaurants",
            nearby: "Nearby Restaurants",
            cuisines: "Popular Cuisines"
        },
        search: {
            title: "Search",
            placeholder: "Search restaurants, cuisines, dishes..."
        },
        filter: {
            all: "All",
            nearby: "Nearby",
            top_rated: "Top Rated",
            halal: "Halal",
            delivery: "Delivery",
            cuisine: "Cuisine",
            price: "Price Range",
            rating: "Rating",
            features: "Features",
            apply: "Apply Filters"
        },
        price: {
            cheap: "$",
            moderate: "$$",
            expensive: "$$$",
            luxury: "$$$$"
        },
        rating: {
            "4_plus": "4+ Stars",
            "3_plus": "3+ Stars"
        },
        feature: {
            halal: "Halal",
            delivery: "Delivery",
            pickup: "Pickup",
            reservation: "Reservation"
        },
        reservations: {
            title: "My Reservations",
            new: "+ New"
        },
        orders: {
            title: "My Orders"
        },
        favorites: {
            title: "My Favorites"
        },
        loyalty: {
            title: "Loyalty Program"
        },
        settings: {
            title: "Settings",
            language: "Language",
            notifications: "Notifications",
            dark_mode: "Dark Mode",
            location: "Location Services"
        },
        help: {
            title: "Help & Support",
            faq: "FAQ",
            contact: "Contact Us",
            chat: "Chat Support",
            email: "Email Support"
        },
        reservation: {
            make_title: "Make a Reservation",
            restaurant: "Restaurant",
            date: "Date",
            time: "Time",
            party_size: "Party Size",
            special_requests: "Special Requests",
            submit: "Book Table"
        },
        order: {
            title: "Place Order",
            subtotal: "Subtotal",
            delivery_fee: "Delivery Fee",
            total: "Total",
            delivery: "Delivery",
            pickup: "Pickup",
            address: "Delivery Address",
            place: "Place Order"
        },
        review: {
            title: "Write a Review",
            rating: "Rating",
            comment: "Your Review",
            photos: "Add Photos",
            submit: "Submit Review"
        },
        login: {
            title: "Login",
            email: "Email",
            password: "Password",
            submit: "Login",
            no_account: "Don't have an account?",
            signup: "Sign Up"
        }
    }
};

class I18n {
    constructor() {
        this.currentLang = localStorage.getItem('language') || 'id';
        this.translations = translations;
        this.availableLanguages = ['id', 'en'];
        this.fallbackLang = 'id';
        this.init();
    }

    init() {
        // Detect browser language
        const browserLang = navigator.language.split('-')[0];
        if (this.availableLanguages.includes(browserLang) && !localStorage.getItem('language')) {
            this.setLanguage(browserLang);
        }

        // Listen for language changes
        window.addEventListener('languagechange', () => {
            const newLang = navigator.language.split('-')[0];
            if (this.availableLanguages.includes(newLang)) {
                this.setLanguage(newLang);
            }
        });
    }

    setLanguage(lang) {
        if (!this.availableLanguages.includes(lang)) {
            console.warn(`Language ${lang} not available, falling back to ${this.fallbackLang}`);
            lang = this.fallbackLang;
        }

        this.currentLang = lang;
        localStorage.setItem('language', lang);
        
        // Update document language attribute
        document.documentElement.lang = lang;
        
        // Update RTL support if needed
        this.updateRTL();
        
        // Update page content
        this.updatePage();
        
        // Dispatch custom event for other components
        window.dispatchEvent(new CustomEvent('languageChanged', { detail: { language: lang } }));
    }

    getLanguage() {
        return this.currentLang;
    }

    getAvailableLanguages() {
        return this.availableLanguages;
    }

    t(key, params = {}) {
        const keys = key.split('.');
        let value = this.translations[this.currentLang];
        
        for (const k of keys) {
            if (value && value[k]) {
                value = value[k];
            } else {
                // Fallback to default language
                value = this.translations[this.fallbackLang];
                for (const k2 of keys) {
                    if (value && value[k2]) {
                        value = value[k2];
                    } else {
                        return key;
                    }
                }
                return value;
            }
        }
        
        // Replace parameters in translation
        if (typeof value === 'string' && Object.keys(params).length > 0) {
            value = this.replaceParams(value, params);
        }
        
        return value;
    }

    replaceParams(text, params) {
        return text.replace(/\{(\w+)\}/g, (match, key) => {
            return params[key] !== undefined ? params[key] : match;
        });
    }

    updateRTL() {
        const rtlLanguages = ['ar', 'he', 'fa'];
        const isRTL = rtlLanguages.includes(this.currentLang);
        
        if (isRTL) {
            document.documentElement.dir = 'rtl';
            document.body.classList.add('rtl');
        } else {
            document.documentElement.dir = 'ltr';
            document.body.classList.remove('rtl');
        }
    }

    updatePage() {
        // Update elements with data-i18n attribute
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            const params = el.getAttribute('data-i18n-params');
            const parsedParams = params ? JSON.parse(params) : {};
            el.textContent = this.t(key, parsedParams);
        });

        // Update elements with data-i18n-placeholder attribute
        document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            el.placeholder = this.t(key);
        });

        // Update elements with data-i18n-title attribute
        document.querySelectorAll('[data-i18n-title]').forEach(el => {
            const key = el.getAttribute('data-i18n-title');
            el.title = this.t(key);
        });

        // Update elements with data-i18n-alt attribute
        document.querySelectorAll('[data-i18n-alt]').forEach(el => {
            const key = el.getAttribute('data-i18n-alt');
            el.alt = this.t(key);
        });

        // Update language button
        const langBtn = document.getElementById('langBtn');
        if (langBtn) {
            langBtn.textContent = this.currentLang.toUpperCase();
        }

        // Update language select
        const langSelect = document.getElementById('languageSelect');
        if (langSelect) {
            langSelect.value = this.currentLang;
        }

        // Update content based on language
        this.updateLanguageSpecificContent();
    }

    updateLanguageSpecificContent() {
        // Show/hide elements based on language
        document.querySelectorAll('[data-lang]').forEach(el => {
            const lang = el.getAttribute('data-lang');
            if (lang === this.currentLang) {
                el.style.display = '';
            } else {
                el.style.display = 'none';
            }
        });

        // Update images with language-specific sources
        document.querySelectorAll('[data-i18n-src]').forEach(el => {
            const key = el.getAttribute('data-i18n-src');
            const src = this.t(key);
            if (src && src !== key) {
                el.src = src;
            }
        });
    }

    addLanguage(langCode, translations) {
        if (!this.availableLanguages.includes(langCode)) {
            this.availableLanguages.push(langCode);
            this.translations[langCode] = translations;
        }
    }

    formatNumber(number, options = {}) {
        return new Intl.NumberFormat(this.currentLang, options).format(number);
    }

    formatCurrency(amount, currency = 'IDR') {
        return new Intl.NumberFormat(this.currentLang, {
            style: 'currency',
            currency: currency
        }).format(amount);
    }

    formatDate(date, options = {}) {
        return new Intl.DateTimeFormat(this.currentLang, options).format(new Date(date));
    }

    formatTime(date, options = {}) {
        return new Intl.DateTimeFormat(this.currentLang, {
            ...options,
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    }
}

const i18n = new I18n();
