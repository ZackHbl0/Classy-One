<div class="flex min-h-screen w-full bg-white">
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
        style="background-image: url('https://ecole-gestion-omnia.ma/wp-content/uploads/2025/06/A7401725-scaled.jpg'); background-size: cover; background-position: center;">

        <!-- Navy Blue Overlay exactly #1A3A5D at 80% opacity -->
        <div class="absolute inset-0 z-10" style="background-color: rgba(26, 58, 93, 0.80);"></div>

        <div class="relative z-20 flex flex-col items-center justify-center text-center px-12 w-full h-full pb-10">

            <!-- Logo: Sharp, smaller (56px), rounded corners -->
            <div class="bg-white flex items-center justify-center rounded-xl shadow-lg mb-8"
                style="width: 56px; height: 56px;">
                <span class="text-[#0F172A] font-bold uppercase tracking-widest text-[1.05rem]">OSBT</span>
            </div>

            <!-- OSBT NOTIFY: Bold and Golden Yellow -->
            <h1 class="font-bold tracking-tighter mb-5 text-[2.5rem]"
                style="color: #e6b522 !important; letter-spacing: -0.01em ; font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-size: 1.9rem; font-weight: 800;">
                OSBT NOTIFY</h1>

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


            <?php if (isset($component)) { $__componentOriginald09a0ea6d62fc9155b01d885c3fdffb3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald09a0ea6d62fc9155b01d885c3fdffb3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.form.index','data' => ['wire:submit' => 'authenticate','class' => 'w-full space-y-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:submit' => 'authenticate','class' => 'w-full space-y-5']); ?>
                <?php echo e($this->form); ?>


                <!-- Filament form action button -->
                <div class="w-full mt-2">
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
<?php /**PATH C:\xampp\htdocs\osbt-api\resources\views/filament/pages/auth/login.blade.php ENDPATH**/ ?>