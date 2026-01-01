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

La documentation API sera disponible sur `/api/documentation` (à implémenter avec Swagger/OpenAPI).

## 🔐 Rôles Utilisateurs

- **admin**: Administrateur système
- **traveler**: Voyageur
- **provider**: Prestataire de services
- **institution**: Institution partenaire

## 📄 Licence

MIT

