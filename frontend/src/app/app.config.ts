/**
 * ============================================================
 * FITXER: src/app/app.config.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Arxiu de configuració global de l'aplicació Angular (versió
 * standalone). Actua com a registre central per als proveïdors
 * de serveis globals, com ara el sistema de rutes i el client HTTP.
 * És l'equivalent modern de l'antic AppModule, simplificant la 
 * injecció de dependències a nivell global.
 *
 * MAPA DE CONNEXIONS:
 * → Consumit per: src/main.ts (s'injecta al bootstrapApplication)
 * → Importa: routes (src/app/app.routes.ts) per definir l'arbre de navegació.
 * → Importa: authInterceptor (src/app/interceptors/auth.interceptor.ts)
 * per protegir totes les peticions que surten cap al backend.
 * ============================================================
 */

import { ApplicationConfig } from '@angular/core';
import { provideRouter } from '@angular/router';
import { routes } from './app.routes';

// Importem eines HTTP i el nostre interceptor
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { authInterceptor } from './interceptors/auth.interceptor';

export const appConfig: ApplicationConfig = {
  // ── REGISTRE DE PROVEÏDORS GLOBALS ────────────────────────
  // Aquests serveis estaran disponibles per a tota l'aplicació des de l'inici.
  providers: [
    // Activa el sistema de navegació injectant l'array de rutes definit a app.routes.ts.
    // Això permet l'ús de router-outlet i RouterLink als components.
    provideRouter(routes),
    
    // Activa el client HTTP global perquè els serveis puguin fer peticions API.
    // withInterceptors() afegeix l'authInterceptor com a middleware global.
    // D'aquesta manera, evitem haver de configurar manualment el token JWT 
    // a cada petició individual que fa qualsevol servei de l'aplicació.
    provideHttpClient(withInterceptors([authInterceptor])) 
  ]
};