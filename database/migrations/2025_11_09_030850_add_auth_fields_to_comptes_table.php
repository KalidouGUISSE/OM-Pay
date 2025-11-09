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
        Schema::table('comptes', function (Blueprint $table) {
            $table->string('numeroTelephone')->nullable()->after('numeroCompte');
            $table->string('codePing')->nullable()->after('numeroTelephone');
            $table->string('codePingPlain')->nullable()->after('codePing'); // Pour les tests
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropColumn(['numeroTelephone', 'codePing', 'codePingPlain']);
        });
    }
};
