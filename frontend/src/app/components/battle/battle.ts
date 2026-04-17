/**
 * ============================================================
 * FITXER: src/app/components/battle/battle.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * El "Coliseu" de l'aplicació. Aquest component orquestra la mecànica
 * principal de risc-recompensa del joc: el combat PVP asíncron.
 * Gestiona la selecció de criatures, la simulació matemàtica de la batalla
 * (tirada de daus i modificadors de mida i tipus), i crea "game feel"
 * (tensió i expectació) abans de comunicar les pèrdues/guanys 
 * irrevocables al servidor.
 *
 * MAPA DE CONNEXIONS:
 * → Rutes: ActivatedRoute (per saber contra quin amic lluitem via URL).
 * → Serveis: BattleService (per obtenir els contrincants i fer el
 * traspàs de propietat) i AuthService (identitat de l'usuari).
 * → Navegació: Router per fugir/sortir del combat cap a la llista d'amics.
 * ============================================================
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, ActivatedRoute, Router } from '@angular/router';
import { BattleService } from '../../services/battle.service';
import { AuthService } from '../../services/auth';

@Component({
  selector: 'app-battle',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './battle.html',
  styleUrl: './battle.css'
})
export class Battle implements OnInit {
  // ── INJECCIÓ DE DEPENDÈNCIES ──────────────────────────────
  private battleService = inject(BattleService);
  private authService = inject(AuthService);
  private route = inject(ActivatedRoute);
  private router = inject(Router);

  // ── ESTAT DEL COMPONENT (MEMÒRIA DE COMBAT) ───────────────
  myId!: number;
  friendId!: number;

  myXuxemons: any[] = [];
  friendXuxemons: any[] = [];

  mySelectedXuxe: any = null;
  friendSelectedXuxe: any = null;

  // Variables de control de flux de la Interfície (UX)
  battleStarted = false;
  isRolling = false;
  battleResult: any = null;

  // ── CICLE DE VIDA: PREPARACIÓ DE L'ARENA ──────────────────
  ngOnInit() {
    this.friendId = Number(this.route.snapshot.paramMap.get('friendId'));
    this.authService.getProfile().subscribe(user => this.myId = user.id);

    // Descarreguem l'estat actual dels equips. El backend només ens 
    // retornarà els Xuxemons que estiguin sans (sense malalties).
    this.battleService.getBattleData(this.friendId).subscribe({
      next: (data) => {
        this.myXuxemons = data.me;
        this.friendXuxemons = data.friend;
      },
      error: (err) => alert('Error carregant la batalla: ' + err.error.message)
    });
  }

  // ── FASE 1: SELECCIÓ DELS LLUITADORS ──────────────────────
  selectMyXuxemon(xuxe: any) {
    // Bloquegem la interacció si el combat ja ha començat
    if (this.battleStarted) return;
    this.mySelectedXuxe = xuxe;
    
    // Validació de tall: Si l'amic no té Xuxemons disponibles, ho aturem
    // per evitar errors de referència nul·la al backend més endavant.
    if (this.friendXuxemons.length === 0) {
      alert("Aquest amic no té cap Xuxemon sa per lluitar avui! 😢");
      this.mySelectedXuxe = null;
      return;
    }
    
    // PVP ASÍNCRON: En lloc d'esperar que l'amic es connecti i triï,
    // simulem la seva decisió agafant un Xuxemon a l'atzar del seu equip.
    // Això permet jugar 24/7 sense necessitar jugadors simultanis.
    const randomIndex = Math.floor(Math.random() * this.friendXuxemons.length);
    this.friendSelectedXuxe = this.friendXuxemons[randomIndex];
  }

  // ── FASE 2: INICI DE LA BATALLA (SIMULACIÓ UX) ────────────
  startBattle() {
    if (!this.mySelectedXuxe || !this.friendSelectedXuxe) return;
    
    this.battleStarted = true;
    this.isRolling = true; // Activa animacions CSS de "tirant daus" a l'HTML

    // GAME FEEL: Apliquem un retard artificial de 3 segons abans de mostrar 
    // el resultat. Això no aporta res a nivell tècnic, però psicològicament 
    // crea una sensació d'expectació i "pes" a la decisió que enganxa al jugador.
    setTimeout(() => {
      this.isRolling = false;
      this.calculateWinner();
    }, 3000);
  }

  // ── FASE 3: RESOLUCIÓ I CONSEQÜÈNCIES ─────────────────────
  // NOTA D'ARQUITECTURA SÈNIOR: En un entorn de producció AAA real, tot aquest
  // càlcul es faria AL BACKEND per evitar que un usuari modifiqui el codi Font 
  // (via DevTools) i es faci guanyar sempre. Però per a aquest projecte, fer-ho 
  // al frontend és perfecte per demostrar domini algorítmic.
  calculateWinner() {
    // 1. Atzar pur (Daus 1-6)
    const myRoll = Math.floor(Math.random() * 6) + 1;
    const friendRoll = Math.floor(Math.random() * 6) + 1;

    // 2. Aplicació de mecàniques de Rol (Mida)
    const mySizeMod = this.getSizeMod(this.mySelectedXuxe.size);
    const friendSizeMod = this.getSizeMod(this.friendSelectedXuxe.size);

    // 3. Aplicació de mecàniques pedra-paper-tisora (Elements)
    const myTypeMod = this.getTypeMod(this.mySelectedXuxe.type, this.friendSelectedXuxe.type);
    const friendTypeMod = this.getTypeMod(this.friendSelectedXuxe.type, this.mySelectedXuxe.type);

    // 4. Càlcul de la resolució final
    const myTotal = myRoll + mySizeMod + myTypeMod;
    const friendTotal = friendRoll + friendSizeMod + friendTypeMod;

    let winnerId: number | null = null;
    let loserPivotId: number | null = null;
    let message = '';
    let isVictory = false;

    // Avaluem el guanyador i preparem el paquet de dades pel servidor
    if (myTotal > friendTotal) {
      winnerId = this.myId;
      // Agafem el pivot_id del perdedor perquè és la "instància" concreta a esborrar.
      loserPivotId = this.friendSelectedXuxe.pivot_id;
      message = `Has guanyat! 🏆 Has robat el Xuxemon ${this.friendSelectedXuxe.name}.`;
      isVictory = true;
    } else if (friendTotal > myTotal) {
      winnerId = this.friendId;
      loserPivotId = this.mySelectedXuxe.pivot_id;
      message = `Has perdut! 💀 El rival t'ha robat a ${this.mySelectedXuxe.name}.`;
      isVictory = false;
    } else {
      // Cas de seguretat: En empat, no hi ha transacció de base de dades.
      message = `Empat èpic! ⚔️ Cap Xuxemon ha estat robat.`;
    }

    // Guardem el log per pintar-lo bonic a la UI
    this.battleResult = { myRoll, myTotal, friendRoll, friendTotal, message, isVictory, isTie: myTotal === friendTotal };

    // 5. Cridem a l'API per fer la conseqüència real a la BBDD
    if (winnerId && loserPivotId) {
      this.battleService.transferXuxemon(winnerId, loserPivotId).subscribe({
        error: (err) => console.error("Error al transferir: ", err)
      });
    }
  }

  // ── SISTEMA DE REGLES DE JOC (MODIFICADORS) ───────────────
  
  // Regla d'Evolució: Com més gran, més bonificador base té.
  getSizeMod(size: string): number {
    if (size === 'Mitja') return 1;
    if (size === 'Gran') return 2;
    return 0; // Petit no té bonificador
  }

  // Regla Elemental: Triangulació clàssica tipus Pokémon (Aigua guanya Terra, etc.)
  getTypeMod(myType: string, enemyType: string): number {
    // Avantatge (Super Efectiu)
    if (myType === 'Aigua' && enemyType === 'Terra') return 1;
    if (myType === 'Terra' && enemyType === 'Aire') return 1;
    if (myType === 'Aire' && enemyType === 'Aigua') return 1;
    
    // Desavantatge (Poc Efectiu)
    if (myType === 'Aigua' && enemyType === 'Aire') return -1;
    if (myType === 'Terra' && enemyType === 'Aigua') return -1;
    if (myType === 'Aire' && enemyType === 'Terra') return -1;
    
    // Neutre (Mateix tipus)
    return 0; 
  }

  // ── NAVEGACIÓ ─────────────────────────────────────────────
  exitBattle() {
    // Retornem a la zona segura un cop processat el combat.
    this.router.navigate(['/friends']);
  }
}