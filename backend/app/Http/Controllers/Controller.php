<?php

namespace App\Http\Controllers;

/**
 * ============================================================
 * FITXER: app/Http/Controllers/Controller.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Classe base abstracta de la qual hereten tots els controladors 
 *   del projecte XOXEMONS. Proveeix l'estructura estàndard de 
 *   Laravel 11/12 per a la gestió de peticions i respostes.
 *
 * ARQUITECTURA:
 *   Els controladors d'aquesta aplicació segueixen un patró de 
 *   responsabilitat única, separant la gestió d'usuaris (Auth), 
 *   criatures (Xuxemon), interaccions socials (Friend/Chat) 
 *   i administració (Admin).
 * ============================================================
 */

/**
 * Classe abstracta Controller.
 * 
 * Aquesta classe serveix com a nexe d'unió per a tota la lògica de control 
 * del backend. Tot i que actualment és minimalista, és el punt d'extensió 
 * ideal per afegir validacions globals, mètodes de resposta JSON 
 * estandarditzats o loggers compartits per tots els controladors.
 */
abstract class Controller
{
    /**
     * Tots els controladors de l'aplicació es troben organitzats a la carpeta:
     * backend/app/Http/Controllers/
     * 
     * Cada controlador interactua amb el seu model corresponent situat a:
     * backend/app/Models/
     */
}