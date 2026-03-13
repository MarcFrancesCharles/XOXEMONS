import { ApplicationConfig } from '@angular/core';
import { provideRouter } from '@angular/router';
import { routes } from './app.routes';

// Importem eines HTTP i el nostre interceptor
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { authInterceptor } from './interceptors/auth.interceptor';

export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(routes),
    // Aquí li diem a Angular que totes les peticions HTTP passin per l'interceptor
    provideHttpClient(withInterceptors([authInterceptor])) 
  ]
};