/**
 * ============================================================
 * FITXER: src/app/services/chat.service.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Servei que gestiona el xat privat entre jugadors. Implementa
 *   un model de "polling" (peticions periòdiques) per simular
 *   missatgeria en temps quasi-real sense WebSockets. Manté
 *   l'historial de missatges en un BehaviorSubject reactiu.
 *
 * MAPA DE CONNEXIONS:
 *   → Usat per: Chat component (loadMessages, sendMessage, messages$)
 *   → Crida a: GET /api/chat/{friendId} (historial de missatges)
 *   → Crida a: POST /api/chat/{friendId} (enviar missatge nou)
 *   → El Chat component fa polling cada 2s cridant loadMessages()
 *     repetidament per simular temps real.
 * ============================================================
 */

import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class ChatService {
  private apiUrl = 'http://localhost:8000/api/chat';
  private http = inject(HttpClient);

  // BehaviorSubject per mantenir la llista de missatges actualitzada.
  // Cada vegada que loadMessages() rep noves dades, el Subject
  // emet la nova llista i el component actualitza la UI automàticament.
  private messagesSubject = new BehaviorSubject<any[]>([]);
  public messages$ = this.messagesSubject.asObservable();

  // Carrega l'historial complet de missatges amb un amic específic.
  // El backend retorna els missatges ordenats per data (asc),
  // i tap() els desa al Subject per actualitzar la UI.
  loadMessages(friendId: number): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/${friendId}`).pipe(
      tap(messages => this.messagesSubject.next(messages))
    );
  }

  // Envia un missatge nou i l'afegeix optimísticament al Subject.
  // En lloc d'esperar que el polling detecti el nou missatge (2s de
  // retard), l'afegim immediatament a la llista local des de la
  // resposta del servidor. Això fa que la UI sembli instantània.
  sendMessage(friendId: number, content: string): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/${friendId}`, { content }).pipe(
      tap((res) => {
        // Obtenim l'estat actual del Subject, hi afegim el nou missatge
        // amb spread operator (immutabilitat) i el publiquem.
        // Usem spread per crear un nou array: Angular detecta el canvi
        // de referència i actualitza la llista visualment.
        const currentMessages = this.messagesSubject.getValue();
        this.messagesSubject.next([...currentMessages, res.data]);
      })
    );
  }
}