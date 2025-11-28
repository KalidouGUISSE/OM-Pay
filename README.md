# OM Pay - API de Gestion de Comptes et Transactions

## Description du projet

OM Pay est une application API REST développée avec Laravel, conçue pour la gestion de comptes bancaires ou financiers et de transactions. Inspirée des services de paiement mobile comme Orange Money, l'application fournit une plateforme sécurisée pour l'authentification des utilisateurs, la gestion des comptes et les opérations de paiement. Elle intègre un système d'authentification moderne basé sur OTP (One-Time Password) et supporte les transferts entre comptes via numéros de téléphone sénégalais.

## Fonctionnalités principales

### 1. Authentification
- **Authentification OTP moderne** : Connexion en deux étapes avec génération automatique de codes temporaires
- **Authentification traditionnelle** : Support maintenu pour compatibilité (numéro de téléphone + PIN)
- **Gestion des tokens** : Utilisation de Laravel Passport pour l'authentification OAuth2
- **Expiration automatique** : Tokens temporaires valides 5 minutes, tokens d'accès 1 heure

### 2. Gestion des comptes
- **CRUD complet** : Création, lecture, mise à jour et suppression de comptes
- **Filtrage avancé** : Par type (simple/marchand), statut (actif/bloqué/fermé)
- **Recherche** : Par numéro de compte ou informations client
- **Pagination** : Résultats paginés pour une performance optimale
- **Validation stricte** : Numéros de téléphone sénégalais uniquement (+221...)
- **Génération QR code** : QR codes automatiques pour chaque compte

### 3. Gestion des transactions
- **Création de transactions** : Transferts entre comptes via numéros de téléphone
- **Calcul de solde** : Solde en temps réel basé sur l'historique des transactions
- **Historique complet** : Transactions par expéditeur/destinataire avec filtrage
- **Références uniques** : Génération automatique de références de transaction
- **Validation des montants** : Contrôles stricts sur les montants et références

### 4. Architecture modulaire
- **Séparation des préoccupations** : Services métier, repositories, contrôleurs
- **Réponses standardisées** : Trait ResponseTraits pour uniformiser les réponses API
- **Utilisation d'UUID** : Identifiants uniques pour tous les enregistrements
- **Middleware de logging** : Journalisation automatique des requêtes
- **Middleware de rôles** : Contrôle d'accès basé sur les rôles (client/admin)

## Architecture et technologies

### Technologies principales
- **PHP** : Version 8.1 ou supérieure
- **Laravel Framework** : Version 10.10
- **Laravel Passport** : Version 12.4 (authentification OAuth2)
- **Laravel Sanctum** : Version 3.3 (authentification légère)
- **Base de données** : MySQL/MariaDB
- **Documentation API** : L5-Swagger 8.6

### Dépendances externes
- **Guzzle HTTP** : ^7.2 (requêtes HTTP externes)
- **Endroid QR Code** : ^6.0 (génération de QR codes)
- **Twilio SDK** : ^8.8 (services SMS)
- **Debugbar** : ^3.16 (développement)
- **PHPUnit** : ^10.1 (tests)

### Architecture applicative
L'application suit le pattern MVC (Modèle-Vue-Contrôleur) de Laravel avec des extensions :

- **Modèles Eloquent** : Relations et logique métier
- **Contrôleurs** : Gestion des requêtes HTTP et réponses
- **Services** : Logique métier centralisée (AuthService, TransactionService, etc.)
- **Repositories** : Abstraction de l'accès aux données
- **Traits** : Réutilisation de code (ResponseTraits)
- **Middleware** : Filtrage et traitement des requêtes
- **Requests** : Validation des données d'entrée

## Modèles de données

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
- `code_qr` : QR code en base64
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

### Table `otp_verifications`
- `id` : Clé primaire
- `numero_telephone` : Numéro de téléphone associé
- `otp_code` : Code OTP de 6 chiffres
- `expires_at` : Date d'expiration (5 minutes)
- `used` : Indicateur d'utilisation
- `timestamps`

## API endpoints

