/**
 * ============================================================
 * FITXER: src/app/services/auth.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Servei central d'autenticació. Gestiona tot el cicle de vida
 *   de la sessió de l'usuari: registre, login, logout, perfil,
 *   actualització de dades i eliminació de compte. A més, conté
 *   la lògica de persistència del token JWT al localStorage i
 *   la detecció d'expiració en el costat del client (2h).
 *
 * MAPA DE CONNEXIONS:
 *   → Usat per: authInterceptor (per llegir/verificar token)
 *   → Usat per: authGuard (per verificar existència de token)
 *   → Usat per: adminGuard (per obtenir el perfil i verificar rol)
 *   → Usat per: Login, Register, Main, Profile (components)
 *   → Crida a: GET/POST /api/me, /api/login, /api/register,
 *     /api/logout, /api/user/profile, /api/user/account (Laravel)
 *   → El token s'emmagatzema al localStorage amb la seva marca
 *     temporal per detectar expiració sense crida al backend.
 * ============================================================
 */

import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);

  // ── ESTAT REACTIU ─────────────────────────────────────────
  // Usem un BehaviorSubject per emetre l'estat d'autenticació 
  // a tota l'app en temps real.
  private authStatus = new BehaviorSubject<boolean>(!!this.getToken());
  authStatus$ = this.authStatus.asObservable();

  // ── REGISTRE ──────────────────────────────────────────────
  register(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, userData);
  }

  // ── LOGIN ─────────────────────────────────────────────────
  login(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, credentials);
  }

  // ── GESTIÓ DEL TOKEN ──────────────────────────────────────

  setToken(token: string): void {
    // Validació crítica: Evitem guardar 'undefined' o 'null' com a strings
    // que és el que passa si fem localStorage.setItem('k', undefined).
    if (!token || token === 'undefined' || token === 'null') {
      console.warn('AuthService: S\'ha intentat guardar un token buit o invàlid.');
      return;
    }
    localStorage.setItem('auth_token', token);
    localStorage.setItem('auth_timestamp', Date.now().toString());
    
    // Notifiquem als subscriptors que l'usuari ara està autenticat.
    this.authStatus.next(true);
  }

  getToken(): string | null {
    const token = localStorage.getItem('auth_token');
    // Si per algun error previ s'ha guardat "undefined" com a string, el tractem com a null.
    if (token === 'undefined' || token === 'null') return null;
    return token;
  }

  isTokenExpired(): boolean {
    const timestamp = localStorage.getItem('auth_timestamp');
    if (!timestamp) return true;

    const limit = 2 * 60 * 60 * 1000; // 2 hores
    const now = Date.now();
    return (now - parseInt(timestamp, 10)) > limit;
  }

  // ── LOGOUT ────────────────────────────────────────────────
  logout(): Observable<any> {
    return this.http.post(`${this.apiUrl}/logout`, {});
  }

  removeToken(): void {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_timestamp');
    // Notifiquem als subscriptors que l'usuari ja no està autenticat.
    this.authStatus.next(false);
  }

  // ── PERFIL ────────────────────────────────────────────────
  getProfile(): Observable<any> {
    return this.http.get(`${this.apiUrl}/me`);
  }

  updateProfile(userData: any): Observable<any> {
    return this.http.patch(`${this.apiUrl}/user/profile`, userData);
  }

  deleteAccount(): Observable<any> {
    return this.http.delete(`${this.apiUrl}/user/account`);
  }
}