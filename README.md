# PPE - Attribution des places de parking

## Table des matières

- [1. Présentation](#1-présentation)
- [2. Spécification du besoin](#2-spécification-du-besoin)
- [3. Résultats attendus](#3-résultats-attendus)
  - [3.1 Sécurité](#31-sécurité)
  - [3.2 Gestion des mots de passe](#32-gestion-des-mots-de-passe)
  - [3.3 Espace utilisateur](#33-espace-utilisateur)
  - [3.4 Espace administrateur](#34-espace-administrateur)
  - [3.5 Pages web](#35-pages-web)
  - [3.6 Documentation](#36-documentation)
  - [3.7 Accès depuis le réseau local](#37-accès-depuis-le-réseau-local)
- [4. Productions](#4-productions)
  - [4.1 Chef de projet](#41-chef-de-projet)
  - [4.2 Équipe](#42-équipe)
  - [4.3 Individuel](#43-individuel)

---

## 1. Présentation

Afin d'éviter le stationnement sauvage dans le labyrinthe qu'est le parking, il a été décidé d'attribuer à chaque membre qui le demandait une place de parking numérotée.

---

## 2. Spécification du besoin

Le front-office doit être sécurisé et n'accepter que les demandes du personnel des ligues. Les inscriptions au service de réservation de place doivent être validées (ou créées) par un administrateur.

L'administrateur, seul utilisateur du back-office, doit pouvoir éditer la liste des places et gérer les inscriptions des utilisateurs.

Lorsqu'un utilisateur en fait la demande, une place libre lui est attribuée aléatoirement et immédiatement par l'application, la réservation expire automatiquement au bout d'une durée par défaut déterminée par l'administrateur.

Si une demande ne peut pas être satisfaite, l'utilisateur est placé en liste d'attente.

L'utilisateur ne peut pas choisir la date à laquelle une place lui est attribuée, les réservations sont toujours immédiates. Un utilisateur ne peut pas faire une demande de réservation s'il est en file d'attente ou qu'il occupe une place.

Un utilisateur ou l'administrateur peuvent fermer une réservation avant la date d'expiration prévue. Une fois celle-ci expirée, l'utilisateur doit refaire une demande s'il souhaite obtenir une place.

---

## 3. Résultats attendus

Il serait souhaitable que les fonctionnalités ci-après soient mises en place. Si vous n'avez pas le temps de toutes les traiter, occupez-vous d'abord des plus importantes. S'il reste du temps, cherchez des améliorations à apporter.

### 3.1 Sécurité

- Protection des accès par mot de passe.
- Contrôles de saisie des données côté serveur.
- Contrôles de saisie côté client.
- Protection contre les attaques par injection.

### 3.2 Gestion des mots de passe

- Fonction "mot de passe perdu ?".
- Hachage des mots de passe.

### 3.3 Espace utilisateur

- Vérification de l'identité par saisie d'un mot de passe.
- Possibilité de visualiser le numéro de place attribuée, ainsi que l'historique des places précédemment attribuées.
- Possibilité de faire une demande de réservation.
- Possibilité de connaître son rang sur la file d'attente.
- Modification du mot de passe.

### 3.4 Espace administrateur

- Protection de l'accès par mot de passe.
- Édition de la liste des utilisateurs, réinitialisation des mots de passe.
- Édition de la liste des places.
- Consultation de la liste d'attente.
- Consultation de l'historique d'attribution des places.
- Attribution manuelle des places.
- Édition de la file d'attente (modification de la position des personnes en attente).

### 3.5 Pages web

- Mise aux normes HTML5 et CSS des pages web.
- Utilisation d'un design responsive.

### 3.6 Documentation

- Liste des tâches à accomplir triées par ordre de priorité.
- Le plan du site, dans lequel vous préciserez les URLs.
- Documentation utilisateur, accessible depuis l'application.
- Documentation permettant de comprendre comment est construite l'application (schémas, impressions d'écran, architecture de l'application, MCD...).

### 3.7 Accès depuis le réseau local

- Installation de l'application sur un serveur accessible depuis le réseau local.

---

## 4. Productions

### 4.1 Chef de projet

Remise à la fin de la séance d'un rapport indiquant :

- La répartition des tâches et le planning (il est conseillé de faire un diagramme de Gantt).
- Il est conseillé d'utiliser un logiciel de gestion de tâches et de travail collaboratif (Trello, Asana, Slack, etc.)

### 4.2 Équipe

- Présentation sur diapositives (PDF ou PowerPoint) des productions par l'équipe. Le chef de projet anime la présentation, mais tous les membres de l'équipe doivent intervenir au moins une fois.
- Démonstration des productions.

### 4.3 Individuel

Ajoutez à votre portfolio :

- Un compte-rendu d'activité détaillant le travail que vous avez effectué (extraits de code, explications, impressions d'écran, compétences du référentiel mises en œuvre).
- Le code source sur [GitHub](http://github.com/).
- La documentation utilisateur.
- La documentation développeur.

---

*Ce document a été traduit de LaTeX par [HEVEA](http://hevea.inria.fr/).*




Barême :

Base de données
Note maximale6
git
Tout le monde a utilisé git
Note maximale3
Connexion
Note maximale1
Réserver
Note maximale3
Historique résas
Note maximale1
Liste attente
Note maximale1
Validations inscriptions
Note maximale1
Places (ajouter/modifier/supprimer)
Note maximale1
Forcer réservations
Note maximale1
Edition liste attente
Note maximale1
Historique place
Note maximale1