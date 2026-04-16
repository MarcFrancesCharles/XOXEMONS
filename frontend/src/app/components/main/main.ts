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
  userData: any = null;
  
  // Variable per saber si estem reclamant la recompensa (evita doble clic)
  isClaiming: boolean = false;

  private authService = inject(AuthService);
  private router = inject(Router);
  private loadingService = inject(LoadingService);
  private xuxemonService = inject(XuxemonService); // <-- Afegit per la Recompensa

  ngOnInit() {
    if (!this.authService.getToken() || this.authService.isTokenExpired()) {
      this.authService.removeToken();
      this.router.navigate(['/login']);
      return;
    }

    this.authService.getProfile().subscribe({
      next: (data) => {
        this.userData = data;
      },
      error: (err) => {
        console.error('Error de seguretat', err);
        this.authService.removeToken();
        this.router.navigate(['/login']);
      }
    });
  }

  // --- RECOMPENSA DIÀRIA ---
  claimReward() {
    this.isClaiming = true;
    this.xuxemonService.claimDailyReward().subscribe({
      next: (response) => {
        alert(response.message); // Missatge d'èxit
        this.isClaiming = false;
      },
      error: (err) => {
        alert('⚠️ ' + err.error.message); // Missatge d'error ("Ja has reclamat avui...")
        this.isClaiming = false;
      }
    });
  }

  // --- LA FUNCIÓ DE TANCAR SESSIÓ ESTÀ AQUÍ DINS ---
  onLogout() {
      // 3. Mostramos la pantalla negra de "CARREGANT..."
      this.loadingService.show('TANCANT SESSIÓ...');

      this.authService.logout().subscribe({
        next: () => {
          this.authService.removeToken();
          
          // Navegamos al login y luego ocultamos la pantalla negra con un pequeño retraso
          this.router.navigate(['/login']).then(() => {
            setTimeout(() => {
              this.loadingService.hide();
            }, 300); // 300ms de retraso para que el efecto de fundido se vea bien
          });
        },
        error: (err) => {
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