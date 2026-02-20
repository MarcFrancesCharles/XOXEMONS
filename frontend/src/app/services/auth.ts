import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  // ruta a la API de Laravel
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) { }

  register(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, userData);
  }

  login(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, credentials);
  }

  //Obtenim el token guardat 
  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  //Esborram el token quan l'usuari fa logout
  logout(): Observable<any> {
    const token = this.getToken();
    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
    return this.http.post(`${this.apiUrl}/logout`, {}, { headers });
  }
  //esborrar token del navegador
  removeToken(): void {
    localStorage.removeItem('auth_token');
  }

  //Demanar a Laravel que ens retorni les dades de l'usuari actual (per mostrar el perfil, per exemple)
  getProfile(): Observable<any> {
    const token = this.getToken();
    //Afegim el token a les capçaleres de la petició per autenticar-nos davant Laravel
    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
    return this.http.get(`${this.apiUrl}/me`, { headers });
  }
}