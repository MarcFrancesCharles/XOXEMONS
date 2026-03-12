import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';
import { AuthService } from './auth';

@Injectable({ providedIn: 'root' })
export class InventoryService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);
  private authService = inject(AuthService);

  // BehaviorSubject per la Motxilla
  private inventorySubject = new BehaviorSubject<any[]>([]);
  public inventory$ = this.inventorySubject.asObservable();

  loadInventory(): Observable<any> {
    const headers = new HttpHeaders().set('Authorization', `Bearer ${this.authService.getToken()}`);
    return this.http.get<any[]>(`${this.apiUrl}/inventory`, { headers }).pipe(
      tap(items => this.inventorySubject.next(items))
    );
  }
}