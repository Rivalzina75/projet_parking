# Documentation développeur - ParkingPro

## 1. Objectif du projet
ParkingPro est une application Laravel de gestion des places de parking pour un personnel interne.

Principes métier:
- un utilisateur standard peut demander une place;
- si une place est libre, elle est attribuée immédiatement et aléatoirement;
- sinon, l'utilisateur est placé en file d'attente;
- une réservation peut se terminer manuellement (utilisateur/admin) ou automatiquement (expiration).

## 2. Stack technique
- Backend: Laravel 12, PHP
- Frontend: Blade, Vite, Tailwind CSS 4, JavaScript vanilla
- Base de données: SQLite (dev rapide) ou MySQL 8 (Sail)
- Tests: PHPUnit (Feature + Unit)

Note runtime:
- dans ce dépôt (dépendances actuellement installées), l'exécution CLI requiert PHP 8.4+.
- si votre machine est en PHP 8.3 ou moins, utilisez Sail.

## 3. Architecture applicative

Composants principaux:
- `app/Http/Controllers/auth`: inscription, connexion, réinitialisation mot de passe
- `app/Http/Controllers/UserController.php`: dashboard, historique, profil utilisateur
- `app/Http/Controllers/AdminController.php`: gestion utilisateurs, places, file d'attente, paramètres
- `app/Http/Controllers/ReservationController.php`: demande, clôture, forçage/résiliation admin
- `app/Services/ParkingService.php`: logique métier centrale (attribution, file d'attente, expiration)
- `app/Models`: modèles Eloquent (`User`, `ParkingSpot`, `Reservation`, `WaitingListEntry`, `Param`)
- `routes/web.php`: routes HTTP
- `routes/console.php`: scheduler (tâches toutes les minutes)

## 4. Modèle de données

### `users`
- colonnes métier clés: `lastname`, `role`, `is_validated`
- `role` attendu: `admin` ou `user`
- `is_validated` pilote l'accès aux espaces métier

### `parking_spots`
- `number` (unique), `location`, `is_active`

### `reservations`
- FK: `user_id`, `parking_spot_id`
- dates: `starts_at`, `expires_at` (nullable), `ended_at` (nullable)
- audit: `closed_by` (nullable), `notes` (nullable)

Sémantique:
- réservation active si `ended_at IS NULL` et (`expires_at IS NULL` ou `expires_at > now()`).
- `expires_at = NULL` signifie une réservation infinie (durée paramétrée = 0).

### `waiting_list_entries`
- une ligne par utilisateur (`user_id` unique)
- position ordonnée (`position` unique)

### `params`
Paramètres applicatifs stockés en clé/valeur (`name`, `value`):
- `default_reservation_hours` (int, défaut 8)
- `double_consent_enabled` (bool)

## 5. Règles métier implémentées

### 5.1 Demande de réservation
Méthode: `ParkingService::requestReservation(User $user)`

Règles:
- refuse si l'utilisateur a déjà une réservation active;
- refuse si l'utilisateur est déjà en file d'attente;
- tente une place active libre aléatoire;
- sinon ajoute l'utilisateur en fin de file d'attente.

### 5.2 Attribution manuelle admin
Méthode: `ParkingService::assignSpecificSpotToUser(User $user, ParkingSpot $spot, ?int $closedBy)`

Règles:
- utilisateur doit être validé;
- utilisateur ne doit pas avoir de réservation active;
- place doit être active et libre;
- suppression de l'utilisateur de la file d'attente après attribution.

### 5.3 Clôture de réservation
Méthode: `ParkingService::closeReservation(Reservation $reservation, ?int $closedBy)`

Effets:
- met `ended_at` et `closed_by`;
- tente d'attribuer immédiatement la place libérée au premier de file.

### 5.4 File d'attente
- ajout automatique si parking saturé;
- déplacement manuel admin via `moveWaitingEntry()`;
- réordonnancement systématique des positions.

### 5.5 Expiration automatique
`closeExpiredReservations()` ferme les réservations expirées, puis déclenche la mécanique de réattribution.

## 6. Sécurité et contrôle d'accès

Middlewares:
- `auth`: utilisateur connecté
- `validated`: compte validé obligatoire pour espace utilisateur
- `admin`: rôle administrateur obligatoire
- `BlockUnvalidatedAccountMiddleware` (web global): bloque les comptes non validés hors page dédiée

Mesures présentes:
- validation serveur systématique (`$request->validate`)
- hash des mots de passe (`Hash::make`)
- régénération de session après login
- contrôle de propriété/admin pour clôture d'une réservation

## 7. Routes fonctionnelles (résumé)

Public/guest:
- `GET /` accueil
- `GET /aide` aide
- `GET /mentions-legales` mentions légales
- `GET|POST /inscription`
- `GET|POST /login`
- `GET|POST /reset-password`
- `POST /reset-password/confirm`

Utilisateur validé (`/utilisateur`):
- `GET /dashboard`
- `GET /historique`
- `GET /profil`
- `POST /profil/password`
- `POST /reservation`
- `POST /reservation/{reservation}/close`

Admin (`/admin`):
- utilisateurs: listing, création, fiche, validation, reset mdp
- réservations: forcer attribution, enlever attribution, clôturer
- places: listing, ajout, mise à jour, suppression, historique place, attribution manuelle
- file d'attente: listing + déplacement
- paramètres: page + sauvegarde

## 8. Démarrage en local

### Option A - Exécution locale native
Prérequis:
- PHP 8.4+
- Composer
- Node.js 20+
- npm

Étapes:
1. `cp .env.example .env`
2. `composer install`
3. `npm install`
4. `php artisan key:generate`
5. configurer la base de données (SQLite ou MySQL) dans `.env`
6. `php artisan migrate --seed`
7. `php artisan serve`
8. `npm run dev`
9. dans un 3e terminal: `php artisan schedule:work`

### Option B - Exécution avec Sail (recommandée si PHP local incompatible)
1. `cp .env.example .env`
2. `composer install`
3. `./vendor/bin/sail up -d`
4. `./vendor/bin/sail artisan key:generate`
5. configurer `.env` MySQL (`DB_HOST=mysql`, port interne 3306)
6. `./vendor/bin/sail artisan migrate --seed`
7. `./vendor/bin/sail npm install`
8. `./vendor/bin/sail npm run dev`
9. dans un autre terminal: `./vendor/bin/sail artisan schedule:work`

## 9. Données de démo (seed)
- Admin: `admin@parking.local` / `Admin@123456`
- Utilisateur: `user@parking.local` / `User@123456`
- 8 utilisateurs supplémentaires via factory
- 10 places de `P-01` à `P-10`

## 10. Scheduler
Déclaré dans `routes/console.php`:
- `parking-close-expired` (chaque minute)
- `parking-assign-waiting` (chaque minute, si file non vide)

En production, le scheduler Laravel doit tourner en continu.

## 11. Tests

Commandes utiles:
- `php artisan test`
- `composer test`

Couverture actuelle (principales familles):
- Auth (login/register/reset/password policy)
- Middleware de validation
- Flux de réservation
- Intégration admin (utilisateurs, places, file)
- Service `ParkingService`
- Modèles et relations
- Sécurité (validation, injection, XSS)
- Scheduler

## 12. Points de vigilance
- Le paramètre `double_consent_enabled` agit côté front (modal de confirmation sur actions sensibles).
- Si `default_reservation_hours = 0`, `expires_at` est `NULL` (réservation infinie): toute logique d'affichage doit gérer ce cas.
- En environnement Docker, si port 3306 déjà pris, utiliser `FORWARD_DB_PORT=3307` dans `.env`.
