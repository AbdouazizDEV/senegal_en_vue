# Tests d'Authentification - Sénégal en Vue

## Base URL
```
http://127.0.0.1:8000/api/v1/auth
```

---

## 1. Inscription d'un Voyageur

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/register/traveler \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Amadou Diallo",
    "email": "amadou.diallo@example.com",
    "phone": "+221771234567",
    "password": "password123",
    "password_confirmation": "password123",
    "preferences": {
      "language": "fr",
      "notifications": true
    }
  }'
```

**Réponse attendue :**
```json
{
  "success": true,
  "message": "Inscription réussie",
  "data": {
    "user": { ... },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

---

## 2. Inscription d'un Prestataire

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/register/provider \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Mamadou Ndiaye",
    "email": "mamadou.ndiaye@example.com",
    "phone": "+221772345678",
    "password": "password123",
    "password_confirmation": "password123",
    "business_name": "Safari Sénégal",
    "address": "Route de la Corniche, Ouakam",
    "city": "Dakar",
    "region": "dakar",
    "bio": "Guide touristique expérimenté spécialisé dans les safaris",
    "business_registration_number": "SN-DKR-2024-001",
    "preferences": {
      "language": "fr"
    }
  }'
```

**Réponse attendue :**
```json
{
  "success": true,
  "message": "Inscription réussie",
  "data": {
    "user": { ... },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

---

## 3. Connexion (Login)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "amadou.diallo@example.com",
    "password": "password123"
  }'
```

**Réponse attendue :**
```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": {
    "user": {
      "id": 1,
      "uuid": "...",
      "name": "Amadou Diallo",
      "email": "amadou.diallo@example.com",
      "role": "traveler",
      "status": "active",
      ...
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

**Note :** L'utilisateur doit avoir le statut `active` ou `verified` pour pouvoir se connecter.

---

## 4. Rafraîchir le Token (Refresh)

```bash
# Remplacez YOUR_TOKEN par le token obtenu lors de la connexion
curl -X POST http://127.0.0.1:8000/api/v1/auth/refresh \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Réponse attendue :**
```json
{
  "success": true,
  "message": "Token rafraîchi",
  "data": {
    "user": { ... },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

---

## 5. Déconnexion (Logout)

```bash
# Remplacez YOUR_TOKEN par le token obtenu lors de la connexion
curl -X POST http://127.0.0.1:8000/api/v1/auth/logout \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Réponse attendue :**
```json
{
  "success": true,
  "message": "Déconnexion réussie",
  "data": null
}
```

---

## 6. Mot de passe oublié (Forgot Password)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/password/forgot \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "amadou.diallo@example.com"
  }'
```

---

## 7. Réinitialisation du mot de passe (Reset Password)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/password/reset \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "amadou.diallo@example.com",
    "token": "reset_token_from_email",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

---

## Exemple complet avec variable

```bash
# 1. Connexion et récupération du token
RESPONSE=$(curl -s -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "amadou.diallo@example.com",
    "password": "password123"
  }')

# 2. Extraire le token
TOKEN=$(echo "$RESPONSE" | python3 -c "import sys, json; print(json.load(sys.stdin)['data']['token'])")

# 3. Utiliser le token pour les requêtes authentifiées
curl -X POST http://127.0.0.1:8000/api/v1/auth/refresh \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

---

## Statuts des utilisateurs

- `pending_verification` : En attente de vérification (ne peut pas se connecter)
- `active` : Actif (peut se connecter)
- `verified` : Vérifié (peut se connecter)
- `inactive` : Inactif (ne peut pas se connecter)
- `suspended` : Suspendu (ne peut pas se connecter)

---

## Codes de réponse HTTP

- `200` : Succès
- `201` : Créé (inscription réussie)
- `400` : Erreur de validation
- `401` : Non authentifié
- `403` : Accès refusé (compte non actif)
- `422` : Erreur de validation des données
- `500` : Erreur serveur

---

## Notes importantes

1. **Token JWT** : Le token expire après 60 minutes (3600 secondes) par défaut
2. **Format des réponses** : Toutes les réponses suivent le format standardisé `ApiResponse`
3. **Validation** : Les emails et téléphones doivent être uniques
4. **Rôles** : `traveler`, `provider`, `admin`, `institution`
5. **Régions valides** : `dakar`, `thies`, `saint-louis`, `diourbel`, `fatick`, `kaffrine`, `kaolack`, `kedougou`, `kolda`, `louga`, `matam`, `sedhiou`, `tambacounda`, `ziguinchor`

