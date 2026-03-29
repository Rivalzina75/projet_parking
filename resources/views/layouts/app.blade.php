<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <!-- Meta essentielles -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="ParkingPro - Système sécurisé de gestion des places de parking pour le personnel. Attribution immédiate ou file d'attente en temps réel.">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="format-detection" content="telephone=no">
    
    <!-- SEO -->
    <meta name="robots" content="index, follow">
    <meta name="language" content="fr">
    <meta property="og:site_name" content="ParkingPro">
    <meta property="og:type" content="website">
    
    <title>@yield('title', 'ParkingPro')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%231d4ed8' width='100' height='100'/><text x='50' y='70' font-size='70' font-weight='bold' fill='white' text-anchor='middle'>P</text></svg>">
    
    @vite(['resources/css/style.css', 'resources/js/script.js'])
</head>
<body>
    <a href="#main-content" class="sr-only">Accéder au contenu principal</a>
    
    <header class="topbar" role="banner">
        <div class="container topbar-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="ParkingPro - Acceuil">
                <span class="brand-mark" aria-hidden="true">P</span>
                <span>ParkingPro</span>
            </a>

            <button type="button" class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation" aria-controls="nav-menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="topbar-nav" id="nav-menu" role="navigation">
                <div class="topbar-links">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Accueil</a>
                    <a class="nav-link {{ request()->routeIs('help') ? 'active' : '' }}" href="{{ route('help') }}">Aide</a>
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.users') }}">Administration</a>
                        @else
                            <a class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('user.dashboard') }}">Mon espace</a>
                        @endif
                    @else
                        <a class="nav-link {{ request()->routeIs('login', 'login.*') ? 'active' : '' }}" href="{{ route('login') }}">Connexion</a>
                    @endauth
                </div>

                @guest
                <div class="topbar-actions">
                        <a href="{{ route('register.show') }}" class="btn btn-primary">Inscription</a>
                </div>
                @endguest
            </nav>
        </div>
    </header>

    <main class="container" id="main-content" role="main">
        @if(session('message'))
            <div class="alert success" role="status" aria-live="polite">
                <span aria-hidden="true">✓</span>
                <div>{{ session('message') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert error" role="alert" aria-live="polite">
                <span aria-hidden="true">✕</span>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="container" role="contentinfo">
        <span>ParkingPro · Gestion interne des places de stationnement</span>
        <a href="{{ route('legal') }}">Mentions légales</a>
    </footer>

    <div id="consent-modal" class="consent-modal" aria-hidden="true">
        <div class="consent-modal-backdrop" data-consent-close="true"></div>
        <section class="consent-modal-card" role="dialog" aria-modal="true" aria-labelledby="consent-modal-title" aria-describedby="consent-modal-text">
            <h2 id="consent-modal-title">Confirmation requise</h2>
            <p id="consent-modal-text">Confirmer cette action&nbsp;?</p>
            <div class="consent-modal-actions">
                <button type="button" class="btn btn-sm" id="consent-cancel">Annuler</button>
                <button type="button" class="btn btn-sm btn-primary" id="consent-confirm">Confirmer</button>
            </div>
        </section>
    </div>
    
    <script>
        window.appSettings = {
            doubleConsentEnabled: @json((bool) ($doubleConsentEnabled ?? false)),
        };
    </script>
</body>
</html>