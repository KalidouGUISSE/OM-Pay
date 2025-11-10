<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type_transaction'); // e.g., "Transfert d'argent"
            $table->string('destinataire'); // numéro du destinataire
            $table->string('expediteur'); // numéro de l'expéditeur
            $table->decimal('montant', 15, 2);
            $table->dateTime('date');
            $table->string('reference')->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
