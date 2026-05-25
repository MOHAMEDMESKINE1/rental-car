<?php

namespace App\Models;

// class User extends Authenticatable implements HasMedia
// {
//     use HasFactory, HasRoles, InteractsWithMedia, Notifiable;

//     protected $fillable = [
//         'name',
//         'email',
//         'password',
//         'current_team_id',
//     ];

//     protected $hidden = [
//         'password',
//         'remember_token',
//          'two_factor_secret',
//         'two_factor_recovery_codes',
//     ];

//     protected function casts(): array
//     {
//         return [
//             'email_verified_at' => 'datetime',
//             'password'          => 'hashed',
//             'two_factor_confirmed_at' => 'datetime',

//         ];
//     }


use App\Concerns\HasTeams;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;


#[Fillable(['name', 'email', 'password', 'current_team_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser, HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasTeams, Notifiable, InteractsWithMedia, HasRoles, PasskeyAuthenticatable, TwoFactorAuthenticatable {
        // Resolve collision between HasTeams::teams and HasRoles::teams
        HasTeams::teams insteadof HasRoles;
        // If you still need the HasRoles teams method, alias it
        HasRoles::teams as rolesTeams;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    // ── Relations ──────────────────────────────────────────────
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    // ── Helpers ────────────────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole(['admin', 'manager']);
    }

    public function isAgent(): bool
    {
        return $this->hasRole(['admin', 'manager', 'agent']);
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }
}
