# OM Pay - API de Gestion de Comptes

## Vue d'ensemble

OM Pay est une application Laravel conçue pour la gestion de comptes bancaires ou financiers, inspirée des services de paiement mobile comme Orange Money. L'application fournit une API REST pour l'authentification des utilisateurs et la gestion des comptes.

## Architecture du Projet

Le projet suit l'architecture standard de Laravel 10 avec une séparation claire des responsabilités :

- **Modèles** : `User` et `Compte` avec relations Eloquent
- **Contrôleurs** : `AuthController` pour l'authentification, `CompteController` pour la gestion des comptes
- **Services** : `CompteService` pour la logique métier
- **Traits** : `ResponseTraits` pour standardiser les réponses API
- **Base de données** : MySQL avec migrations, seeders et factories

## Fonctionnalités Principales

### 1. Authentification
- Connexion utilisateur via API
- Génération de tokens d'accès (OAuth2 avec Passport)

### 2. Gestion des Comptes
- Création, lecture, mise à jour et suppression de comptes
- Filtrage par type (simple/marchand) et statut (actif/bloqué/fermé)
- Recherche par numéro de compte ou informations client
- Pagination des résultats

### 3. Architecture Modulaire
- Séparation des préoccupations avec services et traits
- Réponses API standardisées (succès/erreur)
- Utilisation d'UUID pour les identifiants

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
- `type` : Type de compte (simple/marchand)
- `dateCreation` : Date de création
- `statut` : Statut du compte (actif/bloqué/fermé)
- `metadata` : Données JSON supplémentaires
- `timestamps`

## Dépendances

- Laravel Framework 10.10
- Laravel Passport 12.4 (authentification API)
- Laravel Sanctum 3.3 (authentification légère)
- Guzzle HTTP (requêtes externes)
- Debugbar (développement)

## Installation et Configuration

1. Cloner le repository
2. Installer les dépendances : `composer install`
3. Configurer l'environnement : `cp .env.example .env`
4. Générer la clé d'application : `php artisan key:generate`
5. Configurer la base de données dans `.env`
6. Exécuter les migrations : `php artisan migrate`
7. (Optionnel) Peupler la base : `php artisan db:seed`

## API Endpoints

### Authentification
- `POST /api/login` : Connexion utilisateur

### Comptes
- `GET /api/comptes` : Lister les comptes (avec filtres et recherche)
- `GET /api/comptes/{id}` : Détails d'un compte

## Authentification - Implémentation Réalisée

### Fonctionnement Actuel (Après Modifications)
L'authentification se fait maintenant par **numéro de téléphone** et **code PIN** du compte :
- **Champs d'authentification** : `numeroTelephone` et `codePing` dans la table `comptes`
- **Validation** : Numéro de téléphone requis, code PIN minimum 4 caractères
- **Vérification** : Recherche du compte par numéro téléphone, vérification du hash du code PIN
- **Contrôle de statut** : Vérification que le compte est actif
- **Token** : Génération de token Passport au nom de l'utilisateur propriétaire du compte

### Structure de Base de Données Mise à Jour

#### Table `comptes` (champs ajoutés)
- `numeroTelephone` : Numéro de téléphone unique (nullable pour compatibilité)
- `codePing` : Code PIN hashé (nullable pour compatibilité)
- `codePing` masqué dans les réponses JSON (`$hidden`)

### API Authentification

#### Endpoint de connexion
```
POST /api/login
Content-Type: application/json

{
    "numeroTelephone": "+221771234567",
    "codePing": "1234"
}
```

#### Réponse de succès
```json
{
    "success": true,
    "message": "Connexion réussie",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
        "compte": {
            "id": "uuid-compte",
            "numeroCompte": "NCMTPABC123",
            "numeroTelephone": "+221771234567",
            "type": "simple",
            "statut": "actif",
            "user": {
                "id": "uuid-user",
                "nom": "Dupont",
                "prenom": "Jean",
                "role": "client"
            }
        }
    }
}
```

#### Réponse d'erreur
```json
{
    "success": false,
    "message": "Numéro de téléphone ou code PIN invalide",
    "errors": "auth_failed"
}
```

### Sécurité Implémentée

1. **Hashage des codes PIN** : Utilisation de `Hash::make()` pour stocker les codes PIN
2. **Masquage des données sensibles** : `codePing` caché dans les réponses JSON
3. **Validation stricte** : Longueur minimale pour le code PIN
4. **Vérification de statut** : Comptes inactifs rejetés
5. **Gestion d'erreurs standardisée** : Utilisation du trait `ResponseTraits`

### Données de Test

Les factories génèrent maintenant des comptes avec :
- Numéros de téléphone uniques générés automatiquement
- Code PIN par défaut : `1234` (hashé)
- Comptes actifs par défaut

### Utilisation pour les Tests

Pour tester l'authentification :
1. Créer un compte avec seeder : `php artisan db:seed --class=UserSeeder`
2. Récupérer un numéro de téléphone depuis la base
3. Faire une requête POST vers `/api/login` avec `numeroTelephone` et `codePing: "1234"`

Cette implémentation respecte les standards de sécurité Laravel et fournit une authentification robuste basée sur les comptes plutôt que les utilisateurs directement.
