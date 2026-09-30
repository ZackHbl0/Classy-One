<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <form wire:submit.prevent="register" autocomplete="off" x-data x-init="
        const clearAutofill = () => {
            const inputs = $el.querySelectorAll('input');
            inputs.forEach(input => {
                if (input.type === 'password' || (input.id && input.id.includes('password')) || (input.name && input.name.includes('password'))) {
                    if (input.value === '12345678') {
                        input.value = '';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
                if ((input.id && input.id.includes('numero_tuteur')) || (input.name && input.name.includes('numero_tuteur')) || (input.placeholder && input.placeholder.includes('tuteur'))) {
                    if (input.value.includes('@') || input.value === 'admin@osbt.com') {
                        input.value = '';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            });
        };
        setTimeout(clearAutofill, 50);
        setTimeout(clearAutofill, 200);
        setTimeout(clearAutofill, 600);
    ">
        <!-- Hidden traps to intercept aggressive browser credential autofill -->
        <input type="text" name="chrome_prevent_autofill_user" style="position: absolute; opacity: 0; height: 0; width: 0; z-index: -1;" tabindex="-1" autocomplete="off" />
        <input type="password" name="chrome_prevent_autofill_pwd" style="position: absolute; opacity: 0; height: 0; width: 0; z-index: -1;" tabindex="-1" autocomplete="new-password" />
        <?php echo e($this->form); ?>


        <div class="mt-8 flex justify-end">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->getFormActions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo e($action); ?>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </form>

    <style>
        /* Green Asterisk */
        .fi-fo-field-wrp-label sup {
            color: #10b981 !important; /* Emerald 500 */
        }
        
        /* Section Styles */
        html:not(.dark) .custom-section-student, html:not(.dark) .custom-section-class {
            background-color: #ffffff !important;
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.05) !important;
        }
        .dark .custom-section-student, .dark .custom-section-class {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        .custom-section-student, .custom-section-class {
            border-radius: 1rem !important;
            border: none !important;
            margin-bottom: 1.5rem;
        }

        /* Section Header Container */
        .custom-section-student .fi-section-header, .custom-section-class .fi-section-header {
            padding: 1.5rem !important;
        }
        html:not(.dark) .custom-section-student .fi-section-header, html:not(.dark) .custom-section-class .fi-section-header {
            border-bottom: 1px solid #f1f5f9;
        }
        .dark .custom-section-student .fi-section-header, .dark .custom-section-class .fi-section-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Section Icons Backgrounds */
        .custom-section-student .fi-section-header-icon {
            padding: 0.6rem;
            border-radius: 50%;
            width: 3rem;
            height: 3rem;
        }
        html:not(.dark) .custom-section-student .fi-section-header-icon {
            background-color: #e0f2fe; /* Light Blue/Teal */
            color: #0284c7;
        }
        .dark .custom-section-student .fi-section-header-icon {
            background-color: rgba(2, 132, 199, 0.2); 
            color: #38bdf8;
        }

        .custom-section-class .fi-section-header-icon {
            padding: 0.6rem;
            border-radius: 50%;
            width: 3rem;
            height: 3rem;
        }
        html:not(.dark) .custom-section-class .fi-section-header-icon {
            background-color: #dcfce7; /* Light Green */
            color: #16a34a;
        }
        .dark .custom-section-class .fi-section-header-icon {
            background-color: rgba(22, 163, 74, 0.2);
            color: #4ade80;
        }

        /* Custom Input Styling (Optional, Filament already does it, but we make sure of the roundedness) */
        .fi-fo-text-input, .fi-fo-select {
            border-radius: 0.75rem !important;
        }

        /* Custom Submit Button */
        .custom-submit-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #10b981 100%) !important;
            border: none !important;
            border-radius: 99px !important; /* Pill shape */
            padding-left: 2rem !important;
            padding-right: 2rem !important;
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3) !important;
            transition: all 0.3s ease !important;
            font-weight: 600 !important;
        }
        .custom-submit-btn:hover {
            box-shadow: 0 15px 25px -5px rgba(16, 185, 129, 0.4) !important;
            transform: translateY(-2px);
        }
        .custom-submit-btn .fi-btn-label {
            font-size: 1rem !important;
        }
        
        /* Add right arrow to button using CSS pseudo-element since Filament actions have one icon slot */
        .custom-submit-btn::after {
            content: "→";
            margin-left: 0.75rem;
            font-size: 1.25rem;
            font-family: system-ui, -apple-system, sans-serif;
            font-weight: bold;
        }
    </style>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\Classy-One\resources\views/filament/pages/inscription.blade.php ENDPATH**/ ?>