/**
 * ============================================================
 * FITXER: src/app/components/chat/chat.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Interfície de missatgeria directa entre jugadors. Com que el backend
 * no utilitza WebSockets (connexions bidireccionals contínues), aquest
 * component simula el temps real utilitzant una tècnica anomenada 
 * "Short Polling" (preguntar repetidament al servidor si hi ha missatges nous).
 * A més, gestiona l'experiència d'usuari (UX) forçant l'scroll 
 * automàtic cap avall quan arriben missatges.
 *
 * MAPA DE CONNEXIONS:
 * → Rutes: ActivatedRoute per capturar l'ID de l'amic de la URL (/chat/:friendId).
 * → Serveis: ChatService (estat reactiu i API) i AuthService (identitat pròpia).
 * → DOM: Utilitza @ViewChild per tenir accés directe a l'element HTML
 * del xat i manipular-ne la barra de desplaçament (scroll).
 * ============================================================
 */

import { Component, OnInit, OnDestroy, AfterViewChecked, ElementRef, ViewChild, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { ChatService } from '../../services/chat.service';
import { AuthService } from '../../services/auth';

@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './chat.html',
  styleUrl: './chat.css'
})
// Com a Arquitecte, declarem explícitament OnInit, AfterViewChecked i OnDestroy
// per garantir que TypeScript validi l'existència d'aquests mètodes vitals.
export class Chat implements OnInit, AfterViewChecked, OnDestroy {
  // ── INJECCIÓ DE DEPENDÈNCIES ──────────────────────────────
  private chatService = inject(ChatService);
  private route = inject(ActivatedRoute);
  private authService = inject(AuthService);
  
  // Guardem la referència a l'interval temporal. És CRÍTIC tenir aquesta 
  // variable per poder "matar" el bucle quan sortim de la pantalla.
  private pollingInterval: any; 

  // ── ESTAT DEL COMPONENT ───────────────────────────────────
  friendId!: number;
  myId!: number;
  messages: any[] = [];
  newMessage: string = '';

  // @ViewChild cerca al template HTML un element marcat amb #chatScroll (ex: <div #chatScroll>)
  // PER QUÈ: A Angular hem d'evitar usar `document.getElementById()`. ViewChild 
  // és la forma "Angular-nativa" i segura d'accedir a elements del DOM.
  @ViewChild('chatScroll') private chatScrollContainer!: ElementRef;

  // ── CICLE DE VIDA: INICIALITZACIÓ ─────────────────────────
  ngOnInit() {
    // Extraiem l'ID de l'amic directament dels paràmetres dinàmics de la URL.
    this.friendId = Number(this.route.snapshot.paramMap.get('friendId'));
    
    // Obtenim el nostre propi ID per poder diferenciar visualment a l'HTML 
    // quins missatges són meus (dreta) i quins són de l'amic (esquerra).
    this.authService.getProfile().subscribe(user => this.myId = user.id);

    // 1. Càrrega inicial i subscripció a l'estat reactiu
    this.chatService.loadMessages(this.friendId).subscribe();
    this.chatService.messages$.subscribe(msgs => this.messages = msgs);

    // 2. TÈCNICA DE SHORT POLLING
    // PER QUÈ: Sense WebSockets, l'única manera de saber si ens han escrit 
    // és demanar-ho manualment. Fem un GET cada 2 segons (2000ms).
    // Nota de rendiment: Això genera molta càrrega al servidor, però és una
    // solució vàlida per a prototips o aplicacions amb poc trànsit simultani.
    this.pollingInterval = setInterval(() => {
      this.chatService.loadMessages(this.friendId).subscribe();
    }, 2000);
  }

  // ── CICLE DE VIDA: DESTRUCCIÓ (MOLT IMPORTANT) ────────────
  ngOnDestroy() {
    // PER QUÈ: Els intervals de JavaScript viuen fora del cicle de vida dels components.
    // Si l'usuari clica "Tornar enrere" i no netegem això (clearInterval), 
    // l'aplicació continuarà fent peticions a l'API en segon pla per sempre, 
    // causant una fuita de memòria (Memory Leak) i col·lapsant el navegador a la llarga.
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
    }
  }

  // ── CICLE DE VIDA: ACTUALITZACIÓ DEL DOM ──────────────────
  // Aquest mètode s'executa automàticament cada vegada que Angular detecta un canvi 
  // que afecta la vista (ex: entra un missatge nou i es pinta l'HTML).
  ngAfterViewChecked() {
    this.scrollToBottom();
  }

  // Força que la barra de desplaçament es posicioni abaix de tot.
  scrollToBottom(): void {
    try {
      // Igualem la posició actual de l'scroll (scrollTop) a l'altura màxima
      // total del contenidor (scrollHeight), empenyent-lo al final.
      this.chatScrollContainer.nativeElement.scrollTop = this.chatScrollContainer.nativeElement.scrollHeight;
    } catch(err) { 
      // El try-catch és necessari perquè durant la primera renderització (milisegons),
      // el ViewChild pot no estar disponible encara al DOM.
    }
  }

  // ── ACCIONS DE L'USUARI ───────────────────────────────────
  sendMessage() {
    // Validació per evitar enviar missatges buits o fets només d'espais
    if (!this.newMessage.trim()) return;

    // 1. Capturem el valor i apliquem "Optimistic UI" parcial:
    // Netegem l'input immediatament abans d'esperar al servidor.
    // Això dona la sensació psicològica de velocitat extrema a l'usuari.
    const content = this.newMessage;
    this.newMessage = ''; 

    // 2. Enviem al servidor
    this.chatService.sendMessage(this.friendId, content).subscribe({
      next: () => {
        // L'èxit és silenciós. El servei ja ha afegit el missatge 
        // al BehaviorSubject internament i la UI s'ha actualitzat sola!
      },
      error: (err) => alert('Error enviant: ' + err.error.message)
    });
  }

  // ── FUNCIONS D'UTILITAT (HELPERS) ─────────────────────────
  // Transforma marques de temps ISO (2026-04-15T10:00:00Z) en formats humans.
  // PER QUÈ: Millora dràsticament l'experiència d'usuari llegir "Fa 5 min" 
  // en lloc d'una data llarga en formats tècnics.
  timeAgo(dateString: string): string {
    const messageDate = new Date(dateString);
    const now = new Date();
    
    // Calculem la diferència en mil·lisegons i la passem a minuts
    const diffMs = now.getTime() - messageDate.getTime();
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) return 'Ara mateix';
    if (diffMins < 60) return `Fa ${diffMins} min`;
    
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `Fa ${diffHours} h`;
    
    // Fallback: Si fa més d'un dia, mostrem la data normal.
    return messageDate.toLocaleDateString();
  }
}