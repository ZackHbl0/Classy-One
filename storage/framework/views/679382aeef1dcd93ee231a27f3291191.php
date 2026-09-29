<div class="cl-root-viewport">
<style>
/* ============================================================
   CLASSY-ONE AUTH PAGE - SPLIT SCREEN CLEAN DESIGN
============================================================ */
*, *::before, *::after {
    box-sizing: border-box;
}

html, body {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    height: 100% !important;
    overflow: hidden !important;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
    background-color: #f8fafc !important;
}

/* Screen reader text MUST be hidden */
.sr-only,
.cl-card .sr-only,
.cl-card span[class*="sr-only"] {
    display: none !important;
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border-width: 0 !important;
}

.cl-root-viewport {
    width: 100vw;
    height: 100vh;
    max-height: 100vh;
    display: flex;
    overflow: hidden;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background-color: #f8fafc;
}

/* ============================================================
   LEFT HALF - DARK FOREST TEAL OVERLAY WITH BRANDING
============================================================ */
.cl-left-half {
    width: 50%;
    min-width: 50%;
    height: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 60px 40px;
    user-select: none;
    overflow: hidden;
    background-color: #041e18;
}

.cl-left-half::before {
    content: '';
    position: absolute;
    inset: -20px;
    background-image: linear-gradient(145deg, rgba(8, 48, 38, 0.94) 0%, rgba(4, 30, 24, 0.96) 100%), url('/images/login-bg.jpg');
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    z-index: 1;
    animation: clBgPanZoom 20s infinite alternate ease-in-out;
}

.cl-brand-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    max-width: 360px;
    z-index: 2;
}

/* Glowing Graduation Cap Circle */
.cl-cap-glow-container {
    position: relative;
    margin-bottom: 22px;
}

.cl-cap-halo {
    position: absolute;
    inset: -14px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(52, 211, 153, 0.45) 0%, rgba(16, 185, 129, 0.1) 60%, transparent 75%);
    filter: blur(10px);
    pointer-events: none;
    animation: clPulseGlow 3s infinite ease-in-out;
}

