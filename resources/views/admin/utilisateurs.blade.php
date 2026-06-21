@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')
@section('page-title', 'Utilisateurs')

@section('content')

    {{-- ===== Toolbar ===== --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <p class="text-muted mb-0" style="font-size:13px">
            {{ $utilisateurs->count() }} compte(s) enregistré(s)
        </p>
        <button class="btn btn-amira btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreer">
            <i class="bi bi-person-plus me-1"></i>Nouvel utilisateur
        </button>
    </div>

    {{-- ===== KPIs rôles ===== --}}
    <div class="row g-3 mb-4">
        @php
            $nbAdmins    = $utilisateurs->where('role', 'admin')->count();
            $nbCaissiers = $utilisateurs->where('role', 'caissier')->count();
            $nbInactifs  = $utilisateurs->where('actif', false)->count();
        @endphp
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-shield-lock"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Administrateurs</div>
                    <div class="stat-value" style="font-size:22px">{{ $nbAdmins }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-cash"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Caissiers</div>
                    <div class="stat-value" style="font-size:22px">{{ $nbCaissiers }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-person-slash"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Inactifs</div>
                    <div class="stat-value" style="font-size:22px">{{ $nbInactifs }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Tableau utilisateurs ===== --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Liste des comptes</span>
            {{-- Filtre rôle --}}
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-amira role-filter active" data-role="all">Tous</button>
                <button class="btn btn-sm btn-outline-secondary role-filter" data-role="admin">Admin</button>
                <button class="btn btn-sm btn-outline-secondary role-filter" data-role="caissier">Caissier</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-amira mb-0">
                    <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th class="text-center">Rôle</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody id="tableUtilisateurs">
                    @forelse($utilisateurs as $u)
                        <tr class="user-row" data-role="{{ $u->role }}">
                            {{-- Utilisateur --}}
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar-table" style="
                                    width:38px;height:38px;border-radius:50%;
                                    background:{{ match($u->role) {
                                        'admin'     => 'var(--danger-light)',
                                        'caissier'  => 'var(--info-light)',
                                        'cuisinier' => 'var(--success-light)',
                                        default     => 'var(--border-color)'
                                    } }};
                                    display:flex;align-items:center;justify-content:center;
                                    font-size:15px;font-weight:700;flex-shrink:0;
                                    color:{{ match($u->role) {
                                        'admin'     => 'var(--danger)',
                                        'caissier'  => 'var(--info)',
                                        'cuisinier' => 'var(--success)',
                                        default     => 'var(--text-muted)'
                                    } }}">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600;font-size:13.5px">
                                            {{ $u->name }}
                                            @if($u->id === auth()->id())
                                                <span class="badge bg-secondary-subtle text-secondary ms-1" style="font-size:10px">Vous</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Email --}}
                            <td style="color:var(--text-muted);font-size:13px">{{ $u->email }}</td>

                            {{-- Rôle --}}
                            <td class="text-center">
                            <span class="user-role badge-role-{{ $u->role }}" style="
                                font-size:11px;font-weight:700;padding:3px 10px;
                                border-radius:20px;text-transform:uppercase;letter-spacing:.04em">
                                @switch($u->role)
                                    @case('admin')      Admin     @break
                                    @case('caissier')  Caissier  @break
                                @endswitch
                            </span>
                            </td>

                            {{-- Statut --}}
                            <td class="text-center">
                                @if($u->actif)
                                    <span class="stock-badge stock-ok">Actif</span>
                                @else
                                    <span class="stock-badge stock-critique">Inactif</span>
                                @endif
                            </td>


                            {{-- Actions --}}
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- Éditer --}}
                                    <button class="btn btn-sm btn-outline-secondary btn-edit-mp"
                                            title="Modifier"
                                            data-id="{{ $u->id }}"
                                            data-nom="{{ $u->name }}"
                                            data-telephone="{{ $u->telephone }}"
                                            data-email="{{ $u->email }}"
                                            data-adresse="{{ $u->adresse }}"
                                            data-actif="{{ $u->actif ? '1' : '0' }}"
                                            data-bs-toggle="modal" data-bs-target="#modalEditer">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    {{-- Désactiver / Réactiver --}}
                                    @if($u->id !== auth()->id())
                                        <form method="POST"
                                              action="{{ route('admin.utilisateurs.destroy', $u) }}">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                    class="btn btn-sm {{ $u->actif ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                    title="{{ $u->actif ? 'Désactiver' : 'Réactiver' }}"
                                                    data-msg="{{ $u->actif ? 'Désactiver' : 'Réactiver' }} le compte de {{ $u->name }} ?"
                                                    data-type="{{ $u->actif ? 'danger' : 'warning' }}"
                                                    data-confirm-text="{{ $u->actif ? 'Désactiver' : 'Réactiver' }}"
                                                    onclick="confirmForm(this.closest('form'), this.dataset.msg, {type:this.dataset.type,title:'Compte utilisateur',confirmText:this.dataset.confirmText})">
                                                <i class="bi bi-{{ $u->actif ? 'person-slash' : 'person-check' }}"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-2 d-block mb-2 opacity-50"></i>
                                Aucun utilisateur enregistré
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Modal Créer utilisateur ===== --}}
    <div class="modal fade" id="modalCreer" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.utilisateurs.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-person-plus text-success me-2"></i>Nouvel utilisateur
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="Prénom Nom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse e-mail</label>
                            <input type="email" name="email" class="form-control"
                                   placeholder="prenom@amira.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rôle</label>
                            <select name="role" class="form-select" required>
                                <option value="caissier">Caissier</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <input type="password" name="password" id="mdpCreer"
                                       class="form-control" placeholder="Min. 8 caractères" required>
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="toggleMdp('mdpCreer', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-amira">
                            <i class="bi bi-check2 me-1"></i>Créer le compte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Modal Éditer utilisateur ===== --}}
    <div class="modal fade" id="modalEditer" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="formEditer" action="">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-pencil text-warning me-2"></i>Modifier l'utilisateur
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="name" id="edit-name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adresse e-mail</label>
                            <input type="email" name="email" id="edit-email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rôle</label>
                            <select name="role" id="edit-role" class="form-select" required>
                                <option value="caissier">Caissier</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Statut du compte</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="actif"
                                       id="edit-actif" value="1">
                                <label class="form-check-label" for="edit-actif"
                                       style="font-size:13px">Compte actif</label>
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">
                                Nouveau mot de passe
                                <span class="text-muted fw-normal">(laisser vide pour ne pas changer)</span>
                            </label>
                            <div class="input-group">
                                <input type="password" name="password" id="mdpEditer"
                                       class="form-control" placeholder="Min. 8 caractères">
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="toggleMdp('mdpEditer', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-amira">
                            <i class="bi bi-check2 me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.btn-edit-mp').forEach(btn => {
            console.log('stts')
            btn.addEventListener('click', () => {
                const f = document.getElementById('formEditer');
                f.action = `/admin/utilisateurs/${btn.dataset.id}`;
                document.getElementById('edit-name').value  = data.name;
                document.getElementById('edit-email').value = data.email;
                document.getElementById('edit-role').value  = data.role;
                document.getElementById('edit-actif').checked = data.actif;
                document.getElementById('mdpEditer').value  = '';
            });
        });

        function toggleMdp(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon  = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        // Filtre par rôle
        document.querySelectorAll('.role-filter').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.role-filter').forEach(b => {
                    b.classList.remove('active', 'btn-amira');
                    b.classList.add('btn-outline-secondary');
                });
                btn.classList.add('active', 'btn-amira');
                btn.classList.remove('btn-outline-secondary');

                const role = btn.dataset.role;
                document.querySelectorAll('.user-row').forEach(row => {
                    row.style.display = (role === 'all' || row.dataset.role === role) ? '' : 'none';
                });
            });
        });
    </script>
@endpush
