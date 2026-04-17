/**
 * ============================================================
 * FITXER: src/app/services/inventory.service.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Servei que gestiona l'estat reactiu de la motxilla de l'usuari.
 *   Carrega els ítems des del backend i els publica via BehaviorSubject,
 *   permetent que tant el component Inventory (visual de la motxilla)
 *   com el component Xuxedex (selector de xuxes/vacunes al modal)
 *   vegin sempre la mateixa font de veritat actualitzada.
 *
 * MAPA DE CONNEXIONS:
 *   → Usat per: Inventory component (per mostrar la graella d'ítems)
 *   → Usat per: Xuxedex component (per mostrar ítems als modals de
 *     feed i vaccinate, i per recarregar després de consumir-ne)
 *   → Crida a: GET /api/inventory (Laravel InventoryController)
 *   → Token JWT afegit automàticament per l'authInterceptor.
 * ============================================================
 */

import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class InventoryService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);

  // BehaviorSubject inicialitzat amb array buit per evitar que els
  // components que s'hi subscriuen rebin null i hagin de gestionar
  // el cas nul: amb [] l'app funciona correctament (llista buida).
  private inventorySubject = new BehaviorSubject<any[]>([]);
  public inventory$ = this.inventorySubject.asObservable();

  // Carrega l'inventari del backend i actualitza el Subject.
  // tap() permet encadenar la lògica d'estat sense trencar el flux
  // Observable: el component que crida loadInventory() pot seguir
  // fent subscribe() per saber quan ha acabat la càrrega.
  loadInventory(): Observable<any> {
    return this.http.get<any[]>(`${this.apiUrl}/inventory`).pipe(
      tap(items => this.inventorySubject.next(items))
    );
  }
}