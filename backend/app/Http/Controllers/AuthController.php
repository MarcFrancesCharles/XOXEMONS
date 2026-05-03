<?php

/**
 * ============================================================================
 * FITXER: app/Http/Controllers/AuthController.php
 * ============================================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest controlador gestiona tot el cicle de vida de l'usuari i la seva
 *   seguretat. Utilitza JSON Web Tokens (JWT) per mantenir la sessió activa
 *   sense necessitat d'estat al servidor (stateless).
 *
 * RESPONSABILITATS PRINCIPALS:
 *   - Registre d'usuaris amb assignació de rols automàtica.
 *   - Autenticació (Login) i generació de tokens d'accés.
 *   - Gestió del perfil (Consulta, Actualització i Eliminació).
 *   - Lògica de gamificació inicial (Recompensa diària).
 *
 * TECNOLOGIES:
 *   - Laravel Validator per a la integritat de dades.
 *   - Bcrypt (via Hash) per a la seguretat de les contrasenyes.
 *   - Tymon JWTAuth per a la gestió de tokens.
 * ============================================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;
use App\Models\Xuxemon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // GESTIÓ D'USUARIS (Registre i Accés)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Registra un nou usuari al sistema.
     * 
     * Flux detallat:
     * 1. Validació: Comprovem que l'email no estigui repetit i que la contrasenya estigui confirmada.
     * 2. Rol: Si és el primer usuari de la base de dades, se li assigna el rol 'robot' (admin).
     * 3. ID Personalitzat: Es genera un identificador únic (ex: Jan#1234).
     * 4. Persistència: Es guarda l'usuari amb la contrasenya encriptada (bcrypt).
     *
     * @param Request $request Dades del formulari de registre.
     * @return \Illuminate\Http\JsonResponse Objecte de l'usuari creat o errors de validació.
     */
    public function register(Request $request)
    {
        // Pas 1: Validació de dades. Laravel retorna automàticament els errors si falla.
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'surnames' => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Pas 2: Assignació del rol. El primer de tots és l'administrador del sistema (robot).
        $isFirstUser = User::count() === 0;
        $role = $isFirstUser ? 'robot' : 'player';

        // Pas 3: Generació de l'identificador visual únic per a la comunitat (Nom#0000).
        $cleanName = str_replace(' ', '', $request->name);
        do {
            $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $customId = $cleanName . '#' . $randomNumber;
        } while (User::where('custom_id', $customId)->exists());

        // Pas 4: Creació del registre a la base de dades.
        $user = User::create([
            'custom_id' => $customId,
            'name'      => $request->name,
            'surnames'  => $request->surnames,
            'email'     => $request->email,
            'password'  => Hash::make($request->password), // Encriptem per seguretat.
            'role'      => $role,
        ]);

        return response()->json([
            'message' => 'Usuari registrat correctament!',
            'user'    => $user
        ], 201);
    }

    /**
     * Autentica un usuari i retorna un token d'accés JWT.
     * 
     * @param Request $request Credencials (custom_id i password).
     * @return \Illuminate\Http\JsonResponse Token bearer o error 401 si les credencials fallen.
     */
    public function login(Request $request)
    {
        // Només agafem els camps necessaris per l'intent de login.
        $credentials = $request->only('custom_id', 'password');

        // Intentem l'autenticació. JWTAuth s'encarrega de verificar el Hash de la contrasenya.
        if (!$token = $this->jwtGuard()->attempt($credentials)) {
            return response()->json(['error' => 'Credencials invàlides.'], 401);
        }

        // Si té èxit, preparem l'estructura del token per al client d'Angular.
        return $this->respondWithToken($token);
    }

    /**
     * Tanca la sessió actual.
     * 
     * @return \Illuminate\Http\JsonResponse Missatge de confirmació.
     */
    public function logout()
    {
        // Invalida el token per a que no pugui ser usat de nou fins que es torni a fer login.
        $this->jwtGuard()->logout();
        return response()->json(['message' => 'Sessió tancada correctament.']);
    }


    // ─────────────────────────────────────────────────────────────────────────
    // GESTIÓ DEL PERFIL
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Obté el perfil de l'usuari actualment autenticat a través del token.
     * 
     * @return \Illuminate\Http\JsonResponse Objecte User complet.
     */
    public function me()
    {
        // Recuperem l'usuari a partir del token de la petició.
        return response()->json(auth('api')->user());
    }

    /**
     * Actualitza la informació del compte de l'usuari (Nom, Email, Contrasenya).
     * 
     * @param Request $request Camps a modificar.
     * @return \Illuminate\Http\JsonResponse Usuari actualitzat.
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = auth('api')->user();

        // Validació opcional: només es validen els camps que s'envien (sometimes).
        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|required|string|max:255',
            'surnames' => 'sometimes|required|string|max:255',
            'email'    => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Actualitzem les propietats si l'usuari les ha proporcionat al formulari.
        if ($request->filled('name'))     $user->name = $request->name;
        if ($request->filled('surnames')) $user->surnames = $request->surnames;
        if ($request->filled('email'))    $user->email = $request->email;
        if ($request->filled('password')) $user->password = Hash::make($request->password);

        $user->save();

        return response()->json([
            'message' => 'Perfil actualitzat correctament.',
            'user'    => $user
        ]);
    }

    /**
     * Elimina el compte de l'usuari de forma permanent del sistema.
     * 
     * @return \Illuminate\Http\JsonResponse Confirmació de l'eliminació.
     */
    public function deleteAccount()
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        // Invalida el token abans d'esborrar el registre per evitar sessions fantasmes.
        $this->jwtGuard()->logout();

        // Esborra l'usuari i les seves relacions en cascada si està configurat a la BD.
        $user->delete();

        return response()->json(['message' => 'Compte eliminat. Adéu per sempre!']);
    }


    // ─────────────────────────────────────────────────────────────────────────
    // SISTEMA DE RECOMPENSES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Sistema de recompensa diària.
     * 
     * Regala un Xuxemon de mida "Petit" aleatori i un pack de 10 xuxes (ítems)
     * si han passat 24 hores des de l'última reclamació.
     * 
     * @return \Illuminate\Http\JsonResponse Resultat de la reclamació amb missatge d'èxit o espera.
     */
    public function claimDailyReward(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $now  = now();

        // Control de temps: només es permet una recompensa per dia natural.
        if ($user->last_daily_reward && $user->last_daily_reward->isToday()) {
            return response()->json(
                ['message' => 'Ja has reclamat el teu regal d\'avui! Torna demà.'],
                400
            );
        }

        // 1. REGAL: Busquem un Xuxemon de mida "Petit" aleatori.
        $randomXuxemon = Xuxemon::where('size', 'Petit')->inRandomOrder()->first();
        if ($randomXuxemon) {
            $user->xuxemons()->attach($randomXuxemon->id, ['food_eaten' => 0, 'disease' => null]);
        }

        // 2. REGAL: Entreguem 10 xuxes (5 unitats de cada un de dos tipus diferents).
        $xuxes = Item::where('type', 'xuxe')->inRandomOrder()->take(2)->get();

        foreach ($xuxes as $xuxe) {
            $existingItem = $user->items()->where('item_id', $xuxe->id)->first();
            if ($existingItem) {
                // Si ja en té, sumem a la quantitat existent al pivot.
                $user->items()->updateExistingPivot($xuxe->id, [
                    'quantity' => $existingItem->pivot->quantity + 5
                ]);
            } else {
                // Si és nou, creem la relació inicial a la motxilla.
                $user->items()->attach($xuxe->id, ['quantity' => 5]);
            }
        }

        // Actualitzem la data de l'última recompensa per bloquejar el proper intent.
        $user->last_daily_reward = Carbon::instance($now);
        $user->save();

        return response()->json([
            'message' => 'Has rebut el teu pack diari! Revisa la teva col·lecció i motxilla.'
        ]);
    }


    // ─────────────────────────────────────────────────────────────────────────
    // UTILITATS PRIVADES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Prepara la resposta JSON que conté el token i la informació bàsica de l'usuari.
     * Centralitza la configuració del temps d'expiració per al client d'Angular.
     * 
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $guard = $this->jwtGuard();
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $guard->factory()->getTTL() * 60, // Segons restants de validesa.
            'user'         => $guard->user()
        ]);
    }

    /**
     * Accés tipat al guard de JWT per evitar avisos de l'IDE i centralitzar el guard 'api'.
     * 
     * @return \Tymon\JWTAuth\JWTGuard
     */
    private function jwtGuard(): \Tymon\JWTAuth\JWTGuard
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        return $guard;
    }
}
