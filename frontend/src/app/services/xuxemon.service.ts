/**
 * ============================================================
 * FITXER: src/app/services/xuxemon.service.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Servei que centralitza totes les operacions relacionades amb
 *   Xuxemons: càrrega de la col·lecció personal (Xuxedex), alimentació,
 *   vacunació, recompensa diària i configuració global de malalties
 *   (per al panell d'admin). Usa BehaviorSubject per a reactivitat.
 *
 * MAPA DE CONNEXIONS:
 *   → Usat per: Xuxedex component (loadXuxedex, feedXuxemon, vaccinateXuxemon)
 *   → Usat per: Main component (claimDailyReward)
 *   → Usat per: Admin component (getSettings, updateSettings)
 *   → Crida a: GET /api/xuxedex, POST /api/xuxemons/{id}/feed,
 *     POST /api/xuxemons/{id}/vaccinate, POST /api/user/daily-reward,
 *     GET/POST /api/admin/settings (Laravel)
 *   → El token JWT s'afegeix automàticament per l'authInterceptor.
 * ============================================================
 */

import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class XuxemonService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);

  // BehaviorSubject per mantenir l'estat reactiu de la col·lecció.
  // Qualsevol component subscrit a xuxedex$ veurà els canvis
  // automàticament sense haver de tornar a cridar loadXuxedex().
  private xuxedexSubject = new BehaviorSubject<any[]>([]);
  public xuxedex$ = this.xuxedexSubject.asObservable();

  // Carrega la col·lecció de Xuxemons de l'usuari autenticat.
  // tap() actualitza el BehaviorSubject sense modificar l'Observable
  // retornat, permetent que el component es subscrigui si vol fer
  // alguna acció addicional en completar-se.
  loadXuxedex(): Observable<any> {
    return this.http.get<any[]>(`${this.apiUrl}/xuxedex`).pipe(
      tap(xuxemons => this.xuxedexSubject.next(xuxemons))
    );
  }

  // Alimenta un Xuxemon concret amb un ítem de l'inventari.
  // Usem pivotId (user_xuxemons.id) perquè és l'identificador
  // de la INSTÀNCIA del Xuxemon de l'usuari, no del tipus base.
  // Després de l'alimentació, recarreguem la Xuxedex perquè
  // el component vegi el nou food_eaten, disease i possiblement
  // el nou xuxemon_id si ha evolucionat.
  feedXuxemon(pivotId: number, itemId: number): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/xuxemons/${pivotId}/feed`, { item_id: itemId }).pipe(
      tap(() => {
        this.loadXuxedex().subscribe();
      })
    );
  }

  // Aplica una vacuna a un Xuxemon malalt.
  // Igual que feed(), recarreguem la Xuxedex per veure la cura
  // reflectida immediatament a la UI (disease → null).
  vaccinateXuxemon(pivotId: number, itemId: number): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/xuxemons/${pivotId}/vaccinate`, { item_id: itemId }).pipe(
      tap(() => {
        this.loadXuxedex().subscribe();
      })
    );
  }

  // Reclama la recompensa diària (1 Xuxemon + 10 xuxes).
  // No actualitzem cap BehaviorSubject aquí perquè Main no
  // mostra la col·lecció; Xuxedex i Inventory es recarreguen
  // quan l'usuari navega a elles.
  claimDailyReward(): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/user/daily-reward`, {});
  }

  // ── CONFIGURACIÓ GLOBAL (Panell Admin) ────────────────────

  // Llegeix les probabilitats de malalties actuals.
  getSettings(): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/admin/settings`);
  }

  // Desa la nova configuració de probabilitats.
  updateSettings(settings: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/admin/settings`, settings);
  }
}