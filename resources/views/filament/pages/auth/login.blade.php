<div class="custom-login-wrapper">

    <!-- Left Side: Visual / Background -->
    <div class="custom-login-left">
        <!-- Dark Green Overlay -->
        <div class="custom-login-overlay"></div>

        <div class="custom-login-content">

            <div class="custom-login-logo-container">
                <div class="custom-login-logo-glow"></div>
                <div class="custom-login-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2.08-1.13L21 9V19h2V7.91l-1-.54L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z" />
                    </svg>
                </div>
            </div>

            <h1>Classy<span>One</span></h1>
            <div class="custom-login-divider"></div>

            <div class="custom-login-desc">
                <p>Restez connecté à votre école.</p>
                <p>Notifications, planning, événements —</p>
                <p>tout en un seul endroit.</p>
            </div>

            <div class="custom-login-footer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.315 48.315 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                </svg>
                OMNIA School of Business & Technologies
            </div>
        </div>
    </div>

    <!-- Right Side: Form Container -->
    <div class="custom-login-right">

        <!-- Faint dotted background patterns (decorative) -->
        <div class="pattern-dots"></div>
        <div class="pattern-circles"></div>

        <!-- Form Card -->
        <div class="custom-login-card">

            <div class="custom-profile-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>

            <h3 class="welcome-title">Bienvenue !</h3>
            <p class="welcome-subtitle">Connectez-vous pour accéder à votre espace</p>

            <div class="form-wrapper">
                <x-filament-panels::form wire:submit="authenticate">
                    {{ $this->form }}

                    <!-- Filament form action button -->
                    <div class="submit-wrapper">
                        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="true" />
                    </div>
                </x-filament-panels::form>
            </div>

            <div class="footer-link" style="margin-top: 2.5rem;">
                Accès sécurisé réservé au personnel et aux étudiants.
            </div>

        </div>
    </div>

    <style>
        /* Force typography globally */
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

        .custom-login-wrapper,
        .custom-login-wrapper * {
            font-family: 'Outfit', sans-serif !important;
            box-sizing: border-box;
        }

        html,
        body {
            overflow: hidden !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            background-color: #ffffff;
        }

        .custom-login-wrapper {
            display: flex;
            height: 100vh;
            width: 100vw;
            background-color: #ffffff;
            overflow: hidden;
            animation: fadeIn 0.8s ease-out;
        }

        /* --- Keyframes for animations --- */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideRightFade {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes slowZoom {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        @keyframes floatUpDown {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        @keyframes shimmer {
            100% { left: 200%; }
        }

        @keyframes pulse-glow {
            0% { opacity: 0.3; transform: scale(0.95); }
            100% { opacity: 0.8; transform: scale(1.1); }
        }

        @keyframes movePattern {
            0% { background-position: 0 0; }
            100% { background-position: 32px 32px; }
        }

        @keyframes cardBreathe {
            0%, 100% { box-shadow: 0 30px 60px -15px rgba(16, 185, 129, 0.1), 0 0 0 1px rgba(0,0,0,0.02); }
            50% { box-shadow: 0 40px 70px -15px rgba(16, 185, 129, 0.2), 0 0 0 1px rgba(0,0,0,0.02); }
        }

        /* --- Left Side --- */
        .custom-login-left {
            display: none;
            position: relative;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        @media (min-width: 1024px) {
            .custom-login-left {
                display: flex;
                flex: 1.2;
            }
        }

        .custom-login-left::before {
            content: '';
            position: absolute;
            inset: -10%;
            background-image: url('https://images.unsplash.com/photo-1542744173-8e7e53415bb0?q=80&w=1200&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            z-index: 1;
            animation: slowZoom 20s ease-in-out infinite alternate;
        }

        .custom-login-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(2, 44, 34, 0.85) 0%, rgba(6, 78, 59, 0.95) 100%);
            backdrop-filter: blur(4px);
            z-index: 10;
        }

        .custom-login-overlay::before {
            content: '';
            position: absolute;
            top: 10%;
            right: 10%;
            width: 40vw;
            height: 40vw;
            background: radial-gradient(circle, rgba(16,185,129,0.15) 0%, transparent 60%);
            border-radius: 50%;
            z-index: 11;
            animation: floatUpDown 8s ease-in-out infinite reverse;
        }

        .custom-login-overlay::after {
            content: '';
            position: absolute;
            bottom: -10%;
            left: -10%;
            width: 60vw;
            height: 60vw;
            background: radial-gradient(circle, rgba(52,211,153,0.1) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 11;
            animation: floatUpDown 12s ease-in-out infinite;
        }

        .custom-login-content {
            position: relative;
            z-index: 20;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            width: 100%;
            height: 100%;
            animation: slideRightFade 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }

        .custom-login-logo-container {
            margin-bottom: 2rem;
            position: relative;
        }

        .custom-login-logo-glow {
            position: absolute;
            inset: -15px;
            background: linear-gradient(45deg, #10b981, #34d399, #059669);
            border-radius: 50%;
            filter: blur(25px);
            opacity: 0.5;
            animation: pulse-glow 2.5s infinite alternate;
        }

        .custom-login-logo {
            width: 6.5rem;
            height: 6.5rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 10;
            color: #ffffff;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            animation: floatUpDown 4s ease-in-out infinite;
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.3);
        }
        
        .custom-login-logo:hover {
            transform: scale(1.1) rotate(5deg) !important;
            box-shadow: 0 0 30px rgba(52, 211, 153, 0.6);
            animation-play-state: paused;
        }

        .custom-login-content h1 {
            font-size: 4.5rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            margin: 0 0 1.5rem 0;
            color: white;
            line-height: 1.1;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .custom-login-content h1 span {
            color: #34d399;
        }

        .custom-login-divider {
            width: 5rem;
            height: 4px;
            background: linear-gradient(90deg, transparent, #34d399, transparent);
            margin-bottom: 2rem;
            border-radius: 2px;
            position: relative;
            overflow: hidden;
        }
        
        .custom-login-divider::after {
            content: "";
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.9), transparent);
            animation: shimmer 2s infinite;
        }

        .custom-login-desc {
            max-width: 28rem;
            font-size: 1.05rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 3rem;
            font-weight: 300;
        }

        .custom-login-desc p {
            margin: 0;
        }

        .custom-login-footer {
            margin-top: auto;
            position: absolute;
            bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            background: rgba(0, 0, 0, 0.25);
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            animation: slideUpFade 1s backwards;
            animation-delay: 0.5s;
        }
        
        .custom-login-footer:hover {
            background: rgba(0, 0, 0, 0.5);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgba(0,0,0,0.3);
        }

        /* --- Right Side --- */
        .custom-login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 2rem;
            background-color: #ffffff;
            overflow-y: auto;
            overflow-x: hidden;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .custom-login-right::-webkit-scrollbar {
            display: none;
        }

        /* Ambient Orbs on Right Side */
        .custom-login-right::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            border-radius: 50%;
            animation: floatUpDown 10s ease-in-out infinite;
            pointer-events: none;
            z-index: 1;
        }

        .custom-login-right::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(52, 211, 153, 0.05) 0%, transparent 70%);
            bottom: -150px;
            left: -150px;
            border-radius: 50%;
            animation: floatUpDown 12s ease-in-out infinite reverse;
            pointer-events: none;
            z-index: 1;
        }

        /* Moving dots background */
        .pattern-dots {
            position: absolute;
            top: -100%; left: -100%; right: -100%; bottom: -100%;
            opacity: 0.5;
            pointer-events: none;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 32px 32px;
            z-index: 0;
            animation: movePattern 20s linear infinite;
        }

        .custom-login-card {
            width: 100%;
            max-width: 28rem;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(25px);
            border-radius: 2rem;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0,0,0,0.02);
            padding: 3.5rem 3rem;
            position: relative;
            z-index: 10;
            text-align: center;
            animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards, cardBreathe 8s infinite alternate ease-in-out;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .custom-login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 40px 80px -15px rgba(16, 185, 129, 0.15), 0 0 0 1px rgba(16,185,129,0.1) !important;
            animation-play-state: paused;
        }

        .custom-profile-icon {
            width: 5rem;
            height: 5rem;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            color: #059669;
            box-shadow: 0 15px 25px -5px rgba(16, 185, 129, 0.15);
            transform: rotate(-10deg);
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        }
        
        .custom-login-card:hover .custom-profile-icon {
            transform: rotate(0deg) scale(1.08);
            box-shadow: 0 20px 30px -5px rgba(16, 185, 129, 0.25);
            animation: pulse-glow 2s infinite alternate;
        }

        .custom-profile-icon svg {
            width: 2.5rem;
            height: 2.5rem;
        }

        .welcome-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.03em;
        }

        .welcome-subtitle {
            font-size: 0.95rem;
            color: #64748b;
            margin: 0 0 2.5rem 0;
            font-weight: 400;
        }

        .form-wrapper {
            text-align: left;
            width: 100%;
        }

        .submit-wrapper {
            width: 100%;
            margin-top: 2rem;
        }

        .footer-link {
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 400;
            margin-top: 2.5rem;
            animation: fadeIn 1.5s backwards;
            animation-delay: 0.8s;
        }

        .footer-link a {
            color: #059669;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s;
        }

        .footer-link a:hover {
            color: #10b981;
        }

        /* -------------------------------------------------------------
           FILAMENT FORM OVERRIDES TO PERFECTLY MATCH THE MOCKUP
           ------------------------------------------------------------- */

        .fi-simple-layout,
        .fi-simple-main,
        .fi-main {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }

        .fi-simple-page {
            max-width: 100% !important;
            padding: 0 !important;
        }

        .fi-fo-form {
            gap: 1.5rem !important;
        }

        /* Cascading Animation for Form Fields */
        .fi-fo-form > div:nth-child(1) { animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards; animation-delay: 0.2s; }
        .fi-fo-form > div:nth-child(2) { animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards; animation-delay: 0.3s; }
        .fi-fo-form > div:nth-child(3) { animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards; animation-delay: 0.4s; }
        .submit-wrapper { animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards; animation-delay: 0.5s; }

        /* 1. Labels */
        .fi-fo-field-wrp {
            position: relative;
            margin-bottom: 0 !important;
        }

        .fi-fo-field-wrp-label {
            display: block;
            margin-bottom: 0.5rem !important;
        }

        .fi-fo-field-wrp-label span {
            font-size: 0.9rem !important;
            font-weight: 600 !important;
            color: #334155 !important;
        }

        .fi-fo-field-wrp-label sup {
            display: none !important;
        }

        .fi-fo-field-wrp-header {
            display: block !important;
            margin-bottom: 0 !important;
        }

        .fi-fo-field-wrp-header a {
            color: #059669 !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            position: absolute;
            right: 0;
            top: 0;
            z-index: 20;
            transition: all 0.2s ease;
        }

        .fi-fo-field-wrp-header a:hover {
            color: #10b981 !important;
            transform: translateX(-2px);
        }

        /* 2. Input Container Styling */
        .fi-input-wrp {
            border-radius: 1rem !important;
            border: 2px solid transparent !important;
            background-color: #f1f5f9 !important;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02) !important;
            position: relative;
            z-index: 1;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden !important;
            display: flex;
            align-items: center;
        }

        .fi-input-wrp::before,
        .fi-input-wrp::after {
            display: none !important;
        }

        .fi-input-wrp:focus-within {
            background-color: #ffffff !important;
            border-color: #34d399 !important;
            box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.15), inset 0 2px 4px rgba(0,0,0,0) !important;
            transform: translateY(-2px);
        }

        /* 3. Input Text */
        .fi-input-wrp input {
            padding: 1rem 1rem 1rem 0.5rem !important;
            font-size: 1rem !important;
            font-weight: 500 !important;
            color: #0f172a !important;
            background-color: transparent !important;
            width: 100% !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            transition: all 0.2s;
        }

        .fi-input-wrp input::placeholder {
            color: #94a3b8 !important;
            font-weight: 400 !important;
        }

        /* 4. Injection of icons */
        .fi-fo-form>div:nth-child(1) .fi-input-wrp::before {
            content: "";
            display: inline-block;
            width: 1.25rem;
            height: 1.25rem;
            margin-left: 1rem;
            margin-right: -0.25rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            transition: all 0.3s;
        }

        .fi-fo-form>div:nth-child(2) .fi-input-wrp::before {
            content: "";
            display: inline-block;
            width: 1.25rem;
            height: 1.25rem;
            margin-left: 1rem;
            margin-right: -0.25rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            transition: all 0.3s;
        }
        
        .fi-input-wrp:focus-within::before {
            filter: brightness(0) saturate(100%) invert(43%) sepia(82%) saturate(417%) hue-rotate(113deg) brightness(92%) contrast(92%);
            transform: scale(1.1);
        }

        /* 5. Button (Se connecter) */
        .fi-btn {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            color: #ffffff !important;
            border-radius: 9999px !important;
            padding: 1rem !important;
            font-weight: 600 !important;
            font-size: 1.05rem !important;
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            border: none !important;
            box-shadow: 0 10px 20px -3px rgba(5, 150, 105, 0.3) !important;
            position: relative;
            outline: none !important;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            overflow: hidden;
        }

        .fi-btn::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.5s;
            animation: shimmer 3s infinite;
            animation-delay: 2s;
        }

        .fi-btn:hover::before {
            left: 100%;
            transition: all 0.5s ease-in-out;
            animation: none;
        }

        .fi-btn:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 30px -5px rgba(5, 150, 105, 0.4) !important;
        }
        
        .fi-btn:active {
            transform: translateY(1px) scale(0.98);
        }

        .fi-btn-label {
            font-family: 'Outfit', sans-serif !important;
            letter-spacing: 0.02em;
        }

        .fi-btn::after {
            content: "→";
            position: absolute;
            right: 1.5rem;
            font-size: 1.25rem;
            font-weight: 400;
            transition: transform 0.3s ease;
        }
        
        .fi-btn:hover::after {
            transform: translateX(8px);
        }

        .fi-form-actions {
            width: 100% !important;
            margin: 0 !important;
            gap: 0 !important;
        }

        .fi-form-actions>* {
            width: 100% !important;
            margin: 0 !important;
        }

        /* 6. Checkbox (Remember me) */
        .fi-checkbox {
            width: 1.25rem !important;
            height: 1.25rem !important;
            border-radius: 0.375rem !important;
            border: 2px solid #cbd5e1 !important;
            color: #059669 !important;
            cursor: pointer;
            outline: none !important;
            box-shadow: none !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .fi-checkbox:checked {
            background-color: #059669 !important;
            border-color: #059669 !important;
            transform: scale(1.1);
        }

        .fi-checkbox-label span {
            font-size: 0.9rem !important;
            color: #475569 !important;
            font-weight: 500 !important;
            margin-left: 0.5rem;
            user-select: none;
            transition: color 0.2s ease;
        }
        
        .fi-checkbox:hover {
            border-color: #10b981 !important;
        }

        .fi-fo-form>div:nth-child(3) {
            margin-top: -0.5rem !important;
            margin-bottom: 0.5rem !important;
        }
    </style>
</div>