.cl-cap-circle {
    position: relative;
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: radial-gradient(circle at 35% 35%, #34d399 0%, #059669 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 25px rgba(52, 211, 153, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.35);
}

.cl-cap-circle svg {
    width: 34px;
    height: 34px;
    color: #ffffff;
    stroke: #ffffff;
}

/* ClassyOne Logo Typography */
.cl-logo-text {
    font-family: 'Outfit', sans-serif;
    font-size: 42px;
    font-weight: 800;
    letter-spacing: -0.5px;
    line-height: 1.1;
    margin: 0;
    color: #ffffff;
}

.cl-logo-text span {
    color: #34d399;
}

/* Subtle Green Divider */
.cl-logo-divider {
    width: 34px;
    height: 3px;
    background: #34d399;
    border-radius: 2px;
    margin: 16px auto 18px;
    opacity: 0.85;
}

/* Subtitle lines */
.cl-tagline {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
    line-height: 1.65;
    font-weight: 400;
    margin: 0;
}

/* Bottom School Pill Badge */
.cl-school-badge {
    position: absolute;
    bottom: 32px;
    left: 50%;
    transform: translateX(-50%);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 18px;
    border-radius: 9999px;
    background: rgba(0, 0, 0, 0.38);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: rgba(255, 255, 255, 0.88);
    font-size: 12px;
    font-weight: 500;
    white-space: nowrap;
    z-index: 2;
}

.cl-school-badge svg {
    width: 15px;
    height: 15px;
    color: #34d399;
}

/* ============================================================
   RIGHT HALF - CLEAN LIGHT CARD
============================================================ */
.cl-right-half {
    width: 50%;
    min-width: 50%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 24px;
    position: relative;
    background-color: #fafbfc;
    background-image: 
        radial-gradient(circle at 85% 15%, rgba(16, 185, 129, 0.08) 0%, transparent 45%),
        radial-gradient(#e2e8f0 1.2px, transparent 1.2px);
    background-size: 100% 100%, 24px 24px;
    overflow-y: auto;
}

/* Floating White Card */
.cl-card {
    width: 100%;
    max-width: 600px;
    background: #ffffff;
    border-radius: 32px;
    padding: 100px 64px 80px;
    box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0, 0, 0, 0.02);
    border: 1px solid rgba(226, 232, 240, 0.7);
    position: relative;
    z-index: 10;
}

/* Card User Badge */
.cl-user-badge {
    width: 80px;
    height: 80px;
    border-radius: 22px;
    background: #e6f7f0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 32px;
    transform: rotate(-6deg);
}

.cl-user-badge svg {
    width: 36px;
    height: 36px;
    color: #059669;
    stroke: #059669;
    transform: rotate(6deg);
}

/* Card Header */
.cl-card-title {
    font-family: 'Outfit', sans-serif;
    font-size: 32px;
    font-weight: 800;
    color: #0f172a;
    text-align: center;
    margin: 0 0 10px 0;
    letter-spacing: -0.3px;
}

.cl-card-subtitle {
    font-size: 16px;
    color: #64748b;
    text-align: center;
    margin: 0 0 56px 0;
}

/* Security Disclaimer Footer */
.cl-card-disclaimer {
    font-size: 11.5px;
    color: #94a3b8;
    text-align: center;
    margin-top: 40px;
    line-height: 1.5;
}

/* ============================================================
   FILAMENT FORM STYLING OVERRIDES (CLEAN LIGHT THEME)
============================================================ */
.cl-card .fi-fo-field-wrp {
    margin-bottom: 32px !important;
}

.cl-card .fi-fo-field-wrp-label {
    margin-bottom: 8px !important;
    display: flex !important;
    align-items: center !important;
}

.cl-card .fi-fo-field-wrp-label span {
    font-size: 14px !important;
    font-weight: 700 !important;
    color: #334155 !important;
    letter-spacing: 0.1px !important;
}

.cl-card .fi-fo-field-wrp-label sup {
    color: #10b981 !important;
    margin-left: 2px !important;
}

/* Input Wrappers: soft grey pill */
.cl-card .fi-input-wrp {
    background-color: #f1f4f9 !important;
    border: 1.5px solid transparent !important;
    border-radius: 16px !important;
    box-shadow: none !important;
    transition: all 0.2s ease !important;
    height: 56px !important;
    padding: 0 16px !important;
    display: flex !important;
    align-items: center !important;
    overflow: hidden !important;
}

.cl-card .fi-input-wrp:focus-within {
    background-color: #ffffff !important;
    border-color: #10b981 !important;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12) !important;
}

/* Remove default vertical border separators inside input */
.cl-card .fi-input-wrp-prefix {
    border-right: 1px solid #e2e8f0 !important;
    padding: 0 10px 0 0 !important;
    margin: 0 10px 0 0 !important;
    background: transparent !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.cl-card .fi-input-wrp-suffix {
    border: none !important;
    padding: 0 !important;
    margin: 0 !important;
    background: transparent !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin-left: auto !important;
    padding-right: 4px !important;
}

/* Force the middle div containing the input to expand */
.cl-card .fi-input-wrp > div:not(.fi-input-wrp-prefix):not(.fi-input-wrp-suffix) {
    flex-grow: 1 !important;
    width: 100% !important;
    display: flex !important;
}

.cl-card .fi-input-wrp input {
    background: transparent !important;
    color: #1e293b !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    height: 100% !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    padding: 10px 8px !important;
    flex-grow: 1 !important;
    width: 100% !important;
}

.cl-card .fi-input-wrp input::placeholder {
    color: #94a3b8 !important;
    font-size: 13.5px !important;
}

/* Fix Browser Autofill Background */
.cl-card input:-webkit-autofill,
.cl-card input:-webkit-autofill:hover,
.cl-card input:-webkit-autofill:focus,
.cl-card input:-webkit-autofill:active {
    -webkit-box-shadow: 0 0 0 30px #f1f4f9 inset !important;
    -webkit-text-fill-color: #1e293b !important;
    border-radius: 8px !important;
}

/* Prefix Icons */
.cl-card .fi-input-wrp-prefix svg {
    color: #94a3b8 !important;
    width: 18px !important;
    height: 18px !important;
    margin-left: 4px !important;
}

/* CRITICAL FIX FOR PASSWORD TOGGLE ICONS: Respect Alpine's x-show display: none */
.cl-card .fi-input-wrp-suffix [style*="display: none"],
.cl-card .fi-input-wrp-suffix button[style*="display: none"],
.cl-card .fi-input-wrp-suffix [style*="display:none"],
.cl-card .fi-input-wrp-suffix [hidden],
.cl-card .fi-input-wrp-suffix [x-cloak] {
    display: none !important;
}

/* Password Reveal Toggle Button - ONLY THE ACTIVE SINGLE ICON */
.cl-card .fi-input-wrp-suffix .fi-icon-btn,
.cl-card .fi-input-wrp-suffix button {
    background: transparent !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    cursor: pointer !important;
    padding: 6px !important;
    margin: 0 !important;
    color: #94a3b8 !important;
    transition: color 0.15s ease !important;
    border-radius: 8px !important;
}

.cl-card .fi-input-wrp-suffix .fi-icon-btn:hover,
.cl-card .fi-input-wrp-suffix button:hover {
    color: #059669 !important;
    background: rgba(5, 150, 105, 0.08) !important;
}

.cl-card .fi-input-wrp-suffix svg {
    width: 18px !important;
    height: 18px !important;
    color: inherit !important;
}

/* Checkbox "Se souvenir de moi" */
.cl-card .fi-fo-checkbox {
    margin-top: 2px !important;
    margin-bottom: 40px !important;
    display: flex !important;
    align-items: center !important;
}

.cl-card .fi-fo-checkbox label {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #475569 !important;
    cursor: pointer !important;
    margin-left: 8px !important;
}

.cl-card .fi-fo-checkbox input[type="checkbox"] {
    border-radius: 6px !important;
    border-color: #cbd5e1 !important;
    color: #059669 !important;
    cursor: pointer !important;
    width: 20px !important;
    height: 20px !important;
}

.cl-card .fi-fo-checkbox input[type="checkbox"]:focus {
    ring-color: #059669 !important;
}

/* Submit Action Button */
.cl-card .fi-btn,
.cl-card button[wire\:click*="authenticate"],
.cl-card button[type="submit"] {
    background: #059669 !important;
    border-radius: 9999px !important;
    height: 56px !important;
    width: 100% !important;
    border: none !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    font-size: 16px !important;
    cursor: pointer !important;
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35) !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    margin-top: 16px !important;
}

.cl-card .fi-btn:hover,
.cl-card button[wire\:click*="authenticate"]:hover,
.cl-card button[type="submit"]:hover {
    background: #047857 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 20px rgba(5, 150, 105, 0.45) !important;
}

.cl-card .fi-btn-label {
    color: #ffffff !important;
    font-size: 15px !important;
    font-weight: 600 !important;
}

.cl-card .fi-btn svg {
    color: #ffffff !important;
    width: 18px !important;
    height: 18px !important;
    transition: transform 0.2s ease !important;
}

.cl-card .fi-btn:hover svg {
    transform: translateX(3px) !important;
}

/* ============================================================
   RESPONSIVENESS
============================================================ */
@media (max-width: 960px) {
    html, body {
        height: auto !important;
        overflow-y: auto !important;
    }
    .cl-root-viewport {
        flex-direction: column;
        height: auto;
        max-height: none;
        overflow-y: auto;
    }
    .cl-left-half {
        width: 100%;
        min-width: 100%;
        height: auto;
        padding: 48px 24px;
        min-height: 280px;
    }
    .cl-school-badge {
        position: static;
        transform: none;
        margin-top: 24px;
    }
    .cl-right-half {
        width: 100%;
        min-width: 100%;
        height: auto;
        padding: 32px 16px;
    }
    .cl-card {
        padding: 32px 24px 28px;
    }
}

/* ============================================================
   ANIMATIONS
============================================================ */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes clPulseGlow {
    0% { transform: scale(1); opacity: 0.6; }
    50% { transform: scale(1.15); opacity: 0.2; }
    100% { transform: scale(1); opacity: 0.6; }
}

@keyframes clFloat {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-8px); }
    100% { transform: translateY(0px); }
}

