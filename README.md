# Projet Sortir

Ce projet est une application web développée avec Symfony qui permet aux utilisateurs de proposer et de s'inscrire à des sorties.

## Fonctionnalités clés

### Gestion des utilisateurs
- **Inscription :** Les utilisateurs peuvent créer un compte en fournissant un pseudo, un nom, un prénom, une adresse e-mail et un mot de passe.
- **Connexion :** Les utilisateurs peuvent se connecter avec leur pseudo ou leur adresse e-mail et leur mot de passe.
- **Profil :** Les utilisateurs peuvent consulter et modifier leur profil, y compris leur photo de profil.

### Gestion des sorties
- **Création :** Les utilisateurs connectés peuvent créer de nouvelles sorties en fournissant des détails tels que le nom, la date, la durée, le lieu, etc.
- **Modification :** Les organisateurs de sorties peuvent modifier les informations de leurs sorties.
- **Annulation :** Les organisateurs peuvent annuler leurs sorties.
- **Publication :** Les organisateurs peuvent publier leurs sorties pour les rendre visibles aux autres utilisateurs.

### Recherche et filtre de sorties
- Les utilisateurs peuvent rechercher des sorties par mot-clé.
- Les utilisateurs peuvent filtrer les sorties par campus, par date, et par type de sortie (organisateur, inscrit, non inscrit, passées).

### Inscription et désistement aux sorties
- Les utilisateurs peuvent s'inscrire aux sorties qui les intéressent.
- Les utilisateurs peuvent se désister des sorties auxquelles ils sont inscrits.

### Gestion des lieux et des villes
- Les utilisateurs peuvent ajouter de nouveaux lieux en spécifiant une rue, une ville, une latitude et une longitude.
- L'application utilise Leaflet.js pour afficher une carte interactive des lieux.

### Interface d'administration
- Les administrateurs ont accès à une interface spéciale pour gérer les utilisateurs et les sorties.
- Les administrateurs peuvent créer, modifier et supprimer des utilisateurs.
- Les administrateurs peuvent annuler des sorties.

## Processus d'authentification et d'inscription

### Connexion
- Le processus de connexion est géré par le `SecurityController` (`src/Controller/SecurityController.php`).
- La route `/login` (`app_login`) affiche le formulaire de connexion (`templates/security/login.html.twig`).
- Le formulaire est de type `PasswordLoginType` (`src/Form/PasswordLoginType.php`) et demande le pseudo ou l'e-mail et le mot de passe.
- La validation de l'authentification est gérée par le pare-feu de sécurité de Symfony.

### Inscription
Le processus d'inscription se déroule en deux étapes :

**1. Vérification de l'adresse e-mail :**
- L'utilisateur accède à la page d'inscription (`/inscription`, route `app_register_check_email`) gérée par le `RegistrationController` (`src/Controller/RegistrationController.php`).
- Il saisit son adresse e-mail dans un formulaire de type `EmailCheckType` (`src/Form/EmailCheckType.php`).
- Le système vérifie que l'adresse e-mail se termine par `@campus-eni.fr` ou `@eni-ecole.fr`.
- Si l'adresse e-mail n'existe pas dans la base de données, un nouvel utilisateur (`Participant`) est créé avec un statut non vérifié (`isVerified = false`) et un jeton d'activation unique.
- Un e-mail d'activation est envoyé à l'utilisateur via le service `MailerInterface`. Le contenu de l'e-mail est généré à partir des templates Twig `emails/activation.html.twig` et `emails/activation.text.twig`.

**2. Finalisation de l'inscription :**
- L'utilisateur clique sur le lien d'activation dans l'e-mail, qui le redirige vers la page de finalisation de l'inscription (`/inscription/finaliser/{token}`, route `app_register_complete_with_token`), également gérée par le `RegistrationController`.
- Le système vérifie la validité du jeton et son expiration.
- L'utilisateur remplit le formulaire d'inscription complet (`RegistrationFormType` - `src/Form/RegistrationFormType.php`) avec son pseudo, nom, prénom et mot de passe.
- Le mot de passe est haché à l'aide du service `UserPasswordHasherInterface`.
- Le statut de l'utilisateur est mis à jour (`isVerified = true`, `actif = true`), le jeton d'activation est supprimé.
- L'utilisateur est ensuite redirigé vers la page de connexion.

### Réinitialisation du mot de passe
- Le processus de réinitialisation du mot de passe est géré par le `ResetPasswordController` (`src/Controller/ResetPasswordController.php`).
- Ce processus utilise le bundle `symfonycasts/reset-password-bundle` pour générer et valider les jetons de réinitialisation de mot de passe.

## Technologies utilisées

- **Backend :** Symfony 7, PHP 8
- **Frontend :** Twig, Tailwind CSS, JavaScript
- **Base de données :** MariaDB (via Docker)
- **Autres :** Leaflet.js pour les cartes interactives
