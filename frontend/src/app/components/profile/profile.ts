/**
 * ============================================================
 * FITXER: src/app/components/profile/profile.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Component de UI dedicat a la gestió del compte de l'usuari.
 * Permet visualitzar les dades actuals, alternar cap a un mode
 * d'edició (formulari) per actualitzar la informació personal
 * i sol·licitar l'eliminació permanent del compte (zona de perill).
 *
 * MAPA DE CONNEXIONS:
 * → Protegit per: authGuard
 * → Importa serveis: AuthService (getProfile, updateProfile, deleteAccount).
 * → Moduls: FormsModule (per emplaçar les dades de manera bidireccional amb [(ngModel)]).
 * → Router: Navega enrere a /main o redirigeix a /login si s'elimina el compte.
 * ============================================================
 */

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
  // Estat de només lectura amb la font de veritat de l'usuari actualitzada des de DB.
  userData: any = null;
  
  // Flag que commuta visualment l'HTML entre la vista de lectura (fals) i el formulari (cert).
  isEditing: boolean = false;
  
  // Objecte buffer per emmagatzemar les modificacions d'entrada temporalment.
  editData: any = {};
  
  // Variables per a la gestió de feedback de la UI i control d'errors al formulari.
  message: string = '';
  isError: boolean = false;

  // ── INJECCIÓ DE DEPENDÈNCIES ──────────────────────────────
  private authService = inject(AuthService);
  private router = inject(Router);

  // ── INITIALITZACIÓ I RECUPERACIÓ DE DADES ─────────────────
  ngOnInit() {
    // 1. Demanem les dades al Laravel
    // L'interceptor intercepta aquesta petició i hi adjunta el Bearer Token automàticament.
    this.authService.getProfile().subscribe({
      next: (data) => {
        this.userData = data;
      },
      error: (err) => {
        // En cas de fallada de sessió (ex: esborrat de DB o ban de l'administrador), apliquem
        // la política de tancament forçós per protegir les vistes de dades nules.
        console.error('Error carregant el perfil', err);
        this.authService.removeToken();
        this.router.navigate(['/login']);
      }
    });
  }

  // ── NAVEGACIÓ ─────────────────────────────────────────────
  // Funció per tornar enrere
  goBack() {
    // Sempre forcem un return absolut cap a /main per evitar comportaments anòmals
    // amb l'historial del navegador si l'usuari ha usat les fletxes endarrere/endavant.
    this.router.navigate(['/main']);
  }

  // ── GESTIÓ DE L'ESTAT D'EDICIÓ ────────────────────────────
  // Activa/Desactiva el mode edició
  toggleEdit() {
    this.isEditing = !this.isEditing;
    
    if (this.isEditing) {
      // Copiem les dades actuals per no modificar userData directament
      // Ho fem així (clonació de valors) perquè si l'usuari clica "Cancel·lar"
      // sense guardar, la informació visible original no hagi estat mutada en temps real.
      this.editData = { 
        name: this.userData.name, 
        surnames: this.userData.surnames, 
        email: this.userData.email,
        password: '', // Aquest camp per seguretat el deixem sempre buit de base.
        password_confirmation: ''
      };
    }
    // Sanejem qualsevol avís previ o missatge d'error quan la UI canvia d'estat
    this.message = '';
  }

  // ── ACTUALITZACIÓ DEL PERFIL (PATCH) ──────────────────────
  // Desa els canvis al servidor
  saveProfile() {
    this.authService.updateProfile(this.editData).subscribe({
      next: (res) => {
        // Sobre-escrivim la font de veritat només amb la resposta validada del backend
        this.userData = res.user;
        this.isEditing = false;
        this.message = 'Perfil actualitzat correctament!';
        this.isError = false;
      },
      error: (err) => {
        // Exemple comú d'arribada fins aquí: correu en ús per altre user, o passwords curts.
        console.error('Error actualitzant el perfil', err);
        this.message = 'Error al desar els canvis. Revisa les dades.';
        this.isError = true;
      }
    });
  }

  // ── ZONA DE PERILL: ESBORRAT DE COMPTE ────────────────────
  // Esborra el compte
  deleteAccount() {
    // Com que és una acció destructiva de base de dades on s'aplica on cascade delete 
    // l'inventari, xuxemons i friends, SEMPRE necessitem validació explícita per evitar accidents.
    if (confirm('Estàs segur que vols esborrar el teu compte permanentment? Aquesta acció no es pot desfer.')) {
      this.authService.deleteAccount().subscribe({
        next: () => {
          // Un cop netejades les relacions a backend, destruïm la sessió al navegador 
          // i reboquem els accessos frontals de guard.
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