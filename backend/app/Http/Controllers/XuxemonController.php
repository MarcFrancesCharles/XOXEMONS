<?php

/**
 * ============================================================
 * FITXER: app/Http/Controllers/XuxemonController.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest controlador és el motor principal de la interacció
 *   amb les criatures (Xuxemons). Gestiona la consulta de la
 *   col·lecció personal, el sistema d'alimentació amb evolució
 *   dinàmica i el sistema de salut (malalties i vacunes).
 *
 * FUNCIONALITATS CLAU:
 *   - Llistar el Xuxedex de l'usuari amb dades d'estat.
 *   - Alimentar Xuxemons: consumeix ítems, augmenta experiència
 *     i activa la probabilitat d'evolució o infecció.
 *   - Vacunar Xuxemons: utilitza medicaments específics per
 *     netejar estats negatius (malalties).
 *
 * MAPA DE CONNEXIONS:
 *   → Model: App\Models\UserXuxemon (instàncies úniques de cada Xuxemon)
 *   → Model: App\Models\Xuxemon (catàleg base d'espècies)
 *   → Model: App\Models\Setting (configuració de probabilitats del sistema)
 *   → Relacions: User ↔ Item (per al consum de recursos)
 * ============================================================
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserXuxemon;
use App\Models\Xuxemon;
use App\Models\Setting;

class XuxemonController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // LLISTAR COL·LECCIÓ (Xuxedex)
    // ─────────────────────────────────────────────────────────

    /**
     * Retorna tots els Xuxemons de la col·lecció de l'usuari autenticat.
     * 
     * Inclou dades crítiques del pivot com 'food_eaten' i 'disease' per a que
     * el frontend d'Angular pugui renderitzar correctament les barres de vida,
     * estats d'evolució i icones de malaltia.
     */
    public function index()
    {
        // Utilitzem withPivot per garantir que Eloquent ens porti les columnes 
        // de la taula de relació (user_xuxemons). L'id del pivot és necessari
        // per identificar la instància exacta en accions posteriors.
        $xuxemons = Auth::user()->xuxemons()->withPivot('id', 'food_eaten', 'disease')->get();
        return response()->json($xuxemons);
    }


    // ─────────────────────────────────────────────────────────
    // ALIMENTAR UN XUXEMON
    // ─────────────────────────────────────────────────────────

    /**
     * Alimenta un Xuxemon concret utilitzant una xuxe de la motxilla.
     * 
     * Aquest mètode executa la lògica de negoci més complexa:
     * 1. Validació de propietat i estats bloquejants (Atracón).
     * 2. Gestió de l'inventari (consum de l'ítem).
     * 3. Càlcul de llindars d'evolució segons la mida i estats de salut.
     * 4. Sistema de probabilitats per a la infecció de malalties post-ingesta.
     *
     * @param Request $request Conté l'id de l'ítem (xuxe) a utilitzar.
     * @param int $pivot_id ID de la relació específica a user_xuxemons.
     */
    public function feed(Request $request, $pivot_id)
    {
        $user = Auth::user();

        // Validació: Ens assegurem que el Xuxemon existeix i pertany realment a l'usuari.
        $userXuxemon = UserXuxemon::where('id', $pivot_id)
                        ->where('user_id', $user->id)
                        ->first();

        if (!$userXuxemon) {
            return response()->json(['message' => 'Xuxemon no trobat a la teva col·lecció'], 404);
        }

        // REGLA DE NEGOCI: L'estat 'Atracón' és un bloqueig total d'alimentació.
        // El jugador ha de curar el Xuxemon abans de poder seguir alimentant-lo.
        if ($userXuxemon->disease === 'Atracón') {
            return response()->json([
                'message' => 'Aquest Xuxemon té un Atracón i no pot menjar més fins que el vacunis!'
            ], 400);
        }

        // Gestió d'inventari: Verifiquem si queda stock de la xuxe demanada.
        $itemId   = $request->input('item_id');
        $userItem = $user->items()->where('item_id', $itemId)->first();

        if (!$userItem || $userItem->pivot->quantity < 1) {
            return response()->json(['message' => 'No tens suficients xuxes d\'aquest tipus a la motxilla'], 400);
        }

        // Consum del recurs: Restem una unitat de la taula pivot user_items.
        $user->items()->updateExistingPivot($itemId, [
            'quantity' => $userItem->pivot->quantity - 1
        ]);

        // Incrementem el comptador d'alimentació.
        $userXuxemon->food_eaten += 1;

        // ── LÒGICA D'EVOLUCIÓ ──────────────────────────────────
        // L'evolució depèn de la mida actual de l'espècie i del menjar acumulat.
        $currentXuxemon = Xuxemon::find($userXuxemon->xuxemon_id);
        $evolved        = false;
        $nextSize       = '';
        $requiredFood   = 0;

        // Definició de llindars: Petit (3) -> Mitjà, Mitjà (5) -> Gran.
        if ($currentXuxemon->size === 'Petit') {
            $nextSize     = 'Mitja';
            $requiredFood = 3;
        } elseif ($currentXuxemon->size === 'Mitja') {
            $nextSize     = 'Gran';
            $requiredFood = 5;
        }

        // PENALITZACIÓ: El 'Bajón de azúcar' és una malaltia que fa el Xuxemon més gandul.
        // Necessita 2 unitats extres de menjar per poder evolucionar.
        if ($userXuxemon->disease === 'Bajón de azúcar') {
            $requiredFood += 2;
        }

        // Si s'arriba al llindar, busquem l'espècie equivalent de mida superior.
        if ($nextSize !== '' && $userXuxemon->food_eaten >= $requiredFood) {
            $nextXuxemon = Xuxemon::where('type', $currentXuxemon->type)
                                ->where('size', $nextSize)
                                ->first();

            if ($nextXuxemon) {
                // Evolució: Reset de comptadors i eliminació automàtica de malalties (cura biològica).
                $userXuxemon->xuxemon_id = $nextXuxemon->id;
                $userXuxemon->food_eaten = 0;
                $userXuxemon->disease    = null;
                $evolved = true;
            }
        }

        // ── SISTEMA D'INFECCIÓ ─────────────────────────────────
        // Si el Xuxemon no ha evolucionat, hi ha risc que emmalalteixi pel menjar.
        if (!$evolved) {
            $chance = rand(1, 100);

            // Recuperem les probabilitats globals configurades per l'administrador.
            $probAtracon    = Setting::where('key', 'atracon_prob')->value('value') ?? 15;
            $probSobredosis = Setting::where('key', 'sobredosis_prob')->value('value') ?? 10;
            $probBajon      = Setting::where('key', 'bajon_prob')->value('value') ?? 5;

            // Aplicació de probabilitats acumulatives per determinar el nou estat de salut.
            if ($chance <= $probAtracon) {
                $userXuxemon->disease = 'Atracón';
            } elseif ($chance <= ($probAtracon + $probSobredosis)) {
                $userXuxemon->disease = 'Sobredosis de sucre';
            } elseif ($chance <= ($probAtracon + $probSobredosis + $probBajon)) {
                $userXuxemon->disease = 'Bajón de azúcar';
            }
        }

        // Persistim els canvis a la base de dades.
        $userXuxemon->save();

        return response()->json([
            'message'    => $evolved ? 'Enhorabona! El teu Xuxemon ha evolucionat!' : 'Xuxemon alimentat correctament!',
            'food_eaten' => $userXuxemon->food_eaten,
            'evolved'    => $evolved,
            'disease'    => $userXuxemon->disease,
        ]);
    }


    // ─────────────────────────────────────────────────────────
    // VACUNAR UN XUXEMON
    // ─────────────────────────────────────────────────────────

    /**
     * Cura una malaltia d'un Xuxemon utilitzant un medicament de la motxilla.
     * 
     * Cada medicament té una especificitat:
     * - Xocolatina: Cura el 'Bajón de azúcar'.
     * - Xal de fruites: Cura l'Atracón'.
     * - Inxulina: És la vacuna universal (cura qualsevol malaltia).
     */
    public function vaccinate(Request $request, $pivot_id)
    {
        $user = Auth::user();

        $userXuxemon = UserXuxemon::where('id', $pivot_id)
                        ->where('user_id', $user->id)
                        ->first();

        if (!$userXuxemon) {
            return response()->json(['message' => 'Xuxemon no trobat a la teva col·lecció'], 404);
        }

        // Si el Xuxemon ja està sa, no permetem malbaratar el recurs.
        if (!$userXuxemon->disease) {
            return response()->json(['message' => 'Aquest Xuxemon ja està totalment sa! Estàs malbaratant la vacuna.'], 400);
        }

        // Verificació d'stock de la vacuna triada.
        $itemId   = $request->input('item_id');
        $userItem = $user->items()->where('item_id', $itemId)->first();

        if (!$userItem || $userItem->pivot->quantity < 1) {
            return response()->json(['message' => 'No tens aquesta vacuna a la teva motxilla'], 400);
        }

        // ── COMPATIBILITAT DE MEDICAMENTS ─────────────────────
        $vaccineName    = $userItem->name;
        $currentDisease = $userXuxemon->disease;
        $cured          = false;

        // Comprovem si el medicament seleccionat serveix per a la malaltia actual.
        if ($vaccineName === 'Xocolatina' && $currentDisease === 'Bajón de azúcar') {
            $cured = true;
        } elseif ($vaccineName === 'Xal de fruites' && $currentDisease === 'Atracón') {
            $cured = true;
        } elseif ($vaccineName === 'Inxulina') {
            $cured = true; // L'Inxulina ho cura tot.
        }

        if (!$cured) {
            return response()->json([
                'message' => "La vacuna $vaccineName no serveix per curar l'estat: $currentDisease!"
            ], 400);
        }

        // Consum del medicament i actualització de l'estat de salut a 'null' (Sa).
        $user->items()->updateExistingPivot($itemId, [
            'quantity' => $userItem->pivot->quantity - 1
        ]);

        $userXuxemon->disease = null;
        $userXuxemon->save();

        return response()->json([
            'message' => 'El teu Xuxemon s\'ha curat correctament i torna a estar actiu! 🩺✨'
        ]);
    }
}