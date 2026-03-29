{{-- resources/views/partials/sidebar_user.blade.php --}}
<aside class="page-sidebar sb-user">
    <div class="sb-brand">ParkingPro</div>
    <a class="sb-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}"
       href="{{ route('user.dashboard') }}">
        <span>📊</span> Tableau de bord
    </a>
    <a class="sb-link {{ ($active ?? '') === 'profil' ? 'active' : '' }}"
       href="{{ route('user.profile') }}">
        <span>👤</span> Mon profil
    </a>
    <form method="POST" action="{{ route('logout') }}" style="margin-top:auto;" data-requires-consent="true"
          data-consent-message="Êtes-vous sûr de vouloir vous déconnecter ?">
        @csrf
        <button type="submit" class="sb-logout" aria-label="Se déconnecter">
            Déconnexion
        </button>
    </form>
</aside>