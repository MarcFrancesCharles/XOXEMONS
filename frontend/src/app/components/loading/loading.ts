/**
 * ============================================================
 * FITXER: src/app/components/loading/loading.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Aquest és el component visual (Overlay) associat a l'estat global
 * de càrrega. La seva funció és bloquejar la interfície d'usuari (UI)
 * mostrant una pantalla de transició (ex: "CARREGANT...") mentre es
 * resolen operacions asíncrones crítiques, com ara iniciar o tancar sessió.
 *
 * MAPA DE CONNEXIONS:
 * → Pare estructural: app.html (s'instancia una única vegada a l'arrel).
 * → Serveis: Injecta LoadingService per observar els canvis d'estat.
 * → Template: loading.html llegeix directament de l'estat reactiu d'aquest servei.
 * ============================================================
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LoadingService } from '../../services/loading';

@Component({
  selector: 'app-loading',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './loading.html',
  styleUrl: './loading.css'
})
export class Loading {
  // ── INJECCIÓ I EXPOSICIÓ DE L'ESTAT ────────────────────────
  // PER QUÈ ÉS 'public'?: Normalment els serveis s'injecten com a 'private' per
  // encapsulació. Però aquí el fem 'public' expressament. Això permet que el 
  // fitxer HTML (loading.html) pugui accedir directament a 'loadingService.loading$' 
  // i usar el pipe '| async'.
  //
  // Aquest patró d'Arquitectura Reactiva ens estalvia haver de fer un .subscribe()
  // dins del TypeScript, evitant així haver de gestionar el cicle de vida
  // (ngOnDestroy) i blindant el component contra fugues de memòria (Memory Leaks).
  public loadingService = inject(LoadingService);
}