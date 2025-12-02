<?php
// app/Models/PasswordReset.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_reset_tokens';
    protected $primaryKey = 'email';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'otp',
        'otp_expires_at',
        'created_at'
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
        'created_at' => 'datetime'
    ];

    /**
     * Generate a 6-digit OTP
     */
    public static function generateOTP(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->otp_expires_at < now();
    }

    /**
     * Check if OTP is valid
     */
    public function isValidOTP(string $otp): bool
    {
        return !$this->isExpired() && $this->otp === $otp;
    }
}