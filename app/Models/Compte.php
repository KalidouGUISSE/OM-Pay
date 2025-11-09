<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\User;

class Compte extends Model
{
    use HasFactory;

        protected $table = 'comptes';
    public $incrementing = false; // car on utilise UUID
    protected $keyType = 'string';

    protected $fillable = [
        'id_client',
        'numeroCompte',
        'numeroTelephone',
        'codePing',
        'codePingPlain',
        'type',
        'dateCreation',
        'statut',
        'metadata',
    ];

    protected $casts = [
        'dateCreation' => 'date',
        'metadata' => 'array',
    ];

    protected $hidden = ['codePing'];

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
    public function user()
{
    return $this->belongsTo(User::class, 'id_client', 'id');
}

}
