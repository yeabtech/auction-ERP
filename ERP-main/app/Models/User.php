<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function kycDocuments(): HasMany
    {
        return $this->hasMany(KycDocument::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'owner_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class, 'bidder_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // ─── Role Helpers ─────────────────────────────────────────────────────────

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function assignRole(string $role): void
    {
        $roleModel = Role::firstOrCreate(['name' => $role]);
        $this->roles()->syncWithoutDetaching([$roleModel->id]);
    }

    // ─── KYC Helpers ─────────────────────────────────────────────────────────

    public function isKycApproved(): bool
    {
        return $this->kycDocuments()->where('status', 'approved')->exists();
    }

    public function kycStatus(): string
    {
        $latest = $this->kycDocuments()->latest()->first();
        return $latest ? $latest->status : 'none';
    }

    // ─── Finance Helpers ──────────────────────────────────────────────────────

    public function walletBalance(): float
    {
        $deposits = $this->transactions()->where('type', 'deposit')->where('status', 'completed')->sum('amount');
        $debits   = $this->transactions()->whereIn('type', ['purchase_payment'])->where('status', 'completed')->sum('amount');
        return (float) ($deposits - $debits);
    }
}
