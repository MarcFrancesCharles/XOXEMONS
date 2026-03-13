import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-profile',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './profile.html',
  styleUrl: './profile.css'
})
export class Profile implements OnInit {
  usuari: any = null;
  errorMessage: string = '';

  constructor(private http: HttpClient) {}

  ngOnInit(): void {
    const token = localStorage.getItem('auth_token');
    
    if (token) {
      const headers = new HttpHeaders({
        'Authorization': `Bearer ${token}`
      });

      this.http.get('http://localhost:8000/api/me', { headers }).subscribe({
        next: (data: any) => {
          this.usuari = data;
        },
        error: (err: any) => {
          console.error("Error al carregar el perfil", err);
          this.errorMessage = "No s'han pogut carregar les dades de l'usuari.";
        }
      });
    } else {
      this.errorMessage = "No s'ha trobat cap token d'autenticació. Inicia sessió.";
    }
  }
}
