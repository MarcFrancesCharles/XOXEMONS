<?php

/**
 * ============================================================
 * FITXER: app/Models/User.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Model central de l'aplicació. Representa tant als jugadors 
 *   com als administradors ('robot'). Gestiona l'autenticació 
 *   mitjançant JWT i centralitza les col·leccions d'ítems i 
 *   criatures de cada usuari.
 *
 * FUNCIONALITATS CLAU:
 *   - Autenticació segura amb JWT (JSON Web Tokens).
 *   - Gestió de la motxilla (items) i la col·lecció (xuxemons).
 *   - Seguiment de les recompenses diàries.
 *
 * MAPA DE CONNEXIONS:
 *   → Relació many-to-many ↔ App\Models\Xuxemon (via user_xuxemons)
 *   → Relació many-to-many ↔ App\Models\Item (via user_items)
 *   → Usat per: Pràcticament tots els controladors del backend.
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * Camps que permeten l'assignació massiva.
     * 
     * S'inclouen les dades de perfil, credencials i el control de recompenses.
     */
    protected $fillable = [
        'custom_id',
        'name',
        'surnames',
        'email',
        'password',
        'role',
        'last_daily_reward',
    ];

    /**
     * Camps ocults en les respostes JSON de la API.
     * 
     * Per motius de seguretat, la contrasenya i els tokens de sessió
     * no s'han d'enviar mai al client d'Angular.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Configuració de la conversió de tipus (Casting).
     * 
     * Garantim que les dates es tractin com a objectes Carbon i que 
     * la contrasenya s'encripti automàticament en desar-se.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'last_daily_reward'  => 'datetime',
        ];
    }


    // ─────────────────────────────────────────────────────────
    // IMPLEMENTACIÓ JWTSubject
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna l'identificador únic de l'usuari per al payload del JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Permet afegir informació extra personalitzada al token JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


    // ─────────────────────────────────────────────────────────
    // RELACIONS AMB ALTRES MODELS
    // ─────────────────────────────────────────────────────────

    /**
     * Defineix la col·lecció de Xuxemons de l'usuari.
     * 
     * Utilitza la taula pivot 'user_xuxemons' per emmagatzemar l'estat 
     * individual de cada criatura (menjar i malaltia).
     */
    public function xuxemons()
    {
        return $this->belongsToMany(Xuxemon::class, 'user_xuxemons')
                    ->withPivot('id', 'food_eaten', 'disease')
                    ->withTimestamps();
    }

    /**
     * Defineix l'inventari (motxilla) de l'usuari.
     * 
     * Utilitza la taula pivot 'user_items' per gestionar la quantitat 
     * disponible de cada objecte (xuxes o vacunes).
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'user_items')
                    ->withPivot('quantity');
    }
}