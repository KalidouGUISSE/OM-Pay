<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Compte;

class User extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;

    public $incrementing = false; // pas d’auto-incrément
    protected $keyType = 'string'; // UUID est une string

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['id', 'nom', 'prenom', 'role', 'numero_carte_identite'];

    public function comptes()
    {
        return $this->hasMany(Compte::class, 'id_client', 'id');
    }

}

