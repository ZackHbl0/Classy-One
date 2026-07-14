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
            <h2>Votre portail étudiant <span>intelligent</span></h2>

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
                Pas encore de compte ? <a href="#">Contacter l'administration</a>
            </div>

        </div>
    </div>

    <style>
        /* Force typography globally */
        @import url('https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&display=swap');

        .custom-login-wrapper,
        .custom-login-wrapper * {
            font-family: 'Geist', sans-serif !important;
            box-sizing: border-box;
        }

        html,
        body {
            overflow: hidden !important;
            height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .custom-login-wrapper {
            display: flex;
            height: 100vh;
            width: 100vw;
            background-color: #fafafa;
            overflow: hidden;
        }

        /* --- Left Side --- */
        .custom-login-left {
            display: none;
            position: relative;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=1200&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }

        @media (min-width: 1024px) {
            .custom-login-left {
                display: flex;
                flex: 1;
                /* takes 50% implicitly when right is fixed/flex-1 */
            }
        }

        .custom-login-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(7, 43, 33, 0.95), rgba(16, 94, 77, 0.92));
            z-index: 10;
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
        }

        .custom-login-logo-container {
            margin-bottom: 2rem;
            position: relative;
        }

        .custom-login-logo-glow {
            position: absolute;
            inset: 0;
            background-color: #34d399;
            border-radius: 50%;
            filter: blur(20px);
            opacity: 0.35;
        }

        .custom-login-logo {
            width: 5.5rem;
            height: 5.5rem;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
            color: #105e4d;
        }

        .custom-login-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            margin: 0 0 0.5rem 0;
            color: white;
        }

        .custom-login-content h1 span {
            color: #34d399;
        }

        .custom-login-content h2 {
            font-size: 1.125rem;
            font-weight: 500;
            margin: 0 0 2rem 0;
            color: white;
        }

        .custom-login-content h2 span {
            color: #34d399;
        }

        .custom-login-divider {
            width: 3.5rem;
            height: 1px;
            background-color: rgba(255, 255, 255, 0.3);
            margin-bottom: 1.5rem;
        }

        .custom-login-desc {
            max-width: 24rem;
            font-size: 0.875rem;
            line-height: 1.75;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 3rem;
        }

        .custom-login-desc p {
            margin: 0;
        }

        .custom-login-footer {
            margin-top: auto;
            position: absolute;
            bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 0.75rem 1.25rem;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(4px);
        }

        /* --- Right Side --- */
        .custom-login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 1.5rem;
            background-color: #fafafa;
            overflow-y: auto;
            overflow-x: hidden;
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        .custom-login-right::-webkit-scrollbar {
            display: none;
        }

        .pattern-dots {
            position: absolute;
            top: 2.5rem;
            left: 2.5rem;
            width: 8rem;
            height: 8rem;
            opacity: 0.3;
            pointer-events: none;
            background-image: radial-gradient(#cbd5e1 2px, transparent 2px);
            background-size: 16px 16px;
        }

        .pattern-circles {
            position: absolute;
            bottom: -5rem;
            right: -5rem;
            width: 20rem;
            height: 20rem;
            border: 1px solid #d1fae5;
            border-radius: 50%;
            opacity: 0.5;
            pointer-events: none;
            box-shadow: inset 0 0 0 1rem rgba(209, 250, 229, 0.2);
        }

        .custom-login-card {
            width: 100%;
            max-width: 26rem;
            /* 416px, perfect for form */
            background-color: white;
            border-radius: 1.5rem;
            /* 24px */
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            padding: 2.5rem;
            position: relative;
            z-index: 10;
            text-align: center;
        }

        .custom-profile-icon {
            width: 3.5rem;
            height: 3.5rem;
            background-color: #f8fafc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            color: #105e4d;
        }

        .custom-profile-icon svg {
            width: 1.5rem;
            height: 1.5rem;
        }

        .welcome-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.025em;
        }

        .welcome-subtitle {
            font-size: 0.8125rem;
            color: #64748b;
            margin: 0 0 2rem 0;
        }

        .form-wrapper {
            text-align: left;
            width: 100%;
        }

        .submit-wrapper {
            width: 100%;
            margin-top: 1.5rem;
        }


        .footer-link {
            font-size: 0.75rem;
            color: #64748b;
        }

        .footer-link a {
            color: #105e4d;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s;
        }

        .footer-link a:hover {
            text-decoration: underline;
            color: #34d399;
        }

        /* -------------------------------------------------------------
           FILAMENT FORM OVERRIDES TO PERFECTLY MATCH THE MOCKUP
           ------------------------------------------------------------- */

        /* Hide Filament's layout limitations if injected in a container */
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

        /* Remove gap between inputs because we use our own padding */
        .fi-fo-form {
            gap: 1.75rem !important;
        }

        /* 1. Labels overlapping the border (Floating Label Effect) */
        .fi-fo-field-wrp {
            position: relative;
            margin-bottom: 0 !important;
        }

        .fi-fo-field-wrp-label {
            position: absolute;
            top: -0.5rem;
            /* pull up */
            left: 0.75rem;
            background-color: white;
            padding: 0 0.4rem;
            z-index: 5;
            display: flex;
            align-items: center;
            width: auto;
        }

        .fi-fo-field-wrp-label span {
            font-size: 0.7rem !important;
            font-weight: 500 !important;
            color: #64748b !important;
            margin: 0 !important;
            line-height: 1 !important;
        }

        /* Hide the asterisk if present, since we assign placeholder properly */
        .fi-fo-field-wrp-label sup {
            display: none !important;
        }

        /* Reset the header */
        .fi-fo-field-wrp-header {
            display: block !important;
            margin-bottom: 0 !important;
        }

        /* Forgot password link repositioning */
        .fi-fo-field-wrp-header a {
            color: #34d399 !important;
            /* Lighter emerald text */
            font-size: 0.75rem !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            position: absolute;
            right: 0;
            bottom: -28px;
            /* Positioned parallel to checkbox */
            background: rgba(255, 255, 255, 0.7);
            /* small overlay if overlapping */
            z-index: 20;
        }

        .fi-fo-field-wrp-header a:hover {
            text-decoration: underline !important;
        }

        /* 2. Input Container Styling */
        .fi-input-wrp {
            border-radius: 0.5rem !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            background-color: transparent !important;
            position: relative;
            z-index: 1;
            transition: all 0.2s;
            overflow: hidden !important;
            display: flex;
            align-items: center;
        }

        /* Inner shadow removal */
        .fi-input-wrp::before,
        .fi-input-wrp::after {
            display: none !important;
        }

        .fi-input-wrp:focus-within {
            border-color: #105e4d !important;
            box-shadow: 0 0 0 1px #105e4d !important;
        }

        /* 3. Input Text */
        .fi-input-wrp input {
            padding: 0.85rem 1rem 0.85rem 0.5rem !important;
            font-size: 0.875rem !important;
            color: #1e293b !important;
            background-color: transparent !important;
            width: 100% !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .fi-input-wrp input:focus {
            box-shadow: none !important;
            border: none !important;
            outline: none !important;
        }

        .fi-input-wrp input::placeholder {
            color: #94a3b8 !important;
            font-weight: 400 !important;
        }

        /* 4. Injection of icons based on order (User, Lock) */

        /* Username Field (1st) */
        .fi-fo-form>div:nth-child(1) .fi-input-wrp::before {
            content: "";
            display: inline-block;
            width: 1.125rem;
            height: 1.125rem;
            margin-left: 0.875rem;
            margin-right: -0.25rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
        }

        /* Password Field (2nd) */
        .fi-fo-form>div:nth-child(2) .fi-input-wrp::before {
            content: "";
            display: inline-block;
            width: 1.125rem;
            height: 1.125rem;
            margin-left: 0.875rem;
            margin-right: -0.25rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
        }

        /* 5. Button (Se connecter) */
        .fi-btn {
            background-color: #0f4c3a !important;
            color: #ffffff !important;
            border-radius: 0.5rem !important;
            padding: 0.75rem !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            border: none !important;
            box-shadow: 0 4px 6px -1px rgba(15, 76, 58, 0.1), 0 2px 4px -1px rgba(15, 76, 58, 0.06) !important;
            position: relative;
            outline: none !important;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fi-btn:hover {
            background-color: #0c3e2f !important;
            box-shadow: 0 10px 15px -3px rgba(15, 76, 58, 0.2), 0 4px 6px -2px rgba(15, 76, 58, 0.1) !important;
        }

        .fi-btn-label {
            font-family: 'Geist', sans-serif !important;
        }

        .fi-btn::after {
            content: "→";
            position: absolute;
            right: 1.25rem;
            font-size: 1.125rem;
            font-weight: 400;
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
            width: 1rem !important;
            height: 1rem !important;
            border-radius: 0.25rem !important;
            border: 1px solid #cbd5e1 !important;
            color: #105e4d !important;
            cursor: pointer;
            outline: none !important;
            box-shadow: none !important;
        }

        .fi-checkbox:checked {
            background-color: #105e4d !important;
            border-color: #105e4d !important;
        }

        .fi-checkbox-label span {
            font-size: 0.75rem !important;
            color: #64748b !important;
            font-weight: 400 !important;
            margin-left: 0.25rem;
            user-select: none;
        }

        /* Remove spacing issues on checkbox container */
        .fi-fo-form>div:nth-child(3) {
            margin-top: -0.75rem !important;
            margin-bottom: 0.25rem !important;
        }
    </style>
</div>
