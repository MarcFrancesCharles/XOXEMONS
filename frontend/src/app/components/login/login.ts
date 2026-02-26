import { Component, inject, ViewChild,ElementRef, AfterViewInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../services/auth';
import { LoadingService } from '../../services/loading';


@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './login.html',
  styleUrl: './login.css'
})
export class Login implements AfterViewInit {
  //Per controlar el vídeo de fons i assegurar-nos que es reprodueix correctament
  @ViewChild('bgVideo') bgVideo!: ElementRef<HTMLVideoElement>;

  loginForm: FormGroup;
  errorMessage: string = '';

  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  private router = inject(Router);
  private loadingService = inject(LoadingService);

  constructor() {
    // El login de xuxemons demana el Custom ID (ex: #Marc8160) i la contrasenya
    this.loginForm = this.fb.group({
      custom_id: ['', Validators.required],
      password: ['', Validators.required]
    });
  }

  ngAfterViewInit() {
    if (this.bgVideo){
      this.bgVideo.nativeElement.muted = true; // Assegura que el vídeo està mutat

      this.bgVideo.nativeElement.play().catch(error => {
        console.warn('No s\'ha pogut reproduir el vídeo de fons automàticament:', error);
      });
    }
    }

  onSubmit() {
      if (this.loginForm.valid) {
        this.errorMessage = '';
        
        // 3. ACTIVEM LA PANTALLA NEGRA DE CÀRREGA (Fade In)
        this.loadingService.show();

        this.authService.login(this.loginForm.value).subscribe({
          next: (response) => {
            localStorage.setItem('auth_token', response.access_token);
            
            // Canviem de pàgina a /main
            this.router.navigate(['/main']).then(() => {
              // Un cop la pàgina /main s'ha carregat, amaguem la pantalla negra
              // (Hi poso un petit retard de 500ms perquè vegis l'efecte retro bé)
              setTimeout(() => {
                this.loadingService.hide();
              }, 400);
            });
          },
          error: (error) => {
            // Si hi ha error, amaguem la pantalla i mostrem el text d'error
            this.loadingService.hide();
            this.errorMessage = 'ID de jugador o contrasenya incorrectes.';
          }
        });
      }
    }
  }