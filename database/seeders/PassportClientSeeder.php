<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravel\Passport\Client;

class PassportClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un client pour le password grant
        Client::create([
            'name' => 'OM Pay Password Grant Client',
            'secret' => 'BYmamOVgIpVJWNrHJzFcOkOQ1mkWASWThfwciUCX', // Utiliser le secret généré
            'redirect' => 'http://localhost',
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => false,
        ]);

        // Créer un client pour le personal access
        Client::create([
            'name' => 'OM Pay Personal Access Client',
            'secret' => 'j53MIggdnwhdb7nlgH1LIV9N9sI98FKtTQkLA5DW', // Utiliser le secret généré
            'redirect' => 'http://localhost',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
        ]);
    }
}