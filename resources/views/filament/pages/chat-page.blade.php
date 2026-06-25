<x-filament-panels::page>
    <style>
        /* Hide the Filament page header completely to maximize chat space */
        header.fi-header {
            display: none !important;
        }
        
        /* CSS Variables for Light/Dark Mode (Bypasses Tailwind JIT issues) */
        :root {
            --chat-bg: #fafbfc;
            --chat-sidebar-bg: #ffffff;
            --chat-border: #f3f4f6;
            --chat-text: #111827;
            --chat-text-muted: #6b7280;
            --chat-avatar-bg: #f3f4f6;
            --chat-search-bg: #f9fafb;
            --chat-hover: #f9fafb;
            --chat-selected-bg: #f5f3ff;
            --chat-msg-sent-bg: #eef2ff;
            --chat-msg-sent-text: #4338ca;
            --chat-msg-recv-bg: #ffffff;
            --chat-msg-recv-text: #374151;
            --chat-input-bg: #f9fafb;
            --chat-header-bg: rgba(255, 255, 255, 0.9);
        }

        .dark {
            --chat-bg: #0f172a; /* Slate 900 */
            --chat-sidebar-bg: #1e293b; /* Slate 800 */
            --chat-border: #334155; /* Slate 700 */
            --chat-text: #f8fafc; /* Slate 50 */
            --chat-text-muted: #94a3b8; /* Slate 400 */
            --chat-avatar-bg: #334155; /* Slate 700 */
            --chat-search-bg: #0f172a; /* Slate 900 */
            --chat-hover: #334155; /* Slate 700 */
            --chat-selected-bg: rgba(99, 102, 241, 0.15); /* Indigo tinted */
            --chat-msg-sent-bg: rgba(79, 70, 229, 0.3); /* Indigo tinted */
            --chat-msg-sent-text: #c7d2fe; /* Indigo 200 */
            --chat-msg-recv-bg: #334155; /* Slate 700 */
            --chat-msg-recv-text: #f1f5f9; /* Slate 100 */
            --chat-input-bg: #0f172a; /* Slate 900 */
            --chat-header-bg: rgba(30, 41, 59, 0.9); /* Slate 800 transparent */
        }

        /* Custom scrollbar for chat */
        .chat-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .chat-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .chat-scroll::-webkit-scrollbar-thumb {
            background-color: var(--chat-border);
            border-radius: 20px;
        }

        /* Hover states using CSS classes */
        .chat-student-row {
            transition: all 0.2s;
        }
        .chat-student-row:hover {
            background-color: var(--chat-hover);
        }

        @keyframes pulse-opacity {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        .animate-pulse-opacity {
            animation: pulse-opacity 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>

    <div style="height: 85vh; display: flex; background-color: var(--chat-bg); border-radius: 1rem; overflow: hidden; border: 1px solid var(--chat-border);" x-data="chatApp()">
        
        <!-- Sidebar (Students List) -->
        <div style="width: 350px; display: flex; flex-direction: column; background-color: var(--chat-sidebar-bg); border-right: 1px solid var(--chat-border);">
            
            <!-- Professor Profile Section -->
            <div style="padding: 2rem 1.5rem 1.5rem 1.5rem; display: flex; flex-direction: column; align-items: center; border-bottom: 1px solid var(--chat-border);">
                <!-- Avatar -->
                <div style="width: 5rem; height: 5rem; border-radius: 9999px; background-color: rgba(99, 102, 241, 0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 1.875rem; font-weight: 700; margin-bottom: 1rem;">
                    <span x-text="authUserName.charAt(0).toUpperCase()"></span>
                </div>
                <!-- Name -->
                <h2 style="font-weight: 700; font-size: 1.125rem; margin: 0; color: var(--chat-text);" x-text="authUserName"></h2>
                <div style="display: flex; align-items: center; margin-top: 0.5rem; gap: 0.375rem;">
                    <div style="width: 0.5rem; height: 0.5rem; background-color: #10b981; border-radius: 9999px;"></div>
                    <span style="font-size: 0.75rem; color: #10b981; font-weight: 500;">En ligne</span>
                </div>
            </div>

            <!-- Search Bar -->
            <div style="padding: 1rem 1.5rem;">
                <div style="display: flex; align-items: center; width: 100%; background-color: var(--chat-search-bg); border: 1px solid var(--chat-border); border-radius: 9999px; overflow: hidden; transition: all 0.2s;">
                    <div style="display: flex; align-items: center; justify-content: center; padding-left: 1rem; padding-right: 0.5rem;">
                        <x-heroicon-o-magnifying-glass style="width: 1.25rem; height: 1.25rem; color: var(--chat-text-muted);" />
                    </div>
                    <input type="text" x-model="searchQuery" placeholder="Rechercher un étudiant..." 
                           style="width: 100%; background: transparent; border: none; padding: 0.625rem 1rem 0.625rem 0; color: var(--chat-text); font-size: 0.875rem; outline: none; box-shadow: none;">
                </div>
            </div>

            <div style="padding: 0 1.5rem 0.75rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--chat-text-muted); letter-spacing: 0.05em;">Dernières discussions</h3>
                <button type="button" wire:click="mountAction('createGroup')" style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2); color: #6366f1; border-radius: 0.5rem; padding: 0.25rem 0.5rem; cursor: pointer; display: flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; font-weight: 600; transition: all 0.2s;">
                    <x-heroicon-o-plus style="width: 0.875rem; height: 0.875rem;" /> Nouveau groupe
                </button>
            </div>

            <!-- Contacts List (Scrollable) -->
            <div style="flex: 1; overflow-y: auto; display: flex; flex-direction: column;" class="chat-scroll">
                
                <!-- Groups Section -->
                <template x-if="filteredStudents.filter(s => s.role === 'group').length > 0">
                    <div style="flex-shrink: 0;">
                        <div style="padding: 0.5rem 1.5rem 0.5rem 1.5rem;">
                            <h3 style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--chat-text-muted); letter-spacing: 0.05em;">Groupes</h3>
                        </div>
                        <template x-for="student in filteredStudents.filter(s => s.role === 'group')" :key="'group_' + student.id">
                            <div 
                                @click="selectStudent(student)"
                                :style="selectedStudent?.id === student.id && selectedStudent?.role === student.role
                                    ? `padding: 0.875rem 1.5rem; cursor: pointer; display: flex; flex-direction: row; align-items: center; background-color: var(--chat-selected-bg); border-right: 4px solid #6366f1;` 
                                    : `padding: 0.875rem 1.5rem; cursor: pointer; display: flex; flex-direction: row; align-items: center; background-color: transparent; border-right: 4px solid transparent;`"
                                class="chat-student-row">
                                <!-- Group Avatar -->
                                <div style="width: 2.75rem; height: 2.75rem; border-radius: 9999px; background-color: rgba(99, 102, 241, 0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.25rem; flex-shrink: 0;">
                                    <x-heroicon-o-user-group style="width: 1.25rem; height: 1.25rem;" />
                                </div>
                                <!-- Text Wrapper -->
                                <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; padding-left: 1rem;">
                                    <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--chat-text);" x-text="student.name"></h4>
                                    <span style="font-size: 0.75rem; font-weight: 500; display: flex; align-items: center; gap: 0.25rem; color: var(--chat-text-muted);">
                                        Groupe de discussion
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Students Section Header -->
                <div style="flex-shrink: 0; padding: 1rem 1.5rem 0.5rem 1.5rem; border-top: 1px solid var(--chat-border);" x-show="filteredStudents.filter(s => s.role === 'group').length > 0">
                    <h3 style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--chat-text-muted); letter-spacing: 0.05em;">Étudiants</h3>
                </div>

                <!-- Students Section List -->
                <div style="flex-shrink: 0;">
                    <template x-for="student in filteredStudents.filter(s => s.role !== 'group')" :key="student.role + '_' + student.id">
                        <div 
                            @click="selectStudent(student)"
                            :style="selectedStudent?.id === student.id && selectedStudent?.role === student.role
                                ? `padding: 0.875rem 1.5rem; cursor: pointer; display: flex; flex-direction: row; align-items: center; background-color: var(--chat-selected-bg); border-right: 4px solid #6366f1;` 
                                : `padding: 0.875rem 1.5rem; cursor: pointer; display: flex; flex-direction: row; align-items: center; background-color: transparent; border-right: 4px solid transparent;`"
                            class="chat-student-row">
                            <!-- Student Avatar -->
                            <div style="width: 2.75rem; height: 2.75rem; border-radius: 9999px; background-color: var(--chat-avatar-bg); color: var(--chat-text-muted); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1rem; flex-shrink: 0;">
                                <span x-text="student.name.charAt(0).toUpperCase()"></span>
                            </div>
                            <!-- Text Wrapper -->
                            <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; padding-left: 1rem;">
                                <h4 style="font-weight: 600; font-size: 0.9375rem; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--chat-text);" x-text="student.name"></h4>
                                <span style="font-size: 0.75rem; font-weight: 500; display: flex; align-items: center; gap: 0.25rem;" :style="student.is_online ? 'color: #10b981;' : 'color: var(--chat-text-muted);'">
                                    <div style="width: 0.375rem; height: 0.375rem; border-radius: 9999px;" :style="student.is_online ? 'background-color: #10b981;' : 'background-color: var(--chat-text-muted);'"></div>
                                    <span x-text="student.is_online ? 'En ligne' : (student.last_seen_diff === 'Jamais connecté' ? 'Jamais connecté' : 'En ligne ' + student.last_seen_diff)"></span>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>

                <template x-if="filteredStudents.filter(s => s.role !== 'group').length === 0 && !loadingStudents">
                    <div style="padding: 2rem 1.5rem; text-align: center; color: var(--chat-text-muted); font-size: 0.875rem;">
                        Aucun étudiant trouvé
                    </div>
                </template>
                <template x-if="loadingStudents">
                    <div style="padding: 2rem 1.5rem; text-align: center; color: var(--chat-text-muted); font-size: 0.875rem;">
                        Chargement...
                    </div>
                </template>
            </div>
        </div>

        <!-- Chat Area -->
        <div style="flex: 1; display: flex; flex-direction: column; background-color: var(--chat-bg);">
            <template x-if="!selectedStudent">
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--chat-text-muted);">
                    <div style="width: 5rem; height: 5rem; border-radius: 9999px; background-color: var(--chat-sidebar-bg); display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; border: 1px solid var(--chat-border);">
                        <x-heroicon-o-chat-bubble-oval-left-ellipsis style="width: 2.5rem; height: 2.5rem; color: var(--chat-text-muted);" />
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--chat-text); margin-bottom: 0.5rem;">Vos messages</h3>
                    <p style="font-size: 0.875rem;">Sélectionnez un étudiant dans la liste pour commencer la discussion.</p>
                </div>
            </template>

            <template x-if="selectedStudent">
                <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden; position: relative;">
                    <!-- Chat Header -->
                    <div style="padding: 1.25rem 2rem; border-bottom: 1px solid var(--chat-border); background-color: var(--chat-header-bg); backdrop-filter: blur(8px); display: flex; align-items: center; gap: 1rem; position: sticky; top: 0; z-index: 10;">
                        <div style="width: 3rem; height: 3rem; border-radius: 9999px; background-color: var(--chat-avatar-bg); color: var(--chat-text-muted); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.125rem; flex-shrink: 0;">
                            <span x-text="selectedStudent.name.charAt(0).toUpperCase()"></span>
                        </div>
                        <div 
                            :style="selectedStudent.role === 'group' ? 'cursor: pointer; padding: 0.25rem 0.5rem; margin-left: -0.5rem; border-radius: 0.5rem; transition: background-color 0.2s;' : ''"
                            @click="if(selectedStudent.role === 'group') { $wire.mountAction('groupInfo', { id: selectedStudent.id }) }"
                            @mouseover="$event.currentTarget.style.backgroundColor = selectedStudent.role === 'group' ? 'var(--chat-sidebar-bg)' : ''"
                            @mouseout="$event.currentTarget.style.backgroundColor = 'transparent'"
                        >
                            <h3 style="font-weight: 700; font-size: 1.125rem; margin: 0; color: var(--chat-text); display: flex; align-items: center; gap: 0.5rem;">
                                <span x-text="selectedStudent.name"></span>
                                <template x-if="selectedStudent.role === 'group'">
                                    <x-heroicon-o-information-circle style="width: 1.25rem; height: 1.25rem; color: var(--chat-text-muted);" />
                                </template>
                            </h3>
                            <span style="font-size: 0.75rem; font-weight: 500;" :style="selectedStudent.is_online ? 'color: #10b981;' : 'color: var(--chat-text-muted);'" x-text="selectedStudent.is_online ? 'En ligne' : (selectedStudent.last_seen_diff === 'Jamais connecté' ? 'Jamais connecté' : 'En ligne ' + selectedStudent.last_seen_diff)"></span>
                        </div>
                    </div>

                    <!-- Messages Container -->
                    <div style="flex: 1; overflow-y: auto; padding: 2rem; display: flex; flex-direction: column; gap: 1.25rem;" id="chat-messages" x-ref="messagesContainer" class="chat-scroll">
                        <template x-for="message in messages" :key="message.id || message.tempId">
                            <div :style="(message.sender_type === 'user' && message.sender_id == authUserId) || message.tempId ? 'display: flex; justify-content: flex-end;' : 'display: flex; justify-content: flex-start;'">
                                <!-- Sent Message (Right) -->
                                <template x-if="(message.sender_type === 'user' && message.sender_id == authUserId) || message.tempId">
                                    <div style="background-color: var(--chat-msg-sent-bg); color: var(--chat-msg-sent-text); padding: 0.875rem 1.25rem; border-radius: 1.25rem 1.25rem 0 1.25rem; max-width: 70%; font-size: 0.9375rem; line-height: 1.5; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; flex-direction: column; gap: 0.5rem;">
                                        <template x-if="message.attachment_url">
                                            <a :href="message.attachment_url" target="_blank" style="display: block; margin-bottom: 0.25rem;">
                                                <img x-show="message.attachment_url.match(/\.(jpeg|jpg|gif|png)$/i)" :src="message.attachment_url" style="max-width: 280px; max-height: 350px; width: auto; height: auto; object-fit: cover; border-radius: 0.5rem; display: block;">
                                                <div x-show="!message.attachment_url.match(/\.(jpeg|jpg|gif|png)$/i)" style="display: flex; align-items: center; gap: 0.5rem; background: rgba(0,0,0,0.1); padding: 0.5rem; border-radius: 0.5rem;">
                                                    <x-heroicon-o-document style="width: 1.5rem; height: 1.5rem;" /> Fichier joint
                                                </div>
                                            </a>
                                        </template>
                                        <template x-if="message.audio_url">
                                            <div x-data="{
                                                playing: false,
                                                currentTime: '0:00',
                                                duration: '0:00',
                                                progress: 0,
                                                init() {
                                                    this.$refs.audio.addEventListener('loadedmetadata', () => {
                                                        let d = this.$refs.audio.duration;
                                                        if(isFinite(d) && d > 0) {
                                                            let mins = Math.floor(d / 60);
                                                            let secs = Math.floor(d % 60).toString().padStart(2, '0');
                                                            this.duration = mins + ':' + secs;
                                                        }
                                                    });
                                                    this.$refs.audio.addEventListener('timeupdate', () => {
                                                        let c = this.$refs.audio.currentTime;
                                                        let d = this.$refs.audio.duration;
                                                        if(d > 0) {
                                                            this.progress = (c / d) * 100;
                                                            let minsD = Math.floor(d / 60);
                                                            let secsD = Math.floor(d % 60).toString().padStart(2, '0');
                                                            this.duration = minsD + ':' + secsD;
                                                        }
                                                        let mins = Math.floor(c / 60);
                                                        let secs = Math.floor(c % 60).toString().padStart(2, '0');
                                                        this.currentTime = mins + ':' + secs;
                                                    });
                                                    this.$refs.audio.addEventListener('ended', () => {
                                                        this.playing = false;
                                                        this.progress = 0;
                                                        this.$refs.audio.currentTime = 0;
                                                    });
                                                },
                                                toggle() {
                                                    if (this.playing) {
                                                        this.$refs.audio.pause();
                                                        this.playing = false;
                                                    } else {
                                                        this.$refs.audio.play();
                                                        this.playing = true;
                                                    }
                                                },
                                                seek(e) {
                                                    let rect = e.currentTarget.getBoundingClientRect();
                                                    let clickX = e.clientX - rect.left;
                                                    let percent = clickX / rect.width;
                                                    this.$refs.audio.currentTime = percent * this.$refs.audio.duration;
                                                }
                                            }" style="display: flex; align-items: center; gap: 0.75rem; background-color: rgba(128,128,128,0.15); padding: 0.5rem 1rem; border-radius: 9999px; width: 260px; max-width: 100%;">
                                                <audio x-ref="audio" :src="message.audio_url" style="display: none;" preload="metadata"></audio>
                                                
                                                <button type="button" @click.stop="toggle" style="background: var(--chat-primary, #6366f1); border: none; width: 2.25rem; height: 2.25rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s;">
                                                    <template x-if="!playing">
                                                        <svg style="width: 1.1rem; height: 1.1rem; margin-left: 2px;" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                                    </template>
                                                    <template x-if="playing">
                                                        <svg style="width: 1.1rem; height: 1.1rem;" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                                    </template>
                                                </button>

                                                <div style="flex: 1; display: flex; flex-direction: column; gap: 0.25rem;">
                                                    <div @click.stop="seek" style="width: 100%; height: 20px; display: flex; align-items: center; cursor: pointer; position: relative;">
                                                        <div style="width: 100%; height: 4px; background: rgba(128,128,128,0.3); border-radius: 2px; position: relative;">
                                                            <div :style="'height: 100%; border-radius: 2px; position: absolute; left: 0; top: 0; background: var(--chat-primary, #6366f1); transition: width 0.1s linear; width: ' + progress + '%'"></div>
                                                            <div :style="'width: 10px; height: 10px; border-radius: 50%; position: absolute; top: 50%; transform: translate(-50%, -50%); box-shadow: 0 1px 3px rgba(0,0,0,0.3); background: var(--chat-primary, #6366f1); transition: left 0.1s linear; left: ' + progress + '%'"></div>
                                                        </div>
                                                    </div>
                                                    <div style="display: flex; justify-content: space-between; font-size: 0.65rem; color: inherit; opacity: 0.8; font-weight: 500; margin-top: -6px;">
                                                        <span x-text="currentTime"></span>
                                                        <span x-text="duration"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <p style="margin: 0; word-wrap: break-word;" x-show="message.message" x-text="message.message"></p>
                                        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.25rem; margin-top: 0.25rem; font-size: 0.6875rem; opacity: 0.8;">
                                            <span x-text="message.formatted_time || ''"></span>
                                            <template x-if="message.tick_status === 'sent'">
                                                <svg style="width: 14px; height: 14px; color: rgba(255,255,255,0.7);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            </template>
                                            <template x-if="message.tick_status === 'delivered'">
                                                <svg style="width: 14px; height: 14px; color: rgba(255,255,255,0.7);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 6 7 17 2 12"></polyline><polyline points="22 10 11 21 6 16"></polyline></svg>
                                            </template>
                                            <template x-if="message.tick_status === 'read'">
                                                <svg style="width: 14px; height: 14px; color: #60a5fa;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 6 7 17 2 12"></polyline><polyline points="22 10 11 21 6 16"></polyline></svg>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                
                                <!-- Received Message (Left) -->
                                <template x-if="!(message.sender_type === 'user' && message.sender_id == authUserId) && !message.tempId">
                                    <div style="background-color: var(--chat-msg-recv-bg); color: var(--chat-msg-recv-text); padding: 0.875rem 1.25rem; border-radius: 1.25rem 1.25rem 1.25rem 0; max-width: 70%; font-size: 0.9375rem; line-height: 1.5; border: 1px solid var(--chat-border); box-shadow: 0 2px 4px rgba(0,0,0,0.02); display: flex; flex-direction: column; gap: 0.5rem;">
                                        <template x-if="selectedStudent.role === 'group' && message.sender_name">
                                            <span style="font-size: 0.75rem; font-weight: 600; color: #a855f7; margin-bottom: -0.25rem;" x-text="message.sender_name"></span>
                                        </template>
                                        <template x-if="message.attachment_url">
                                            <a :href="message.attachment_url" target="_blank" style="display: block; margin-bottom: 0.25rem;">
                                                <img x-show="message.attachment_url.match(/\.(jpeg|jpg|gif|png)$/i)" :src="message.attachment_url" style="max-width: 280px; max-height: 350px; width: auto; height: auto; object-fit: cover; border-radius: 0.5rem; display: block;">
                                                <div x-show="!message.attachment_url.match(/\.(jpeg|jpg|gif|png)$/i)" style="display: flex; align-items: center; gap: 0.5rem; background: rgba(0,0,0,0.05); padding: 0.5rem; border-radius: 0.5rem;">
                                                    <x-heroicon-o-document style="width: 1.5rem; height: 1.5rem;" /> Fichier joint
                                                </div>
                                            </a>
                                        </template>
                                        <template x-if="message.audio_url">
                                            <div x-data="{
                                                playing: false,
                                                currentTime: '0:00',
                                                duration: '0:00',
                                                progress: 0,
                                                init() {
                                                    this.$refs.audio.addEventListener('loadedmetadata', () => {
                                                        let d = this.$refs.audio.duration;
                                                        if(isFinite(d) && d > 0) {
                                                            let mins = Math.floor(d / 60);
                                                            let secs = Math.floor(d % 60).toString().padStart(2, '0');
                                                            this.duration = mins + ':' + secs;
                                                        }
                                                    });
                                                    this.$refs.audio.addEventListener('timeupdate', () => {
                                                        let c = this.$refs.audio.currentTime;
                                                        let d = this.$refs.audio.duration;
                                                        if(d > 0) {
                                                            this.progress = (c / d) * 100;
                                                            let minsD = Math.floor(d / 60);
                                                            let secsD = Math.floor(d % 60).toString().padStart(2, '0');
                                                            this.duration = minsD + ':' + secsD;
                                                        }
                                                        let mins = Math.floor(c / 60);
                                                        let secs = Math.floor(c % 60).toString().padStart(2, '0');
                                                        this.currentTime = mins + ':' + secs;
                                                    });
                                                    this.$refs.audio.addEventListener('ended', () => {
                                                        this.playing = false;
                                                        this.progress = 0;
                                                        this.$refs.audio.currentTime = 0;
                                                    });
                                                },
                                                toggle() {
                                                    if (this.playing) {
                                                        this.$refs.audio.pause();
                                                        this.playing = false;
                                                    } else {
                                                        this.$refs.audio.play();
                                                        this.playing = true;
                                                    }
                                                },
                                                seek(e) {
                                                    let rect = e.currentTarget.getBoundingClientRect();
                                                    let clickX = e.clientX - rect.left;
                                                    let percent = clickX / rect.width;
                                                    this.$refs.audio.currentTime = percent * this.$refs.audio.duration;
                                                }
                                            }" style="display: flex; align-items: center; gap: 0.75rem; background-color: rgba(128,128,128,0.15); padding: 0.5rem 1rem; border-radius: 9999px; width: 260px; max-width: 100%;">
                                                <audio x-ref="audio" :src="message.audio_url" style="display: none;" preload="metadata"></audio>
                                                
                                                <button type="button" @click.stop="toggle" style="background: var(--chat-primary, #6366f1); border: none; width: 2.25rem; height: 2.25rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s;">
                                                    <template x-if="!playing">
                                                        <svg style="width: 1.1rem; height: 1.1rem; margin-left: 2px;" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                                    </template>
                                                    <template x-if="playing">
                                                        <svg style="width: 1.1rem; height: 1.1rem;" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                                    </template>
                                                </button>

                                                <div style="flex: 1; display: flex; flex-direction: column; gap: 0.25rem;">
                                                    <div @click.stop="seek" style="width: 100%; height: 20px; display: flex; align-items: center; cursor: pointer; position: relative;">
                                                        <div style="width: 100%; height: 4px; background: rgba(128,128,128,0.3); border-radius: 2px; position: relative;">
                                                            <div :style="'height: 100%; border-radius: 2px; position: absolute; left: 0; top: 0; background: var(--chat-primary, #6366f1); transition: width 0.1s linear; width: ' + progress + '%'"></div>
                                                            <div :style="'width: 10px; height: 10px; border-radius: 50%; position: absolute; top: 50%; transform: translate(-50%, -50%); box-shadow: 0 1px 3px rgba(0,0,0,0.3); background: var(--chat-primary, #6366f1); transition: left 0.1s linear; left: ' + progress + '%'"></div>
                                                        </div>
                                                    </div>
                                                    <div style="display: flex; justify-content: space-between; font-size: 0.65rem; color: inherit; opacity: 0.8; font-weight: 500; margin-top: -6px;">
                                                        <span x-text="currentTime"></span>
                                                        <span x-text="duration"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <p style="margin: 0; word-wrap: break-word;" x-show="message.message" x-text="message.message"></p>
                                        <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 0.25rem; font-size: 0.6875rem; opacity: 0.6;">
                                            <span x-text="message.formatted_time || ''"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <div x-show="loading" style="text-align: center; font-size: 0.875rem; color: var(--chat-text-muted); padding: 1rem 0;">Mise à jour...</div>
                    </div>

                    <!-- Input Area -->
                    <div style="padding: 1.25rem 2rem; background-color: var(--chat-sidebar-bg); border-top: 1px solid var(--chat-border); display: flex; flex-direction: column; gap: 0.5rem;">
                        <template x-if="attachmentFile && !isRecording">
                            <div style="display: flex; align-items: center; justify-content: space-between; background: var(--chat-input-bg); padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid var(--chat-border); font-size: 0.875rem;">
                                <span x-text="attachmentFile.name" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;"></span>
                                <button type="button" @click="attachmentFile = null" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0.25rem;">
                                    <x-heroicon-o-x-mark style="width: 1rem; height: 1rem;" />
                                </button>
                            </div>
                        </template>

                        <form @submit.prevent="sendMessage" style="display: flex; align-items: center; gap: 0.5rem; height: 52px;">
                            <input type="file" x-ref="fileInput" @change="handleFileSelect" style="display: none;">
                            
                            <!-- Main Input Container -->
                            <div style="flex: 1; height: 100%; border-radius: 9999px; background-color: var(--chat-input-bg); border: 1px solid var(--chat-border); display: flex; align-items: center; padding: 0 0.5rem; overflow: hidden; transition: all 0.2s;">
                                
                                <!-- Normal Mode -->
                                <template x-if="!isRecording">
                                    <div style="display: flex; align-items: center; flex: 1; width: 100%;">
                                        <button type="button" @click="$refs.fileInput.click()" style="background: none; border: none; cursor: pointer; padding: 0.5rem; color: var(--chat-text-muted); display: flex; align-items: center; justify-content: center; transition: color 0.2s;">
                                            <x-heroicon-o-paper-clip style="width: 1.25rem; height: 1.25rem;" />
                                        </button>
                                        <input type="text" x-model="newMessage" placeholder="Tapez votre message..." 
                                               style="flex: 1; background: transparent; border: none; padding: 0.875rem 0.5rem; color: var(--chat-text); font-size: 0.9375rem; outline: none;">
                                    </div>
                                </template>

                                <!-- Recording Mode -->
                                <template x-if="isRecording">
                                    <div style="display: flex; align-items: center; flex: 1; width: 100%; padding: 0 0.5rem;">
                                        <!-- Blinking Dot and Timer -->
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="animate-pulse-opacity" style="width: 10px; height: 10px; border-radius: 50%; background-color: #ef4444;"></div>
                                            <span style="color: #ef4444; font-weight: 600; font-variant-numeric: tabular-nums;" x-text="formatDuration(recordingDuration)"></span>
                                        </div>
                                        <div style="flex: 1;"></div>
                                        <!-- Cancel Button -->
                                        <button type="button" @click="cancelRecording()" style="display: flex; align-items: center; gap: 0.25rem; background: none; border: none; cursor: pointer; color: var(--chat-text-muted); padding: 0.5rem; font-size: 0.875rem;">
                                            <x-heroicon-o-trash style="width: 1.25rem; height: 1.25rem;" />
                                            <span>Annuler</span>
                                        </button>
                                    </div>
                                </template>

                            </div>
                            
                            <!-- Mic Button -->
                            <template x-if="!newMessage.trim() && !attachmentFile && !isRecording">
                                <button type="button" @click="startRecording()" style="background-color: var(--chat-input-bg); border: 1px solid var(--chat-border); color: var(--chat-text-muted); border-radius: 9999px; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; flex-shrink: 0; transition: all 0.2s;">
                                    <x-heroicon-s-microphone style="width: 1.5rem; height: 1.5rem;" />
                                </button>
                            </template>

                            <!-- Send Audio Button -->
                            <template x-if="isRecording">
                                <button type="button" @click="stopAndSendRecording()" style="background-color: #a855f7; border: none; color: white; border-radius: 9999px; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; flex-shrink: 0; transition: all 0.2s;">
                                    <x-heroicon-s-paper-airplane style="width: 1.5rem; height: 1.5rem; transform: rotate(-45deg); margin-left: 0.25rem;" />
                                </button>
                            </template>

                            <!-- Send Text Button -->
                            <template x-if="(newMessage.trim() || attachmentFile) && !isRecording">
                                <button type="submit" :disabled="sending" 
                                        style="background-color: #a855f7; color: white; border-radius: 9999px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 52px; height: 52px; flex-shrink: 0; transition: opacity 0.2s;"
                                        :style="sending ? 'opacity: 0.5;' : ''">
                                    <x-heroicon-s-paper-airplane style="width: 1.5rem; height: 1.5rem; transform: rotate(-45deg); margin-left: 0.25rem;" />
                                </button>
                            </template>
                        </form>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chatApp', () => ({
                authUserId: {{ $authUserId }},
                authUserName: '{{ addslashes($authUserName) }}',
                students: [],
                searchQuery: '',
                selectedStudent: null,
                messages: [],
                newMessage: '',
                loading: false,
                loadingStudents: true,
                sending: false,
                pollingInterval: null,
                
                // Media variables
                attachmentFile: null,
                isRecording: false,
                recordingDuration: 0,
                recordingTimer: null,
                mediaRecorder: null,
                audioChunks: [],
                audioBlob: null,

                formatDuration(seconds) {
                    const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
                    const secs = (seconds % 60).toString().padStart(2, '0');
                    return `${mins}:${secs}`;
                },

                get filteredStudents() {
                    if (this.searchQuery.trim() === '') {
                        return this.students;
                    }
                    const query = this.searchQuery.toLowerCase();
                    return this.students.filter(student => 
                        student.name.toLowerCase().includes(query)
                    );
                },

                init() {
                    this.fetchStudents();
                    this.$watch('selectedStudent', (value) => {
                        if (value) {
                            this.fetchHistory();
                            this.startPolling();
                        } else {
                            this.stopPolling();
                        }
                    });
                },

                handleFileSelect(event) {
                    if (event.target.files.length > 0) {
                        this.attachmentFile = event.target.files[0];
                    }
                },

                startRecording() {
                    navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
                        this.mediaRecorder = new MediaRecorder(stream);
                        this.audioChunks = [];

                        this.mediaRecorder.ondataavailable = e => {
                            if (e.data.size > 0) this.audioChunks.push(e.data);
                        };

                        this.mediaRecorder.start();
                        this.isRecording = true;
                        this.recordingDuration = 0;
                        this.recordingTimer = setInterval(() => {
                            this.recordingDuration++;
                        }, 1000);
                    }).catch(err => {
                        console.error('Error accessing microphone', err);
                        alert("Impossible d'accéder au microphone.");
                    });
                },

                stopAndSendRecording() {
                    if (!this.mediaRecorder || this.mediaRecorder.state === 'inactive') return;
                    
                    this.mediaRecorder.onstop = () => {
                        this.audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                        this.mediaRecorder.stream.getTracks().forEach(track => track.stop());
                        this.isRecording = false;
                        clearInterval(this.recordingTimer);
                        this.sendMessage();
                    };
                    this.mediaRecorder.stop();
                },

                cancelRecording() {
                    if (!this.mediaRecorder || this.mediaRecorder.state === 'inactive') return;
                    this.mediaRecorder.onstop = () => {
                        this.mediaRecorder.stream.getTracks().forEach(track => track.stop());
                        this.isRecording = false;
                        clearInterval(this.recordingTimer);
                        this.audioBlob = null;
                        this.audioChunks = [];
                    };
                    this.mediaRecorder.stop();
                },

                async fetchStudents() {
                    this.loadingStudents = true;
                    try {
                        const response = await fetch('/web-chat/students', {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (response.ok) {
                            this.students = await response.json();
                        } else {
                            console.error('Failed to fetch students. Status:', response.status);
                        }
                    } catch (error) {
                        console.error('Error fetching students', error);
                    } finally {
                        this.loadingStudents = false;
                    }
                },

                selectStudent(student) {
                    this.selectedStudent = student;
                },

                async fetchHistory(isPolling = false) {
                    if (!this.selectedStudent) return;
                    if (!isPolling) this.loading = true;

                    try {
                        const targetUrl = this.selectedStudent.role === 'group'
                            ? `/web-chat/history/${this.selectedStudent.id}?target_type=group`
                            : `/web-chat/history/${this.selectedStudent.id}`;
                        
                        const response = await fetch(targetUrl, {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            const isNew = this.messages.length !== data.length;
                            this.messages = data;
                            if (isNew || !isPolling) {
                                this.scrollToBottom();
                            }
                        }
                    } catch (error) {
                        console.error('Error fetching chat history', error);
                    } finally {
                        if (!isPolling) this.loading = false;
                    }
                },

                startPolling() {
                    this.stopPolling();
                    this.pollingInterval = setInterval(() => {
                        this.fetchHistory(true);
                    }, 3000);
                },

                stopPolling() {
                    if (this.pollingInterval) {
                        clearInterval(this.pollingInterval);
                    }
                },

                async sendMessage() {
                    if ((!this.newMessage.trim() && !this.attachmentFile && !this.audioBlob) || !this.selectedStudent || this.sending || this.isRecording) return;

                    this.sending = true;

                    const formData = new FormData();
                    formData.append('receiver_id', this.selectedStudent.id);
                    if (this.selectedStudent.role === 'group') {
                        formData.append('receiver_type', 'group');
                    }
                    if (this.newMessage.trim()) formData.append('message', this.newMessage.trim());
                    if (this.attachmentFile) formData.append('attachment', this.attachmentFile);
                    if (this.audioBlob) formData.append('audio', this.audioBlob, 'audio_message.webm');

                    // Optimistic update (only for text)
                    if (this.newMessage.trim() && !this.attachmentFile && !this.audioBlob) {
                        const now = new Date();
                        const formattedTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                        this.messages.push({
                            tempId: Date.now(),
                            sender_id: this.authUserId,
                            sender_type: 'user',
                            receiver_id: this.selectedStudent.id,
                            receiver_type: this.selectedStudent.role === 'group' ? 'group' : 'student',
                            message: this.newMessage.trim(),
                            formatted_time: formattedTime,
                            tick_status: 'sent'
                        });
                        this.scrollToBottom();
                    }

                    // Clear inputs immediately
                    this.newMessage = '';
                    this.attachmentFile = null;
                    this.audioBlob = null;
                    if (this.$refs.fileInput) this.$refs.fileInput.value = '';

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        
                        const response = await fetch('/web-chat/send', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            body: formData
                        });

                        if (response.ok) {
                            this.fetchHistory(true);
                            this.scrollToBottom();
                        } else {
                            console.error('Error in response', await response.text());
                        }
                    } catch (error) {
                        console.error('Error sending message', error);
                    } finally {
                        this.sending = false;
                    }
                },

                scrollToBottom() {
                    setTimeout(() => {
                        if (this.$refs.messagesContainer) {
                            this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                        }
                    }, 100);
                }
            }));
        });
    </script>
</x-filament-panels::page>
