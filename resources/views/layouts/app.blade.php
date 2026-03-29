<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ParkingPro')</title>
    @vite(['resources/css/style.css'])
</head>
<body>

<header class="topbar">
    <div class="container topbar-inner">
        <a class="brand" href="{{ route('home') }}">
            <span class="brand-mark">P</span>
            ParkingPro
        </a>

        <nav class="topbar-nav">
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

            <div class="topbar-actions">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost" style="font-size:13px;">Déconnexion</button>
                    </form>
                @else
                    <a href="{{ route('register.show') }}" class="btn btn-primary">Inscription</a>
                @endauth
            </div>
        </nav>
    </div>
</header>

<main class="container">
    @if(session('message'))
        <div class="alert success" style="margin-top:0; margin-bottom:20px;">
            <span>✓</span>
            <div>{{ session('message') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert error" style="margin-top:0; margin-bottom:20px;">
            <span>✕</span>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="container">
    <span>ParkingPro · Gestion interne des places de stationnement</span>
    <a href="{{ route('legal') }}" style="color: var(--text-3);">Mentions légales</a>
</footer>

</body>
</html>