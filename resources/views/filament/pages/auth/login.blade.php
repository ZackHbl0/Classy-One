<div class="flex min-h-screen w-full bg-white relative">

    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Absolute Force override of all Typography to Geist */
        html,
        body,
        .fi-body {
            font-family: 'Geist', sans-serif !important;
            background-color: rgb(255, 255, 255) !important;
        }
    </style>

    <!-- Left Side: Visual / Background -->
    <div class="w-1/2 relative flex flex-col justify-center items-center shadow-2xl z-10 overflow-hidden"
        style="background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=1200&auto=format&fit=crop'); background-size: cover; background-position: center;">

        <!-- Deep Professional Dark Blue/Indigo Gradient Overlay -->
        <div class="absolute inset-0 z-10"
            style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 58, 138, 0.85));"></div>

        <div
            class="relative z-20 flex flex-col items-center justify-center text-center px-12 w-full h-full pb-64 lg:pb-[28rem]">

            <!-- ClassyOne: Bold and Golden Yellow -->
            <h1 class="font-bold tracking-tighter mb-5 text-[2.5rem]"
                style="color: #e6b522 !important; letter-spacing: -0.01em ; font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-size: 1.9rem; font-weight: 800;">
                ClassyOne</h1>

            <!-- Description Text: Thinner (Light) and Pure White -->
            <div class="max-w-sm mx-auto leading-relaxed space-y-1 text-[0.65rem]"
                style="font-weight: 500 !important; color: #ffffffc4 !important;font-size: 0.9rem;">
                <p>Restez connecté à votre école.</p>
                <p>Notifications, planning, événements —</p>
                <p>tout en un seul endroit.</p>
            </div>

            <!-- Footer Text: Smaller and Golden Yellow -->
            <div class="mt-16 font-small flex items-center justify-center gap-2 uppercase tracking-widest bg-white/5 py-2.5 px-2 rounded-full backdrop-blur-md text-[0.65rem]"
                style="color: #cc9a04 !important;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-4 h-4" style="color: #FFC107 !important;">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                OMNIA School of Business & Technologies
            </div>
        </div>
    </div>

    <!-- Right Side: Form -->
    <div class="w-1/2 flex flex-col items-center justify-center relative px-12 lg:px-20 min-h-screen"
        style="background-color: rgba(228, 228, 228, 0.671);">

        <div class="w-full max-w-md mx-auto">

            <div class="mb-10 text-left">

                <!-- Bienvenue #0F172A font-600 -->
                <h2 class="font-semibold tracking-tighter mb-1 text-[#0F172A] !text-[#0F172A]"
                    style="font-size: 1.6rem; color: #0F172A !important; letter-spacing: -0.05em;">Bienvenue</h2>
                <!-- Connectez-vous: cleaner, airy -->
                <p class="font-light text-slate-500 !text-slate-500"
                    style="font-size: 0.8rem; color: #64748B !important; font-weight: 300;">Connectez-vous pour accéder
                    à vos
                    notifications et planning.</p>
            </div>


            <x-filament-panels::form wire:submit="authenticate" class="w-full space-y-5">
                {{ $this->form }}

                <!-- Filament form action button -->
                <div class="w-full mt-2">
                    <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="true" />
                </div>
            </x-filament-panels::form>
        </div>
    </div>

    <style>
        /* Typography overrides */
        .fi-fo-field-wrp-label span {
            font-weight: 400 !important;
            color: #475569 !important;
            /* Dark Gray */
            font-size: 0.8rem !important;
            letter-spacing: 0.01em;
            font-family: 'Geist', sans-serif !important;
        }

        /* Inputs slimmer and subtle */
        .fi-input-wrp {
            border-radius: 8px !important;
            border: 1px solid #E2E8F0 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02) !important;
            background-color: #FFFFFF !important;
        }

        .fi-input-wrp:focus-within {
            border-color: #0F172A !important;
            box-shadow: 0 0 0 1px #0F172A !important;
        }

        /* Input padding reduction */
        .fi-input-wrp input {
            padding-top: 0.45rem !important;
            padding-bottom: 0.45rem !important;
            font-size: 0.8rem !important;
            color: #0F172A !important;
            font-weight: 400 !important;
            font-family: 'Geist', sans-serif !important;
        }

        .fi-input-wrp svg {
            color: #94A3B8 !important;
            width: 1.1rem !important;
            height: 1.1rem !important;
        }

        /* Button exact width and style */
        .fi-btn {
            background-color: #0F172A !important;
            color: #FFFFFF !important;
            border-radius: 8px !important;
            padding-top: 0.6rem !important;
            padding-bottom: 0.6rem !important;
            font-weight: 600 !important;
            font-size: 0.8rem !important;
            letter-spacing: 0.02em;
            box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.1) !important;
            width: 100% !important;
            justify-content: center !important;
            border: none !important;
            font-family: 'Geist', sans-serif !important;
        }

        .fi-btn:hover {
            background-color: #1E293B !important;
        }

        /* Ensure form actions are 100% width without margins */
        .fi-form-actions {
            width: 100% !important;
            margin: 0 !important;
        }

        .fi-form-actions>* {
            width: 100% !important;
            margin: 0 !important;
        }

        /* Checkbox size */
        .fi-checkbox {
            width: 0.9rem !important;
            height: 0.9rem !important;
            border-radius: 4px !important;
            border-color: #CBD5E1 !important;
        }

        .fi-checkbox:checked {
            background-color: #0F172A !important;
            border-color: #0F172A !important;
        }
    </style>
</div>