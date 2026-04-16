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

  // 1. Gestió expiració token (2h) - Comprovació proactiva
  if (token && authService.isTokenExpired()) {
    authService.removeToken();
    router.navigate(['/login']);
    return EMPTY; // Cancelem la petició si el token ha expirat
  }

  // 2. Afegir token automàticament
  if (token) {
    const clonedReq = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });

    return next(clonedReq).pipe(
      catchError((error: HttpErrorResponse) => {
        // 3. Captura error 401 → logout automàtic
        if (error.status === 401) {
          authService.removeToken();
          router.navigate(['/login']);
        }
        return throwError(() => error);
      })
    );
  }

  return next(req);
};