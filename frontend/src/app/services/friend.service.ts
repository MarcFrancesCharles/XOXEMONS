/**
 * ============================================================
 * FITXER: src/app/services/friend.service.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Servei central del component social del joc. Actua com a "Font 
 * Única de Veritat" (Single Source of Truth) per a l'estat de les 
 * relacions de l'usuari. Gestiona tot el cicle de vida d'una amistat:
 * cerca d'usuaris, enviament/recepció de sol·licituds, i llistat 
 * d'amics confirmats. 
 *
 * MAPA DE CONNEXIONS:
 * → Usat per: Friends (component) per orquestrar la UI social.
 * → Usat per: Chat i Battle (components) indirectament, ja que 
 * depenen d'aquest llistat d'amics actius.
 * → Interceptors: authInterceptor afegeix el JWT a totes les peticions.
 * → API Endpoints: CRUD a /api/friends i rutes associades (Laravel).
 * ============================================================
 */

import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';

@Injectable({ providedIn: 'root' }) // Disponible de forma global sense importar en mòduls
export class FriendService {
  private apiUrl = 'http://localhost:8000/api/friends';
  private http = inject(HttpClient);

  // ── GESTIÓ D'ESTAT REACTIU (STATE MANAGEMENT) ─────────────
  // PER QUÈ USEM BehaviorSubject?: Emmagatzemen l'últim valor (en aquest cas,
  // la llista d'amics o sol·licituds) i l'emeten instantàniament a qualsevol
  // component nou que s'hi subscrigui. Això evita fer peticions GET innecessàries
  // si l'usuari navega de la vista d'Amics a la vista de Xat i torna a Amics.
  private friendsSubject = new BehaviorSubject<any[]>([]);
  public friends$ = this.friendsSubject.asObservable(); // L'exposem com a Observable només-lectura per seguretat

  private requestsSubject = new BehaviorSubject<any[]>([]);
  public requests$ = this.requestsSubject.asObservable();

  // ── ACCIONS I COMUNICACIÓ AMB L'API ───────────────────────

  // 1. Cercar usuaris (Nivell 4)
  // És una crida "stateless" (no modifica el BehaviorSubject). 
  // Retorna el flux directament al component perquè ell el filtri (el famós switchMap que vam veure).
  searchUsers(query: string): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/search?q=${query}`);
  }

  // 2. Enviar sol·licitud d'amistat
  sendRequest(friendId: number): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/request`, { friend_id: friendId });
  }

  // 3. Carregar sol·licituds pendents
  // L'operador `tap` (efecte secundari) ens permet "espiar" la resposta de l'API
  // i guardar les dades al nostre BehaviorSubject abans d'entregar-les al component.
  loadPendingRequests(): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/requests`).pipe(
      tap(requests => this.requestsSubject.next(requests))
    );
  }

  // 4. Acceptar sol·licitud (Màgia Reactiva)
  acceptRequest(id: number): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/accept/${id}`, {}).pipe(
      // PER QUÈ 'tap' AQUÍ?: Quan acceptem un amic, l'estat de l'aplicació canvia dràsticament:
      // A) Desapareix una sol·licitud pendent.
      // B) Apareix un amic nou.
      // Amb `tap`, forcem al servei a re-descarregar aquestes dues llistes automàticament.
      // Com que els components estan subscrits a friends$ i requests$, la UI s'actualitzarà sola
      // sense que el programador hagi de fer res més a la classe del component!
      tap(() => {
        this.loadPendingRequests().subscribe();
        this.loadFriends().subscribe();
      })
    );
  }

  // 5. Rebutjar sol·licitud
  rejectRequest(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/reject/${id}`).pipe(
      // Forcem l'actualització de la llista de sol·licituds per fer desaparèixer la que acabem de rebutjar.
      tap(() => this.loadPendingRequests().subscribe())
    );
  }

  // 6. Carregar la llista d'amics confirmats
  loadFriends(): Observable<any[]> {
    return this.http.get<any[]>(this.apiUrl).pipe(
      tap(friends => this.friendsSubject.next(friends))
    );
  }

  // 7. Eliminar amic
  removeFriend(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/${id}`).pipe(
      // Al eliminar l'amic, disparem la recàrrega de la llista per esborrar-lo del DOM automàticament.
      tap(() => this.loadFriends().subscribe())
    );
  }
}