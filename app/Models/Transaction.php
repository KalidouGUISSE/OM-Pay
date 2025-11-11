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

    /**
     * Scope pour filtrer par type de transaction
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type_transaction', $type);
    }

    /**
     * Scope pour filtrer par plage de dates
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope pour filtrer par date de début
     */
    public function scopeFromDate($query, $date)
    {
        return $query->where('date', '>=', $date);
    }

    /**
     * Scope pour filtrer par date de fin
     */
    public function scopeToDate($query, $date)
    {
        return $query->where('date', '<=', $date);
    }

    /**
     * Scope pour trier par date décroissante (plus récent en premier)
     */
    public function scopeRecentFirst($query)
    {
        return $query->orderBy('date', 'desc');
    }

    /**
     * Scope pour trier par montant
     */
    public function scopeOrderByAmount($query, $direction = 'desc')
    {
        return $query->orderBy('montant', $direction);
    }

    /**
     * Scope pour filtrer par numéro de téléphone (expéditeur ou destinataire)
     */
    public function scopeForUser($query, $numeroTelephone)
    {
        return $query->where(function ($q) use ($numeroTelephone) {
            $q->where('expediteur', $numeroTelephone)
              ->orWhere('destinataire', $numeroTelephone);
        });
    }

    /**
     * Scope pour les transactions entrantes (reçues)
     */
    public function scopeIncoming($query, $numeroTelephone)
    {
        return $query->where('destinataire', $numeroTelephone);
    }

    /**
     * Scope pour les transactions sortantes (envoyées)
     */
    public function scopeOutgoing($query, $numeroTelephone)
    {
        return $query->where('expediteur', $numeroTelephone);
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
