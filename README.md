# Sortir.com - Application de gestion de sorties

Bienvenue sur le projet Sortir.com. Ce document détaille la procédure d'installation et de configuration, notamment pour un environnement Windows avec WAMP.

## Prérequis

- **WAMP Server** (Version 3.3.0 ou supérieure recommandée)
    - PHP 8.2+
    - MySQL 8.0+ ou MariaDB
- **Composer** (Gestionnaire de dépendances PHP)
- **Git**

## Installation

### 1. Récupération du projet
Ouvrez un terminal dans votre dossier `www` de WAMP (ex: `c:\wamp64\www`).
```bash
git clone <url_du_repo> Sortir
cd Sortir
```

### 2. Installation des dépendances
```bash
composer install
```

### 3. Configuration de l'environnement
Copiez le fichier `.env` en `.env.local` pour définir vos variables locales :
```bash
copy .env .env.local
```
Ouvrez `.env.local` et configurez l'accès à votre base de données :
```dotenv
# Exemple pour WAMP (user: root, pas de mot de passe)
DATABASE_URL="mysql://root:@127.0.0.1:3306/sortir?serverVersion=8.0.32&charset=utf8mb4"
```

### 4. Base de données
Créez la base de données et les tables :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Configuration WAMP (VirtualHost)

Pour que l'application fonctionne correctement, il est **impératif** de créer un VirtualHost pointant vers le dossier `public`.

1. Cliquez gauche sur l'icône WAMP dans la barre des tâches.
2. Allez dans **Vos VirtualHosts** > **Gestion VirtualHost**.
3. Remplissez le formulaire :
    - **Nom du VirtualHost** : `sortir.test` (ou tout autre nom de domaine local)
    - **Chemin complet** : `c:/wamp64/www/Sortir/public`  <-- **Important : pointer vers le dossier /public**
4. Cliquez sur "Démarrer la création".
5. Une fois créé, faites un clic droit sur l'icône WAMP > **Outils** > **Redémarrer le DNS**.
6. Vous pouvez maintenant accéder au site via `http://sortir.test`.

## Configuration des Emails (MAILER_DSN)

Pour l'équipe de développement, nous utilisons une configuration email commune.
Ajoutez ou modifiez ces lignes dans votre fichier `.env.local` :

```dotenv
MAILER_DSN=smtps://contact%40funwithsss.fr:O%2Amn%234cj%40RxrMDEBP%246b@smtp-fr.securemail.pro:465
APP_MAILER_FROM=contact@funwithsss.fr
```

## Utilisation

### Premier accès (Admin)
Une fois connecté en tant qu'administrateur, accédez au panneau d'administration : `http://sortir.test/admin`.

### Import CSV
Dans l'administration, vous pouvez importer des utilisateurs en masse via un fichier CSV.
- Colonnes requises : `mail`. (Les autres colonnes sont optionnelles).

---
*Projet développé avec Symfony 7, Tailwind CSS et MySQL.*
