import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './profile.html',
  styleUrl: './profile.css'
})
export class Profile implements OnInit {
  userData: any = null;

  private authService = inject(AuthService);
  private router = inject(Router);

  ngOnInit() {


    // 1. Demanem les dades al Laravel
    this.authService.getProfile().subscribe({
      next: (data) => {
        this.userData = data;
      },
      error: (err) => {
        console.error('Error carregant el perfil', err);
        this.authService.removeToken();
        this.router.navigate(['/login']);
      }
    });
  }

  // Funció per tornar enrere
  goBack() {
    this.router.navigate(['/main']);
  }
}