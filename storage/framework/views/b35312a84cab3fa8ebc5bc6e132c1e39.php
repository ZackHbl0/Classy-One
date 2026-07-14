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
    <style>
        /* ── Page Layout ── */
        .notes-page-wrapper {
            display: flex;
            gap: 1.5rem;
            min-height: calc(100vh - 12rem);
        }

        /* ── Student Sidebar ── */
        .notes-student-sidebar {
            width: 18rem;
            flex-shrink: 0;
            background: #fff;
            border-radius: 1rem;
            border: 1px solid #f1f5f9;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .notes-sidebar-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .notes-sidebar-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }
        .notes-sidebar-search {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            color: #334155;
            outline: none;
            transition: border-color 0.15s;
        }
        .notes-sidebar-search:focus {
            border-color: #0f4c3a;
        }
        .notes-student-list {
            overflow-y: auto;
            flex: 1;
        }
        .notes-student-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            cursor: pointer;
            border-left: 3px solid transparent;
            transition: all 0.15s;
        }
        .notes-student-item:hover {
            background: #f8fafc;
        }
        .notes-student-item.active {
            background: #f0fdf4;
            border-left-color: #0f4c3a;
        }
        .student-avatar {
            width: 2.25rem;
            height: 2.25rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }
        .student-info-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.2;
        }
        .student-info-mat {
            font-size: 0.6875rem;
            color: #94a3b8;
        }

        /* ── Main Content ── */
        .notes-content {
            flex: 1;
            min-width: 0;
        }
        .notes-content-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .notes-content-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
        }
        .notes-content-subtitle {
            font-size: 0.8125rem;
            color: #64748b;
            margin-top: 0.125rem;
        }
        .notes-breadcrumb {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-bottom: 0.5rem;
        }
        .notes-breadcrumb span { color: #0f4c3a; }

        .notes-actions-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-add-grade {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: #0f4c3a;
            color: white;
            font-size: 0.8125rem;
            font-weight: 600;
            padding: 0.5rem 1.125rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-add-grade:hover { background: #0c3e2f; }

        .grade-search-input {
            padding: 0.45rem 1rem;
            font-size: 0.8125rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            outline: none;
            color: #334155;
            width: 14rem;
        }
        .grade-search-input:focus { border-color: #0f4c3a; }

        /* ── Grade Cards Grid ── */
        .grade-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }
        @media (max-width: 1280px) { .grade-cards-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px)  { .grade-cards-grid { grid-template-columns: 1fr; } }

        .grade-card {
            background: #fff;
            border-radius: 1rem;
            border: 1px solid #f1f5f9;
            padding: 1.25rem;
            position: relative;
            transition: box-shadow 0.2s;
        }
        .grade-card:hover { box-shadow: 0 8px 20px -4px rgba(0,0,0,0.06); }

        .grade-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        .grade-card-student {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }
        .grade-card-avatar {
            width: 2.25rem;
            height: 2.25rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }
        .grade-card-name {
            font-size: 0.8125rem;
            font-weight: 700;
            color: #1e293b;
        }
        .grade-card-mat {
            font-size: 0.6875rem;
            color: #94a3b8;
        }
        .grade-card-note-block {
            text-align: right;
        }
        .grade-card-note-value {
            font-size: 1.125rem;
            font-weight: 800;
        }
        .grade-card-note-denom {
            font-size: 0.75rem;
            font-weight: 500;
            color: #94a3b8;
        }
        .grade-card-appreciation {
            display: inline-block;
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 0.1rem 0.5rem;
            border-radius: 0.375rem;
            margin-top: 0.125rem;
        }

        .grade-card-subject {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }
        .grade-card-subject svg {
            width: 1rem;
            height: 1rem;
            color: #0f4c3a;
        }

        .grade-card-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.375rem;
            margin-bottom: 0.75rem;
        }
        .tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
        }
        .tag-type   { background: #fef3c7; color: #92400e; }
        .tag-class  { background: #e0f2fe; color: #0369a1; }
        .tag-sem    { background: #ede9fe; color: #6d28d9; }

        .grade-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid #f1f5f9;
            padding-top: 0.75rem;
        }
        .grade-card-date {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
            color: #94a3b8;
        }
        .grade-card-date svg { width: 0.875rem; height: 0.875rem; }
        .grade-card-actions {
            display: flex;
            gap: 0.5rem;
        }
        .grade-action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.375rem;
            line-height: 1;
            transition: background 0.15s;
        }
        .grade-action-btn:hover { background: #f1f5f9; }
        .grade-action-btn svg { width: 1rem; height: 1rem; }
        .btn-view { color: #6366f1; }
        .btn-edit { color: #0ea5e9; }
        .btn-del  { color: #ef4444; }
        .btn-del:hover { background: #fef2f2; }

        /* ── Pagination bar ── */
        .grade-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1.5rem;
            font-size: 0.8125rem;
            color: #64748b;
        }

        /* ── Empty State ── */
        .notes-empty {
            text-align: center;
            padding: 4rem 2rem;
            color: #94a3b8;
        }
        .notes-empty svg { width: 3rem; height: 3rem; margin: 0 auto 1rem; }
        .notes-empty h3 { font-size: 1rem; font-weight: 600; color: #64748b; margin-bottom: 0.25rem; }

        /* ── Slide-in Form Panel ── */
        .notes-form-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            z-index: 50;
            display: flex;
            align-items: flex-start;
            justify-content: flex-end;
        }
        .notes-form-panel {
            background: white;
            width: 28rem;
            max-width: 100vw;
            height: 100vh;
            overflow-y: auto;
            padding: 2rem 1.75rem;
            box-shadow: -4px 0 24px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            animation: slideIn 0.2s ease;
        }
        @keyframes slideIn { from { transform: translateX(100%); } to { transform: translateX(0); } }
        .form-panel-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .form-panel-close {
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: 0.25rem;
            border-radius: 0.375rem;
        }
        .form-panel-close:hover { background: #f1f5f9; color: #64748b; }
        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.375rem;
        }
        .form-group .form-control {
            width: 100%;
            padding: 0.55rem 0.875rem;
            font-size: 0.875rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            color: #1e293b;
            outline: none;
            transition: border-color 0.15s;
            background: white;
        }
        .form-group .form-control:focus { border-color: #0f4c3a; }
        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .btn-save {
            background: #0f4c3a;
            color: white;
            width: 100%;
            padding: 0.7rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
            margin-top: auto;
        }
        .btn-save:hover { background: #0c3e2f; }
    </style>

    <?php
        $students        = $this->getStudents();
        $grades          = $this->getGrades();
        $selectedStudent = $this->getSelectedStudent();
        $courses         = $this->getProfessorCourses();
        $gradeTypes      = ['Contrôle 1','Contrôle 2','Examen Final','Examen Blanc','Devoir','TP','Projet'];
    ?>

    <div class="notes-page-wrapper">

        
        <div class="notes-student-sidebar">
            <div class="notes-sidebar-header">
                <div class="notes-sidebar-title">Mes Étudiants</div>
                <input wire:model.live.debounce.300ms="search"
                       type="text"
                       placeholder="Rechercher..."
                       class="notes-sidebar-search" />
            </div>
            <div class="notes-student-list">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="notes-student-item <?php echo e($selectedStudentId == $student->idStudent ? 'active' : ''); ?>"
                         wire:click="selectStudent(<?php echo e($student->idStudent); ?>)">
                        <div class="student-avatar"><?php echo e($this->initials($student)); ?></div>
                        <div>
                            <div class="student-info-name"><?php echo e($student->nom); ?> <?php echo e($student->prenom); ?></div>
                            <div class="student-info-mat"><?php echo e($student->matricule); ?></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div style="padding: 1.5rem; text-align: center; font-size: 0.8125rem; color: #94a3b8;">
                        Aucun étudiant trouvé
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="notes-content">

            
            <div class="notes-content-header">
                <div>
                    <div class="notes-breadcrumb">Notes <span>&rsaquo; Liste</span></div>
                    <div class="notes-content-title">Notes des Étudiants</div>
                    <div class="notes-content-subtitle">Notes des étudiants dans vos classes</div>
                </div>
                <div class="notes-actions-bar">
                    <input wire:model.live.debounce.300ms="gradeSearch"
                           type="text"
                           placeholder="Rechercher une note..."
                           class="grade-search-input" />
                    <button wire:click="openAddForm" class="btn-add-grade">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:1rem;height:1rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ajouter une Note
                    </button>
                </div>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($grades->isEmpty()): ?>
                <div class="notes-empty">
                    <svg fill="none" viewBox="0 0 24 24" stroke="#cbd5e1" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                    </svg>
                    <h3>Aucune note pour cet étudiant</h3>
                    <p>Cliquez sur "+ Ajouter une Note" pour commencer.</p>
                </div>
            <?php else: ?>
                <div class="grade-cards-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $grades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $noteColor  = $this->gradeColor((float)$grade->note);
                            $noteLabel  = $this->gradeLabel((float)$grade->note);
                            $initials   = $this->initials($grade->student);
                            $bgAlpha    = $noteColor . '1a'; {{-- ~10% opacity bg --}}
                        ?>
                        <div class="grade-card">
                            
                            <div class="grade-card-top">
                                <div class="grade-card-student">
                                    <div class="grade-card-avatar"><?php echo e($initials); ?></div>
                                    <div>
                                        <div class="grade-card-name">
                                            <?php echo e($grade->student?->nom); ?> <?php echo e($grade->student?->prenom); ?>

                                        </div>
                                        <div class="grade-card-mat"><?php echo e($grade->student?->matricule); ?></div>
                                    </div>
                                </div>
                                <div class="grade-card-note-block">
                                    <div class="grade-card-note-value" style="color: <?php echo e($noteColor); ?>">
                                        <?php echo e(number_format((float)$grade->note, 2)); ?>

                                        <span class="grade-card-note-denom">/ 20</span>
                                    </div>
                                    <div class="grade-card-appreciation"
                                         style="background: <?php echo e($noteColor); ?>22; color: <?php echo e($noteColor); ?>">
                                        <?php echo e($noteLabel); ?>

                                    </div>
                                </div>
                            </div>

                            
                            <div class="grade-card-subject">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <?php echo e($grade->course?->title ?? $grade->subject_name ?? '—'); ?>

                            </div>

                            
                            <div class="grade-card-tags">
                                <span class="tag tag-type">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:.625rem;height:.625rem;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <?php echo e($grade->type); ?>

                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($grade->classe?->nomClasse): ?>
                                    <span class="tag tag-class"><?php echo e($grade->classe->nomClasse); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($grade->semester): ?>
                                    <span class="tag tag-sem"><?php echo e($grade->semester); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            
                            <div class="grade-card-footer">
                                <div class="grade-card-date">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
                                    </svg>
                                    <?php echo e($grade->exam_date ? $grade->exam_date->format('d/m/Y') : '—'); ?>

                                </div>
                                <div class="grade-card-actions">
                                    <button class="grade-action-btn btn-edit"
                                            wire:click="openEditForm(<?php echo e($grade->id); ?>)"
                                            title="Modifier">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                        </svg>
                                    </button>
                                    <button class="grade-action-btn btn-del"
                                            wire:click="deleteGrade(<?php echo e($grade->id); ?>)"
                                            wire:confirm="Supprimer cette note ?"
                                            title="Supprimer">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div class="grade-pagination">
                    <span>Showing 1 to <?php echo e($grades->count()); ?> of <?php echo e($grades->count()); ?> results</span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAddForm || $showEditForm): ?>
        <div class="notes-form-overlay" wire:click.self="closeForm">
            <div class="notes-form-panel">
                <div class="form-panel-title">
                    <span><?php echo e($showEditForm ? 'Modifier la Note' : 'Ajouter une Note'); ?></span>
                    <button class="form-panel-close" wire:click="closeForm">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:1.25rem;height:1.25rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedStudent): ?>
                <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem;background:#f8fafc;border-radius:.75rem;border:1px solid #f1f5f9;">
                    <div class="student-avatar" style="width:2.5rem;height:2.5rem;"><?php echo e($this->initials($selectedStudent)); ?></div>
                    <div>
                        <div style="font-weight:700;font-size:.875rem;color:#1e293b;"><?php echo e($selectedStudent->nom); ?> <?php echo e($selectedStudent->prenom); ?></div>
                        <div style="font-size:.75rem;color:#94a3b8;"><?php echo e($selectedStudent->matricule); ?></div>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="form-group">
                    <label>Matière / Cours *</label>
                    <select wire:model="formCourseId" class="form-control">
                        <option value="">-- Sélectionner --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($course->id); ?>"><?php echo e($course->title); ?> (<?php echo e($course->classe?->nomClasse); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Note (0–20) *</label>
                        <input wire:model="formNote" type="number" min="0" max="20" step="0.25" class="form-control" placeholder="Ex: 14.5" />
                    </div>
                    <div class="form-group">
                        <label>Type d'Évaluation *</label>
                        <select wire:model="formType" class="form-control">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $gradeTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type); ?>"><?php echo e($type); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Date de l'Examen *</label>
                        <input wire:model="formExamDate" type="date" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Semestre *</label>
                        <select wire:model="formSemester" class="form-control">
                            <option value="S1">Semestre 1</option>
                            <option value="S2">Semestre 2</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Commentaire (facultatif)</label>
                    <textarea wire:model="formComment" rows="3" class="form-control"
                              placeholder="Observations sur la performance..."></textarea>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEditForm): ?>
                    <button wire:click="updateGrade" class="btn-save">💾 Mettre à jour</button>
                <?php else: ?>
                    <button wire:click="saveGrade" class="btn-save">✅ Enregistrer la Note</button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
<?php /**PATH C:\laragon\www\Classy-One\resources\views/filament/pages/notes-card-page.blade.php ENDPATH**/ ?>