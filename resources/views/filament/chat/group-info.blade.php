<div>
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <!-- En-tête -->
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 4rem; height: 4rem; border-radius: 9999px; background-color: rgba(99, 102, 241, 0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.5rem; flex-shrink: 0;">
                <x-heroicon-o-user-group style="width: 2rem; height: 2rem;" />
            </div>
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--chat-text); margin: 0;">{{ $group->name }}</h2>
                <p style="font-size: 0.875rem; color: var(--chat-text-muted); margin: 0.25rem 0 0 0;">
                    Créé le {{ $group->created_at->format('d/m/Y') }}
                </p>
            </div>
        </div>

        <!-- Statistiques -->
        @php
            $professors = $group->users;
            $students = $group->students;
            $totalMembers = $professors->count() + $students->count();
            $admin = $professors->first(); // On suppose que le premier professeur est l'admin
        @endphp

        <div style="display: flex; gap: 1rem; margin-top: 0.5rem; flex-wrap: wrap;">
            <div style="background-color: var(--chat-sidebar-bg); border: 1px solid var(--chat-border); padding: 0.75rem 1rem; border-radius: 0.5rem; flex: 1; min-width: 120px;">
                <div style="font-size: 0.75rem; color: var(--chat-text-muted); font-weight: 600; text-transform: uppercase;">Participants</div>
                <div style="font-size: 1.25rem; font-weight: 700; color: var(--chat-text); margin-top: 0.25rem;">{{ $totalMembers }}</div>
            </div>
            <div style="background-color: var(--chat-sidebar-bg); border: 1px solid var(--chat-border); padding: 0.75rem 1rem; border-radius: 0.5rem; flex: 1; min-width: 120px;">
                <div style="font-size: 0.75rem; color: var(--chat-text-muted); font-weight: 600; text-transform: uppercase;">Administrateur</div>
                <div style="font-size: 1rem; font-weight: 600; color: var(--chat-text); margin-top: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $admin ? $admin->name : 'Inconnu' }}</div>
            </div>
        </div>

        <!-- Liste des membres -->
        <div style="margin-top: 1rem;">
            <h3 style="font-size: 1rem; font-weight: 600; color: var(--chat-text); margin-bottom: 0.75rem;">Membres du groupe</h3>
            
            <div style="max-height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem; padding-right: 0.5rem;">
                <!-- Professeurs -->
                @foreach($professors as $prof)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background-color: var(--chat-sidebar-bg); border-radius: 0.5rem; border: 1px solid var(--chat-border);">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 2.25rem; height: 2.25rem; border-radius: 9999px; background-color: #10b981; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem;">
                                {{ strtoupper(substr($prof->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.9375rem; color: var(--chat-text);">{{ $prof->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--chat-text-muted);">Professeur</div>
                            </div>
                        </div>
                        <span style="background-color: rgba(16, 185, 129, 0.1); color: #10b981; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.625rem; font-weight: 600;">ADMIN</span>
                    </div>
                @endforeach

                <!-- Étudiants -->
                @foreach($students as $student)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background-color: var(--chat-sidebar-bg); border-radius: 0.5rem; border: 1px solid var(--chat-border);">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 2.25rem; height: 2.25rem; border-radius: 9999px; background-color: var(--chat-avatar-bg); color: var(--chat-text-muted); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem;">
                                {{ strtoupper(substr($student->nom, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.9375rem; color: var(--chat-text);">{{ $student->nom }} {{ $student->prenom }}</div>
                                <div style="font-size: 0.75rem; color: var(--chat-text-muted);">Étudiant</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
