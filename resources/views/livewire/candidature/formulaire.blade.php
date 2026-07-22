<div class="livewire-root" style="width:100%;">

    {{-- ============================ ÉCRAN DE SUCCÈS ============================ --}}
    @if ($submitted)
        <div style="background:#fff; border:1px solid #bbf7d0; border-radius:24px; padding:32px; max-width:48rem; margin:0 auto; text-align:center; box-shadow:0 20px 50px rgba(15,23,42,0.06);">
            <div style="margin:0 auto; display:flex; align-items:center; justify-content:center; height:80px; width:80px; border-radius:50%; background:#d1fae5; color:#047857; box-shadow:inset 0 2px 4px rgba(0,0,0,0.06);">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="height:40px; width:40px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </div>
            <h3 style="margin-top:24px; font-size:1.5rem; font-weight:600; color:#0f172a;">Candidature enregistrée avec succès</h3>
            <p style="margin-top:12px; color:#475569; line-height:1.7;">Votre dossier a bien été soumis.</p>
            <p style="margin-top:8px; font-size:0.875rem; color:#64748b;">Un e-mail vous sera envoyé pour suivre votre candidature.</p>
            <div style="margin-top:32px;">
                <button type="button" wire:click="startNewApplication"
                        style="display:inline-flex; align-items:center; justify-content:center; border-radius:16px; background:#0f172a; color:#fff; padding:12px 24px; font-size:0.875rem; font-weight:600; border:none; cursor:pointer; transition:background 0.2s ease;"
                        onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='#0f172a'">
                    Déposer une autre candidature
                </button>
            </div>
        </div>

    {{-- ============================ FORMULAIRE ============================ --}}
    @else
        <div class="application-grid">

            {{-- ===== COLONNE PRINCIPALE ===== --}}
            <div class="application-main">

                {{-- Titre --}}
                <div class="application-header">
                    <span class="application-badge">Nouvelle candidature</span>
                    <h2 class="application-title">{{ $stepsMeta[$step - 1]['label'] }}</h2>
                    <p class="application-subtitle">Veuillez renseigner vos informations pour soumettre votre candidature.</p>
                </div>

                {{-- Stepper 4 étapes --}}
                <div class="application-stepper">
                    <div class="application-bubbles">
                        @foreach ($stepsMeta as $stepMeta)
                            @php
                                $isCompleted = $step > $stepMeta['number'];
                                $isActive    = $step === $stepMeta['number'];
                            @endphp
                            <div class="application-bubble {{ $isActive ? 'is-active' : '' }} {{ $isCompleted ? 'is-completed' : '' }}">
                                <div class="bubble">
                                    @if ($isCompleted)
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="height:18px; width:18px; color:#fff;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    @else
                                        {{ $stepMeta['number'] }}
                                    @endif
                                </div>
                                <div class="label">{{ $stepMeta['label'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="application-stepper-info">
                        <div class="application-stepper-progress">
                            <span class="application-stepper-progress-fill" style="width: {{ $progressPercent }}%;"></span>
                        </div>
                        <div class="application-stepper-meta">
                            
                            <span class="application-stepper-status">{{ $progressPercent }}% complété</span>
                        </div>
                    </div>
                </div>

                <form wire:submit.prevent="save" class="application-stepper" style="background:#fff; border-radius:24px; border:1px solid #e2e8f0; padding:32px; box-shadow:0 20px 50px rgba(15,23,42,0.06);">

                    {{-- ============================ ÉTAPE 1 ============================ --}}
                    @if ($step === 1)
                        <div style="display:grid; gap:24px; grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">

                            <div>
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Nom <span style="color:#e11d48;">*</span></label>
                                <div style="position:relative;">
                                    <span style="pointer-events:none; position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#94a3b8;">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="height:20px; width:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.935 17.935 0 0 1 12 21.75a17.935 17.935 0 0 1-7.5-1.632Z"/></svg>
                                    </span>
                                    <input id="nom" type="text" wire:model.live="nom" placeholder="Votre nom"
                                           style="height:48px; width:100%; border-radius:16px; border:1px solid {{ $errors->has('nom') ? '#fb7185' : '#e2e8f0' }}; background:#fff; padding-left:48px; padding-right:16px; color:#0f172a; outline:none; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                                </div>
                                @error('nom') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Prénom <span style="color:#e11d48;">*</span></label>
                                <div style="position:relative;">
                                    <span style="pointer-events:none; position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#94a3b8;">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="height:20px; width:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.935 17.935 0 0 1 12 21.75a17.935 17.935 0 0 1-7.5-1.632Z"/></svg>
                                    </span>
                                    <input id="prenom" type="text" wire:model.live="prenom" placeholder="Votre prénom"
                                           style="height:48px; width:100%; border-radius:16px; border:1px solid {{ $errors->has('prenom') ? '#fb7185' : '#e2e8f0' }}; background:#fff; padding-left:48px; padding-right:16px; color:#0f172a; outline:none; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                                </div>
                                @error('prenom') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Numéro de téléphone <span style="color:#e11d48;">*</span></label>
                                <div style="position:relative;">
                                    <span style="pointer-events:none; position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#94a3b8;">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="height:20px; width:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25A2.25 2.25 0 0 0 21.75 19.5v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a1.125 1.125 0 0 1-1.21.38 12.035 12.035 0 0 1-7.143-7.143 1.125 1.125 0 0 1 .38-1.21l1.293-.97c.33-.247.5-.656.417-1.073L6.963 3.1A1.125 1.125 0 0 0 5.872 2.25H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                    </span>
                                    <input id="telephone" type="text" wire:model.live="telephone" placeholder="+221 77 000 00 00"
                                           style="height:48px; width:100%; border-radius:16px; border:1px solid {{ $errors->has('telephone') ? '#fb7185' : '#e2e8f0' }}; background:#fff; padding-left:48px; padding-right:16px; color:#0f172a; outline:none; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                                </div>
                                @error('telephone') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Date de naissance <span style="color:#e11d48;">*</span></label>
                                <input id="date_naissance" type="date" wire:model.live="date_naissance"
                                       style="height:48px; width:100%; border-radius:16px; border:1px solid {{ $errors->has('date_naissance') ? '#fb7185' : '#e2e8f0' }}; background:#fff; padding:0 16px; color:#0f172a; outline:none; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                                @error('date_naissance') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>

                            <div style="grid-column:1 / -1;">
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Adresse e-mail <span style="color:#e11d48;">*</span></label>
                                <div style="position:relative;">
                                    <span style="pointer-events:none; position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#94a3b8;">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="height:20px; width:20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21.75 7.5v9A2.25 2.25 0 0 1 19.5 18.75h-15A2.25 2.25 0 0 1 2.25 16.5v-9m19.5 0A2.25 2.25 0 0 0 19.5 5.25h-15A2.25 2.25 0 0 0 2.25 7.5m19.5 0-8.69 5.213a2.25 2.25 0 0 1-2.12 0L2.25 7.5"/></svg>
                                    </span>
                                    <input id="email" type="email" wire:model.live="email" placeholder="prenom.nom@email.com"
                                           style="height:48px; width:100%; border-radius:16px; border:1px solid {{ $errors->has('email') ? '#fb7185' : '#e2e8f0' }}; background:#fff; padding-left:48px; padding-right:16px; color:#0f172a; outline:none; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                                </div>
                                @error('email') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Lieu de naissance <span style="color:#e11d48;">*</span></label>
                                <input id="lieu_naissance" type="text" wire:model.live="lieu_naissance" placeholder="Ville ou pays de naissance"
                                       style="height:48px; width:100%; border-radius:16px; border:1px solid {{ $errors->has('lieu_naissance') ? '#fb7185' : '#e2e8f0' }}; background:#fff; padding:0 16px; color:#0f172a; outline:none; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                                @error('lieu_naissance') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Nationalité <span style="color:#e11d48;">*</span></label>
                                <select id="nationalite" wire:model.live="nationalite"
                                        style="height:48px; width:100%; border-radius:16px; border:1px solid {{ $errors->has('nationalite') ? '#fb7185' : '#e2e8f0' }}; background:#fff; padding:0 16px; color:#0f172a; outline:none; box-shadow:0 1px 2px rgba(0,0,0,0.04); appearance:none;">
                                    <option value="">Sélectionnez votre nationalité</option>
                                    @foreach (['Sénégalaise','Camerounaise','Ivoirienne','Malienne','Burkinabè','Guinéenne','Togolaise','Béninoise','Française','Marocaine','Tunisienne'] as $n)
                                        <option value="{{ $n }}">{{ $n }}</option>
                                    @endforeach
                                </select>
                                @error('nationalite') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>

                            <div style="grid-column:1 / -1;">
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Sexe <span style="color:#e11d48;">*</span></label>
                                <div style="display:flex; align-items:center; gap:32px; padding-top:8px;">
                                    <label style="display:inline-flex; align-items:center; gap:10px; cursor:pointer; font-size:0.875rem; color:#334155;">
                                        <input type="radio" wire:model.live="sexe" value="masculin" style="height:18px; width:18px; accent-color:#9d174d;">
                                        Masculin
                                    </label>
                                    <label style="display:inline-flex; align-items:center; gap:10px; cursor:pointer; font-size:0.875rem; color:#334155;">
                                        <input type="radio" wire:model.live="sexe" value="feminin" style="height:18px; width:18px; accent-color:#9d174d;">
                                        Féminin
                                    </label>
                                </div>
                                @error('sexe') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif

                    {{-- ============================ ÉTAPE 2 ============================ --}}
                    @if ($step === 2)
                        <div style="display:grid; gap:24px; grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
                            <div>
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Pays <span style="color:#e11d48;">*</span></label>
                                <input id="pays" type="text" wire:model.live="pays" placeholder="Sénégal"
                                       style="height:48px; width:100%; border-radius:16px; border:1px solid {{ $errors->has('pays') ? '#fb7185' : '#e2e8f0' }}; background:#fff; padding:0 16px; color:#0f172a; outline:none; box-shadow:0 1px 2px rgba(0,0,0,0.04);">
                                @error('pays') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Dernier diplôme obtenu <span style="color:#e11d48;">*</span></label>
                                <select id="dernier_diplome" wire:model.live="dernier_diplome"
                                        style="height:48px; width:100%; border-radius:16px; border:1px solid {{ $errors->has('dernier_diplome') ? '#fb7185' : '#e2e8f0' }}; background:#fff; padding:0 16px; color:#0f172a; outline:none; appearance:none;">
                                    <option value="">Sélectionner</option>
                                    <option value="baccalaureat">Baccalauréat</option>
                                    <option value="licence">Licence</option>
                                </select>
                                @error('dernier_diplome') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Série du Baccalauréat</label>
                                <select id="serie_baccalaureat" wire:model.live="serie_baccalaureat"
                                        style="height:48px; width:100%; border-radius:16px; border:1px solid #e2e8f0; background:#fff; padding:0 16px; color:#0f172a; outline:none; appearance:none;">
                                    <option value="">Sélectionner une série</option>
                                    <option value="S">Série S</option>
                                    <option value="L">Série L</option>
                                    <option value="G">Série G</option>
                                </select>
                                @error('serie_baccalaureat') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>

                            <div style="grid-column:1 / -1;">
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Adresse <span style="color:#e11d48;">*</span></label>
                                <textarea id="adresse" rows="4" wire:model.live="adresse" placeholder="Votre adresse complète"
                                          style="width:100%; border-radius:16px; border:1px solid {{ $errors->has('adresse') ? '#fb7185' : '#e2e8f0' }}; background:#fff; padding:16px; color:#0f172a; outline:none; box-shadow:0 1px 2px rgba(0,0,0,0.04); resize:vertical;"></textarea>
                                @error('adresse') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif

                    {{-- ============================ ÉTAPE 3 ============================ --}}
                    @if ($step === 3)
                        <div style="display:grid; gap:24px; grid-template-columns:repeat(auto-fit,minmax(260px,1fr));">
                            <div>
                                <label style="display:block; margin-bottom:8px; font-size:0.875rem; font-weight:600; color:#334155;">Formation visée <span style="color:#e11d48;">*</span></label>
                                <select id="programme_id" wire:model.live="programme_id" {{ blank($serie_baccalaureat) ? 'disabled' : '' }}
                                        style="height:48px; width:100%; border-radius:16px; border:1px solid {{ $errors->has('programme_id') ? '#fb7185' : '#e2e8f0' }}; background:#fff; padding:0 16px; color:#0f172a; outline:none; appearance:none; {{ blank($serie_baccalaureat) ? 'cursor:not-allowed; background:#f8fafc;' : '' }}">
                                    <option value="">{{ blank($serie_baccalaureat) ? "Choisir d'abord une série à l'étape 2" : 'Sélectionner une formation' }}</option>
                                    @foreach ($availableProgrammes as $group)
                                        <optgroup label="{{ $group['label'] }}">
                                            @foreach ($group['options'] as $programme)
                                                <option value="{{ $programme['id'] }}">{{ $programme['nom'] }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('programme_id') <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                            </div>

                            @if ($selectedProgramme)
                                <div style="border-radius:20px; border:1px solid #e2e8f0; background:#ffffff; padding:20px; box-shadow:0 4px 12px rgba(15,23,42,0.04);">
                                    <p style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.2em; color:#94a3b8;">Formation sélectionnée</p>
                                    <h3 style="margin-top:8px; font-size:1.125rem; font-weight:600; color:#0f172a;">{{ $selectedProgramme['nom'] }}</h3>
                                    @if ($selectedProgrammeNiveau)
                                        <span style="margin-top:8px; display:inline-flex; align-items:center; border-radius:999px; border:1px solid #bfdbfe; background:#eff6ff; padding:6px 14px; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.15em; color:#1e40af;">
                                            {{ $selectedProgrammeNiveau['libelle'] }}
                                        </span>
                                    @endif
                                    <p style="margin-top:12px; font-size:0.875rem; line-height:1.6; color:#475569;">{{ $selectedProgramme['description'] ?? '' }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ============================ ÉTAPE 4 — Documents selon la formation ============================ --}}
                    @if ($step === 4)
                        @if (! $selectedProgramme)
                            <div style="border-radius:16px; border:1px solid #fde68a; background:#fffbeb; padding:24px; color:#92400e;">
                                <p style="font-weight:600;">Aucune formation sélectionnée.</p>
                                <p style="font-size:0.875rem; margin-top:4px;">Veuillez revenir à l'étape 3 pour choisir une formation.</p>
                            </div>
                        @elseif (count($requiredDocuments) === 0)
                            <div style="border-radius:16px; border:1px solid #fde68a; background:#fffbeb; padding:24px; color:#92400e;">
                                <p style="font-weight:600;">Aucun document requis pour cette formation.</p>
                                <p style="font-size:0.875rem; margin-top:4px;">Exécutez : <code>php artisan db:seed --class=TypesDocumentsSeeder && php artisan db:seed --class=ProgrammesSeeder</code>.</p>
                            </div>
                        @else
                            <div style="margin-bottom:24px; border-radius:16px; border:1px solid #bfdbfe; background:#eff6ff; padding:20px;">
                                <p style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.2em; color:#1e40af;">Formation</p>
                                <h3 style="margin-top:6px; font-size:1.125rem; font-weight:600; color:#0f172a;">
                                    {{ $selectedProgramme['nom'] }} @if ($selectedProgrammeNiveau) — {{ $selectedProgrammeNiveau['libelle'] }} @endif
                                </h3>
                                <p style="margin-top:6px; font-size:0.875rem; color:#475569;">
                                    {{ count($requiredDocuments) }} document(s) à soumettre — configurés par <code>ProgrammesSeeder.php</code> selon la formation visée.
                                </p>
                            </div>

                            <div style="display:grid; gap:20px; grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
                                @foreach ($requiredDocuments as $doc)
                                    <div class="file-drop">
                                        <label style="display:block; font-size:0.875rem; font-weight:600; color:#334155;">
                                            {{ $doc['libelle'] }}
                                            @if ($doc['obligatoire'])
                                                <span style="color:#e11d48;">*</span>
                                            @else
                                                <span style="color:#94a3b8; font-size:0.75rem; font-weight:400;">(facultatif)</span>
                                            @endif
                                        </label>
                                        <input type="file" wire:model="documents.{{ $doc['code'] }}">
                                        @if (! empty($doc['extensions_autorisees']))
                                            <p style="margin-top:8px; font-size:0.75rem; color:#64748b;">
                                                Formats : {{ implode(', ', $doc['extensions_autorisees']) }}
                                                @if (! empty($doc['taille_max_mb'])) · max {{ $doc['taille_max_mb'] }} MB @endif
                                            </p>
                                        @endif
                                        @error('documents.'.$doc['code']) <p style="margin-top:8px; font-size:0.875rem; font-weight:500; color:#e11d48;">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>

                            <div style="margin-top:32px; padding-top:24px; border-top:1px solid #e2e8f0;">
                                <h3 style="font-size:1.125rem; font-weight:600; color:#0f172a; margin-bottom:16px;">Récapitulatif</h3>
                                <div style="display:grid; gap:12px; grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                                    <div><p style="font-size:0.75rem; color:#64748b; text-transform:uppercase;">Nom</p><p style="font-weight:600;">{{ $nom }} {{ $prenom }}</p></div>
                                    <div><p style="font-size:0.75rem; color:#64748b; text-transform:uppercase;">E-mail</p><p style="font-weight:600;">{{ $email }}</p></div>
                                    <div><p style="font-size:0.75rem; color:#64748b; text-transform:uppercase;">Téléphone</p><p style="font-weight:600;">{{ $telephone }}</p></div>
                                    <div><p style="font-size:0.75rem; color:#64748b; text-transform:uppercase;">Naissance</p><p style="font-weight:600;">{{ $date_naissance }}</p></div>
                                    <div><p style="font-size:0.75rem; color:#64748b; text-transform:uppercase;">Pays</p><p style="font-weight:600;">{{ $pays }}</p></div>
                                    <div><p style="font-size:0.75rem; color:#64748b; text-transform:uppercase;">Formation</p><p style="font-weight:600;">{{ $selectedProgramme['nom'] }} @if($selectedProgrammeNiveau) ({{ $selectedProgrammeNiveau['libelle'] }}) @endif</p></div>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- ============================ BOUTONS ============================ --}}
                    <div style="margin-top:32px; padding-top:24px; border-top:1px solid #e2e8f0; display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:space-between;">
                        <button type="button" wire:click="previousStep" {{ $step === 1 ? 'disabled' : '' }}
                                style="display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:16px; border:1px solid #e2e8f0; background:#fff; color:#334155; padding:12px 20px; font-size:0.875rem; font-weight:600; cursor:pointer; transition:all .2s ease; {{ $step === 1 ? 'opacity:0.5; cursor:not-allowed;' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="height:16px; width:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                            Précédent
                        </button>

                        @if ($step < count($stepsMeta))
                            <button type="button" wire:click="nextStep"
                                    style="display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:16px; background:#2563eb; color:#fff; padding:12px 20px; font-size:0.875rem; font-weight:600; border:none; cursor:pointer; box-shadow:0 8px 20px rgba(37,99,235,0.25); transition:background .2s ease;"
                                    onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                                Suivant
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="height:16px; width:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5 15.75 12l-7.5 7.5"/></svg>
                            </button>
                        @else
                            <button type="submit" {{ $canSubmit ? '' : 'disabled' }}
                                    style="display:inline-flex; align-items:center; justify-content:center; gap:8px; border-radius:16px; background:linear-gradient(90deg,#2563eb,#1d4ed8); color:#fff; padding:12px 24px; font-size:0.875rem; font-weight:700; border:none; cursor:pointer; box-shadow:0 8px 20px rgba(37,99,235,0.3); transition:all .2s ease; {{ $canSubmit ? '' : 'opacity:0.5; cursor:not-allowed; background:#cbd5e1;' }}">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="height:16px; width:16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25h12A2.25 2.25 0 0 0 20.25 18V7.5L16.5 3.75ZM16.5 3.75V7.5h3.75M8.25 12h7.5m-7.5 3h7.5"/></svg>
                                Enregistrer la candidature
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            {{-- ============================ SIDEBAR (récap visuel) ============================ --}}
            <aside class="application-aside">
                <div class="sidebar-card">
                    <p class="sidebar-card-title">Votre progression</p>
                    <div class="sidebar-progress-meta">
                        <span class="sidebar-progress-percent">{{ $progressPercent }}%</span>
                        <span style="color:#64748b; font-size:0.875rem;">complété</span>
                    </div>
                    <div class="sidebar-progress-bar">
                        <div class="application-stepper-progress-fill" style="width: {{ $progressPercent }}%;"></div>
                    </div>
                    <ul class="sidebar-progress-list">
                        @foreach ($stepsMeta as $stepMeta)
                            <li class="sidebar-progress-step {{ $step === $stepMeta['number'] ? 'active' : '' }} {{ $step > $stepMeta['number'] ? 'completed' : '' }}">
                                <span class="step-number">{{ $step > $stepMeta['number'] ? '✓' : $stepMeta['number'] }}</span>
                                <span>{{ $stepMeta['label'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="sidebar-card">
                    <p class="sidebar-card-title">Besoin d'aide ?</p>
                    <p class="help-card-text">Notre équipe est disponible pour répondre à vos questions sur la candidature.</p>
                    <div class="help-card-contact">
                        <div><span> contact@epf-africa.com</span></div>
                        <div><span> +221 33 000 0000</span></div>
                    </div>
                </div>
            </aside>
        </div>
    @endif
</div>
