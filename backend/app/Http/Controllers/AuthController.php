<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // --- 1. FUNCIÓ DE REGISTRE ---
    public function register(Request $request)
    {
        // Validem que ens enviïn totes les dades requerides
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'surnames' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // Espera un camp 'password_confirmation'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Determinar si és el primer usuari (🤖 Robot) o no (🕹️ Jugador)
        $isFirstUser = User::count() === 0;
        $role = $isFirstUser ? 'robot' : 'player';

        // Generar el Custom ID: #NomXXXX
        $cleanName = str_replace(' ', '', $request->name); // Treiem espais
        
        // Bucle per assegurar-nos que el número de 4 xifres no estigui repetit
        do {
            $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $customId = $cleanName . '#' . $randomNumber;
        } while (User::where('custom_id', $customId)->exists());

        // Creem l'usuari a la Base de Dades
        $user = User::create([
            'custom_id' => $customId,
            'name' => $request->name,
            'surnames' => $request->surnames,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        return response()->json([
            'message' => 'Usuari registrat correctament!',
            'user' => $user
        ], 201);
    }

    // --- 2. FUNCIÓ DE LOGIN ---
    public function login(Request $request)
    {
        // El document diu que el login és amb el ID i Contrasenya
        $credentials = $request->only('custom_id', 'password');

        // Verificar que el usuari existeix o les credencials son incorrectes
        $user = User::where('custom_id', $credentials['custom_id'])->first();
        if (! $token = $this->jwtGuard()->attempt($credentials)) {
            return response()->json(['error' => 'Credencials invàlides'], 401);
        }

        return $this->respondWithToken($token);
    }

    // --- 3. VEURE INFO DE L'USUARI ---
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    // --- 4. TANCAR SESSIÓ ---
    public function logout()
    {
        $this->jwtGuard()->logout();
        return response()->json(['message' => 'Sessió tancada correctament']);
    }

    // Funció auxiliar per formatar la resposta del token
    protected function respondWithToken($token)
    {
        $guard = $this->jwtGuard();
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
            'user' => $guard->user()
        ]);
    }

    /**
     * Retorna el guard JWT amb el tipus correcte per a l'anàlisi estàtic.
     *
     * @return \Tymon\JWTAuth\JWTGuard
     */
    private function jwtGuard(): \Tymon\JWTAuth\JWTGuard
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        return $guard;
    }

    // --- RECOMPENSA DIÀRIA ---
    public function claimDailyReward(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $now = now();

        // 1. Comprovem si ja l'ha reclamat avui
        if ($user->last_daily_reward && $user->last_daily_reward->isToday()) {
            return response()->json(['message' => 'Ja has reclamat la teva recompensa avui! Torna demà.'], 400);
        }

        // 2. Donem 1 Xuxemon 'Petit' aleatori
        $randomXuxemon = \App\Models\Xuxemon::where('size', 'Petit')->inRandomOrder()->first();
        if ($randomXuxemon) {
            $user->xuxemons()->attach($randomXuxemon->id, ['food_eaten' => 0, 'disease' => null]);
        }

        // 3. Donem 10 xuxes (5 unitats de 2 xuxes aleatòries)
        $xuxes = \App\Models\Item::where('type', 'xuxe')->inRandomOrder()->take(2)->get();
        
        foreach ($xuxes as $xuxe) {
            $existingItem = $user->items()->where('item_id', $xuxe->id)->first();
            if ($existingItem) {
                $user->items()->updateExistingPivot($xuxe->id, [
                    'quantity' => $existingItem->pivot->quantity + 5
                ]);
            } else {
                $user->items()->attach($xuxe->id, ['quantity' => 5]);
            }
        }

        // 4. Actualitzem la data de l'última recompensa
        $user->last_daily_reward = \Illuminate\Support\Carbon::instance($now);
        $user->save();

        return response()->json([
            'message' => '🎉 Recompensa diària reclamada! Has guanyat 10 xuxes i un ' . ($randomXuxemon ? $randomXuxemon->name : 'Nou Xuxemon') . '!'
        ]);
    }


}