# OM Pay - API de Gestion de Comptes et Transactions

## Vue d'ensemble

OM Pay est une application Laravel conçue pour la gestion de comptes bancaires ou financiers et de transactions, inspirée des services de paiement mobile comme Orange Money. L'application fournit une API REST complète pour l'authentification des utilisateurs, la gestion des comptes et les opérations de paiement.

## Architecture du Projet

Le projet suit l'architecture standard de Laravel 10 avec une séparation claire des responsabilités :

- **Modèles** : `User`, `Compte` et `Transaction` avec relations Eloquent
- **Contrôleurs** : `AuthController` pour l'authentification, `CompteController` pour la gestion des comptes, `TransactionController` pour les transactions
- **Services** : `AuthService`, `CompteService` et `TransactionService` pour la logique métier
- **Traits** : `ResponseTraits` pour standardiser les réponses API
- **Base de données** : MySQL avec migrations, seeders et factories
- **Middleware** : Authentification, logging et contrôle des rôles

## Fonctionnalités Principales

### 1. Authentification
- **Nouvelle méthode OTP** : Connexion via numéro de téléphone uniquement (génère un code OTP temporaire)
- **Méthode traditionnelle** : Connexion via numéro de téléphone et code PIN (maintenue pour compatibilité)
- Génération de tokens d'accès (OAuth2 avec Passport)
- Rafraîchissement et déconnexion des tokens

### 2. Gestion des Comptes
- Création, lecture, mise à jour et suppression de comptes
- Filtrage par type (simple/marchand) et statut (actif/bloqué/fermé)
- Recherche par numéro de compte ou informations client
- Pagination des résultats
- Validation des numéros de téléphone sénégalais

### 3. Gestion des Transactions
- Création et consultation des transactions
- Transferts entre comptes via numéros de téléphone
- Calcul du solde en temps réel
- Historique des transactions par expéditeur/destinataire
- Validation des montants et références uniques

### 4. Architecture Modulaire
- Séparation des préoccupations avec services et traits
- Réponses API standardisées (succès/erreur)
- Utilisation d'UUID pour les identifiants
- Middleware de logging et contrôle d'accès

## Structure de la Base de Données

### Table `users`
- `id` : UUID (clé primaire)
- `nom` : Nom de l'utilisateur
- `prenom` : Prénom de l'utilisateur
- `role` : Rôle (client/admin)
- `timestamps`

### Table `comptes`
- `id` : UUID (clé primaire)
- `id_client` : UUID (clé étrangère vers users)
- `numeroCompte` : Numéro unique du compte
- `numeroTelephone` : Numéro de téléphone sénégalais (+221...)
- `codePing` : Code PIN hashé (masqué dans les réponses)
- `type` : Type de compte (simple/marchand)
- `dateCreation` : Date de création
- `statut` : Statut du compte (actif/bloqué/fermé)
- `metadata` : Données JSON supplémentaires
- `timestamps`

### Table `transactions`
- `id` : UUID (clé primaire)
- `type_transaction` : Type de transaction
- `expediteur` : Numéro de téléphone de l'expéditeur
- `destinataire` : Numéro de téléphone du destinataire
- `montant` : Montant de la transaction (décimal)
- `date` : Date et heure de la transaction
- `reference` : Référence unique de la transaction
- `metadata` : Données JSON supplémentaires
- `timestamps`

## Dépendances

- **PHP** : ^8.1
- **Laravel Framework** : ^10.10
- **Laravel Passport** : ^12.4 (authentification OAuth2)
- **Laravel Sanctum** : ^3.3 (authentification légère)
- **Guzzle HTTP** : ^7.2 (requêtes externes)
- **L5-Swagger** : ^8.6 (documentation API)
- **Debugbar** : ^3.16 (développement)
- **PHPUnit** : ^10.1 (tests)

## Installation et Configuration

### Prérequis
- PHP 8.1 ou supérieur
- Composer
- MySQL ou MariaDB
- Node.js et npm (pour les assets frontend)

