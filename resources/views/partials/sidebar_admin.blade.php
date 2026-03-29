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
    <a class="sb-link {{ ($active ?? '') === 'settings' ? 'active' : '' }}"
       href="{{ route('admin.settings.page') }}">
        <span>⚙️</span> Paramètres
    </a>
    <form method="POST" action="{{ route('logout') }}" style="margin-top:auto;" data-requires-consent="true"
          data-consent-message="Êtes-vous sûr de vouloir vous déconnecter ?">
        @csrf
        <button type="submit" class="sb-logout" aria-label="Se déconnecter">
            Déconnexion
        </button>
    </form>
</aside>