### Authentification
- `POST /api/v1/auth/initiate-login` : Initier la connexion (génère OTP)
- `POST /api/v1/auth/verify-otp` : Vérifier l'OTP et obtenir le token complet
- `POST /api/v1/auth/login` : Connexion traditionnelle (numéro + PIN)
- `POST /api/v1/auth/refresh` : Rafraîchir le token
- `POST /api/v1/auth/logout` : Déconnexion
- `GET /api/v1/auth/me` : Informations de l'utilisateur connecté

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
- `GET /api/v1/compte/{numero}/solde` : Solde par numéro de téléphone
- `GET /api/v1/compte/{numero}/transactions` : Transactions par numéro de téléphone

## Installation et configuration

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

## Utilisation

### Authentification OTP (recommandée)

#### Étape 1 : Initiation de la connexion
```bash
POST /api/v1/auth/initiate-login
Content-Type: application/json

{
    "numeroTelephone": "+221818930119"
}
```

#### Étape 2 : Vérification de l'OTP
```bash
POST /api/v1/auth/verify-otp
Content-Type: application/json

{
    "token": "eyJpdiI6Imtka1RhdzBtVzRObkNoYktrS3NGWWc9PSIs...",
    "otp": "805826"
}
```

### Création d'une transaction
```bash
POST /api/v1/compte/+221771234567/transactions
Authorization: Bearer {token}
Content-Type: application/json

{
    "destinataire": "+221781234567",
    "montant": 5000.00,
    "type_transaction": "transfert"
}
```

### Consultation du solde
```bash
GET /api/v1/transactions/solde
Authorization: Bearer {token}
```

### Documentation API
La documentation Swagger est disponible à l'adresse :
`http://127.0.0.1:8001/api/documentation`

## Tests

L'application inclut une suite complète de tests :

### Tests fonctionnels
- **AuthTest** : Tests d'authentification OTP et accès protégé
- **TransactionTest** : Tests de création de transactions et calcul de solde
- **LoadTest** : Tests de charge pour la performance

### Exécution des tests
```bash
php artisan test
```

### Couverture des tests
- Authentification : initiation, vérification OTP, accès protégé
- Transactions : création, solde, QR codes
- Performance : requêtes multiples, calculs de solde concurrents

## Sécurité

### Mesures de sécurité implémentées

1. **Authentification multi-étapes**
   - Système OTP avec expiration automatique (5 minutes)
   - Utilisation unique des codes OTP
   - Chiffrement des tokens temporaires

2. **Validation stricte des données**
   - Numéros de téléphone sénégalais uniquement (+221...)
   - Validation des montants et références uniques
   - Sanitisation des entrées utilisateur

3. **Protection contre les attaques**
   - Middleware d'authentification et autorisation
   - Rate limiting pour prévenir les abus
   - Logging des requêtes pour audit

4. **Gestion sécurisée des données sensibles**
   - Hashage des codes PIN
   - Masquage des données sensibles dans les réponses
   - Utilisation d'UUID pour éviter l'énumération

5. **Architecture sécurisée**
   - Séparation des responsabilités
   - Utilisation de repositories pour l'accès aux données
   - Transactions de base de données pour l'intégrité

## Recommandations

### Pour le développement
1. **Maintenir la couverture de tests** : Étendre les tests unitaires pour les services
2. **Documentation API** : Maintenir à jour la documentation Swagger
3. **Logging avancé** : Implémenter une stratégie de logging structuré
4. **Monitoring** : Ajouter des métriques de performance et d'erreur

### Pour la production
1. **Configuration HTTPS** : Déployer avec certificat SSL
2. **Variables d'environnement** : Sécuriser les clés API et secrets
3. **Sauvegarde** : Mettre en place des sauvegardes automatiques
4. **Monitoring** : Surveiller les logs et métriques en temps réel

### Améliorations futures
1. **Cache** : Implémenter Redis pour améliorer les performances
2. **File storage** : Utiliser des services cloud pour les QR codes
3. **Notifications** : Système de notifications push pour les transactions
4. **Multidevise** : Support de plusieurs devises
5. **API versioning** : Gestion évoluée des versions d'API

---

Ce projet démontre une architecture robuste et sécurisée pour les services de paiement mobile, avec une attention particulière à la sécurité et à l'expérience utilisateur.
