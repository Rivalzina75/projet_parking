<aside class="page-sidebar sb-user">
    <div class="sb-brand">ParkingPro</div>
    <a class="sb-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}" href="{{ route('user.dashboard') }}">Tableau de bord</a>
    <a class="sb-link {{ ($active ?? '') === 'profil' ? 'active' : '' }}" href="{{ route('user.profile') }}">Mon profil</a>
    <form method="POST" action="{{ route('logout') }}" style="margin-top:auto;">
        @csrf
        <button type="submit" class="sb-logout">Déconnexion</button>
    </form>
</aside>