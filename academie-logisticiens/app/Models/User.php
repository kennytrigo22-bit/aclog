<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'prenom', 'email', 'password', 'telephone',
        'date_naissance', 'lieu_naissance', 'situation', 'etablissement', 'niveau_etudes', 'profession', 'secteur_activite',
        'ville', 'pays', 'avatar', 'role', 'points', 'jetons',
        'source_decouverte', 'accept_marketing', 'is_active', 'google_id',
        'suspended_at', 'suspended_until', 'suspension_reason',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_naissance' => 'date',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'accept_marketing' => 'boolean',
            'suspended_at' => 'datetime',
            'suspended_until' => 'datetime',
        ];
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class);
    }

    public function progressions(): HasMany
    {
        return $this->hasMany(Progression::class);
    }

    public function certificats(): HasMany
    {
        return $this->hasMany(Certificat::class);
    }

    public function flagsCaptures(): HasMany
    {
        return $this->hasMany(FlagCapture::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function notificationsInternes(): HasMany
    {
        return $this->hasMany(NotificationInterne::class);
    }

    public function formations()
    {
        return $this->belongsToMany(Formation::class, 'inscriptions')
            ->withPivot('statut', 'date_acces')
            ->withTimestamps();
    }

    public function isSuspended(): bool
    {
        if (!$this->suspended_at) {
            return false;
        }
        if ($this->suspended_until && $this->suspended_until->isPast()) {
            return false;
        }
        return true;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGestionnaire(): bool
    {
        return in_array($this->role, ['admin', 'gestionnaire']);
    }

    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom . ' ' . $this->name);
    }
}
