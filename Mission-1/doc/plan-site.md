# Plan du site (URLs)

## Public
- `/` : accueil
- `/inscription` : demande d’inscription
- `/login` : connexion
- `/reset-password` : mot de passe perdu
- `/aide` : documentation utilisateur
- `/mentions-legales` : mentions légales

## Utilisateur connecté (validé)
- `/utilisateur/dashboard` : place active, file d’attente, demande de réservation, historique
- `/utilisateur/profil` : informations utilisateur + changement de mot de passe

## Administrateur
- `/admin/utilisateurs` : liste des utilisateurs, validation compte, reset mdp, forçage réservation
- `/admin/utilisateurs/{id}` : fiche utilisateur + historique + clôture de réservation
- `/admin/places` : ajout/modification/suppression de places, état d’occupation, durée par défaut
- `/admin/liste-attente` : consultation et édition de la file d’attente
