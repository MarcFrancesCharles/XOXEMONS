<?php

/**
 * ============================================================
 * FITXER: bootstrap/app.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Aquest és el "cor" de la configuració de l'aplicació a 
 *   Laravel 11. Substitueix els antics fitxers Kernel.php. 
 *   Aquí es defineixen les rutes, els middlewares globals i 
 *   la gestió d'excepcions.
 * ============================================================
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    // Configuració de les rutes del sistema
    ->withRouting(
        web: __DIR__.'/../routes/web.php',     // Rutes web (vistes Blade)
        api: __DIR__.'/../routes/api.php',     // Rutes API (JSON, amb JWT)
        commands: __DIR__.'/../routes/console.php', // Comandes Artisan
        health: '/up',                         // Punt de control de salut del servidor
    )
    // Configuració de Middlewares
    ->withMiddleware(function (Middleware $middleware): void {
        // Aquí es poden registrar o modificar middlewares globals.
    })
    // Configuració de Gestió d'Excepcions
    ->withExceptions(function (Exceptions $exceptions): void {
        // Aquí es defineix com ha de respondre l'aplicació davant d'errors específics.
    })->create();
