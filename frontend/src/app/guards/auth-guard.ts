// Protegim les rutes que necessiten estar loguejat

import { inject } from '@angular/core';
import { Router, CanActivateFn } from '@angular/router';
import { AuthService } from '../services/auth';

// Fixa't que posa "export const authGuard"
export const authGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (authService.getToken()) {
    return true;
  } else {
    router.navigate(['/login']);
    return false;
  }
};