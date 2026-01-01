# 🌿 Guide du Workflow Git Flow - Sénégal en Vue

## 📋 Vue d'ensemble

Ce projet utilise un workflow Git Flow simplifié avec deux branches principales :

- **`main`** : Branche de production, toujours stable et déployable
- **`develop`** : Branche de développement, contient les dernières fonctionnalités validées

## 🔄 Structure des branches

```
main (production)
  ↑
develop (développement)
  ↑
feature/nom-fonctionnalite (fonctionnalités)
```

## 📝 Conventions de nommage

### Branches de fonctionnalités
- Format : `feature/nom-de-la-fonctionnalite`
- Exemples :
  - `feature/experience-crud`
  - `feature/booking-system`
  - `feature/payment-integration`

### Messages de commit
Utilisez le format conventionnel :
- `feat:` : Nouvelle fonctionnalité
- `fix:` : Correction de bug
- `docs:` : Documentation
- `style:` : Formatage, point-virgule manquant, etc.
- `refactor:` : Refactorisation du code
- `test:` : Ajout de tests
- `chore:` : Maintenance (dépendances, config, etc.)

Exemples :
```bash
git commit -m "feat: ajout du système de réservation"
git commit -m "fix: correction de l'authentification JWT"
git commit -m "docs: mise à jour de la documentation Swagger"
```

## 🚀 Processus de développement

### 1. Créer une nouvelle fonctionnalité

```bash
# Étape 1 : Se placer sur develop et récupérer les dernières modifications
git checkout develop
git pull origin develop

# Étape 2 : Créer une nouvelle branche feature
git checkout -b feature/nom-de-la-fonctionnalite

# Exemple :
git checkout -b feature/experience-management
```

### 2. Développer la fonctionnalité

```bash
# Faire vos modifications, puis :
git add .
git commit -m "feat: description de la fonctionnalité"

# Continuer à commiter au fur et à mesure
git add .
git commit -m "feat: ajout de la validation des données"
git commit -m "test: ajout des tests unitaires"
```

### 3. Pousser la branche feature

```bash
# Pousser la branche sur GitHub
git push origin feature/nom-de-la-fonctionnalite
```

### 4. Merger sur develop

```bash
# Retourner sur develop
git checkout develop
git pull origin develop

# Merger la feature
git merge feature/nom-de-la-fonctionnalite

# Pousser develop
git push origin develop
```

### 5. Merger develop sur main

```bash
# Se placer sur main
git checkout main
git pull origin main

# Merger develop
git merge develop

# Pousser main
git push origin main
```

### 6. Nettoyer (optionnel)

```bash
# Supprimer la branche feature locale
git branch -d feature/nom-de-la-fonctionnalite

# Supprimer la branche feature distante
git push origin --delete feature/nom-de-la-fonctionnalite
```

## 🔍 Commandes utiles

### Voir les branches
```bash
# Branches locales
git branch

# Branches distantes
git branch -r

# Toutes les branches
git branch -a
```

### Voir l'historique
```bash
# Historique simple
git log --oneline

# Historique avec graphique
git log --oneline --graph --all
```

### Vérifier l'état
```bash
# État du dépôt
git status

# Différences
git diff
```

### Annuler des modifications
```bash
# Annuler les modifications non commitées
git checkout -- fichier

# Annuler le dernier commit (garder les modifications)
git reset --soft HEAD~1

# Annuler le dernier commit (supprimer les modifications)
git reset --hard HEAD~1
```

## ⚠️ Bonnes pratiques

1. **Toujours partir de `develop`** pour créer une nouvelle feature
2. **Ne jamais commit directement sur `main`**
3. **Toujours tester avant de merger sur `develop`**
4. **Utiliser des messages de commit clairs et descriptifs**
5. **Faire des commits fréquents et atomiques** (une fonctionnalité = un commit)
6. **Puller avant de merger** pour éviter les conflits
7. **Resoudre les conflits localement** avant de pousser

## 🐛 Gestion des conflits

Si vous rencontrez des conflits lors d'un merge :

```bash
# 1. Ouvrir les fichiers en conflit
# 2. Résoudre manuellement les conflits (chercher <<<<<<, ======, >>>>>>)
# 3. Ajouter les fichiers résolus
git add fichier-en-conflit.php

# 4. Finaliser le merge
git commit
```

## 📦 Exemple complet

```bash
# 1. Créer une feature pour la gestion des expériences
git checkout develop
git pull origin develop
git checkout -b feature/experience-crud

# 2. Développer
# ... faire des modifications ...
git add .
git commit -m "feat: création du modèle Experience"
git add .
git commit -m "feat: ajout du contrôleur ExperienceController"
git add .
git commit -m "test: ajout des tests pour Experience"

# 3. Pousser
git push origin feature/experience-crud

# 4. Merger sur develop
git checkout develop
git pull origin develop
git merge feature/experience-crud
git push origin develop

# 5. Merger sur main
git checkout main
git pull origin main
git merge develop
git push origin main

# 6. Nettoyer
git checkout develop
git branch -d feature/experience-crud
git push origin --delete feature/experience-crud
```

## 🔐 Protection des branches

Il est recommandé de protéger les branches `main` et `develop` sur GitHub :
- Exiger des Pull Requests pour merger
- Exiger des reviews
- Exiger que les tests passent

## 📚 Ressources

- [Git Flow](https://nvie.com/posts/a-successful-git-branching-model/)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [Git Documentation](https://git-scm.com/doc)

