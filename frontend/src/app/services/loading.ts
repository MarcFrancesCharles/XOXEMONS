import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class LoadingService {
  // Controla si s'ensenya o no (booleà)
  private loadingSubject = new BehaviorSubject<boolean>(false);
  // Controla el text que es mostra (per defecte 'CARREGANT...')
  private messageSubject = new BehaviorSubject<string>('CARREGANT...');
  
  loading$ = this.loadingSubject.asObservable();
  message$ = this.messageSubject.asObservable();

  // Ara li podem passar un text opcional quan cridem al show()
  show(message: string = 'CARREGANT...') {
    this.messageSubject.next(message); // Actualitzem el text
    this.loadingSubject.next(true);    // Mostrem la pantalla negra
  }

  hide() {
    this.loadingSubject.next(false);
  }
}