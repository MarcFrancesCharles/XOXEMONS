import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './login.html',
  styleUrl: './login.css'
})
export class Login {
  loginForm: FormGroup;
  errorMessage: string = '';

  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  private router = inject(Router);

  constructor() {
    // El login de Xoxemons demana el Custom ID (ex: #Marc8160) i la contrasenya
    this.loginForm = this.fb.group({
      custom_id: ['', Validators.required],
      password: ['', Validators.required]
    });
  }

  onSubmit() {
    if (this.loginForm.valid) {
      this.authService.login(this.loginForm.value).subscribe({
        next: (response) => {
          // Si el login és correcte, guardem el token que ens dóna Laravel
          localStorage.setItem('auth_token', response.access_token);
          alert('Sessió iniciada correctament!');
          // L'enviem a la pàgina principal del joc
          this.router.navigate(['/main']);
        },
        error: (error) => {
          this.errorMessage = 'ID de jugador o contrasenya incorrectes.';
        }
      });
    }
  }
}