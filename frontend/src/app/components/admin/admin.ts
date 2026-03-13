import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { AuthService } from '../../services/auth';

@Component({
  selector: 'app-admin',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './admin.html',
  styleUrl: './admin.css'
})
export class Admin implements OnInit {
  private fb = inject(FormBuilder);
  private http = inject(HttpClient);
  private authService = inject(AuthService);

  users: any[] = [];
  itemForm: FormGroup;
  successMessage: string = '';
  errorMessage: string = '';

  constructor() {
    // Creem el formulari reactiu
    this.itemForm = this.fb.group({
      user_id: ['', Validators.required],
      item_type: ['xuxe', Validators.required], // 'xuxe' o 'vacuna'
      item_name: ['', Validators.required],
      quantity: [1, [Validators.required, Validators.min(1)]]
    });
  }

  ngOnInit() {
    this.loadUsers();
  }

  // Carreguem tots els usuaris de la BBDD
  loadUsers() {
    const headers = new HttpHeaders().set('Authorization', `Bearer ${this.authService.getToken()}`);
    // Suposem que tens aquesta ruta creada al Laravel
    this.http.get<any[]>('http://localhost:8000/api/admin/users', { headers }).subscribe({
      next: (data) => this.users = data,
      error: (err) => console.error('Error carregant usuaris', err)
    });
  }
  
  giveRandomXuxemon() {
    // Agafem l'usuari que estigui seleccionat al desplegable
    const userId = this.itemForm.get('user_id')?.value;
    
    if (!userId) {
      this.errorMessage = "Selecciona un jugador primer!";
      return;
    }

    this.http.post('http://localhost:8000/api/admin/give-xuxemon', { user_id: userId }).subscribe({
      next: (response: any) => {
        this.successMessage = response.message;
        this.errorMessage = '';
        setTimeout(() => this.successMessage = '', 3000);
      },
      error: () => this.errorMessage = 'Error en donar el Xuxemon.'
    });
  }

  // Enviem l'ítem a l'usuari
  onSubmit() {
    if (this.itemForm.valid) {
      const headers = new HttpHeaders().set('Authorization', `Bearer ${this.authService.getToken()}`);
      
      this.http.post('http://localhost:8000/api/admin/give-item', this.itemForm.value, { headers }).subscribe({
        next: (response: any) => {
          this.successMessage = 'Ítem enviat correctament!';
          this.errorMessage = '';
          this.itemForm.reset({ item_type: 'xuxe', quantity: 1 }); // Reiniciem el form
          
          // Esborrem el missatge d'èxit al cap de 3 segons
          setTimeout(() => this.successMessage = '', 3000);
        },
        error: (err) => {
          this.errorMessage = 'Error en enviar l\'ítem.';
          this.successMessage = '';
        }
      });
    }
  }
}