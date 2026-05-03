<?php

/**
 * ============================================================
 * FITXER: public/index.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest és el punt d'entrada de totes les peticions HTTP 
 *   que arriben al servidor. És el primer fitxer que executa 
 *   el servidor web (Apache/Nginx). La seva funció és 
 *   carregar l'autoloader de Composer, arrencar el framework 
 *   Laravel i lliurar la petició al nucli de l'aplicació.
 * ============================================================
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Marca el temps d'inici de l'execució per a mètriques de rendiment.
define('LARAVEL_START', microtime(true));

// Comprova si l'aplicació està en mode manteniment.
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Registra l'autoloader de Composer per carregar classes automàticament.
require __DIR__.'/../vendor/autoload.php';

// Arrenca Laravel i gestiona la petició.
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Captura la petició actual i la processa a través del framework.
$app->handleRequest(Request::capture());
