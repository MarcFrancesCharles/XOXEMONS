/**
 * ============================================================
 * FITXER: src/main.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Punt d'entrada absolut de l'aplicació Angular. És el primer
 *   fitxer que executa el navegador (via l'script bundle generat
 *   per la CLI d'Angular). La seva única responsabilitat és
 *   arrencar ("bootstrap") el component arrel App amb la
 *   configuració global definida a appConfig.
 *
 * MAPA DE CONNEXIONS:
 *   → Importa: App (component arrel, app.ts)
 *   → Importa: appConfig (configuració global, app.config.ts)
 *   → No rep res de cap altre fitxer: és el cim de la jerarquia.
 *   → Tota l'aplicació (rutes, serveis, interceptors) s'activa
 *     des d'aquí en cascada.
 * ============================================================
 */

import { bootstrapApplication } from '@angular/platform-browser';
import { appConfig } from './app/app.config';
import { App } from './app/app';

// bootstrapApplication és la funció moderna (Angular 17+) per
// arrencar aplicacions standalone sense AppModule.
// Li passem el component arrel i la configuració global (rutes,
// interceptors HTTP, etc.). Si falla, ho registrem a la consola.
bootstrapApplication(App, appConfig)
  .catch((err) => console.error(err));