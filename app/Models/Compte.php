<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
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
        'code_qr',
    ];

    protected $casts = [
        'dateCreation' => 'date',
        'metadata' => 'array',
    ];

    protected $hidden = ['codePing'];

    /**
     * Validation des règles pour le modèle Compte
     */
    public static function rules()
    {
        return [
            'numeroTelephone' => [
                'nullable',
                'string',
                'regex:/^\+221[0-9]{9}$/',
                'unique:comptes,numeroTelephone',
            ],
            'codePing' => 'nullable|string|min:4',
            'numeroCompte' => 'required|string|unique:comptes,numeroCompte',
            'type' => 'required|in:simple,marchand',
            'dateCreation' => 'required|date',
            'statut' => 'required|in:actif,bloque,ferme',
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public static function messages()
    {
        return [
            'numeroTelephone.regex' => 'Le numéro de téléphone doit être un numéro sénégalais valide commençant par +221 suivi de 9 chiffres.',
            'numeroTelephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
        ];
    }

    // Génération automatique de l'UUID et QR code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = Str::uuid()->toString();
            }

            // Générer automatiquement le QR code
            if (!$model->code_qr) {
                $model->code_qr = $model->generateQrCode();
            }
        });
    }

    /**
     * Génère un QR code pour le compte
     */
    public function generateQrCode(): string
    {
        // Contenu du QR code : ID du compte et numéro de téléphone
        $qrContent = json_encode([
            'id' => $this->id,
            'numero_compte' => $this->numeroCompte,
            'numero_telephone' => $this->numeroTelephone,
            'type' => $this->type,
        ]);

        $qrCode = new QrCode($qrContent);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Retourner le QR code en base64 pour stockage en base de données
        return 'data:image/png;base64,' . base64_encode($result->getString());
    }
    public function user()
{
    return $this->belongsTo(User::class, 'id_client', 'id');
}

}
