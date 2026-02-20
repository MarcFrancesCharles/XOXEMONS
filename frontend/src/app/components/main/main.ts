import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth';

@Component({
  selector: 'app-main',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './main.html',
  styleUrl: './main.css'
})
export class Main implements OnInit {
  userData: any = null;

  private authService = inject(AuthService);
  private router = inject(Router);

  ngOnInit() {
    if (!this.authService.getToken()) {
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

  // --- LA FUNCIÓ DE TANCAR SESSIÓ ESTÀ AQUÍ DINS ---
  onLogout() {
    this.authService.logout().subscribe({
      next: () => {
        this.authService.removeToken();
        this.router.navigate(['/login']);
      },
      error: (err) => {
        console.error('Error al tancar sessió', err);
        this.authService.removeToken();
        this.router.navigate(['/login']);
      }
    });
  }
}