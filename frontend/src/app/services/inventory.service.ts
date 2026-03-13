import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http'; 
import { BehaviorSubject, Observable, tap } from 'rxjs';


@Injectable({ providedIn: 'root' })
export class InventoryService {
  private apiUrl = 'http://localhost:8000/api';
  private http = inject(HttpClient);


  private inventorySubject = new BehaviorSubject<any[]>([]);
  public inventory$ = this.inventorySubject.asObservable();

  loadInventory(): Observable<any> {
    return this.http.get<any[]>(`${this.apiUrl}/inventory`).pipe(
      tap(items => this.inventorySubject.next(items))
    );
  }
}