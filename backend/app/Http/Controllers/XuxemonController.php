<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/XuxemonController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Gestiona totes les interaccions del jugador amb els seus
 *   Xuxemons: consulta de la col·lecció, alimentació (amb lògica
 *   d'evolució i infecció de malalties) i vacunació (curació).
 *   És el controlador amb la lògica de negoci més complexa del projecte.
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\UserXuxemon (pivot amb la instància del Xuxemon de l'usuari)
 *   → Model: App\Models\Xuxemon (dades base del Xuxemon: tipus, mida, nom)
 *   → Model: App\Models\Setting (probabilitats de malalties configurades per l'admin)
 *   → Model: App\Models\User (via Auth::user(), per accedir a items i xuxemons)
 *   → Taula pivot: user_xuxemons (food_eaten, disease, xuxemon_id)
 *   → Taula pivot: user_items (quantity de xuxes i vacunes)
 *   → Cridat des de: routes/api.php (rutes /xuxedex, /xuxemons/{id}/feed,
 *     /xuxemons/{id}/vaccinate)
 * ============================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserXuxemon;
use App\Models\Xuxemon;

class XuxemonController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // LLISTAR COL·LECCIÓ (Xuxedex)
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna tots els Xuxemons de la col·lecció de l'usuari autenticat,
     * incloent les dades del pivot (id, food_eaten, disease) que Angular
     * necessita per mostrar l'estat de cada Xuxemon.
     */
    public function index()
    {
        // withPivot() és imprescindible per portar les columnes extra de la taula pivot.
        // Sense això, food_eaten i disease no apareixerien a la resposta JSON.
        // L'id del pivot (user_xuxemons.id) és el que s'usa a /feed i /vaccinate.
        $xuxemons = Auth::user()->xuxemons()->withPivot('id', 'food_eaten', 'disease')->get();
        return response()->json($xuxemons);
    }


    // ─────────────────────────────────────────────────────────
    // ALIMENTAR UN XUXEMON
    // ─────────────────────────────────────────────────────────

    /**
     * Alimenta un Xuxemon concret amb una xuxe de la motxilla del jugador.
     * Comprova malalties, gestiona l'evolució i pot provocar noves malalties.
     *
     * Flux complet:
     *   1. Verificar que el Xuxemon pertany a l'usuari
     *   2. Bloquejar si té Atracón (no pot menjar)
     *   3. Descomptar 1 xuxe de la motxilla
     *   4. Incrementar food_eaten
     *   5. Comprovar si evoluciona
     *   6. Si no evoluciona, tirar el dau de malalties
     *   7. Desar l'estat i retornar la resposta
     *
     * @param int $pivot_id  ID de la fila a user_xuxemons (no de xuxemons)
     */
    public function feed(Request $request, $pivot_id)
    {
        $user = Auth::user();

        // Busquem per pivot_id I user_id alhora per seguretat:
        // un jugador no pot alimentar el Xuxemon d'un altre jugador
        // enviant un pivot_id aliè.
        $userXuxemon = \App\Models\UserXuxemon::where('id', $pivot_id)
                        ->where('user_id', $user->id)
                        ->first();

        if (!$userXuxemon) {
            return response()->json(['message' => 'Xuxemon no trobat a la teva col·lecció'], 404);
        }

        // REGLA DE NEGOCI: Un Xuxemon amb Atracón no pot menjar més.
        // Bloquejem l'alimentació fins que el jugador l'hagi vacunat.
        // Cal retornar un 400 (no 500) perquè és un error de l'usuari, no del servidor.
        if ($userXuxemon->disease === 'Atracón') {
            return response()->json([
                'message' => 'Aquest Xuxemon té un Atracón i no pot menjar més fins que el vacunis!'
            ], 400);
        }

        // Comprovem que l'usuari té l'ítem demanat i en quantitat suficient.
        $itemId   = $request->input('item_id');
        $userItem = $user->items()->where('item_id', $itemId)->first();

        if (!$userItem || $userItem->pivot->quantity < 1) {
            return response()->json(['message' => 'No tens suficients xuxes daquest tipus'], 400);
        }

        // Descomptem 1 unitat de la xuxe usada. Actualitzem la taula pivot user_items.
        $user->items()->updateExistingPivot($itemId, [
            'quantity' => $userItem->pivot->quantity - 1
        ]);

        // Incrementem el comptador de menjar d'aquesta instància del Xuxemon.
        $userXuxemon->food_eaten += 1;

        // ── LÒGICA D'EVOLUCIÓ ──────────────────────────────────
        // Llegim les dades base del Xuxemon per saber la seva mida actual.
        $currentXuxemon = \App\Models\Xuxemon::find($userXuxemon->xuxemon_id);
        $evolved        = false;
        $nextSize       = '';
        $requiredFood   = 0;

        // Definim les condicions d'evolució per mida:
        // Petit → Mitja (necessita 3 xuxes), Mitja → Gran (necessita 5 xuxes).
        // Els Grans no evolucionen, per tant no entren al if.
        if ($currentXuxemon->size === 'Petit') {
            $nextSize     = 'Mitja';
            $requiredFood = 3;
        } elseif ($currentXuxemon->size === 'Mitja') {
            $nextSize     = 'Gran';
            $requiredFood = 5;
        }

        // MODIFICADOR DE MALALTIA: el "Bajón de azúcar" dificulta l'evolució
        // afegint 2 xuxes addicionals al llindar necessari.
        if ($userXuxemon->disease === 'Bajón de azúcar') {
            $requiredFood += 2;
        }

        // Si s'ha assolit el llindar d'evolució, busquem el Xuxemon de la mida següent
        // del MATEIX TIPUS (Aigua evoluciona a Aigua, no a Terra).
        if ($nextSize !== '' && $userXuxemon->food_eaten >= $requiredFood) {
            $nextXuxemon = \App\Models\Xuxemon::where('type', $currentXuxemon->type)
                                ->where('size', $nextSize)
                                ->first();

            if ($nextXuxemon) {
                // Evolució: canviem el xuxemon_id al nou Xuxemon evolucionat,
                // reiniciem el comptador de menjar i curem qualsevol malaltia
                // (l'evolució actua com a reset biològic del Xuxemon).
                $userXuxemon->xuxemon_id = $nextXuxemon->id;
                $userXuxemon->food_eaten = 0;
                $userXuxemon->disease    = null;
                $evolved = true;
            }
        }

        // ── SISTEMA D'INFECCIÓ ─────────────────────────────────
        // Només tirem el dau de malalties si NO hi ha hagut evolució.
        // Evolucionar cura i "protegeix" d'emmalaltir en aquell torn.
        if (!$evolved) {
            $chance = rand(1, 100);

            // Llegim les probabilitats de la BD (configurades per l'admin).
            // Usem ?? per proporcionar valors per defecte si l'admin encara no
            // ha executat SettingSeeder o no ha configurat res.
            $probAtracon    = \App\Models\Setting::where('key', 'atracon_prob')->value('value') ?? 15;
            $probSobredosis = \App\Models\Setting::where('key', 'sobredosis_prob')->value('value') ?? 10;
            $probBajon      = \App\Models\Setting::where('key', 'bajon_prob')->value('value') ?? 5;

            // Apliquem les probabilitats de forma acumulativa:
            // Si $chance és 1-15 → Atracón
            // Si $chance és 16-25 → Sobredosis
            // Si $chance és 26-30 → Bajón
            // Si $chance és 31-100 → Sa (no passa res)
            // Nota: si un Xuxemon ja té una malaltia, el valor de disease
            // simplement es sobreescriu. Aquesta és una simplificació del model.
            if ($chance <= $probAtracon) {
                $userXuxemon->disease = 'Atracón';
            } elseif ($chance <= ($probAtracon + $probSobredosis)) {
                $userXuxemon->disease = 'Sobredosis de sucre';
            } elseif ($chance <= ($probAtracon + $probSobredosis + $probBajon)) {
                $userXuxemon->disease = 'Bajón de azúcar';
            }
            // Si $chance no entra a cap if, disease roman sense canvis.
        }

        // Desem tots els canvis de cop (food_eaten, xuxemon_id, disease).
        $userXuxemon->save();

        return response()->json([
            'message'    => $evolved ? 'El teu Xuxemon ha evolucionat!' : 'Xuxemon alimentat correctament!',
            'food_eaten' => $userXuxemon->food_eaten,
            'evolved'    => $evolved,
            // Retornem la malaltia per a que Angular pugui actualitzar la UI sense
            // necessitar una nova crida a /xuxedex.
            'disease'    => $userXuxemon->disease,
        ]);
    }


    // ─────────────────────────────────────────────────────────
    // VACUNAR UN XUXEMON (curar malaltia)
    // ─────────────────────────────────────────────────────────

    /**
     * Aplica una vacuna de la motxilla del jugador a un Xuxemon malalt.
     * Cada vacuna cura una malaltia específica, excepte la Inxulina
     * que ho cura tot.
     *
     * @param int $pivot_id  ID de la fila a user_xuxemons
     */
    public function vaccinate(Request $request, $pivot_id)
    {
        $user = Auth::user();

        // Mateixa doble validació que a feed(): pivot_id + user_id per seguretat.
        $userXuxemon = \App\Models\UserXuxemon::where('id', $pivot_id)
                        ->where('user_id', $user->id)
                        ->first();

        if (!$userXuxemon) {
            return response()->json(['message' => 'Xuxemon no trobat a la teva col·lecció'], 404);
        }

        // Si el Xuxemon ja està sa, vacunar-lo seria un malbaratament de l'ítem.
        // Bloquejem i informem l'usuari per evitar que perdi una vacuna.
        if (!$userXuxemon->disease) {
            return response()->json(['message' => 'Aquest Xuxemon ja està totalment sa!'], 400);
        }

        // Verifiquem que l'usuari té la vacuna a la seva motxilla i en quantitat suficient.
        $itemId   = $request->input('item_id');
        $userItem = $user->items()->where('item_id', $itemId)->first();

        if (!$userItem || $userItem->pivot->quantity < 1) {
            return response()->json(['message' => 'No tens aquesta vacuna a la teva motxilla'], 400);
        }

        // ── REGLES DE COMPATIBILITAT VACUNA ↔ MALALTIA ────────
        // Verifiquem que la vacuna és adequada per a la malaltia actual.
        // Nota: els noms de vacuna han de coincidir EXACTAMENT amb els
        // inserits per ItemSeeder a la taula 'items'.
        $vaccineName    = $userItem->name;
        $currentDisease = $userXuxemon->disease;
        $cured          = false;

        if ($vaccineName === 'Xocolatina' && $currentDisease === 'Bajón de azúcar') {
            $cured = true;
        } elseif ($vaccineName === 'Xal de fruites' && $currentDisease === 'Atracón') {
            $cured = true;
        } elseif ($vaccineName === 'Inxulina') {
            // L'Inxulina és la vacuna universal: cura qualsevol malaltia.
            $cured = true;
        }

        // Si la vacuna no és compatible, retornem un error explicatiu per
        // que l'usuari entengui per què no funciona i quin ítem ha d'usar.
        if (!$cured) {
            return response()->json([
                'message' => "La vacuna $vaccineName no serveix per curar $currentDisease!"
            ], 400);
        }

        // Descomptem 1 unitat de la vacuna usada de la motxilla.
        $user->items()->updateExistingPivot($itemId, [
            'quantity' => $userItem->pivot->quantity - 1
        ]);

        // Curem el Xuxemon esborrant el valor de disease (null = sa).
        $userXuxemon->disease = null;
        $userXuxemon->save();

        return response()->json([
            'message' => 'El teu Xuxemon s\'ha curat correctament i torna a estar actiu! 🩺✨'
        ]);
    }
}