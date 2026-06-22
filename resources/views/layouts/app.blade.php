<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Amira a fait le poulet</title>

    <link rel="icon" href="{{ asset('images/logo_black.png') }}" type="image/png">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicon_io/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon_io/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon_io/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('images/favicon_io/site.webmanifest') }}">

    {{-- Bootstrap 5 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Custom CSS --}}
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">

    @stack('head')
</head>
<body>

{{-- Overlay sidebar (mobile) --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- ====================== SIDEBAR ====================== --}}
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-text">
            <span class="brand-icon"><img src="/img/logo.png" class="img-fluid" alt=""></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Vue d'ensemble</span>
        </a>

        {{-- Stock --}}
        <div class="nav-section">Stock</div>
        <a href="{{ route('stock.index') }}" class="nav-item {{ request()->routeIs('stock.index') ? 'active' : '' }}">
            <i class="bi bi-boxes"></i>
            <span>Inventaire</span>
        </a>
        <a href="{{ route('stock.mouvement') }}" class="nav-item {{ request()->routeIs('stock.mouvement') ? 'active' : '' }}">
            <i class="bi bi-arrow-left-right"></i>
            <span>Mouvements</span>
        </a>

        {{-- Ventes --}}
        <div class="nav-section">Ventes & Caisse</div>
        @can('access-pos')
            <a href="{{ route('ventes.pos') }}" class="nav-item {{ request()->routeIs('ventes.pos') ? 'active' : '' }}">
                <i class="bi bi-cart3"></i>
                <span>Prise de commande</span>
            </a>
        @endcan
        <a href="{{ route('ventes.index') }}" class="nav-item {{ request()->routeIs('ventes.index') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i>
            <span>Historique ventes</span>
        </a>
        @can('access-pos')
            <a href="{{ route('caisse.cloture') }}" class="nav-item {{ request()->routeIs('caisse.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i>
                <span>Clôture de caisse</span>
            </a>
        @endcan

        {{-- Rapports - Admin only --}}
        @can('view-reports')
            <div class="nav-section">Rapports</div>
            <a href="{{ route('rapports.index') }}" class="nav-item {{ request()->routeIs('rapports.index') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i>
                <span>Statistiques</span>
            </a>
            {{--<a href="{{ route('rapports.pertes') }}" class="nav-item {{ request()->routeIs('rapports.pertes') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle"></i>
                <span>Rapport des pertes</span>
            </a>--}}
        @endcan

        {{-- Admin --}}
        @can('admin')
            <div class="nav-section">Administration</div>
            <a href="{{ route('admin.menu') }}" class="nav-item {{ request()->routeIs('admin.menu*') ? 'active' : '' }}">
                <i class="bi bi-menu-button-wide"></i>
                <span>Carte & Menu</span>
            </a>
            <a href="{{ route('admin.stock') }}" class="nav-item {{ request()->routeIs('admin.stock*') ? 'active' : '' }}">
                <i class="bi bi-boxes"></i>
                <span>Matières premières</span>
            </a>
            <a href="{{ route('admin.utilisateurs') }}" class="nav-item {{ request()->routeIs('admin.utilisateurs*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Utilisateurs</span>
            </a>
            {{--<a href="{{ route('admin.fournisseurs') }}" class="nav-item {{ request()->routeIs('admin.fournisseurs*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i>
                <span>Fournisseurs</span>
            </a>--}}
        @endcan
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
            <div class="user-details">
                <span class="user-name">{{ auth()->user()->name ?? 'Utilisateur' }}</span>
                <span class="user-role badge-role-{{ auth()->user()->role ?? 'caissier' }}">
                    {{ ucfirst(auth()->user()->role ?? 'caissier') }}
                </span>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout" title="Déconnexion">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>
</div>

{{-- ====================== MAIN CONTENT ====================== --}}
<div class="main-content" id="mainContent">

    {{-- Top bar --}}
    <div class="topbar">
        <button class="topbar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        <div class="topbar-right">
            {{-- Alertes stock --}}
            @php $alertes = \App\Models\Produit::stockCritique()->count(); @endphp
            @if($alertes > 0)
                <a href="{{ route('stock.index') }}?filtre=critique" class="topbar-alert">
                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                    <span class="badge bg-danger">{{ $alertes }}</span>
                </a>
            @endif
            <span class="topbar-date">
                <i class="bi bi-calendar3"></i>
                {{ now()->locale('fr')->isoFormat('dddd D MMM YYYY') }}
            </span>
        </div>
    </div>

    {{-- Flash messages --}}
    <div class="flash-zone">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Page content --}}
    <div class="page-body">
        @yield('content')
    </div>

</div><!-- /.main-content -->

