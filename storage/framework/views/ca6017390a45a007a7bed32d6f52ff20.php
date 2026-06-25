<div>
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <!-- En-tête -->
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 4rem; height: 4rem; border-radius: 9999px; background-color: rgba(99, 102, 241, 0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.5rem; flex-shrink: 0;">
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-user-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['style' => 'width: 2rem; height: 2rem;']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
            </div>
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--chat-text); margin: 0;"><?php echo e($group->name); ?></h2>
                <p style="font-size: 0.875rem; color: var(--chat-text-muted); margin: 0.25rem 0 0 0;">
                    Créé le <?php echo e($group->created_at->format('d/m/Y')); ?>

                </p>
            </div>
        </div>

        <!-- Statistiques -->
        <?php
            $professors = $group->users;
            $students = $group->students;
            $totalMembers = $professors->count() + $students->count();
            $admin = $professors->first(); // On suppose que le premier professeur est l'admin
        ?>

        <div style="display: flex; gap: 1rem; margin-top: 0.5rem; flex-wrap: wrap;">
            <div style="background-color: var(--chat-sidebar-bg); border: 1px solid var(--chat-border); padding: 0.75rem 1rem; border-radius: 0.5rem; flex: 1; min-width: 120px;">
                <div style="font-size: 0.75rem; color: var(--chat-text-muted); font-weight: 600; text-transform: uppercase;">Participants</div>
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--chat-text); margin-top: 0.25rem;"><?php echo e($totalMembers); ?></div>
            </div>
            <div style="background-color: var(--chat-sidebar-bg); border: 1px solid var(--chat-border); padding: 0.75rem 1rem; border-radius: 0.5rem; flex: 1; min-width: 120px;">
                <div style="font-size: 0.75rem; color: var(--chat-text-muted); font-weight: 600; text-transform: uppercase;">Administrateur</div>
                <div style="font-size: 1rem; font-weight: 600; color: var(--chat-text); margin-top: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo e($admin ? $admin->name : 'Inconnu'); ?></div>
            </div>
        </div>

        <!-- Liste des membres -->
        <div style="margin-top: 1rem;">
            <h3 style="font-size: 1rem; font-weight: 600; color: var(--chat-text); margin-bottom: 0.75rem;">Membres du groupe</h3>
            
            <div style="max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem; padding-right: 0.5rem;">
                <!-- Professeurs -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $professors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prof): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background-color: var(--chat-sidebar-bg); border-radius: 0.5rem; border: 1px solid var(--chat-border);">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 2.25rem; height: 2.25rem; border-radius: 9999px; background-color: #10b981; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem;">
                                <?php echo e(strtoupper(substr($prof->name, 0, 1))); ?>

                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.9375rem; color: var(--chat-text);"><?php echo e($prof->name); ?></div>
                                <div style="font-size: 0.75rem; color: var(--chat-text-muted);">Professeur</div>
                            </div>
                        </div>
                        <span style="background-color: rgba(16, 185, 129, 0.1); color: #10b981; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.625rem; font-weight: 600;">ADMIN</span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- Étudiants -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background-color: var(--chat-sidebar-bg); border-radius: 0.5rem; border: 1px solid var(--chat-border);">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 2.25rem; height: 2.25rem; border-radius: 9999px; background-color: var(--chat-avatar-bg); color: var(--chat-text-muted); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem;">
                                <?php echo e(strtoupper(substr($student->nom, 0, 1))); ?>

                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.9375rem; color: var(--chat-text);"><?php echo e($student->nom); ?> <?php echo e($student->prenom); ?></div>
                                <div style="font-size: 0.75rem; color: var(--chat-text-muted);">Étudiant</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Classy-One\resources\views/filament/chat/group-info.blade.php ENDPATH**/ ?>