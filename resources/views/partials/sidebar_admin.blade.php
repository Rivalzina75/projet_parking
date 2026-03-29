<aside class="page-sidebar sb-admin">
    <div class="sb-brand">ParkingPro</div>
    <a class="sb-link {{ ($active ?? '') === 'users' ? 'active' : '' }}" href="{{ route('admin.users') }}">Utilisateurs</a>
    <a class="sb-link {{ ($active ?? '') === 'places' ? 'active' : '' }}" href="{{ route('admin.places') }}">Places</a>
    <a class="sb-link {{ ($active ?? '') === 'waiting' ? 'active' : '' }}" href="{{ route('admin.waiting') }}">Liste d'attente</a>
    <form method="POST" action="{{ route('logout') }}" style="margin-top:auto;">
        @csrf
        <button type="submit" class="sb-logout">Déconnexion</button>
    </form>
</aside>