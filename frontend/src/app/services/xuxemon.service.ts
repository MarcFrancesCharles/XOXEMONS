import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';
import { AuthService } from './auth';

@Injectable({ providedIn: 'root' })
export class XuxemonService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);
  private authService = inject(AuthService);

  // BehaviorSubject per mantenir l'estat de la Xuxedex (llista reactiva)
  private xuxedexSubject = new BehaviorSubject<any[]>([]);
  public xuxedex$ = this.xuxedexSubject.asObservable();

  loadXuxedex(): Observable<any> {
    const headers = new HttpHeaders().set('Authorization', `Bearer ${this.authService.getToken()}`);
    return this.http.get<any[]>(`${this.apiUrl}/xuxedex`, { headers }).pipe(
      tap(xuxemons => this.xuxedexSubject.next(xuxemons)) // Actualitzem l'estat reactiu
    );
  }
}