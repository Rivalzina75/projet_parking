# Documentation utilisateur - ParkingPro

## 1. A quoi sert l'application ?
ParkingPro permet de gérer l'attribution des places de parking du personnel.

Fonctionnement global:
- une demande de réservation attribue immédiatement une place libre (tirage aléatoire);
- s'il n'y a plus de place, l'utilisateur est ajouté à la file d'attente;
- quand une place se libère, le premier de la file est servi automatiquement.

## 2. Profils d'accès

### Utilisateur standard
Peut:
- consulter sa place active;
- faire une demande de réservation;
- voir son rang en file d'attente;
- consulter son historique;
- changer son mot de passe.

### Administrateur
Peut, en plus:
- valider les comptes;
- créer des comptes utilisateurs;
- gérer les places (ajout, suppression, historique);
- forcer ou retirer une attribution;
- gérer la file d'attente;
- configurer les paramètres de sécurité.

## 3. Parcours utilisateur standard

### 3.1 Créer un compte
1. Aller sur `/inscription`.
2. Remplir prénom, nom, email, mot de passe + confirmation.
3. Envoyer le formulaire.
4. Le compte est créé en attente de validation administrateur.

### 3.2 Se connecter
1. Aller sur `/login`.
2. Saisir email et mot de passe.
3. Si le compte n'est pas validé, une page "Compte en attente" s'affiche.

### 3.3 Utiliser le tableau de bord
URL: `/utilisateur/dashboard`

Le dashboard affiche 3 états possibles:
- Place active: numéro de place + localisation + expiration + bouton "Libérer ma place".
- En file d'attente: rang et date d'entrée en file.
- Aucun statut: bouton "Demander une place".

### 3.4 Demander une place
Depuis le dashboard, cliquer sur "Demander une place".

Résultat:
- si place libre: attribution immédiate;
- sinon: ajout en file d'attente avec rang affiché.

### 3.5 Libérer sa place
Depuis le dashboard, cliquer sur "Libérer ma place".

Effet:
- votre réservation est clôturée;
- une place peut être réattribuée automatiquement au premier de la file d'attente.

### 3.6 Consulter l'historique
URL: `/utilisateur/historique`

Vous voyez:
- la place;
- la date de début;
- l'expiration prévue;
- la fin réelle;
- le statut (active/terminée).

### 3.7 Gérer son profil
URL: `/utilisateur/profil`

Vous pouvez:
- consulter vos informations;
- modifier votre mot de passe (mot de passe actuel requis).

Règles mot de passe:
- au moins 10 caractères;
- au moins une minuscule;
- au moins une majuscule;
- au moins un chiffre;
- au moins un caractère spécial.

### 3.8 Mot de passe oublié
1. Aller sur `/reset-password`.
2. Saisir l'email du compte.
3. Un jeton de réinitialisation est affiché.
4. L'utiliser sur l'écran de confirmation pour définir un nouveau mot de passe.

## 4. Parcours administrateur

Les pages d'administration sont accessibles via le menu "Administration".

### 4.1 Utilisateurs
URL: `/admin/utilisateurs`

Actions disponibles:
- créer un compte utilisateur directement;
- valider un compte en attente;
- ouvrir la fiche utilisateur;
- forcer une réservation;
- enlever une réservation (ou retirer de la file d'attente).

### 4.2 Fiche utilisateur
URL: `/admin/utilisateurs/{id}`

Actions disponibles:
- voir les informations complètes;
- consulter la place active;
- clôturer la réservation active;
- réinitialiser le mot de passe (un mot de passe temporaire est généré);
- consulter l'historique de réservations de l'utilisateur.

### 4.3 Gestion des places
URL: `/admin/places`

Actions disponibles:
- visualiser les indicateurs (total, occupées, libres);
- définir la durée par défaut des réservations (en heures);
- ajouter une place;
- attribuer manuellement une place à un utilisateur;
- consulter l'historique d'une place;
- supprimer une place libre.

Important:
- une place occupée ne peut pas être supprimée;
- si la durée par défaut est réglée à `0`, la réservation devient infinie.

### 4.4 File d'attente
URL: `/admin/liste-attente`

Actions disponibles:
- voir l'ordre des utilisateurs en attente;
- modifier manuellement une position.

### 4.5 Paramètres
URL: `/admin/parametres`

Action disponible:
- activer/désactiver le double consentement.

Quand activé:
- les actions sensibles (suppression, reset mot de passe, déconnexion, etc.) demandent une confirmation supplémentaire.

## 5. Règles de fonctionnement importantes
- Un utilisateur ne peut pas avoir plusieurs réservations actives.
- Un utilisateur déjà en file d'attente ne peut pas refaire une demande.
- Les réservations expirées sont clôturées automatiquement.
- La file d'attente est réordonnée automatiquement après chaque changement.
- Les comptes non validés n'accèdent pas aux pages métier.

## 6. Pages utiles
- Accueil: `/`
- Aide: `/aide`
- Mentions légales: `/mentions-legales`
- Inscription: `/inscription`
- Connexion: `/login`
- Mot de passe oublié: `/reset-password`

## 7. Comptes de démonstration (si base seedée)
- Admin: `admin@parking.local` / `Admin@123456`
- Utilisateur: `user@parking.local` / `User@123456`

## 8. Dépannage rapide

### "Votre compte n'est pas validé"
Cause: le compte existe mais attend la validation admin.

Action: demander à un administrateur de valider le compte.

### "Identifiants invalides"
Cause: email ou mot de passe incorrect.

Action: réessayer, puis utiliser le flux "mot de passe oublié" si besoin.

### "Aucune place libre"
Cause: parking complet.

Action: l'utilisateur est ajouté automatiquement à la file d'attente.
