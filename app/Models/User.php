<?php

namespace App\Models;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\EmployerProfile;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Role helpers ──────────────────────────────────────────────────────

    public function isCandidate(): bool
    {
        return $this->role === 'candidate';
    }

    public function isEmployer(): bool
    {
        return $this->role === 'employer';
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function candidateProfile()
    {
        return $this->hasOne(CandidateProfile::class);
    }

    public function employerProfile()
    {
        return $this->hasOne(EmployerProfile::class);
    }

    // ── Override reset URL to point to Next.js frontend ───────────────────

    public function sendPasswordResetNotification($token): void
    {
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return env('FRONTEND_URL', 'http://localhost:3000')
                . '/reset-password'
                . '?token=' . $token
                . '&email=' . urlencode($user->email);
        });

        $this->notify(new ResetPassword($token));
    }
}