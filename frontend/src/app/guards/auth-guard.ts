/**
 * ============================================================
 * FITXER: src/app/guards/auth-guard.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Guàrdia de ruta funcional (CanActivateFn) que protegeix totes
 *   les rutes privades de l'aplicació. És la primera línia de
 *   defensa: si no hi ha token al localStorage, l'usuari no
 *   pot accedir a cap pàgina autenticada.
 *
 *   Nota: aquest guard fa una comprovació local (sense crida API),
 *   per tant és instantani. La validació real del token la fa el
 *   backend quan l'interceptor l'envia a cada petició.
 *
 * MAPA DE CONNEXIONS:
 *   → Registrat a: app.routes.ts (canActivate: [authGuard]) a
 *     totes les rutes privades excepte /admin
 *   → Usa: AuthService (services/auth.ts) per verificar token
 *   → Usa: Router per redirigir a /login si no hi ha sessió
 * ============================================================
 */

import { inject } from '@angular/core';
import { Router, CanActivateFn } from '@angular/router';
import { AuthService } from '../services/auth';

export const authGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  // getToken() llegeix el token del localStorage.
  // Comprovació ràpida i síncrona: no fa cap crida HTTP.
  // Si hi ha token (encara que sigui expirat), deixem passar l'usuari;
  // l'interceptor i/o el component gestionaran l'expiració si cal.
  if (authService.getToken()) {
    return true;
  } else {
    // Redirigim a /login i bloquegem la navegació retornant false.
    router.navigate(['/login']);
    return false;
  }
};