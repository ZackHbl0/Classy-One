<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Top Summary Banner --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl {{ auth()->user()->hasTwoFactorEnabled() ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400' }}">
                        @if(auth()->user()->hasTwoFactorEnabled())
                            <x-heroicon-o-shield-check class="h-7 w-7" />
                        @else
                            <x-heroicon-o-shield-exclamation class="h-7 w-7" />
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                                Authentification à Deux Facteurs (TOTP RFC 6238)
                            </h2>
                            @if(auth()->user()->hasTwoFactorEnabled())
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300">
                                    ✓ Activé &amp; Sécurisé
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-300">
                                    Non activé
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Ajoute une couche de sécurité supplémentaire à votre compte administrateur en exigeant un code à 6 chiffres généré par une application TOTP (Google Authenticator, Microsoft Authenticator, Authy) à chaque connexion.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if(!auth()->user()->hasTwoFactorEnabled() && !$isConfiguring)
                        <x-filament::button wire:click="startSetup" color="primary" icon="heroicon-o-key">
                            Activer le 2FA
                        </x-filament::button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Configuration Modal / Panel --}}
        @if($isConfiguring)
            <div class="rounded-xl border border-primary-200 bg-primary-50/40 p-6 shadow-sm dark:border-primary-900 dark:bg-gray-900">
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-device-phone-mobile class="h-5 w-5 text-primary-600" />
                    Configuration de votre application d'authentification
                </h3>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    {{-- QR Code & Secret --}}
                    <div class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <img 
                            src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($otpauthUri) }}" 
                            alt="QR Code TOTP" 
                            class="w-48 h-48 rounded shadow-sm"
                        />
                        <div class="mt-4 text-center">
                            <span class="text-xs text-gray-400 block uppercase font-mono tracking-wider">Clé secrète manuelle</span>
                            <code class="mt-1 inline-block text-sm font-mono font-bold tracking-widest text-primary-600 dark:text-primary-400 bg-gray-100 dark:bg-gray-900 px-3 py-1.5 rounded border border-gray-200 dark:border-gray-800 select-all">
                                {{ $secret }}
                            </code>
                        </div>
                    </div>

                    {{-- Step by step form --}}
                    <div class="space-y-4">
                        <ol class="space-y-3 text-sm text-gray-600 dark:text-gray-300 list-decimal list-inside">
                            <li>Ouvrez <strong>Google Authenticator</strong> ou votre application TOTP préférée.</li>
                            <li>Appuyez sur <strong>+</strong> puis choisissez <strong>Scanner un code QR</strong>.</li>
                            <li>Entrez ci-dessous le code à 6 chiffres affiché par l'application pour finaliser l'activation.</li>
                        </ol>

                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Code de vérification (6 chiffres)
                            </label>
                            <input 
                                type="text" 
                                maxlength="6" 
                                placeholder="123456" 
                                wire:model="confirmationCode" 
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-center text-2xl font-mono tracking-widest text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            @error('confirmationCode')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <x-filament::button wire:click="confirmSetup" color="success" icon="heroicon-o-check-circle">
                                Confirmer &amp; Activer
                            </x-filament::button>
                            <x-filament::button wire:click="cancelSetup" color="gray" outlined>
                                Annuler
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Recovery Codes Section (Shown on freshly enabled or when regenerated) --}}
        @if($justEnabled && !empty($recoveryCodes))
            <div class="rounded-xl border border-amber-300 bg-amber-50/50 p-6 shadow-sm dark:border-amber-800 dark:bg-amber-950/20">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-amber-600 flex-shrink-0" />
                    <div class="space-y-2">
                        <h3 class="text-base font-bold text-amber-900 dark:text-amber-200">
                            Codes de secours à usage unique (Très important !)
                        </h3>
                        <p class="text-sm text-amber-800 dark:text-amber-300">
                            Conservez ces codes dans un endroit sûr (ex: gestionnaire de mots de passe). Si vous perdez l'accès à votre téléphone, chaque code vous permettra de vous connecter <strong>une seule fois</strong>.
                        </p>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-3">
                            @foreach($recoveryCodes as $rCode)
                                <div class="p-2 text-center font-mono font-bold text-sm bg-white dark:bg-gray-900 rounded border border-amber-200 dark:border-amber-800 text-gray-800 dark:text-gray-100 select-all shadow-sm">
                                    {{ $rCode }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Active 2FA Management Panel --}}
        @if(auth()->user()->hasTwoFactorEnabled() && !$isConfiguring)
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                            Régénérer les codes de récupération
                        </h3>
                        <p class="text-xs text-gray-500">
                            Invalide les codes de secours précédents et génère un nouveau jeu de 8 codes d'urgence.
                        </p>
                    </div>
                    <x-filament::button wire:click="regenerateRecoveryCodes" color="gray" icon="heroicon-o-arrow-path" size="sm">
                        Régénérer les codes
                    </x-filament::button>
                </div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-red-600 dark:text-red-400">
                            Désactiver l'authentification à deux facteurs
                        </h3>
                        <p class="text-xs text-gray-500">
                            Cette action diminuera le niveau de protection de votre compte administratif. Votre mot de passe actuel sera demandé.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-2">
                        <input 
                            type="password" 
                            wire:model="passwordToDisable" 
                            placeholder="Votre mot de passe" 
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                        <input 
                            type="text" 
                            wire:model="codeToDisable" 
                            placeholder="Code TOTP ou secours" 
                            class="w-36 rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-mono dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                        <x-filament::button wire:click="disable2FA" color="danger" size="sm" icon="heroicon-o-lock-open">
                            Désactiver 2FA
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
