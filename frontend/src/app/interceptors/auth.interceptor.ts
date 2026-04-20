/**
 * ============================================================
 * FITXER: src/app/interceptors/auth.interceptor.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Interceptor HTTP funcional (pattern Angular 17+ standalone).
 *   Actua com a "middleware" entre cada petició HTTP de l'app
 *   i el servidor. Gestiona tres responsabilitats de seguretat:
 *   1. Comprovació proactiva d'expiració del token (2h frontend).
 *   2. Injecció automàtica del token JWT a la capçalera Authorization.
 *   3. Gestió reactiva d'errors 401 (token invalidat al servidor).
 *
 * MAPA DE CONNEXIONS:
 *   → Registrat globalment a: app.config.ts (withInterceptors)
 *   → Usa: AuthService (auth.ts) per llegir token i verificar expiració
 *   → Usa: Router per redirigir a /login en cas d'error d'autenticació
 *   → Intercepta: TOTES les peticions HttpClient de l'app
 *   → Afecta: tots els serveis (auth, xuxemon, inventory, etc.)
 * ============================================================
 */

import { HttpInterceptorFn, HttpErrorResponse } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError } from 'rxjs/operators';
import { throwError, EMPTY } from 'rxjs';
import { AuthService } from '../services/auth';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const authService = inject(AuthService);
  const router = inject(Router);
  const token = authService.getToken();

  // ── COMPROVACIÓ PROACTIVA D'EXPIRACIÓ ─────────────────────
  // Verifiquem el token ABANS d'enviar la petició per evitar
  // que el servidor respongui amb un 401 innecessari.
  // isTokenExpired() compara el timestamp del localStorage amb les 2h.
  // Retornem EMPTY (Observable que completa sense emetre res) per
  // cancel·lar la petició sense llançar un error visible a la consola.
  if (token && authService.isTokenExpired()) {
    console.warn('AuthInterceptor: El token ha expirat proactivament. Redirigint...');
    authService.removeToken();
    router.navigate(['/login']);
    return EMPTY;
  }

  // ── INJECCIÓ AUTOMÀTICA DEL TOKEN ─────────────────────────
  if (token) {
    const clonedReq = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });

    return next(clonedReq).pipe(
      catchError((error: HttpErrorResponse) => {
        // ── GESTIÓ REACTIVA D'ERRORS 401 ─────────────────────
        if (error.status === 401) {
          console.error('AuthInterceptor: El servidor ha retornat 401 (Unauthorized). Expulsant usuari...');
          authService.removeToken();
          router.navigate(['/login']);
        }
        return throwError(() => error);
      })
    );
  }

  // Si no hi ha token (rutes públiques: login, register),
  // passem la petició tal qual sense modificació.
  return next(req);
};