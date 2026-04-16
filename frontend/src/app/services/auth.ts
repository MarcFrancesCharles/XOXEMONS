import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);

  register(userData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, userData);
  }

  login(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, credentials);
  }

  setToken(token: string): void {
    localStorage.setItem('auth_token', token);
    localStorage.setItem('auth_timestamp', Date.now().toString());
  }

  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  isTokenExpired(): boolean {
    const timestamp = localStorage.getItem('auth_timestamp');
    if (!timestamp) return true;

    const limit = 2 * 60 * 60 * 1000; // 2 hores en mil·lisegons
    const now = Date.now();
    return (now - parseInt(timestamp, 10)) > limit;
  }

  logout(): Observable<any> {
    return this.http.post(`${this.apiUrl}/logout`, {});
  }

  removeToken(): void {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_timestamp');
  }

  getProfile(): Observable<any> {
    return this.http.get(`${this.apiUrl}/me`);
  }

  updateProfile(userData: any): Observable<any> {
    return this.http.patch(`${this.apiUrl}/user/profile`, userData);
  }

  deleteAccount(): Observable<any> {
    return this.http.delete(`${this.apiUrl}/user/account`);
  }
}