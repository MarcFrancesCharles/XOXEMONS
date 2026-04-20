/**
 * ============================================================
 * FITXER: src/app/components/login/login.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Component encarregat de l'autenticació inicial de l'usuari.
 * És la porta d'entrada a l'aplicació. Gestiona el formulari
 * de credencials, comunica amb l'AuthService per validar-les,
 * i redirigeix l'usuari a la vista principal (/main) o a l'admin
 * segons el seu rol.
 *
 * MAPA DE CONNEXIONS:
 * → Importa: AuthService (per cridar a l'API de login i guardar el token)
 * → Importa: LoadingService (per activar l'overlay visual durant la petició HTTP)
 * → Router: Redirigeix a /main o /register.
 * → HTML: login.html (conté el formulari reactiu o template-driven)
 * ============================================================
 */

import { Component, inject } from '@angular/core';
import { Router, RouterLink } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../../services/auth';
import { LoadingService } from '../../services/loading';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [RouterLink, ReactiveFormsModule],
  templateUrl: './login.html',
  styleUrls: ['./login.css']
})
export class Login {
  // ── INJECCIÓ DE DEPENDÈNCIES ──────────────────────────────
  private authService = inject(AuthService);
  private router = inject(Router);
  private loadingService = inject(LoadingService);
  private fb = inject(FormBuilder);

  // ── ESTAT DEL COMPONENT ───────────────────────────────────
  // Definició del formulari reactiu per gestionar les credencials.
  loginForm: FormGroup = this.fb.group({
    custom_id: ['', [Validators.required]],
    password: ['', [Validators.required]]
  });

  // Variable per mostrar missatges d'error al DOM sense usar alert().
  errorMessage: string = '';

  // ── LÒGICA D'AUTENTICACIÓ ─────────────────────────────────

  // S'executa en fer submit al formulari HTML.
  onSubmit() {
    // Validació simple de frontend per estalviar crides innecessàries al backend.
    if (this.loginForm.invalid) {
      this.errorMessage = 'Si us plau, omple tots els camps.';
      return;
    }

    // Netegem errors previs i mostrem la pantalla de càrrega per bloquejar UI
    // i donar feedback visual a l'usuari de que s'està processant la petició.
    this.errorMessage = '';
    this.loadingService.show('Iniciant sessió...');

    // Ens subscrivim a l'Observable del servei d'autenticació.
    this.authService.login(this.loginForm.value).subscribe({
      next: (response) => {
        // Guardem el token JWT rebut pel backend al localStorage.
        // NOTA: El backend de Laravel (respondWithToken) l'envia com 'access_token'.
        this.authService.setToken(response.access_token);
        
        // Amaguem l'overlay de càrrega just abans de redirigir
        this.loadingService.hide();

        // Redirigim a la pàgina principal on ja actuarà el authGuard
        this.router.navigate(['/main']);
      },
      error: (err) => {
        // En cas d'error (ex: 401 Unauthorized), amaguem la pantalla de càrrega
        // i mostrem l'error al DOM perquè l'usuari ho vegi.
        this.loadingService.hide();
        this.errorMessage = err.error?.message || 'Credencials incorrectes. Torna-ho a provar.';
      }
    });
  }
}