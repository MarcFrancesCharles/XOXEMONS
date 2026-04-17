/**
 * ============================================================
 * FITXER: src/app/components/register/register.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Component encarregat de l'onboarding o captació de nous usuaris.
 * Utilitza l'estratègia de Formularis Reactius (Reactive Forms) 
 * d'Angular per tenir un control absolut i síncron sobre la validació 
 * de les dades directament a la classe TypeScript, abans d'enviar 
 * cap petició innecessària al servidor.
 *
 * MAPA DE CONNEXIONS:
 * → Importa: AuthService per executar la petició POST cap a l'API.
 * → Utilitza: FormBuilder i FormGroup per construir el model de dades.
 * → Router: Redirigeix a /login quan el registre és satisfactori o a 
 * /main si l'usuari ja estava prèviament autenticat.
 * ============================================================
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth';

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './register.html',
  styleUrl: './register.css'
})
export class Register implements OnInit {
  // ── INJECCIÓ DE DEPENDÈNCIES ──────────────────────────────
  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  private router = inject(Router);

  // ── ESTAT DEL COMPONENT ───────────────────────────────────
  registerForm: FormGroup;
  errorMessage: string = '';

  constructor() {
    // Inicialitzem el formulari reactiu dins del constructor perquè 
    // l'objecte FormGroup estigui llest abans que el template HTML es renderitzi.
    // PER QUÈ REACTIVE FORMS?: Ens permet definir regles de validació estrictes 
    // (com email o minLength) al costat del codi, evitant que l'usuari pugui 
    // manipular l'HTML per saltar-se les restriccions del frontend.
    this.registerForm = this.fb.group({
      name: ['', Validators.required],
      surnames: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      password_confirmation: ['', Validators.required]
    });
  }

  // ── CICLE DE VIDA ─────────────────────────────────────────
  ngOnInit() {
    // Barrera de prevenció UX/Seguretat:
    // Si un usuari ja té un token actiu i intenta accedir manualment a la URL '/register', 
    // el redirigim a l'aplicació principal. Això evita estats inconsistents on un
    // usuari loguejat podria crear un compte paral·lel.
    if (this.authService.getToken()) {
      this.router.navigate(['/main']);
    }
  }

  // ── LÒGICA DE NEGOCI (SUBMIT) ─────────────────────────────
  onSubmit() {
    // 1. Validació de primera capa (Nivell Angular)
    // Abans de fer cap crida HTTP, assegurem-nos que totes les condicions 
    // definides al FormBuilder es compleixen. Estalvia trànsit innecessari de xarxa.
    if (this.registerForm.valid) {
      
      // 2. Validació de segona capa (Lògica específica)
      // Comprovem manualment que les dues contrasenyes siguin idèntiques.
      // Retornem aviat (early return) per interrompre el flux si fallen.
      if (this.registerForm.value.password !== this.registerForm.value.password_confirmation) {
        this.errorMessage = 'Les contrasenyes no coincideixen!';
        return;
      }

      // 3. Execució de l'acció asíncrona
      // L'observable gestiona la petició al backend de Laravel
      this.authService.register(this.registerForm.value).subscribe({
        next: (response) => {
          // El backend retorna dades crucials com el `custom_id` autogenerat.
          // Donem feedback clar a l'usuari i l'enviem cap al Login.
          // NOTA: No iniciem sessió automàticament; obligar l'usuari a posar les seves
          // credencials ara mateix ajuda a que les memoritzi (especialment el custom_id).
          alert(`Benvingut als xuxemons! El teu ID és: ${response.user.custom_id} (${response.user.role})`);
          this.router.navigate(['/login']); 
        },
        error: (error) => {
          // Gestionem fallades com: email ja en ús o errors de connexió
          console.error('Registre erroni', error);
          this.errorMessage = 'Error en el registre. Potser el correu ja existeix o hi ha problemes de connexió.';
        }
      });
    } else {
      // Feedback genèric si han intentat enviar el formulari amb errors estructurals
      // o camps buits que no han superat les condicions del FormBuilder.
      this.errorMessage = 'Si us plau, omple tots els camps correctament (revisa el correu i els 6 caràcters mínims de la contrasenya).';
    }
  }
}