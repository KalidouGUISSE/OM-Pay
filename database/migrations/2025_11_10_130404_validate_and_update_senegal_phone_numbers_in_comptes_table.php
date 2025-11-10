<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier si la colonne existe avant de faire la mise à jour
        $hasColumn = Schema::hasColumn('comptes', 'numeroTelephone');

        if ($hasColumn) {
            // Mettre à jour les numéros de téléphone existants pour qu'ils soient valides
            DB::statement("
                UPDATE comptes
                SET \"numeroTelephone\" = CONCAT('+221', LPAD(FLOOR(RANDOM() * 1000000000)::text, 9, '0'))
                WHERE \"numeroTelephone\" IS NULL
                   OR \"numeroTelephone\" NOT LIKE '+221%'
                   OR LENGTH(REPLACE(\"numeroTelephone\", '+221', '')) != 9
                   OR REPLACE(\"numeroTelephone\", '+221', '') !~ '^[0-9]+$'
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pas de rollback possible pour cette migration car elle modifie des données
        // Les données originales ne peuvent pas être récupérées
    }
};
