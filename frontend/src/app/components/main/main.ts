/**
 * ============================================================
 * FITXER: src/app/components/main/main.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Component principal (Dashboard/Hub) on aterra l'usuari després
 * de fer el login correctament. Actua com a menú central del joc
 * i punt de partida cap a l'inventari, xuxedex, amics, etc. 
 * A més, gestiona la lògica de la recompensa diària i el tancament
 * de sessió segur.
 *
 * MAPA DE CONNEXIONS:
 * → Protegit per: authGuard (només usuaris amb token actiu hi entren).
 * → Importa serveis: AuthService (per dades d'usuari i logout), 
 * LoadingService (per UI bloquejant durant logout) i 
 * XuxemonService (per la recompensa diària).
 * → HTML/CSS: main.html / main.css
 * ============================================================
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth';
import { LoadingService } from '../../services/loading';
import { XuxemonService } from '../../services/xuxemon.service';

@Component({
  selector: 'app-main',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './main.html',
  styleUrl: './main.css'
})
export class Main implements OnInit {
  // Emmagatzema les dades del perfil de l'usuari (nom, monedes, rol, etc.)
  userData: any = null;
  
  // Flag per evitar múltiples peticions si l'usuari fa "doble clic" ràpid al botó de recompensa.
  isClaiming: boolean = false;

  // ── INJECCIÓ DE DEPENDÈNCIES ──────────────────────────────
  private authService = inject(AuthService);
  private router = inject(Router);
  private loadingService = inject(LoadingService);
  private xuxemonService = inject(XuxemonService); // <-- Afegit per la Recompensa

  // ── CICLE DE VIDA: INITIALITZACIÓ ─────────────────────────
  ngOnInit() {
    // 1. Doble validació de seguretat frontal: tot i que el guard ha deixat passar,
    // validem si el token està caducat. Això prevé errors 401 si l'usuari ha deixat
    // la pestanya oberta durant hores.
    if (!this.authService.getToken() || this.authService.isTokenExpired()) {
      this.authService.removeToken();
      this.router.navigate(['/login']);
      return;
    }

    // 2. Demanem les dades fresques de l'usuari al servidor. 
    // Important: Això manté la UI sincronitzada amb la base de dades (ex: si l'admin li ha canviat el rol).
    this.authService.getProfile().subscribe({
      next: (data) => {
        this.userData = data;
      },
      error: (err) => {
        // Si el perfil falla (ex: token manipulat o revocat al backend),
        // destruïm la sessió immediatament.
        console.error('Error de seguretat', err);
        this.authService.removeToken();
        this.router.navigate(['/login']);
      }
    });
  }

  // ── LÒGICA DE NEGOCI: RECOMPENSA DIÀRIA ───────────────────
  claimReward() {
    // Bloquegem el botó posant isClaiming a true per evitar spam de peticions HTTP concurrents (Rate Limiting per UI).
    this.isClaiming = true;
    
    this.xuxemonService.claimDailyReward().subscribe({
      next: (response) => {
        // El servidor ha validat que han passat 24h i ha afegit la recompensa.
        alert(response.message); // Missatge d'èxit
        this.isClaiming = false; // Alliberem el botó perquè la UI torni al seu estat
      },
      error: (err) => {
        // El servidor rebutja l'acció (ex: ja s'ha reclamat avui). Donem feedback clar a l'usuari.
        alert('⚠️ ' + err.error.message); // Missatge d'error ("Ja has reclamat avui...")
        this.isClaiming = false;
      }
    });
  }

  // ── LÒGICA DE NEGOCI: TANCAR SESSIÓ ───────────────────────
  onLogout() {
      // Activen l'overlay global per bloquejar la interacció amb l'app mentre el servidor processa el logout.
      // Això evita navegacions "fantasmes" mentre es destrueix el token a l'API.
      this.loadingService.show('TANCANT SESSIÓ...');

      this.authService.logout().subscribe({
        next: () => {
          // El backend ha posat el token a la blacklist amb èxit. Ara el netegem localment.
          this.authService.removeToken();
          
          // Naveguem asíncronament al login. Un cop Angular ha resolt la ruta,
          // esperem 300ms per amagar l'overlay, donant una transició suau i polida.
          this.router.navigate(['/login']).then(() => {
            setTimeout(() => {
              this.loadingService.hide();
            }, 300); // 300ms de retraso para que el efecto de fundido se vea bien
          });
        },
        error: (err) => {
          // Si hi ha un error de connexió, per seguretat netegem el token local igualment.
          // Preferim "expulsar" l'usuari proactivament abans que deixar l'estat de la sessió incert.
          console.error('Error al tancar sessió', err);
          this.authService.removeToken();
          
          this.router.navigate(['/login']).then(() => {
            setTimeout(() => {
              this.loadingService.hide();
            }, 300);
          });
        }
      });
  }
}