/**
 * ============================================================
 * FITXER: src/app/components/admin/admin.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Panell de control exclusiu (Dashboard) per als administradors
 * del joc (rol 'robot'). Aquest component té l'autoritat per
 * manipular l'economia i el balanç del joc en temps real. 
 * Les seves dues responsabilitats principals són:
 * 1. Operacions d'usuari: Regalar ítems o Xuxemons als jugadors.
 * 2. Balanç de joc (Game Design): Modificar les probabilitats 
 * globals de les malalties que afecten totes les instàncies.
 *
 * MAPA DE CONNEXIONS:
 * → Protegit per: adminGuard (només hi accedeixen usuaris autoritzats).
 * → Mòduls: Combina ReactiveFormsModule (per la complexitat del 
 * formulari de regals) i FormsModule (per l'enllaç bidireccional 
 * senzill a la zona de configuració de malalties).
 * → Serveis: XuxemonService (configuracions globals), AuthService,
 * i HttpClient per crides administratives directes a l'API.
 * ============================================================
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ReactiveFormsModule, FormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms'; 
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { AuthService } from '../../services/auth';
import { XuxemonService } from '../../services/xuxemon.service';

@Component({
  selector: 'app-admin',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, FormsModule, RouterModule], 
  templateUrl: './admin.html',
  styleUrl: './admin.css'
})
export class Admin implements OnInit {
  // ── INJECCIÓ DE DEPENDÈNCIES ──────────────────────────────
  private fb = inject(FormBuilder);
  private http = inject(HttpClient);
  private authService = inject(AuthService);
  private xuxemonService = inject(XuxemonService);

  // ── ESTAT DEL COMPONENT: ZONA DE REGALS ───────────────────
  users: any[] = [];
  itemForm: FormGroup;
  successMessage: string = '';
  errorMessage: string = '';

  // ── ESTAT DEL COMPONENT: CONFIGURACIÓ GLOBAL ──────────────
  // Valors per defecte "hardcoded" que actuaran com a fallback (pla B)
  // en cas que la base de dades falli en retornar els paràmetres reals.
  settings = {
    atracon_prob: 15,
    sobredosis_prob: 10,
    bajon_prob: 5
  };
  isSaving = false;

  constructor() {
    // Inicialitzem el formulari reactiu de regals.
    // PER QUÈ REACTIU AQUÍ?: Donar ítems a usuaris arbitraris és una operació
    // sensible. Utilitzem Validators per garantir que l'admin no pugui
    // enviar quantitats negatives o nul·les abans de cridar a l'API.
    this.itemForm = this.fb.group({
      user_id: ['', Validators.required],
      item_type: ['xuxe', Validators.required], 
      item_name: ['', Validators.required],
      quantity: [1, [Validators.required, Validators.min(1)]]
    });
  }

  // ── CICLE DE VIDA ─────────────────────────────────────────
  ngOnInit() {
    // Disparem les càrregues de dades crítiques en paral·lel al muntar el component.
    this.loadUsers();
    this.loadSettings(); 
  }

  // ==========================================
  // 1. LÒGICA DE REGALS (USuaris i Ítems)
  // ==========================================
  
  // Carrega el llistat de tots els usuaris per alimentar el `<select>` de l'HTML.
  loadUsers() {
    // NOTA D'ARQUITECTURA: Tot i que l'interceptor global ja injecta el token, 
    // en panells d'administració és una pràctica de doble seguretat ("belt and suspenders")
    // forçar manualment les capçaleres d'autorització en crides destructives o d'alt nivell.
    const headers = new HttpHeaders().set('Authorization', `Bearer ${this.authService.getToken()}`);
    
    this.http.get<any[]>('http://localhost:8000/api/admin/users', { headers }).subscribe({
      next: (data) => this.users = data,
      error: (err) => console.error('Error carregant usuaris', err)
    });
  }
  
  // Assigna un Xuxemon aleatori a l'usuari seleccionat.
  giveRandomXuxemon() {
    const userId = this.itemForm.get('user_id')?.value;
    
    // Validació de tall: Protegim el backend d'errors de paràmetres nuls.
    if (!userId) {
      this.errorMessage = "Selecciona un jugador primer!";
      return;
    }

    this.http.post('http://localhost:8000/api/admin/give-xuxemon', { user_id: userId }).subscribe({
      next: (response: any) => {
        // Mostrem el missatge d'èxit a la interfície
        this.successMessage = response.message;
        this.errorMessage = '';
        
        // UX Pattern: Esborrem el missatge d'èxit automàticament passats 3 segons.
        // Això evita que l'admin es confongui si fa diverses operacions seguides.
        setTimeout(() => this.successMessage = '', 3000);
      },
      error: () => this.errorMessage = 'Error en donar el Xuxemon.'
    });
  }

  // Injecta objectes (xuxes/vacunes) a l'inventari d'un usuari.
  onSubmit() {
    if (this.itemForm.valid) {
      const headers = new HttpHeaders().set('Authorization', `Bearer ${this.authService.getToken()}`);
      
      this.http.post('http://localhost:8000/api/admin/give-item', this.itemForm.value, { headers }).subscribe({
        next: (response: any) => {
          this.successMessage = 'Ítem enviat correctament!';
          this.errorMessage = '';
          
          // Un cop enviat l'ítem, resetegem el formulari als seus valors per defecte
          // preparant-lo per a la següent acció ràpida de l'administrador.
          this.itemForm.reset({ item_type: 'xuxe', quantity: 1 }); 
          
          setTimeout(() => this.successMessage = '', 3000);
        },
        error: (err) => {
          this.errorMessage = 'Error en enviar l\'ítem.';
          this.successMessage = '';
        }
      });
    }
  }

  // ==========================================
  // 2. LÒGICA DE CONFIGURACIÓ GLOBAL (MALALTIES)
  // ==========================================

  // Descarrega el balanç actual de la base de dades.
  loadSettings() {
    this.xuxemonService.getSettings().subscribe({
      next: (data) => {
        // Utilitzem assignació condicional. Si la base de dades ens retorna
        // camps buits o no definits, l'estat local mantindrà els valors 
        // per defecte declarats a l'inici, evitant errors lògics de matemàtiques al backend.
        if (data.atracon_prob !== undefined) this.settings.atracon_prob = data.atracon_prob;
        if (data.sobredosis_prob !== undefined) this.settings.sobredosis_prob = data.sobredosis_prob;
        if (data.bajon_prob !== undefined) this.settings.bajon_prob = data.bajon_prob;
      },
      error: (err) => console.error('Error carregant configuració', err)
    });
  }

  // Guarda les noves probabilitats.
  saveSettings() {
    // Validació lògica fonamental abans d'enviar al backend.
    // PER QUÈ: Aquestes variables són percentatges de probabilitat. 
    // Un total que superi el 100% trencaria el motor de resolució aleatòria (RNG)
    // del backend i faria el joc impredictible o llançaria excepcions en la lògica de generació.
    const total = this.settings.atracon_prob + this.settings.sobredosis_prob + this.settings.bajon_prob;
    if (total > 100) {
      alert('⚠️ Error: La suma de les probabilitats no pot superar el 100%. Actualment suma: ' + total + '%');
      return;
    }

    // Bloquegem el botó per evitar "doble clics" accidentals (Rate-limiting visual)
    this.isSaving = true;
    
    this.xuxemonService.updateSettings(this.settings).subscribe({
      next: (res) => {
        alert(res.message);
        this.isSaving = false;
      },
      error: (err) => {
        alert('Error al guardar: ' + err.error.message);
        this.isSaving = false;
      }
    });
  }
}