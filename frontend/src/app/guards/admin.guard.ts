/**
 * ============================================================
 * FITXER: src/app/guards/admin.guard.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Guàrdia de ruta funcional (CanActivateFn) de segon nivell
 *   que protegeix exclusivament la ruta /admin. S'executa DESPRÉS
 *   d'authGuard (que ja ha verificat que hi ha sessió activa).
 *   Fa una crida al backend per verificar el rol de l'usuari,
 *   garantint que un jugador no pugui accedir al panell d'admin
 *   manipulant el localStorage.
 *
 * MAPA DE CONNEXIONS:
 *   → Registrat a: app.routes.ts (canActivate: [authGuard, adminGuard])
 *     únicament a la ruta /admin
 *   → Usa: AuthService → GET /api/me (crida al backend)
 *   → Usa: Router per redirigir a /main (jugadors normals) o
 *     a /login (si hi ha error d'autenticació)
 *   → Depèn de: authGuard (s'executa primer, garantint que hi
 *     ha un token vàlid per fer la crida)
 * ============================================================
 */

import { inject } from '@angular/core';
import { Router, CanActivateFn } from '@angular/router';
import { AuthService } from '../services/auth';
import { map, catchError, of } from 'rxjs';

export const adminGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  // Fem una crida real al backend per verificar el rol.
  // Confiar únicament en dades del localStorage seria un risc de
  // seguretat: un usuari podria manipular el seu role localment.
  // La font de veritat ha de ser sempre el servidor.
  return authService.getProfile().pipe(
    map(user => {
      if (user && user.role === 'robot') {
        // És admin: deixem passar i el panell es carrega.
        return true;
      } else {
        // És jugador normal: el redirigim al joc principal.
        // No mostrem un error 403 per no donar informació sobre
        // l'existència de la ruta /admin.
        router.navigate(['/main']);
        return false;
      }
    }),
    catchError(() => {
      // Si la crida falla (token expirat, servidor caigut...),
      // redirigim al login perquè l'usuari s'autentiqui de nou.
      router.navigate(['/login']);
      return of(false);
    })
  );
};