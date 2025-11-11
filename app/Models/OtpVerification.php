<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OtpVerification extends Model
{
    use HasFactory;

    protected $table = 'otp_verifications';

    protected $fillable = [
        'numero_telephone',
        'otp_code',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Génère un OTP aléatoire de 6 chiffres
     */
    public static function generateOtp(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Crée un nouvel OTP pour un numéro de téléphone
     */
    public static function createForPhone(string $numeroTelephone): self
    {
        // Invalider les OTP précédents pour ce numéro
        self::where('numero_telephone', $numeroTelephone)
            ->where('used', false)
            ->update(['used' => true]);

        return self::create([
            'numero_telephone' => $numeroTelephone,
            'otp_code' => self::generateOtp(),
            'expires_at' => Carbon::now()->addMinutes(5),
            'used' => false,
        ]);
    }

    /**
     * Vérifie si l'OTP est valide
     */
    public function isValid(string $otpCode): bool
    {
        return !$this->used &&
               $this->otp_code === $otpCode &&
               $this->expires_at->isFuture();
    }

    /**
     * Marque l'OTP comme utilisé
     */
    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }

    /**
     * Nettoie les OTP expirés
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', Carbon::now())->delete();
    }
}