### Étapes d'installation

1. **Cloner le repository**
   ```bash
   git clone <repository-url>
   cd om-pay
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Configuration de l'environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configuration de la base de données**
   - Modifier le fichier `.env` avec vos paramètres de base de données
   - Créer la base de données MySQL

5. **Exécuter les migrations**
   ```bash
   php artisan migrate
   ```

6. **Configuration de Passport**
   ```bash
   php artisan passport:install
   php artisan passport:keys
   ```

7. **Peupler la base de données (optionnel)**
   ```bash
   php artisan db:seed
   ```

8. **Démarrer le serveur**
   ```bash
   php artisan serve --host=127.0.0.1 --port=8001
   ```

### Configuration Docker (optionnel)
```bash
docker-compose up -d
```

## API Endpoints

### Authentification
- `POST /api/v1/auth/initiate-login` : Initier la connexion (génère OTP)
- `POST /api/v1/auth/verify-otp` : Vérifier l'OTP et obtenir le token complet
- `POST /api/v1/auth/login` : Connexion traditionnelle (numéro + PIN) - maintenue
- `POST /api/v1/auth/refresh` : Rafraîchir le token
- `POST /api/v1/auth/logout` : Déconnexion

### Comptes
- `GET /api/v1/comptes` : Lister les comptes (avec filtres et recherche)
- `GET /api/v1/comptes/{id}` : Détails d'un compte

### Transactions
- `GET /api/v1/transactions` : Lister les transactions de l'utilisateur
- `GET /api/v1/transactions/solde` : Obtenir le solde du compte
- `POST /api/v1/transactions` : Créer une nouvelle transaction
- `GET /api/v1/transactions/{id}` : Détails d'une transaction
- `GET /api/v1/transactions/expediteur/{numero}` : Transactions par expéditeur
- `GET /api/v1/transactions/destinataire/{numero}` : Transactions par destinataire

### Utilisateur
- `GET /api/v1/user` : Informations de l'utilisateur connecté

## Authentification - Nouvelle Implémentation OTP

### Fonctionnement Actuel (Méthode OTP - Recommandée)
L'authentification se fait maintenant en **deux étapes** avec un système OTP :

#### Étape 1 : Initiation de la connexion
- **Endpoint** : `POST /api/v1/auth/initiate-login`
- **Corps de la requête** : Uniquement le numéro de téléphone
- **Processus** :
  - Vérification de l'existence du compte
  - Génération d'un code OTP de 6 chiffres
  - Création d'un token temporaire (valable 5 minutes)
  - Stockage sécurisé de l'OTP en base

#### Étape 2 : Vérification de l'OTP
- **Endpoint** : `POST /api/v1/auth/verify-otp`
- **Corps de la requête** : Token temporaire + Code OTP
- **Processus** :
  - Validation du token temporaire
  - Vérification de l'OTP (non expiré, non utilisé)
  - Génération du token d'authentification complet
  - Marquage de l'OTP comme utilisé

### Structure de Base de Données Mise à Jour

#### Table `otp_verifications` (nouvelle)
- `id` : Clé primaire
- `numero_telephone` : Numéro de téléphone associé
- `otp_code` : Code OTP de 6 chiffres
- `expires_at` : Date d'expiration (5 minutes)
- `used` : Indicateur d'utilisation
- `timestamps`

### API Authentification - Nouvelle Méthode OTP

#### Étape 1 : Initier la connexion
```
POST /api/v1/auth/initiate-login
Content-Type: application/json

{
    "numeroTelephone": "+221818930119"
}
```

#### Réponse de succès (Étape 1)
```json
{
    "success": true,
    "message": "OTP envoyé avec succès",
    "data": {
        "temp_token": "eyJpdiI6Imtka1RhdzBtVzRObkNoYktrS3NGWWc9PSIs...",
        "message": "OTP envoyé avec succès",
        "expires_in": 300
    }
}
```

#### Étape 2 : Vérifier l'OTP
```
POST /api/v1/auth/verify-otp
Content-Type: application/json

