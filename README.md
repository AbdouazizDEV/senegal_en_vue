# 🇸🇳 Sénégal en Vue - Backend API

Plateforme de tourisme local connectant voyageurs et prestataires sénégalais pour des réservations d'expériences authentiques.

## 🚀 Stack Technique

- **Framework**: Laravel 12 (PHP 8.3+)
- **Base de données**: MySQL 8.0+
- **Authentification**: JWT (tymon/jwt-auth) avec RBAC
- **Queue**: Laravel Queue avec Redis
- **Cache**: Redis
- **API**: RESTful avec versioning (v1)

## 📦 Installation

```bash
# Installer les dépendances
composer install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Générer la clé JWT
php artisan jwt:secret

# Exécuter les migrations
php artisan migrate

# Lancer les seeders
php artisan db:seed
```

## 🏗️ Architecture

Le projet suit une architecture **Domain-Driven Design (DDD)** avec séparation claire des responsabilités:

- **Domain/**: Logique métier pure (Models, Enums, Events, ValueObjects)
- **Application/**: Cas d'usage (Commands, Queries, Handlers)
- **Infrastructure/**: Implémentations techniques (Repositories, Services, External APIs)
- **Presentation/**: Couche API (Controllers, Requests, Resources, Routes)
- **Support/**: Utilitaires partagés (Helpers, Traits, Exceptions)

## 🧪 Tests

```bash
# Lancer tous les tests
php artisan test

# Tests avec couverture
php artisan test --coverage
```

## 📝 Documentation API

La documentation Swagger est disponible sur `/api/documentation` après avoir lancé le serveur :

```bash
php artisan serve
# Accéder à http://127.0.0.1:8000/api/documentation
```

## 🌿 Workflow Git Flow

Le projet utilise un workflow Git Flow avec les branches suivantes :

- **main** : Branche principale (production)
- **develop** : Branche de développement

### Créer une nouvelle fonctionnalité

```bash
# 1. Se placer sur develop
git checkout develop
git pull origin develop

# 2. Créer une branche feature
git checkout -b feature/nom-de-la-fonctionnalite

# 3. Développer et commiter
git add .
git commit -m "feat: description de la fonctionnalité"

# 4. Pousser la branche
git push origin feature/nom-de-la-fonctionnalite

# 5. Merger sur develop
git checkout develop
git merge feature/nom-de-la-fonctionnalite
git push origin develop

# 6. Merger develop sur main
git checkout main
git merge develop
git push origin main

# 7. Supprimer la branche feature (optionnel)
git branch -d feature/nom-de-la-fonctionnalite
```

Voir `GIT_WORKFLOW.md` pour plus de détails.

## 🔐 Rôles Utilisateurs

- **admin**: Administrateur système
- **traveler**: Voyageur
- **provider**: Prestataire de services
- **institution**: Institution partenaire

## 📄 Licence

MIT

