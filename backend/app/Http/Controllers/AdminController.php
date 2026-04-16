<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/AdminController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Proporciona les eines de gestió del joc per a l'usuari
 *   administrador (rol 'robot'). Permet donar ítems i Xuxemons
 *   als jugadors des del panell d'admin d'Angular, i gestionar
 *   la configuració global de probabilitats de malalties.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\User (cercar jugadors i modificar-ne la motxilla)
 *   → Model: App\Models\Item (crear o trobar ítems, associar-los a usuaris)
 *   → Model: App\Models\Xuxemon (seleccionar un Xuxemon aleatori)
 *   → Model: App\Models\Setting (llegir i escriure configuració global)
 *   → Taula pivot: user_items (via relació $user->items())
 *   → Taula pivot: user_xuxemons (via relació $user->xuxemons())
 *   → Cridat des de: routes/api.php (rutes /admin/*)
 * ============================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;

class AdminController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // LLISTAR USUARIS (per al selector del panell d'admin)
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna la llista de jugadors per omplir el <select> del panell d'admin.
     * Només retornem els camps mínims necessaris per no exposar dades sensibles
     * com la contrasenya (tot i que el model User ja la oculta via $hidden).
     */
    public function getUsers()
    {
        // select() limita les columnes retornades: no necessitem l'email, role, etc.
        // per a un simple selector de destinatari.
        return response()->json(User::select('id', 'name', 'custom_id')->get());
    }


    // ─────────────────────────────────────────────────────────
    // DONAR UN ÍTEM A UN JUGADOR
    // ─────────────────────────────────────────────────────────

    /**
     * Afegeix un ítem (xuxe o vacuna) a la motxilla d'un jugador,
     * respectant el límit de 20 espais i la lògica d'apilament.
     *
     * Lògica d'espais:
     *   - Xuxes (apilables): cada grup de 5 unitats ocupa 1 espai.
     *   - Vacunes (no apilables): cada unitat ocupa 1 espai.
     */
    public function giveItem(Request $request)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'item_type' => 'required|in:xuxe,vacuna',
            'item_name' => 'required|string',
            'quantity'  => 'required|integer|min:1'
        ]);

        $user = User::findOrFail($request->user_id);

        // firstOrCreate és clau aquí: si l'ítem ja existeix a la taula 'items'
        // el recupera, i si no existeix el crea. Això permet que l'admin pugui
        // crear nous tipus d'ítems des del panell sense modificar la BD directament.
        $item = Item::firstOrCreate(
            ['name' => $request->item_name, 'type' => $request->item_type],
            // is_stackable depèn del tipus: les xuxes s'apilen, les vacunes no.
            ['is_stackable' => $request->item_type === 'xuxe']
        );

        // Calculem quants espais de motxilla ocupa ACTUALMENT l'inventari de l'usuari.
        // Iterem sobre tots els seus ítems per acumular el total d'espais usats.
        $totalSlotsUsed = 0;
        foreach ($user->items as $userItem) {
            if ($userItem->is_stackable) {
                // Les xuxes s'apilen de 5 en 5 per espai.
                // ceil() arrodoneix cap amunt: 6 xuxes = 2 espais (no 1.2).
                $totalSlotsUsed += ceil($userItem->pivot->quantity / 5);
            } else {
                // Cada vacuna ocupa 1 espai, independentment de la quantitat.
                $totalSlotsUsed += $userItem->pivot->quantity;
            }
        }

        // Si la motxilla ja és plena (20 espais), bloquejem l'operació.
        // Retornem 400 perquè és un error de negoci (no un error del servidor).
        if ($totalSlotsUsed >= 20) {
            return response()->json([
                'error' => 'La motxilla daquest jugador està plena (20/20 espais). Els objectes han estat descartats.'
            ], 400);
        }

        // Comprovem si l'usuari ja té exactament aquest ítem a la motxilla.
        // Si en té, sumem la quantitat nova a l'existent (actualitzem el pivot).
        // Si no en té, creem una nova fila a user_items (attach).
        $existingItem = $user->items()->where('item_id', $item->id)->first();

        if ($existingItem) {
            $user->items()->updateExistingPivot($item->id, [
                'quantity' => $existingItem->pivot->quantity + $request->quantity
            ]);
        } else {
            $user->items()->attach($item->id, ['quantity' => $request->quantity]);
        }

        return response()->json(['message' => 'Ítem afegit correctament a la motxilla!']);
    }


    // ─────────────────────────────────────────────────────────
    // DONAR UN XUXEMON ALEATORI
    // ─────────────────────────────────────────────────────────

    /**
     * Afegeix un Xuxemon aleatori (de qualsevol tipus i mida) a la
     * col·lecció d'un jugador. Usat des del panell d'admin com a premi.
     */
    public function giveRandomXuxemon(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($request->user_id);

        // inRandomOrder() aplica ORDER BY RAND() a SQL, garantint aleatoreïtat real.
        // first() limita a 1 resultat per eficiència.
        $randomXuxemon = \App\Models\Xuxemon::inRandomOrder()->first();

        // Comprovació defensiva: si no hi ha cap Xuxemon creat a la BD,
        // retornem un 404 en lloc de fallar silenciosament.
        if (!$randomXuxemon) {
            return response()->json(['error' => 'No hi ha cap Xuxemon creat a la BBDD!'], 404);
        }

        // attach() crea una nova fila a user_xuxemons sense camps pivot addicionals.
        // Cada attach() crea una instància independent del Xuxemon per a l'usuari.
        $user->xuxemons()->attach($randomXuxemon->id);

        return response()->json(['message' => 'Has regalat el Xuxemon: ' . $randomXuxemon->name]);
    }


    // ─────────────────────────────────────────────────────────
    // CONFIGURACIÓ GLOBAL DEL JOC (probabilities de malalties)
    // ─────────────────────────────────────────────────────────

    /**
     * Llegeix la configuració global de probabilitats de malalties.
     * Retorna un objecte clau-valor que Angular pot consumir directament
     * sense transformació addicional.
     */
    public function getSettings()
    {
        // pluck('value', 'key') converteix la col·lecció en un array associatiu:
        // ['atracon_prob' => 10, 'sobredosis_prob' => 5, 'bajon_prob' => 5]
        // Molt més còmode per Angular que un array d'objectes [{key:..., value:...}].
        $settings = \App\Models\Setting::pluck('value', 'key');
        return response()->json($settings);
    }

    /**
     * Desa la nova configuració de probabilitats.
     * XuxemonController llegirà aquests valors en cada alimentació
     * per determinar si un Xuxemon emmalalteix.
     */
    public function updateSettings(\Illuminate\Http\Request $request)
    {
        // Validem que els 3 paràmetres siguin enters entre 0 i 100.
        // No validem que la suma sigui ≤ 100: responsabilitat de l'admin.
        $validated = $request->validate([
            'atracon_prob'    => 'required|integer|min:0|max:100',
            'sobredosis_prob' => 'required|integer|min:0|max:100',
            'bajon_prob'      => 'required|integer|min:0|max:100',
        ]);

        // updateOrCreate per a cada paràmetre: si la clau ja existeix, actualitza el valor.
        // Si no existeix (primera configuració), la crea.
        // Això permet que funcioni tant en el primer ús com en actualitzacions posteriors.
        foreach ($validated as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['message' => '⚙️ Configuració global del joc actualitzada amb èxit!']);
    }
}