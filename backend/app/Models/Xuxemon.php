<?php

/**
 * ============================================================
 * FITXER: app/Models/Xuxemon.php
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Representa el catàleg mestre de les criatures (Xuxemons). 
 *   Aquest model no emmagatzema les instàncies individuals dels 
 *   jugadors, sinó les definicions base de cada espècie: nom, 
 *   tipus elemental, mida i imatge.
 *
 * ESTRUCTURA DE DADES:
 *   - Tipus: Aigua, Terra, Aire (determina les branques d'evolució).
 *   - Mides: Petit, Mitja, Gran (determina l'estat de creixement).
 *
 * MAPA DE CONNEXIONS:
 *   → Relacionat amb User via la taula pivot user_xuxemons.
 *   → Usat en la lògica d'evolució per trobar l'espècie següent.
 * ============================================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Xuxemon extends Model
{
    /**
     * El catàleg de Xuxemons és gestionat exclusivament per l'administrador
     * o mitjançant seeders. Per aquest motiu, el model actua principalment 
     * com una font de lectura per al joc.
     * 
     * Propietats de l'espècie:
     * - name: Nom identificatiu de la criatura.
     * - type: Element al qual pertany (influeix en l'evolució).
     * - size: Estadi de creixement actual de la definició.
     * - image: Ruta a l'asset visual que es mostra a Angular.
     */
}