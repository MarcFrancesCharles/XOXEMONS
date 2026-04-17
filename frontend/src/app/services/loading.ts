/**
 * ============================================================
 * FITXER: src/app/services/loading.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Servei global d'estat de l'overlay de càrrega (pantalla negra).
 *   Qualsevol component de l'app pot activar o desactivar la
 *   pantalla de transició retro cridant show() o hide().
 *   Usa BehaviorSubject per propagar els canvis reactius al
 *   component Loading, que és l'únic que observa aquest servei.
 *
 * MAPA DE CONNEXIONS:
 *   → Usat per: Login (show/hide durant el login)
 *   → Usat per: Main (show/hide durant el logout)
 *   → Consumit per: Loading component (subscrit a loading$ i message$)
 *   → No fa cap crida HTTP: gestiona únicament estat de UI.
 * ============================================================
 */

import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class LoadingService {
  // BehaviorSubject: emmagatzema l'últim valor i l'emet immediatament
  // als nous subscriptors. Usem BehaviorSubject en lloc d'un Subject
  // simple perquè el component Loading ha de saber l'estat actual
  // en el moment en què es munta al DOM.
  private loadingSubject = new BehaviorSubject<boolean>(false);

  // El text del missatge és configurable per personalitzar el missatge
  // de transició (ex: "CARREGANT..." vs "TANCANT SESSIÓ...").
  private messageSubject = new BehaviorSubject<string>('CARREGANT...');

  // Exposem Observables (no BehaviorSubjects) per garantir que els
  // consumidors no puguin emetre valors directament: l'estat és
  // privat i controlat únicament per show() i hide().
  loading$ = this.loadingSubject.asObservable();
  message$ = this.messageSubject.asObservable();

  // Activa la pantalla negra amb el missatge indicat.
  // El valor per defecte 'CARREGANT...' evita haver de passar sempre
  // el missatge quan s'usa per a càrregues genèriques.
  show(message: string = 'CARREGANT...') {
    this.messageSubject.next(message); // Actualitzem el text primer
    this.loadingSubject.next(true);    // Després activem l'overlay
  }

  // Desactiva la pantalla negra. El missatge es manté fins al proper show(),
  // però com que l'overlay és invisible (opacity: 0), no importa.
  hide() {
    this.loadingSubject.next(false);
  }
}