{{-- ===== Modal Confirmation global ===== --}}
<div class="modal fade" id="modalConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 8px 40px rgba(0,0,0,.18)">
            <div class="modal-body p-4 text-center">
                <div id="confirmIconWrap"
                     class="d-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:64px;height:64px;border-radius:50%">
                    <i class="bi fs-2" id="confirmIcon"></i>
                </div>
                <h6 class="fw-bold mb-1" id="confirmTitle" style="font-size:16px"></h6>
                <p class="mb-4" style="font-size:13px;color:var(--text-secondary);line-height:1.5" id="confirmMessage"></p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal"
                            style="font-size:13px;border-radius:10px;min-width:100px">
                        Annuler
                    </button>
                    <button type="button" class="btn px-4" id="confirmBtn"
                            style="font-size:13px;border-radius:10px;min-width:100px">
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Modal Alerte global ===== --}}
<div class="modal fade" id="modalAlert" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 8px 40px rgba(0,0,0,.18)">
            <div class="modal-body p-4 text-center">
                <div id="alertIconWrap"
                     class="d-flex align-items-center justify-content-center mx-auto mb-3"
                     style="width:64px;height:64px;border-radius:50%">
                    <i class="bi fs-2" id="alertIcon"></i>
                </div>
                <h6 class="fw-bold mb-1" id="alertTitle" style="font-size:16px"></h6>
                <p class="mb-4" style="font-size:13px;color:var(--text-secondary);line-height:1.5" id="alertMessage"></p>
                <button type="button" class="btn px-5" id="alertCloseBtn" data-bs-dismiss="modal"
                        style="font-size:13px;border-radius:10px">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>


<script>
    function toggleSidebar() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        if (window.innerWidth < 992) {
            var isOpen = sidebar.classList.contains('mobile-open');
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('show');
            document.body.style.overflow = isOpen ? '' : 'hidden';
        } else {
            sidebar.classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
        }
    }

    // Fermer la sidebar en cliquant sur l'overlay (mobile)
    document.getElementById('sidebarOverlay').addEventListener('click', function () {
        document.getElementById('sidebar').classList.remove('mobile-open');
        this.classList.remove('show');
        document.body.style.overflow = '';
    });

    // Réinitialiser l'état au redimensionnement
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            document.getElementById('sidebar').classList.remove('mobile-open');
            document.getElementById('sidebarOverlay').classList.remove('show');
            document.body.style.overflow = '';
        }
    });

    // Auto-close flash messages after 4s
    setTimeout(() => {
        document.querySelectorAll('.flash-zone .alert').forEach(el => {
            new bootstrap.Alert(el).close();
        });
    }, 4000);

    // ---- Helpers modaux globaux ----
    var _confirmCallback = null;
    var _confirmPresets = {
        danger:  { bg: 'var(--danger-light)',  color: 'var(--danger)',  icon: 'bi-exclamation-triangle-fill', cls: 'btn-danger',  label: 'Confirmer' },
        warning: { bg: 'var(--warning-light)', color: 'var(--warning)', icon: 'bi-exclamation-circle-fill',  cls: 'btn-warning', label: 'Confirmer' },
    };
    var _alertPresets = {
        danger:  { bg: 'var(--danger-light)',  color: 'var(--danger)',  icon: 'bi-x-circle-fill',          cls: 'btn-danger',  title: 'Erreur' },
        success: { bg: 'var(--success-light)', color: 'var(--success)', icon: 'bi-check-circle-fill',       cls: 'btn-success', title: 'Succès' },
        warning: { bg: 'var(--warning-light)', color: 'var(--warning)', icon: 'bi-exclamation-circle-fill', cls: 'btn-warning', title: 'Attention' },
    };

    function showConfirm(message, callback, opts) {
        opts = opts || {};
        var preset = _confirmPresets[opts.type || 'danger'];
        document.getElementById('confirmIconWrap').style.background = preset.bg;
        var icon = document.getElementById('confirmIcon');
        icon.className = 'bi fs-2 ' + preset.icon;
        icon.style.color = preset.color;
        document.getElementById('confirmTitle').textContent   = opts.title       || 'Confirmation';
        document.getElementById('confirmMessage').textContent = message;
        var btn = document.getElementById('confirmBtn');
        btn.className   = 'btn px-4 ' + preset.cls;
        btn.textContent = opts.confirmText || preset.label;
        _confirmCallback = callback;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfirm')).show();
    }

    function confirmForm(form, message, opts) {
        showConfirm(message, function() { form.submit(); }, opts);
    }

    document.getElementById('confirmBtn').addEventListener('click', function () {
        bootstrap.Modal.getInstance(document.getElementById('modalConfirm')).hide();
        if (_confirmCallback) { _confirmCallback(); _confirmCallback = null; }
    });

    function showAlert(message, type, title) {
        var preset = _alertPresets[type || 'danger'];
        document.getElementById('alertIconWrap').style.background = preset.bg;
        var icon = document.getElementById('alertIcon');
        icon.className = 'bi fs-2 ' + preset.icon;
        icon.style.color = preset.color;
        document.getElementById('alertTitle').textContent   = title   || preset.title;
        document.getElementById('alertMessage').textContent = message;
        var btn = document.getElementById('alertCloseBtn');
        btn.className = 'btn px-5 ' + preset.cls;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAlert')).show();
    }
</script>

@stack('scripts')
</body>
</html>
