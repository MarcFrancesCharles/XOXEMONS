<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/AuthController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Gestiona tot el cicle de vida d'un usuari: registre, login,
 *   logout, consulta del perfil propi, recompensa diària,
 *   actualització de dades i eliminació de compte.
 *   És el controlador més transversal del projecte perquè
 *   afecta directament el Model User, que és la base de totes
 *   les relacions.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\User (lectura, creació, actualització, eliminació)
 *   → Model: App\Models\Xuxemon (per a la recompensa diària)
 *   → Model: App\Models\Item (per a la recompensa diària)
 *   → Llibreria: Tymon\JWTAuth (generació i invalidació de tokens)
 *   → Cridat des de: routes/api.php (rutes /register, /login, /me,
 *     /logout, /user/profile, /user/account, /user/daily-reward)
 * ============================================================
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
    // ─────────────────────────────────────────────────────────
    // REGISTRE
    // ─────────────────────────────────────────────────────────

    /**
     * Crea un nou usuari al sistema.
     *
     * Flux: Angular envia les dades del formulari → Laravel valida →
     * es determina el rol → es genera un custom_id únic → es desa a la BD.
     */
    public function register(Request $request)
    {
        // Validem tots els camps del formulari d'una vegada.
        // 'confirmed' a password exigeix que existeixi un camp 'password_confirmation'
        // amb el mateix valor, evitant errors tipogràfics a la contrasenya.
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'surnames' => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Si hi ha errors, retornem un 400 amb el detall de cada camp incorrecte.
        // Això permet a Angular mostrar missatges d'error específics per camp.
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // El primer usuari que es registra rep el rol 'robot' (administrador).
        // Tots els posteriors seran 'player'. D'aquesta manera no cal un
        // procés d'instal·lació manual per crear l'admin.
        $isFirstUser = User::count() === 0;
        $role = $isFirstUser ? 'robot' : 'player';

        // Generar el Custom ID: #NomXXXX
        $cleanName = str_replace(' ', '', $request->name); // Treiem espais

        // Bucle per assegurar-nos que el número de 4 xifres no estigui repetit
        do {
            $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $customId = $cleanName . '#' . $randomNumber;
        } while (User::where('custom_id', $customId)->exists());

        // Desem l'usuari. Hash::make() aplica bcrypt a la contrasenya;
        // mai desem contrasenyes en text pla.
        $user = User::create([
            'custom_id' => $customId,
            'name'      => $request->name,
            'surnames'  => $request->surnames,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $role,
        ]);

        // Retornem 201 Created (no 200 OK) perquè hem creat un nou recurs.
        return response()->json([
            'message' => 'Usuari registrat correctament!',
            'user'    => $user
        ], 201);
    }


    // ─────────────────────────────────────────────────────────
    // LOGIN
    // ─────────────────────────────────────────────────────────

    /**
     * Autentica l'usuari i retorna un token JWT.
     *
     * Flux: Angular envia custom_id + password → JWT intenta autenticar
     * contra la BD → si és correcte, retorna el token.
     */
    public function login(Request $request)
    {
        // Agafem NOMÉS els camps que necessitem, descartant qualsevol altra dada
        // de la petició per seguretat (evitar mass assignment a l'autenticació).
        $credentials = $request->only('custom_id', 'password');

        // Verificar que el usuari existeix o les credencials son incorrectes
        $user = User::where('custom_id', $credentials['custom_id'])->first();

        // Creació del token JWT
        if (!$token = $this->jwtGuard()->attempt($credentials)) {
            return response()->json(['error' => 'Credencials invàlides'], 401);
        }

        // El token és vàlid: formatem la resposta amb les dades que Angular necessita.
        return $this->respondWithToken($token);
    }


    // ─────────────────────────────────────────────────────────
    // CONSULTA DEL PERFIL PROPI
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna les dades de l'usuari propietari del token actual.
     * El middleware 'auth:api' ja ha validat el token i injectat l'usuari,
     * per tant auth('api')->user() és sempre vàlid aquí.
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }


    // ─────────────────────────────────────────────────────────
    // LOGOUT
    // ─────────────────────────────────────────────────────────

    /**
     * Invalida el token JWT actual, afegint-lo a la blacklist de JWT.
     * Després d'això, qualsevol petició amb aquest token rebrà un 401.
     */
    public function logout()
    {
        $this->jwtGuard()->logout();
        return response()->json(['message' => 'Sessió tancada correctament']);
    }


    // ─────────────────────────────────────────────────────────
    // RECOMPENSA DIÀRIA
    // ─────────────────────────────────────────────────────────

    /**
     * Dona al jugador 1 Xuxemon petit aleatori + 10 xuxes, una vegada al dia.
     *
     * Flux: Angular crida /user/daily-reward → comprovem last_daily_reward
     * → si no s'ha reclamat avui, donem la recompensa i actualitzem la data.
     */
    public function claimDailyReward(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $now  = now();

        // Comprovem si last_daily_reward és d'avui.
        // ->isToday() compara amb la timezone del servidor (UTC per defecte).
        // Si l'usuari ja ha reclamat avui, bloquejem la petició.
        if ($user->last_daily_reward && $user->last_daily_reward->isToday()) {
            return response()->json(
                ['message' => 'Ja has reclamat la teva recompensa avui! Torna demà.'],
                400
            );
        }

        // Donem un Xuxemon 'Petit' aleatori. Els Petits son el punt d'entrada
        // del joc: el jugador els ha d'alimentar per evolucionar-los.
        $randomXuxemon = Xuxemon::where('size', 'Petit')->inRandomOrder()->first();
        if ($randomXuxemon) {
            // Afegim el Xuxemon a la taula pivot user_xuxemons.
            // Inicialitzem food_eaten a 0 i disease a null (Xuxemon sa i nou).
            $user->xuxemons()->attach($randomXuxemon->id, ['food_eaten' => 0, 'disease' => null]);
        }

        // Donem 10 xuxes en forma de 2 tipus de xuxes × 5 unitats cadascun.
        // Agafem 2 ítems aleatoris de tipus 'xuxe' de la BD.
        $xuxes = Item::where('type', 'xuxe')->inRandomOrder()->take(2)->get();

        foreach ($xuxes as $xuxe) {
            // Comprovem si el jugador ja té aquest tipus de xuxe a la motxilla.
            // Si en té, sumem les 5 unitats noves a les existents (lògica d'apilament).
            // Si no en té, creem una nova fila a user_items amb 5 unitats.
            $existingItem = $user->items()->where('item_id', $xuxe->id)->first();
            if ($existingItem) {
                $user->items()->updateExistingPivot($xuxe->id, [
                    'quantity' => $existingItem->pivot->quantity + 5
                ]);
            } else {
                $user->items()->attach($xuxe->id, ['quantity' => 5]);
            }
        }

        // Marquem la data de la recompensa com avui. Usem Carbon::instance()
        // per assegurar que és un objecte Carbon compatible amb el cast 'datetime' del model.
        $user->last_daily_reward = Carbon::instance($now);
        $user->save();

        return response()->json([
            'message' => '🎉 Recompensa diària reclamada! Has guanyat 10 xuxes i un '
                       . ($randomXuxemon ? $randomXuxemon->name : 'Nou Xuxemon') . '!'
        ]);
    }


    // ─────────────────────────────────────────────────────────
    // ACTUALITZAR PERFIL
    // ─────────────────────────────────────────────────────────

    /**
     * Actualitza parcialment les dades de perfil de l'usuari.
     * Tots els camps són opcionals ('sometimes'), per tant el client
     * pot enviar només els camps que vol modificar.
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = auth('api')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'surnames' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->filled('name'))
            $user->name = $request->name;
        if ($request->filled('surnames'))
            $user->surnames = $request->surnames;
        if ($request->filled('email'))
            $user->email = $request->email;
        if ($request->filled('password'))
            $user->password = Hash::make($request->password);

        $user->save();

        return response()->json([
            'message' => 'Perfil actualitzat correctament!',
            'user' => $user
        ]);
    }


    // ─────────────────────────────────────────────────────────
    // ELIMINAR COMPTE
    // ─────────────────────────────────────────────────────────

    /**
     * Esborra permanentment el compte de l'usuari autenticat.
     * Primer invalida el token per evitar peticions orfes amb el token
     * d'un usuari que ja no existeix.
     */
    public function deleteAccount()
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        // Invalitem el token ABANS d'esborrar l'usuari. Si ho féssim al revés,
        // el guard JWT intentaria buscar l'usuari per invalidar el token i fallaria.
        $this->jwtGuard()->logout();

        // L'eliminació en cascada (definida a les FK de les migrations) esborra
        // automàticament tots els user_xuxemons, user_items, friendships i messages
        // relacionats amb aquest usuari.
        $user->delete();

        return response()->json(['message' => 'Compte esborrat correctament.']);
    }


    // ─────────────────────────────────────────────────────────
    // MÈTODES AUXILIARS PRIVATS
    // ─────────────────────────────────────────────────────────

    /**
     * Formata la resposta estàndard d'autenticació amb token JWT.
     * Centralitzem el format aquí perquè tant login() com futures
     * funcions de refresh puguin usar-lo sense duplicar codi.
     */
    protected function respondWithToken($token)
    {
        $guard = $this->jwtGuard();
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            // getTTL() retorna els minuts; multipliquem per 60 per obtenir segons.
            // Angular l'utilitza per saber quan ha de fer logout automàtic.
            'expires_in'   => $guard->factory()->getTTL() * 60,
            'user'         => $guard->user()
        ]);
    }

    /**
     * Retorna el guard JWT amb el tipus correcte per a l'anàlisi estàtic de PhpStorm/Intelephense.
     * Sense aquest wrapper, auth('api') retorna un tipus genèric Guard que no exposa
     * els mètodes específics de JWTGuard com attempt(), factory(), etc.
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