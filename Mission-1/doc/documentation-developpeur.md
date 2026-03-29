# Documentation développeur

## Stack
- Laravel 12
- PHP 8.4+ (requis par les dépendances)
- Blade + Vite CSS

## Modèle de données
- `users` : comptes + rôles (`admin`/`user`) + validation (`is_validated`)
- `parking_spots` : places de parking
- `reservations` : attributions de places (active + historique)
- `waiting_list_entries` : file d’attente ordonnée
- `app_settings` : paramètres applicatifs (durée par défaut)

## Logique métier
- Attribution aléatoire d’une place libre via `ParkingService`.
- Si aucune place libre: insertion en file d’attente.
- Expiration/fermeture de réservation -> tentative d’attribution au premier de la file.
- Réordonnancement automatique des positions d’attente.

## Sécurité
- Validation serveur sur formulaires.
- Mots de passe hashés (`Hash::make`).
- Routes protégées par middlewares `auth`, `validated`, `admin`.
- Contrôle d’accès sur clôture de réservation.

## Seed de démonstration
- Admin: `admin@parking.local` / `Admin@123456`
- Utilisateur: `user@parking.local` / `User@123456`
- 10 places initiales (`P-01` à `P-10`)

## Lancement local
1. Installer dépendances.
2. Configurer `.env` + base de données.
3. Exécuter `php artisan migrate --seed`.
4. Lancer `php artisan serve` et `npm run dev`.
5. Lancer le scheduler: `php artisan schedule:work`.

## Tâches automatiques (Scheduler)
- `parking-close-expired` (chaque minute): clôture les réservations expirées.
- `parking-assign-waiting` (chaque minute): attribue une place au premier de la file si possible.

## Remarque environnement
- Si PHP local < 8.4, exécuter le projet via conteneur (Docker/Sail) ou mettre à jour PHP.
