/**
 * ============================================================
 * FITXER: src/app/components/friends/friends.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Component social o "Hub d'Amistats" de l'aplicació. Permet a 
 * l'usuari gestionar la seva xarxa: llistar els amics actuals, 
 * veure i respondre a les sol·licituds pendents, i buscar nous 
 * usuaris de forma reactiva i en temps real.
 *
 * MAPA DE CONNEXIONS:
 * → Importa: FriendService per executar accions i consumir els
 * fluxos reactius (requests$ i friends$).
 * → Moduls: ReactiveFormsModule. Utilitza FormControl per 
 * desacoblar l'estat de l'input de cerca de l'HTML i permetre
 * l'ús del patró "Observable Pipeline" (RxJS).
 * → Template: friends.html i friends.css
 * ============================================================
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ReactiveFormsModule, FormControl } from '@angular/forms';
import { FriendService } from '../../services/friend.service';

// Importem els operadors d'RxJS essencials per optimitzar les crides HTTP
import { debounceTime, distinctUntilChanged, filter, switchMap } from 'rxjs/operators';
import { of } from 'rxjs';

@Component({
  selector: 'app-friends',
  standalone: true,
  imports: [CommonModule, RouterModule, ReactiveFormsModule],
  templateUrl: './friends.html',
  styleUrl: './friends.css'
})
export class Friends implements OnInit {
  // ── INJECCIÓ DE DEPENDÈNCIES ──────────────────────────────
  private friendService = inject(FriendService);

  // ── ESTAT DEL COMPONENT ───────────────────────────────────
  pendingRequests: any[] = [];
  friendsList: any[] = [];
  
  // Instanciem un FormControl. A diferència d'[(ngModel)], això ens dóna
  // un Observable directe (valueChanges) sobre el qual podem aplicar 
  // transformacions (pipes) de rendiment abans de fer la cerca al backend.
  searchControl = new FormControl('');
  searchResults: any[] = [];

  // ── CICLE DE VIDA ─────────────────────────────────────────
  ngOnInit() {
    // 1. Demanem les dades inicials
    // Disparem la càrrega inicial; el servei farà els GETs pertinents.
    this.friendService.loadFriends().subscribe();
    this.friendService.loadPendingRequests().subscribe();

    // 2. Subscripció als BehaviorSubjects globals
    // PER QUÈ: A l'escoltar l'estat global en lloc de la resposta HTTP directa, 
    // ens garantim que quan aquest component mateix accepti/elimini un amic, 
    // la llista de la UI s'actualitzarà automàticament i de forma síncrona.
    this.friendService.requests$.subscribe(reqs => this.pendingRequests = reqs);
    this.friendService.friends$.subscribe(friends => this.friendsList = friends);

    // 3. Lògica del Cercador Reactiu (Pipeline d'alt rendiment)
    // PER QUÈ AQUESTA ESTRUCTURA?: Prevenir sobrecàrrega del servidor i Race Conditions.
    this.searchControl.valueChanges.pipe(
      // debounceTime: Evita fer un GET per cada lletra. Només farà la crida
      // si l'usuari ha deixat d'escriure durant 300 mil·lisegons.
      debounceTime(300), 
      
      // distinctUntilChanged: Si l'usuari escriu 'A', esborra ràpidament i torna 
      // a escriure 'A' dins de la finestra de 300ms, no fem crides duplicades.
      distinctUntilChanged(), 
      
      // filter: Evitem buscar consultes massa curtes que retornarien gairebé
      // tota la base de dades. Com a regla de disseny, demanem mínim 3 lletres.
      filter(value => {
        if (value && value.length >= 3) {
          return true;
        } else {
          // Si esborren el text, netegem manualment l'array de resultats de la UI
          // per donar una resposta visual immediata i interrompem el pipeline.
          this.searchResults = []; 
          return false;
        }
      }),
      
      // switchMap: LA MÀGIA. Si l'usuari busca "Marc", la crida triga 1 segon, 
      // i abans de rebre-la l'usuari canvia a "Marcel", switchMap CANCEL·LA 
      // la petició HTTP HTTP de "Marc". Així evitem el temut "Race Condition" on els
      // resultats vells podrien sobreescriure els nous per latència de xarxa.
      switchMap(value => this.friendService.searchUsers(value!)) 
    ).subscribe({
      next: (results) => this.searchResults = results,
      error: (err) => console.error('Error a la cerca', err)
    });
  }

  // ── LÒGICA SOCIAL (INTERACCIONS) ──────────────────────────

  // Enviar petició d'amistat
  sendRequest(userId: number) {
    this.friendService.sendRequest(userId).subscribe({
      next: (res) => {
        alert('✅ ' + res.message);
        // Després d'enviar la sol·licitud, buidem el cercador per tancar 
        // visualment el flux de recerca per a l'usuari.
        this.searchControl.setValue(''); 
      },
      error: (err) => alert('❌ Error: ' + err.error.message)
    });
  }

  // Acceptar una sol·licitud entrant
  acceptRequest(id: number) {
    this.friendService.acceptRequest(id).subscribe({
      // Nota: No cal manipular els arrays `pendingRequests` ni `friendsList` aquí,
      // el FriendService ja s'encarrega d'actualitzar el BehaviorSubject internament.
      next: (res) => alert('🎉 ' + res.message),
      error: (err) => alert('Error: ' + err.error.message)
    });
  }

  // Rebutjar una sol·licitud entrant
  rejectRequest(id: number) {
    // Afegim una confirmació per evitar missclicks accidentals
    // que podrien portar a frustració de l'usuari.
    if(confirm('Segur que vols rebutjar aquesta sol·licitud?')) {
      this.friendService.rejectRequest(id).subscribe({
        next: (res) => alert('🗑️ ' + res.message),
        error: (err) => alert('Error: ' + err.error.message)
      });
    }
  }
  
  // Eliminar un amic existent de la xarxa
  removeFriend(id: number) {
    // Aquesta és una acció destructiva severa (trenca una relació BBDD).
    // Obliguem a doble confirmació mitjançant el prompt del navegador.
    if(confirm('Segur que vols eliminar aquest amic? La decisió és irreversible!')) {
      this.friendService.removeFriend(id).subscribe({
        next: (res) => alert('🗑️ ' + res.message),
        error: (err) => alert('Error: ' + err.error.message)
      });
    }
  }
}