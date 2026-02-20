import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth'; // <-- Importem el teu auth.ts

@Component({
  selector: 'app-register',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './register.html', // (Si el teu arxiu HTML es diu register.component.html, posa-ho aquí)
  styleUrl: './register.css'      // (Igual amb el CSS)
})
export class Register {
  registerForm: FormGroup;
  errorMessage: string = '';

  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  private router = inject(Router);

  constructor() {
    // Definim els camps del formulari i les seves normes (obligatori, email, mínim 6 lletres...)
    this.registerForm = this.fb.group({
      name: ['', Validators.required],
      surnames: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(6)]],
      password_confirmation: ['', Validators.required]
    });
  }

  onSubmit() {
    if (this.registerForm.valid) {
      // Validem que les dues contrasenyes siguin iguals
      if (this.registerForm.value.password !== this.registerForm.value.password_confirmation) {
        this.errorMessage = 'Les contrasenyes no coincideixen!';
        return;
      }

      // Cridem el Laravel a través del teu servei
      this.authService.register(this.registerForm.value).subscribe({
        next: (response) => {
          alert(`Benvingut als Xoxemons! El teu ID és: ${response.user.custom_id} (${response.user.role})`);
          this.router.navigate(['/login']); // L'enviem al Login
        },
        error: (error) => {
          this.errorMessage = 'Error en el registre. Potser el correu ja existeix?';
        }
      });
    } else {
      this.errorMessage = 'Si us plau, omple tots els camps correctament.';
    }
  }
}