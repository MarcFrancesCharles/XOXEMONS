import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const authService = inject(AuthService);
  const token = authService.getToken(); // Agafem el token del localStorage

  // Si l'usuari té un token, clonem la petició i li afegim la "clau" (capçalera)
  if (token) {
    const clonedReq = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
    return next(clonedReq); // Enviem la petició modificada al Laravel
  }

  // Si no hi ha token (com quan fem login o registre), l'enviem normal
  return next(req);
};