{
    "token": "eyJpdiI6Imtka1RhdzBtVzRObkNoYktrS3NGWWc9PSIs...",
    "otp": "805826"
}
```

#### Réponse de succès (Étape 2)
```json
{
    "success": true,
    "message": "Authentification réussie",
    "data": {
        "access_token": "21|17vf3MfQS8c64IZvf4j5szpBMkPQF7uSLYoa70jkc33515bb",
        "token_type": "Bearer",
        "user": {
            "id": "uuid-user",
            "nom": "Dupont",
            "prenom": "Jean",
            "role": "client"
        },
        "compte_id": "uuid-compte",
        "numero_telephone": "+221818930119",
        "compte": { ... },
        "role": "client",
        "permissions": []
    }
}
```

### API Authentification - Méthode Traditionnelle (Maintenue)

#### Endpoint de connexion traditionnelle
```
POST /api/v1/auth/login
Content-Type: application/json

{
    "numeroTelephone": "+221771234567",
    "codePing": "1234"
}
```

### Sécurité Implémentée

1. **Chiffrement des tokens temporaires** : Utilisation de `Crypt::encryptString()`
2. **Expiration automatique** : Tokens temporaires valables 5 minutes
3. **Utilisation unique** : Chaque OTP ne peut être utilisé qu'une fois
4. **Nettoyage automatique** : Suppression des OTP expirés
5. **Validation stricte** : Numéros sénégalais uniquement (+221...)
6. **Masquage des données sensibles** : Codes PIN cachés dans les réponses
7. **Gestion d'erreurs standardisée** : Utilisation du trait `ResponseTraits`

### Données de Test

Pour tester l'authentification OTP :
1. Peupler la base : `php artisan db:seed --class=UserSeeder`
2. Récupérer un numéro de téléphone existant
3. Faire une requête POST vers `/api/v1/auth/initiate-login`
4. Récupérer l'OTP généré en base de données
5. Faire une requête POST vers `/api/v1/auth/verify-otp` avec le token et l'OTP

Cette implémentation fournit une authentification moderne et sécurisée en deux étapes, tout en maintenant la compatibilité avec l'ancienne méthode.

## API Transactions

### Créer une transaction
```
POST /api/v1/transactions
Authorization: Bearer {token}
Content-Type: application/json

{
    "type_transaction": "transfert",
    "expediteur": "+221771234567",
    "destinataire": "+221781234567",
    "montant": 5000.00,
    "date": "2025-11-11T08:00:00Z",
    "reference": "TXN-2025-001"
}
```

### Réponse de succès
```json
{
    "success": true,
    "message": "Transaction créée avec succès",
    "data": {
        "id": "uuid-transaction",
        "type_transaction": "transfert",
        "expediteur": "+221771234567",
        "destinataire": "+221781234567",
        "montant": 5000.00,
        "date": "2025-11-11T08:00:00Z",
        "reference": "TXN-2025-001"
    }
}
```

### Obtenir le solde
```
GET /api/v1/transactions/solde
Authorization: Bearer {token}
```

### Réponse
```json
{
    "success": true,
    "message": "Solde récupéré avec succès",
    "data": {
        "solde": 15000.50,
        "numeroTelephone": "+221771234567"
    }
}
```

## Sécurité et Validation

- **Authentification** : Tokens JWT via Passport
- **Autorisation** : Middleware de rôles (client/admin)
- **Validation** : Règles strictes pour numéros sénégalais et montants
- **Logging** : Middleware de journalisation des requêtes
- **Rate Limiting** : Protection contre les abus

## Tests

Exécuter les tests avec PHPUnit :
```bash
php artisan test
```

## Documentation API

La documentation Swagger est disponible via :
- URL : `http://127.0.0.1:8001/api/documentation`
- Génération : `php artisan l5-swagger:generate`

## Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit les changements (`git commit -am 'Ajouter nouvelle fonctionnalité'`)
4. Push vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request

## Licence

Ce projet est sous licence MIT.
