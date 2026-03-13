import { inject } from '@angular/core';
import { Router, CanActivateFn } from '@angular/router';
import { AuthService } from '../services/auth';
import { map, catchError, of } from 'rxjs';

export const adminGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  // Comprovem el perfil al backend per veure si és 'robot'
  return authService.getProfile().pipe(
    map(user => {
      if (user && user.role === 'robot') {
        return true; // És admin, el deixem passar
      } else {
        router.navigate(['/main']); // És jugador normal, cap al main!
        return false;
      }
    }),
    catchError(() => {
      router.navigate(['/login']);
      return of(false);
    })
  );
};