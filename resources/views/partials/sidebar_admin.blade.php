{{-- resources/views/partials/sidebar_admin.blade.php --}}
<aside class="page-sidebar sb-admin">
    <div class="sb-brand">Admin</div>
    <a class="sb-link {{ ($active ?? '') === 'users' ? 'active' : '' }}"
       href="{{ route('admin.users') }}">
        <span>👥</span> Utilisateurs
    </a>
    <a class="sb-link {{ ($active ?? '') === 'places' ? 'active' : '' }}"
       href="{{ route('admin.places') }}">
        <span>🅿</span> Places
    </a>
    <a class="sb-link {{ ($active ?? '') === 'waiting' ? 'active' : '' }}"
       href="{{ route('admin.waiting') }}">
        <span>⏳</span> File d'attente
    </a>
    <form method="POST" action="{{ route('logout') }}" style="margin-top:auto;">
        @csrf
        <button type="submit" class="sb-logout">
            <span>→</span> Déconnexion
        </button>
    </form>
</aside>