/**
 * ============================================================
 * FITXER: src/app/components/inventory/inventory.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Component encarregat de visualitzar i gestionar la "Motxilla" 
 * (Inventari) del jugador. A diferència d'un simple llistat, 
 * aquest component implementa la lògica de negoci típica d'un 
 * videojoc clàssic: una graella de capacitat fixa (20 espais) 
 * amb regles d'apilament (stacking) limitat per a cada tipus d'objecte.
 *
 * MAPA DE CONNEXIONS:
 * → Importa: InventoryService per forçar la càrrega des del backend 
 * i subscriure's de forma reactiva als canvis (inventory$).
 * → Dades d'entrada: Rep el camp `pivot.quantity` (relació N:M 
 * entre User i Item a Laravel).
 * → Template: inventory.html renderitzarà l'array `slots` pintant
 * cadascun dels 20 espais de la graella.
 * ============================================================
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { InventoryService } from '../../services/inventory.service';

@Component({
  selector: 'app-inventory',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './inventory.html',
  styleUrl: './inventory.css'
})
export class Inventory implements OnInit {
  // ── INJECCIÓ DE DEPENDÈNCIES ──────────────────────────────
  private inventoryService = inject(InventoryService);
  
  // ── ESTAT DEL COMPONENT ───────────────────────────────────
  // Creem una estructura de dades fixa de 20 posicions inicialitzada a null.
  // PER QUÈ: Mantenir un array fix de 20 posicions garanteix que la UI 
  // (la graella HTML) sempre dibuixi exactament 20 caselles, fins i tot 
  // si estan buides, mantenint la coherència visual de "motxilla" independentment 
  // del nombre d'objectes que tingui l'usuari.
  slots: any[] = new Array(20).fill(null);

  // ── CICLE DE VIDA ─────────────────────────────────────────
  ngOnInit() {
    // 1. Demanem les dades al backend
    // Aquesta crida fa que el servei faci el GET a l'API i actualitzi el seu BehaviorSubject.
    this.inventoryService.loadInventory().subscribe();

    // 2. Ens subscrivim al BehaviorSubject (reactivitat pura)
    // PER QUÈ: En lloc de processar les dades directament del subscribe() anterior,
    // escoltem el flux global (inventory$). Així, si qualsevol altre component 
    // (ex: una botiga o una batalla) altera l'inventari, aquest component s'actualitzarà 
    // automàticament sense haver de fer res més.
    this.inventoryService.inventory$.subscribe(items => {
      this.calculateSlots(items);
    });
  }

  // ── LÒGICA DE NEGOCI (ALGORISME D'APILAMENT) ──────────────
  // Converteix una llista plana d'ítems i quantitats en una distribució física per slots.
  calculateSlots(items: any[]) {
    // Netegem la motxilla a cada canvi d'estat per recalcular-ho tot des de zero
    // i evitar que quedin objectes "fantasma" de l'estat anterior.
    this.slots = new Array(20).fill(null); 
    let currentSlot = 0;

    for (let item of items) {
      // La quantitat real s'extreu de la taula pivot de la base de dades
      let qty = item.pivot.quantity; 

      if (item.is_stackable) {
        // ── LÒGICA PER OBJECTES APILABLES (Ex: Pocions, Vacunes) ──
        // Es divideixen en grups d'un màxim de 5 per cada casella.
        // El bucle while s'assegura de no sobrepassar el límit de 20 caselles totals
        // perquè l'inventari no "desbordi" la UI.
        while (qty > 0 && currentSlot < 20) {
          // Agafem un bloc de 5, o el que quedi si és menor a 5.
          let chunk = qty > 5 ? 5 : qty;
          
          // Clonem l'objecte (...item) per no mutar la referència original i hi 
          // injectem la quantitat específica que mostrarà aquest slot (displayQuantity).
          this.slots[currentSlot] = { ...item, displayQuantity: chunk };
          
          // Restem els processats i avancem a la següent casella.
          qty -= chunk;
          currentSlot++;
        }
      } else {
        // ── LÒGICA PER OBJECTES NO APILABLES (Ex: Armes o Items Clau) ──
        // Cada instància ocupa obligatòriament 1 slot sencer.
        while (qty > 0 && currentSlot < 20) {
          this.slots[currentSlot] = { ...item, displayQuantity: 1 };
          qty--;
          currentSlot++;
        }
      }
    }
  }
}