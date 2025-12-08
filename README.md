# Basket Club API (Backend)

API Backend pour l'application de gestion de club de basket. Développée avec **Laravel**, elle gère l'authentification, les données des joueurs, les équipes, les matchs et les statistiques.

## 🛠 Tech Stack

- **Framework** : Laravel 12.xd
- **Langage** : PHP 8.2+
- **Base de données** : MySQL / MariaDBd
- **Authentification** : Laravel Sanctum (API Tokens)
- **Documentation API** : Scribe
- **Environnement de Dev** : DDEV (recommandé) ou Laravel Sail / Serve

## 🚀 Installation & Démarrage

### Prérequis

- [Docker](https://www.docker.com/) & [DDEV](https://ddev.readthedocs.io/) (Recommandé)
- OU PHP 8.2+ & Composer & MySQL installés localement.

### Option 1 : Via DDEV (Recommandé)

DDEV configure automatiquement Nginx, PHP et MySQL dans des conteneurs isolés.

1. **Cloner le projet**
   ```bash
   git clone <url-du-repo>
   cd backend-basketclub-app
   ```

2. **Démarrer l'environnement**
   ```bash
   ddev start
   ```

3. **Installer les dépendances & Configurer**
   ```bash
   ddev composer install
   ddev artisan key:generate
   ```

4. **Migrations & Seed (Données de test)**
   ```bash
   ddev artisan migrate --seed
   ```
   *Cela créera les tables et des utilisateurs de test (Admin, Coach, Joueur).*

5. **Accéder au projet**
   - URL : `https://backend-basketclub-app.ddev.site` (ou voir la sortie de `ddev describe`)

### Option 2 : Installation Manuelle (Local sans Docker)

1. **Configurer l'environnement**
   ```bash
   cp .env.example .env
   # Editez .env pour configurer votre base de données (DB_HOST, DB_DATABASE, etc.)
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   php artisan key:generate
   ```

3. **Base de données**
   Assurez-vous que votre serveur MySQL tourne et que la BDD existe.
   ```bash
   php artisan migrate --seed
   ```

4. **Lancer le serveur**
   ```bash
   php artisan serve
   ```
   URL : `http://localhost:8000`

## 🏗 Architecture & Fonctionnalités Clés

### Modèles Principaux (`app/Models`)

- **User / UserType** : Gestion des utilisateurs (Admins, Coachs, Joueurs).
- **Team / Category** : Gestion des équipes et catégories d'âge (U11, Senior...).
- **Season** : Gestion des saisons sportives.
- **Event / Encounter** : Calendrier, Matchs, et Entraînements.
- **IndividualStat / EncounterStat** : Statistiques détaillées par joueur et par match.

### API & Routes

Les routes sont définies dans `routes/api.php`. Authentification via **Sanctum** (Bearer Token).

- `POST /login` : Connexion, retourne un token.
- `GET /user` : Récupère l'utilisateur connecté.
- `GET /teams`, `GET /encounters` : Ressources publiques ou protégées.

## 📚 Documentation API

La documentation de l'API est générée automatiquement avec **Scribe**.

Pour générer (ou régénérer) la doc HTML locale :
```bash
ddev artisan scribe:generate
```
La doc sera accessible via une URL locale (ex: `/docs` ou fichier statique dans `public/docs`).

## ✅ Tests

Les tests sont écrits avec **PHPUnit**.

```bash
# Via DDEV
ddev artisan test

# Manuel
php artisan test
```

## 🚢 Déploiement


## 🚢 Déploiement (Production)

### Configuration Serveur (ex: RunCloud)

1. **Type d'app** : Application PHP (Laravel).
2. **Domaine** : Configurez votre domaine (ex: `api.basketclub.com`).
3. **Document Root** : `/public`.

### Base de Données

1. Créez une base de données MySQL et un utilisateur sur votre hébergeur.
2. Ajoutez ces variables dans le `.env` de production :
   ```bash
   APP_ENV=production
   APP_DEBUG=false
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=basketclub_db
   DB_USERNAME=votre_user
   DB_PASSWORD=votre_password
   ```

### Script de Déploiement

Exemple de script à lancer après chaque `git pull` (ou dans "After Install Script" sur RunCloud) :

```bash
# Installer les dépendances prod
composer install --no-dev --optimize-autoloader

# Nettoyer les fichiers de dev indésirables (Important)
rm -rf .ddev tests phpunit.xml

# Mettre à jour la base de données
php artisan migrate --force

# Optimiser le cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🤝 Contribution

1. Respecter les standards PSR-12 (Linting via Laravel Pint : `./vendor/bin/pint`).
2. Créer une branche pour chaque feature.
3. Ajouter des tests si nécessaire.
