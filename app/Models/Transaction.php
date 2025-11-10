<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    public $incrementing = false; // car on utilise UUID
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'type_transaction',
        'destinataire',
        'expediteur',
        'montant',
        'date',
        'reference',
        'metadata',
    ];

    protected $casts = [
        'date' => 'datetime',
        'montant' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected $hidden = [];

    /**
     * Validation des règles pour le modèle Transaction
     */
    public static function rules()
    {
        return [
            'type_transaction' => 'required|string',
            'destinataire' => 'required|string|regex:/^\+221[0-9]{9}$/',
            'expediteur' => 'required|string|regex:/^\+221[0-9]{9}$/',
            'montant' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'reference' => 'required|string|unique:transactions,reference',
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public static function messages()
    {
        return [
            'destinataire.regex' => 'Le numéro du destinataire doit être un numéro sénégalais valide commençant par +221 suivi de 9 chiffres.',
            'expediteur.regex' => 'Le numéro de l\'expéditeur doit être un numéro sénégalais valide commençant par +221 suivi de 9 chiffres.',
            'reference.unique' => 'Cette référence de transaction existe déjà.',
        ];
    }

    // Génération automatique de l'UUID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = Str::uuid()->toString();
            }
        });
    }
}
