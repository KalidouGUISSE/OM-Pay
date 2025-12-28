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

        if (str_contains($message, 'could not connect to server')) {
            return 'Impossible de se connecter au serveur de base de données. Vérifiez la configuration réseau.';
        }

        if (str_contains($message, 'connection refused')) {
            return 'Connexion refusée par le serveur de base de données. Vérifiez que le serveur est en cours d\'exécution.';
        }

        if (str_contains($message, 'authentication failed') || str_contains($message, 'password authentication failed')) {
            return 'Échec de l\'authentification à la base de données. Vérifiez les identifiants de connexion.';
        }

        if (str_contains($message, 'database') && str_contains($message, 'does not exist')) {
            return 'La base de données spécifiée n\'existe pas. Vérifiez le nom de la base de données.';
        }

        if (str_contains($message, 'connection timed out') || str_contains($message, 'timeout')) {
            return 'Délai d\'attente de connexion dépassé. Vérifiez la connectivité réseau.';
        }

        if (str_contains($message, 'host') && str_contains($message, 'not found')) {
            return 'Hôte de base de données introuvable. Vérifiez l\'adresse du serveur.';
        }

        if (str_contains($message, 'no route to host')) {
            return 'Aucune route vers l\'hôte. Vérifiez la configuration réseau.';
        }

        if (str_contains($message, 'permission denied')) {
            return 'Permission refusée pour accéder à la base de données. Vérifiez les droits d\'accès.';
        }

        // Pour les autres erreurs, retourner null pour utiliser le message par défaut
        return null;
    }
}