@keyframes clBgPanZoom {
    0% { transform: scale(1) translate(0, 0); }
    100% { transform: scale(1.08) translate(-1%, -1%); }
}

.cl-brand-wrapper {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.cl-card {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards;
    opacity: 0;
}

</style>


<div class="cl-left-half">
    <div class="cl-brand-wrapper">
        
        <div class="cl-cap-glow-container">
            <div class="cl-cap-halo"></div>
            <div class="cl-cap-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
        </div>

        
        <h1 class="cl-logo-text">Classy<span>One</span></h1>

        
        <div class="cl-logo-divider"></div>

        
        <p class="cl-tagline">
            Restez connecté à votre école.<br>
            Notifications, planning, événements —<br>
            tout en un seul endroit.
        </p>
    </div>

    
    <div class="cl-school-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-6 9 6v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            <polyline points="9,22 9,12 15,12 15,22"/>
        </svg>
        <span>OMNIA School of Business &amp; Technologies</span>
    </div>
</div>


<div class="cl-right-half">
    <div class="cl-card">
        
        <div class="cl-user-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
        </div>

        
        <h2 class="cl-card-title">Bienvenue !</h2>
        <p class="cl-card-subtitle">Connectez-vous pour accéder à votre espace</p>

        
        <input type="text" name="decoy_username" style="display:none!important;position:absolute;left:-9999px" tabindex="-1" autocomplete="off" />
        <input type="password" name="decoy_password" style="display:none!important;position:absolute;left:-9999px" tabindex="-1" autocomplete="new-password" />

        
        <?php if (isset($component)) { $__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.form.index','data' => ['wire:submit' => 'authenticate','autocomplete' => 'off']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:submit' => 'authenticate','autocomplete' => 'off']); ?>
            <?php echo e($this->form); ?>


            <div style="margin-top: 10px;">
                <?php if (isset($component)) { $__componentOriginal742ef35d02cb00943edd9ad8ebf61966 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal742ef35d02cb00943edd9ad8ebf61966 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.form.actions','data' => ['actions' => $this->getCachedFormActions(),'fullWidth' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::form.actions'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['actions' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getCachedFormActions()),'full-width' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal742ef35d02cb00943edd9ad8ebf61966)): ?>
<?php $attributes = $__attributesOriginal742ef35d02cb00943edd9ad8ebf61966; ?>
<?php unset($__attributesOriginal742ef35d02cb00943edd9ad8ebf61966); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal742ef35d02cb00943edd9ad8ebf61966)): ?>
<?php $component = $__componentOriginal742ef35d02cb00943edd9ad8ebf61966; ?>
<?php unset($__componentOriginal742ef35d02cb00943edd9ad8ebf61966); ?>
<?php endif; ?>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3)): ?>
<?php $attributes = $__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3; ?>
<?php unset($__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3)): ?>
<?php $component = $__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3; ?>
<?php unset($__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3); ?>
<?php endif; ?>

        
        <div class="cl-card-disclaimer">
            Accès sécurisé réservé au personnel et aux étudiants.
        </div>
    </div>
</div>
</div><?php /**PATH C:\laragon\www\Classy-One\resources\views/filament/pages/auth/login.blade.php ENDPATH**/ ?>