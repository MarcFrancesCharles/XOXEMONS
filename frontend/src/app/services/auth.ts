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
import { Observable } from 'rxjs';

// providedIn: 'root' crea una única instància (singleton) del servei
// disponible a tota l'app sense necessitat de declarar-lo a cap mòdul.
@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);

  // ── REGISTRE ──────────────────────────────────────────────
  // Envia les dades del formulari de registre al backend Laravel.
  // El backend valida, genera el custom_id i retorna el nou usuari.
  register(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, userData);
  }

  // ── LOGIN ─────────────────────────────────────────────────
  // Envia custom_id i password. El backend retorna el token JWT
  // si les credencials són correctes.
  login(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, credentials);
  }

  // ── GESTIÓ DEL TOKEN ──────────────────────────────────────

  // Desa el token i una marca temporal al localStorage.
  // La marca temporal (timestamp en ms) permet calcular l'expiració
  // al costat del client sense consultar el backend en cada petició.
  setToken(token: string): void {
    localStorage.setItem('auth_token', token);
    localStorage.setItem('auth_timestamp', Date.now().toString());
  }

  // Llegeix el token del localStorage. Retorna null si no existeix
  // (usuari no autenticat o token eliminat manualment).
  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  // Comprovació d'expiració del costat del client (2 hores).
  // Comparar timestamps és O(1) i evita una crida HTTP a cada
  // comprovació de l'interceptor. El backend sempre valida de nou,
  // per tant és una capa d'optimització, no de seguretat final.
  isTokenExpired(): boolean {
    const timestamp = localStorage.getItem('auth_timestamp');
    if (!timestamp) return true;

    const limit = 2 * 60 * 60 * 1000; // 2 hores en mil·lisegons
    const now = Date.now();
    return (now - parseInt(timestamp, 10)) > limit;
  }

  // ── LOGOUT ────────────────────────────────────────────────
  // Crida al backend perquè invalidi el token a la blacklist JWT.
  // Després d'això, el token no pot ser reutilitzat ni si no ha
  // caducat formalment.
  logout(): Observable<any> {
    return this.http.post(`${this.apiUrl}/logout`, {});
  }

  // Elimina token i timestamp del localStorage.
  // S'usa després del logout o quan es detecta un 401.
  removeToken(): void {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_timestamp');
  }

  // ── PERFIL ────────────────────────────────────────────────
  // Crida autenticada: l'interceptor afegeix el token automàticament.
  // Retorna les dades completes de l'usuari propietari del token.
  getProfile(): Observable<any> {
    return this.http.get(`${this.apiUrl}/me`);
  }

  // Actualització parcial del perfil (PATCH: no cal enviar tots els camps).
  updateProfile(userData: any): Observable<any> {
    return this.http.patch(`${this.apiUrl}/user/profile`, userData);
  }

  // Elimina permanentment el compte de l'usuari autenticat.
  deleteAccount(): Observable<any> {
    return this.http.delete(`${this.apiUrl}/user/account`);
  }
}