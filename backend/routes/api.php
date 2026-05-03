<?php

/**
 * ============================================================
 * FITXER: routes/api.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest fitxer és el "directori central" de tota la API REST
 *   del backend de Xuxemons. Totes les peticions HTTP que arriben
 *   al servidor passen per aquí primer. És el punt d'entrada que
 *   decideix quin Controlador ha de gestionar cada ruta i si cal
 *   estar autenticat o no per accedir-hi.
 *
 * MAPA DE CONNEXIONS:
 *   → Utilitza tots els Controllers: AuthController, AdminController,
 *     InventoryController, XuxemonController, FriendController,
 *     ChatController, BattleController.
 *   → Es recolza en el middleware 'auth:api' definit a config/auth.php,
 *     que internament usa el guard JWT configurat a config/jwt.php.
 *   → Laravel carrega aquest fitxer automàticament des de bootstrap/app.php.
 * ============================================================
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\XuxemonController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\ChatController;

// ─────────────────────────────────────────────────────────────
// RUTES PÚBLIQUES
// Aquestes rutes NO requereixen token JWT perquè són el punt
// d'entrada al sistema: un usuari nou no pot tenir token encara.
// ─────────────────────────────────────────────────────────────

// El client envia nom, email i contrasenya → AuthController crea l'usuari a la BD.
Route::post('/register', [AuthController::class, 'register']);

// El client envia custom_id i contrasenya → AuthController retorna un token JWT.
Route::post('/login', [AuthController::class, 'login']);


// ─────────────────────────────────────────────────────────────
// RUTES PROTEGIDES
// Tot el que hi ha dins d'aquest grup requereix que la petició
// porti una capçalera "Authorization: Bearer <token>".
// El middleware 'auth:api' intercepta la petició, valida el token
// JWT i, si és vàlid, injecta l'usuari autenticat a la sessió.
// Si el token és invàlid o ha caducat, retorna un error 401.
// ─────────────────────────────────────────────────────────────
Route::group(['middleware' => 'auth:api'], function () {

    // ── Autenticació i Perfil ─────────────────────────────────
    // Retorna les dades de l'usuari propietari del token actual.
    Route::get('/me', [AuthController::class, 'me']);

    // Invalida el token JWT actual, tancant la sessió.
    Route::post('/logout', [AuthController::class, 'logout']);

    // Permet modificar nom, cognoms, email o contrasenya de l'usuari.
    // Usem PATCH perquè és una actualització parcial (no tots els camps son obligatoris).
    Route::patch('/user/profile', [AuthController::class, 'updateProfile']);

    // Elimina el compte permanentment. Usem DELETE perquè és una operació destructiva.
    Route::delete('/user/account', [AuthController::class, 'deleteAccount']);

    // Reclama la recompensa diària (1 Xuxemon + 10 xuxes). Té control intern per evitar abusos.
    Route::post('/user/daily-reward', [AuthController::class, 'claimDailyReward']);


    // ── Inventari i Xuxemons ──────────────────────────────────
    // Retorna tots els ítems (xuxes i vacunes) de la motxilla de l'usuari autenticat.
    Route::get('/inventory', [InventoryController::class, 'index']);

    // Retorna tots els Xuxemons de la col·lecció de l'usuari autenticat.
    Route::get('/xuxedex', [XuxemonController::class, 'index']);

    // Alimenta un Xuxemon específic. S'usa {pivot_id} (id de user_xuxemons, no de xuxemons)
    // perquè un usuari pot tenir diverses instàncies del mateix Xuxemon.
    Route::post('/xuxemons/{pivot_id}/feed', [XuxemonController::class, 'feed']);

    // Vacuna un Xuxemon malalt. Mateix raonament que /feed per usar pivot_id.
    Route::post('/xuxemons/{pivot_id}/vaccinate', [XuxemonController::class, 'vaccinate']);


    // ── Panell d'Administrador ────────────────────────────────
    // Nota: Idealment aquestes rutes haurien de tenir un middleware 'role:robot'
    // per impedir que jugadors normals les cridin. Això es pot millorar en futures versions.

    // Llista tots els usuaris (id, nom, custom_id) per omplir el selector del panell.
    Route::get('/admin/users', [AdminController::class, 'getUsers']);

    // Dona un ítem (xuxe o vacuna) a un jugador des del panell d'admin.
    Route::post('/admin/give-item', [AdminController::class, 'giveItem']);

    // Dona un Xuxemon aleatori a un jugador des del panell d'admin.
    Route::post('/admin/give-xuxemon', [AdminController::class, 'giveRandomXuxemon']);

    // Llegeix la configuració global de probabilitats de malalties.
    Route::get('/admin/settings', [AdminController::class, 'getSettings']);

    // Desa la nova configuració global de probabilitats.
    Route::post('/admin/settings', [AdminController::class, 'updateSettings']);


    // ── Sistema d'Amistats ────────────────────────────────────
    // Cerca usuaris per custom_id (format Nom#XXXX) per enviar-los una sol·licitud.
    Route::get('/friends/search', [FriendController::class, 'searchUsers']);

    // Envia una sol·licitud d'amistat a un usuari concret.
    Route::post('/friends/request', [FriendController::class, 'sendRequest']);

    // Llista les sol·licituds d'amistat rebudes i pendents d'acceptar.
    Route::get('/friends/requests', [FriendController::class, 'getPendingRequests']);

    // Accepta una sol·licitud d'amistat pendent. Usem POST (podria ser PATCH).
    Route::post('/friends/accept/{id}', [FriendController::class, 'acceptRequest']);

    // Rebutja (i elimina) una sol·licitud d'amistat. Usem DELETE per semàntica RESTful.
    Route::delete('/friends/reject/{id}', [FriendController::class, 'rejectRequest']);

    // Llista tots els amics acceptats de l'usuari autenticat (relació bidireccional).
    Route::get('/friends', [FriendController::class, 'getFriends']);

    // Elimina un amic (esborra la fila de friendships, no l'usuari).
    Route::delete('/friends/{id}', [FriendController::class, 'removeFriend']);


    // ── Xat ──────────────────────────────────────────────────
    // Recupera l'historial de missatges entre l'usuari autenticat i un amic.
    Route::get('/chat/{friendId}', [ChatController::class, 'getMessages']);

    // Envia un nou missatge a un amic.
    Route::post('/chat/{friendId}', [ChatController::class, 'sendMessage']);
});