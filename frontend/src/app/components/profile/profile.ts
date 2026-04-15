import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './profile.html',
  styleUrl: './profile.css'
})
export class Profile implements OnInit {
  userData: any = null;
  isEditing: boolean = false;
  editData: any = {};
  message: string = '';
  isError: boolean = false;

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

  // Activa/Desactiva el mode edició
  toggleEdit() {
    this.isEditing = !this.isEditing;
    if (this.isEditing) {
      // Copiem les dades actuals per no modificar userData directament
      this.editData = { 
        name: this.userData.name, 
        surnames: this.userData.surnames, 
        email: this.userData.email,
        password: '',
        password_confirmation: ''
      };
    }
    this.message = '';
  }

  // Desa els canvis al servidor
  saveProfile() {
    this.authService.updateProfile(this.editData).subscribe({
      next: (res) => {
        this.userData = res.user;
        this.isEditing = false;
        this.message = 'Perfil actualitzat correctament!';
        this.isError = false;
      },
      error: (err) => {
        console.error('Error actualitzant el perfil', err);
        this.message = 'Error al desar els canvis. Revisa les dades.';
        this.isError = true;
      }
    });
  }

  // Esborra el compte
  deleteAccount() {
    if (confirm('Estàs segur que vols esborrar el teu compte permanentment? Aquesta acció no es pot desfer.')) {
      this.authService.deleteAccount().subscribe({
        next: () => {
          this.authService.removeToken();
          this.router.navigate(['/login']);
        },
        error: (err) => {
          console.error('Error esborrant el compte', err);
          alert('No s\'ha pogut esborrar el compte.');
        }
      });
    }
  }
}