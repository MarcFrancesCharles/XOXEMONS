<?php

/**
 * ============================================================
 * FITXER: app/Models/User.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Model central del projecte. Representa un jugador (o administrador)
 *   i defineix totes les seves relacions amb la resta d'entitats:
 *   Xuxemons, ítems de la motxilla i autenticació JWT.
 *   Pràcticament tots els controladors l'usen directament o indirectament.
 *
 * MAPA DE CONNEXIONS:
 *   → Taula BD: users (migració: 0001_01_01_000000_create_users_table.php)
 *   → Relació many-to-many → App\Models\Xuxemon (via user_xuxemons)
 *   → Relació many-to-many → App\Models\Item (via user_items)
 *   → Implementa: JWTSubject (requerit per tymon/jwt-auth)
 *   → Usat per: AuthController, AdminController, XuxemonController,
 *     FriendController, BattleController, InventoryController
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

// Implementem JWTSubject perquè tymon/jwt-auth necessita dos mètodes
// específics (getJWTIdentifier i getJWTCustomClaims) per generar tokens.
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * Camps que es poden omplir massivament (via create() o fill()).
     * Tots els camps de la taula users excepte id, remember_token i timestamps.
     * last_daily_reward s'inclou perquè AuthController el modifica via $user->save().
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
     * Camps que s'exclouen de les respostes JSON per seguretat.
     * password no hauria de mai sortir en una API, ni remember_token.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts automàtics de tipus quan Eloquent llegeix o escriu els camps.
     * last_daily_reward → 'datetime' permet usar mètodes de Carbon com ->isToday()
     * directament sobre $user->last_daily_reward a AuthController.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',    // Hash automàtic en escritura (Laravel 10+)
            'last_daily_reward'  => 'datetime',
        ];
    }


    // ─────────────────────────────────────────────────────────
    // IMPLEMENTACIÓ JWT (obligatoria per JWTSubject)
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna l'identificador únic que JWT emmagatzemat com a "sub" al payload del token.
     * Usem la PK (id) perquè és immutable i sempre única.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Permet afegir claims personalitzats al payload JWT.
     * Buit per ara: podria incloure 'role' en el futur per validar permisos
     * directament al token sense consultar la BD.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


    // ─────────────────────────────────────────────────────────
    // RELACIONS ELOQUENT
    // ─────────────────────────────────────────────────────────

    /**
     * Relació many-to-many amb Xuxemon a través de la taula pivot user_xuxemons.
     *
     * withPivot() és imprescindible per portar les columnes extra de la taula pivot:
     *   - id: per identificar una instància específica (pivot_id) a /feed i /vaccinate
     *   - food_eaten: comptador d'alimentació per a l'evolució
     *   - disease: malaltia actual del Xuxemon
     * withTimestamps() permet que Eloquent gestioni created_at i updated_at del pivot.
     */
    public function xuxemons()
    {
        return $this->belongsToMany(Xuxemon::class, 'user_xuxemons')
                    ->withPivot('id', 'food_eaten', 'disease')
                    ->withTimestamps();
    }

    /**
     * Relació many-to-many amb Item a través de la taula pivot user_items.
     *
     * withPivot('quantity') permet llegir i modificar la quantitat de cada ítem
     * a la motxilla de l'usuari via updateExistingPivot() als controladors.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'user_items')
                    ->withPivot('quantity');
    }
}