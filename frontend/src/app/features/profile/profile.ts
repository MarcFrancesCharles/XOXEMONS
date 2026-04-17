/**
 * ============================================================
 * FITXER: src/app/components/profile/profile.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Component de presentació autònom per a la vista del Perfil.
 * En aquesta implementació, el component assumeix la responsabilitat
 * completa del seu flux de dades: no depèn de serveis externs (com 
 * AuthService), sinó que llegeix el token de seguretat directament 
 * del navegador i fa la crida HTTP manualment.
 *
 * MAPA DE CONNEXIONS:
 * → Mòduls: CommonModule (necessari per directives estructurals com *ngIf a l'HTML).
 * → Serveis Nadius: HttpClient (per la petició al backend de Laravel).
 * → Emmagatzematge: Llegeix directament de l'API nativa `localStorage`.
 * ============================================================
 */

import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-profile',
  standalone: true, // Component independent, no requereix ser declarat en cap NgModule
  imports: [CommonModule],
  templateUrl: './profile.html',
  styleUrl: './profile.css'
})
export class Profile implements OnInit {
  // ── ESTAT DEL COMPONENT ───────────────────────────────────
  // 'usuari' s'inicialitza a null. A l'HTML, usarem un *ngIf="usuari" 
  // per no intentar pintar dades abans que el servidor respongui.
  usuari: any = null;
  
  // Variable de control d'errors per donar feedback visual a l'usuari
  // si la crida falla o la sessió està caducada.
  errorMessage: string = '';

  // ── INJECCIÓ DE DEPENDÈNCIES (VIA CONSTRUCTOR) ────────────
  // Utilitzem la injecció clàssica pel constructor. Angular ens 
  // proveirà la instància global de HttpClient.
  constructor(private http: HttpClient) {}

  // ── CICLE DE VIDA ─────────────────────────────────────────
  ngOnInit(): void {
    // 1. Recuperació síncrona del token
    // Accedim directament a la memòria del navegador.
    const token = localStorage.getItem('auth_token');
    
    if (token) {
      // 2. Construcció manual de capçaleres de seguretat
      // PER QUÈ: Com que no utilitzem un HttpInterceptor global per a 
      // aquesta petició, som nosaltres els que hem de construir la 
      // capçalera 'Authorization' amb l'estàndard Bearer perquè Laravel 
      // ens deixi passar.
      const headers = new HttpHeaders({
        'Authorization': `Bearer ${token}`
      });

      // 3. Execució de la petició asíncrona
      // Passem l'objecte de capçaleres com a segon argument a la crida GET.
      this.http.get('http://localhost:8000/api/me', { headers }).subscribe({
        next: (data: any) => {
          // El backend reconeix el token i ens retorna el perfil.
          // Enllacem les dades a la variable d'estat perquè l'HTML es renderitzi.
          this.usuari = data;
        },
        error: (err: any) => {
          // Si el token ha caducat, ha estat manipulat, o el servidor cau,
          // capturem l'error per evitar trencar l'aplicació en silenci.
          console.error("Error al carregar el perfil", err);
          this.errorMessage = "No s'han pogut carregar les dades de l'usuari.";
        }
      });
    } else {
      // Caselles de seguretat front-end: Si no hi ha token físic, ni tan sols
      // intentem fer la petició HTTP per estalviar recursos i evitar un 401 segur.
      this.errorMessage = "No s'ha trobat cap token d'autenticació. Inicia sessió.";
    }
  }
}