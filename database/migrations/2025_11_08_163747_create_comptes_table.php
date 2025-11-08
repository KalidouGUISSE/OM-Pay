<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary(); // seule clé primaire
            $table->foreignUuid('id_client') // même type que users.id
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->string('numeroCompte')->unique();
            $table->enum('type', ['simple', 'marchand']);
            $table->date('dateCreation');
            $table->enum('statut', ['actif', 'bloque', 'ferme'])->default('actif');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
