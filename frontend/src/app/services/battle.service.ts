import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class BattleService {
  private apiUrl = 'http://localhost:8000/api/battle';
  private http = inject(HttpClient);

  getBattleData(friendId: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/${friendId}`);
  }

  transferXuxemon(winnerId: number, loserPivotId: number): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/transfer`, {
      winner_id: winnerId,
      loser_xuxemon_pivot_id: loserPivotId
    });
  }
}