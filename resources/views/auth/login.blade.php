<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Amira a fait le poulet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --or: #F4621F; --or-l: #FEF0E8; --or-d: #C44E18;
            --bg: #F7F4F0; --card: #fff;
            --bd: #EAE5DF; --t1: #1A1410; --t2: #6B5E52;
        }
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; background: var(--bg); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: var(--card); border: 1px solid var(--bd); border-radius: 16px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 4px 24px rgba(0,0,0,.07); }
        .brand { text-align: center; margin-bottom: 32px; }
        .brand-icon { font-size: 48px; display: block; margin-bottom: 10px; }
        .brand-name { font-size: 22px; font-weight: 700; color: var(--t1); }
        .brand-sub { font-size: 13px; color: var(--t2); margin-top: 2px; }
        .form-label { font-size: 13px; font-weight: 600; color: var(--t2); }
        .form-control { border-color: var(--bd); border-radius: 8px; font-size: 14px; padding: 10px 14px; }
        .form-control:focus { border-color: var(--or); box-shadow: 0 0 0 3px rgba(244,98,31,.12); }
        .btn-login { background: var(--or); border-color: var(--or); color: #fff; font-weight: 600; border-radius: 8px; padding: 11px; font-size: 15px; width: 100%; transition: background .2s; }
        .btn-login:hover { background: var(--or-d); border-color: var(--or-d); color: #fff; }
        .divider { text-align: center; font-size: 11px; color: #aaa; margin: 20px 0; }
        .role-hint { background: var(--or-l); border-radius: 8px; padding: 10px 14px; font-size: 12px; color: var(--or-d); }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand">
        <span class="brand-icon"><img src="/img/logo.jpg" alt=""></span>
        <div class="brand-name">Amira.A.FAIT.LE.POULET</div>
        <div class="brand-sub">Tableau de bord de gestion</div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2 mb-3" style="font-size:13px;border-radius:8px">
            <i class="bi bi-x-circle me-1"></i>{{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="prenom@amira.com" autofocus required>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember" style="font-size:13px">Se souvenir de moi</label>
            </div>
        </div>

        <button type="submit" class="btn btn-login">
            Se connecter
        </button>
    </form>

    <div class="divider">Comptes de démonstration</div>
    <div class="role-hint">
        <strong>Admin :</strong> admin@amira.com<br>
        <strong>Caissier :</strong> caissier@amira.com<br>
        <span style="opacity:.7">Mot de passe : <strong>password</strong></span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
