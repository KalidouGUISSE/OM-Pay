<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use PDOException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Gérer les erreurs de connexion à la base de données PostgreSQL
        if ($exception instanceof PDOException || $exception instanceof QueryException) {
            $message = $this->translateDatabaseError($exception);
            if ($message) {
                return response()->json([
                    'error' => 'Erreur de base de données',
                    'message' => $message
                ], 500);
            }
        }

        return parent::render($request, $exception);
    }

    /**
     * Traduit les erreurs de base de données PostgreSQL en français
     */
    private function translateDatabaseError(Throwable $exception): ?string
    {
        $message = strtolower($exception->getMessage());

        // Messages spécifiques pour Railway PostgreSQL
        if (str_contains($message, 'could not connect to server')) {
            return '👉 Ton application locale essaie de se connecter à la base PostgreSQL Railway mais la connexion échoue. Vérifiez la configuration réseau et les identifiants.';
        }

        if (str_contains($message, 'connection refused')) {
            return '👉 La connexion à la base PostgreSQL Railway est refusée. Vérifiez que le serveur Railway est accessible et en cours d\'exécution.';
        }

        if (str_contains($message, 'authentication failed') || str_contains($message, 'password authentication failed')) {
            return '👉 Échec de l\'authentification à la base PostgreSQL Railway. Vérifiez les identifiants DATABASE_URL dans votre fichier .env.';
        }

        if (str_contains($message, 'database') && str_contains($message, 'does not exist')) {
            return '👉 La base de données PostgreSQL Railway n\'est pas accessible ou n\'existe pas. Vérifiez la configuration Railway.';
        }

        if (str_contains($message, 'connection timed out') || str_contains($message, 'timeout')) {
            return '👉 Timeout lors de la connexion à la base PostgreSQL Railway. Vérifiez votre connexion internet et la disponibilité du service Railway.';
        }

        if (str_contains($message, 'host') && str_contains($message, 'not found')) {
            return '👉 Hôte PostgreSQL Railway introuvable. Vérifiez l\'URL de connexion dans DATABASE_URL.';
        }

        if (str_contains($message, 'no route to host')) {
            return '👉 Aucune route vers l\'hôte Railway. Vérifiez votre connexion réseau et les paramètres de sécurité Railway.';
        }

        if (str_contains($message, 'permission denied')) {
            return '👉 Permission refusée pour accéder à la base PostgreSQL Railway. Vérifiez les droits d\'accès et la configuration Railway.';
        }

        // Messages génériques pour autres erreurs PostgreSQL
        if (str_contains($message, 'ssl connection')) {
            return '👉 Problème de connexion SSL à la base PostgreSQL Railway. Vérifiez la configuration SSL.';
        }

        if (str_contains($message, 'server closed the connection')) {
            return '👉 Le serveur PostgreSQL Railway a fermé la connexion. Vérifiez la stabilité du service Railway.';
        }

        // Pour les autres erreurs, retourner null pour utiliser le message par défaut
        return null;
    }
}
