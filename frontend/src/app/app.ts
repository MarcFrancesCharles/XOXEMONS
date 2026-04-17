/**
 * ============================================================
 * FITXER: src/app/app.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Component arrel de l'aplicació Angular. És la "closca" que
 *   conté tota l'app. El seu template (app.html) té dues parts:
 *   el <router-outlet> (on Angular injecta el component de la
 *   ruta activa) i <app-loading> (l'overlay de càrrega global
 *   que pot activar qualsevol component des de LoadingService).
 *
 * MAPA DE CONNEXIONS:
 *   → Consumit per: src/main.ts (component arrel del bootstrap)
 *   → Template: app.html
 *   → Importa: RouterOutlet (per renderitzar el component de la
 *     ruta activa)
 *   → Importa: Loading (component overlay de pantalla negra global)
 *   → No té lògica de negoci: és un contenidor estructural pur.
 * ============================================================
 */

import { Component, signal } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { Loading } from './components/loading/loading';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, Loading],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App {
  // Signal de només lectura. No s'usa a l'HTML actual però és
  // la forma moderna d'Angular 17+ per gestionar estat reactiu
  // sense necessitat de ChangeDetectionStrategy.OnPush manual.
  protected readonly title = signal('frontend');
}