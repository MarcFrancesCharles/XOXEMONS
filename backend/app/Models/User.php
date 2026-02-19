<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject; //Implementem JWTSubject interface

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'custom_id',
        'name',
        'surnames',
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
            'password' => 'hashed',
        ];
    }

    // Implementació dels mètodes de JWTSubject
    public function getJWTIdentifier()    {
        return $this->getKey(); // Retorna l'identificador únic de l'usuari, normalment l'id.
    }

    public function getJWTCustomClaims() {
        return [];
    }
}
