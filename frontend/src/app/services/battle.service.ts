/**
 * ============================================================
 * FITXER: src/app/services/battle.service.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Aquest servei actua com a "Àrbitre de Xarxa" per a l'ecosistema 
 * de batalles PVP (Jugador contra Jugador). És l'encarregat de 
 * comunicar-se amb el backend per preparar l'escenari de combat 
 * i, el més important, d'oficiar les conseqüències destructives 
 * d'alta prioritat de final de partida (el traspàs de propietat
 * d'un Xuxemon del perdedor al guanyador).
 *
 * MAPA DE CONNEXIONS:
 * → Consumit per: Battle (component) per orquestrar la lògica visual 
 * i de torns a la pantalla de combat.
 * → Interceptors: authInterceptor afegeix el JWT a totes les crides 
 * per garantir que l'usuari no pugui fer trampes en l'ID de guanyador.
 * → API Endpoints: GET /api/battle/{friendId} i POST /api/battle/transfer
 * ============================================================
 */

import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' }) // Singleton: Disponible globalment
export class BattleService {
  private apiUrl = 'http://localhost:8000/api/battle';
  
  // Utilitzem la funció inject() moderna d'Angular per acoblar el HttpClient 
  // sense inflar el constructor del servei.
  private http = inject(HttpClient);

  // ── PREPARACIÓ DE LA BATALLA ──────────────────────────────
  // Recupera la informació necessària per iniciar el combat contra un amic.
  // PER QUÈ: Aquesta crida (GET) normalment demana al backend que resolgui 
  // quins són els Xuxemons actius i les seves estadístiques (vida, atac) 
  // tant per a l'usuari loguejat (atacant) com per a l'amic (defensor).
  getBattleData(friendId: number): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/${friendId}`);
  }

  // ── CONSEQÜÈNCIES DE FINAL DE PARTIDA (RISC-RECOMPENSA) ───
  // Executa l'acció crítica on el perdedor perd el seu Xuxemon i el guanyador se'l queda.
  transferXuxemon(winnerId: number, loserPivotId: number): Observable<any> {
    // PER QUÈ USEM EL 'loserPivotId' I NO NOMÉS L'ID DEL XUXEMON?: 
    // Un jugador pot tenir tres "Pikachus" (per exemple). Si usem només l'ID genèric 
    // de la raça, el backend no sabria quin dels tres eliminar. 
    // L'ID del pivot representa la *instància única* (relació taula user_xuxemons) 
    // amb la seva pròpia vida, malaltia i historial de menjar. 
    // És una pràctica de disseny de base de dades excel·lent.
    return this.http.post<any>(`${this.apiUrl}/transfer`, {
      winner_id: winnerId,
      loser_xuxemon_pivot_id: loserPivotId
    });
  }
}