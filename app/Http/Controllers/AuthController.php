<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compte;
use Illuminate\Support\Facades\Hash;
use App\Traits\ResponseTraits;

class AuthController extends Controller
{
    use ResponseTraits;

    public function login(Request $request)
    {
        $request->validate([
            'numeroTelephone' => 'required|string',
            'codePing' => 'required|string|min:4',
        ]);

        $compte = Compte::where('numeroTelephone', $request->numeroTelephone)->first();

        if (!$compte || !Hash::check($request->codePing, $compte->codePing)) {
            return $this->errorResponse('Numéro de téléphone ou code PIN invalide', 'auth_failed', 401);
        }

        if ($compte->statut !== 'actif') {
            return $this->errorResponse('Votre compte n\'est pas actif', 'account_inactive', 403);
        }

        $token = $compte->user->createToken('Personal Access Token')->plainTextToken;

        return $this->successResponse('Connexion réussie', [
            'token' => $token,
            'compte' => $compte->load('user'),
        ]);
    }
}
