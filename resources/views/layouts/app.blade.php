<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Parking')</title>
    @vite(['resources/css/style.css'])
</head>
<body>
<header class="topbar">
    <div class="container topbar-inner">
        <a class="brand" href="{{ route('home') }}">
            <span class="brand-mark">P</span>
            <span>ParkingPro</span>
        </a>
        <nav class="row gap topbar-nav">
            <div class="row topbar-links">
                <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Accueil</a>
                <a class="nav-link {{ request()->routeIs('help') ? 'active' : '' }}" href="{{ route('help') }}">Aide</a>
                <a class="nav-link {{ request()->routeIs('legal') ? 'active' : '' }}" href="{{ route('legal') }}">Mentions légales</a>
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.users') }}">Admin</a>
                    @else
                        <a class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}" href="{{ route('user.dashboard') }}">Mon espace</a>
                    @endif
                @else
                    <a class="nav-link {{ request()->routeIs('login', 'login.*') ? 'active' : '' }}" href="{{ route('login') }}">Connexion</a>
                @endauth
            </div>

            <div class="row topbar-actions">
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-light">Déconnexion</button>
                    </form>
                @else
                    <a href="{{ route('register.show') }}" class="btn btn-primary">Inscription</a>
                @endauth
            </div>
        </nav>
    </div>
</header>

<main class="container py">
    @if(session('message'))
        <div class="alert success">{{ session('message') }}</div>
    @endif

    @if($errors->any())
        <div class="alert error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="container footer">
    <p>ParkingPro · Application interne d'attribution des places</p>
</footer>
</body>
</html>