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
  private battleService = inject(BattleService);
  private authService = inject(AuthService);
  private route = inject(ActivatedRoute);
  private router = inject(Router);

  myId!: number;
  friendId!: number;

  myXuxemons: any[] = [];
  friendXuxemons: any[] = [];

  mySelectedXuxe: any = null;
  friendSelectedXuxe: any = null;

  battleStarted = false;
  isRolling = false;
  battleResult: any = null;

  ngOnInit() {
    this.friendId = Number(this.route.snapshot.paramMap.get('friendId'));
    this.authService.getProfile().subscribe(user => this.myId = user.id);

    this.battleService.getBattleData(this.friendId).subscribe({
      next: (data) => {
        this.myXuxemons = data.me;
        this.friendXuxemons = data.friend;
      },
      error: (err) => alert('Error carregant la batalla: ' + err.error.message)
    });
  }

  selectMyXuxemon(xuxe: any) {
    if (this.battleStarted) return;
    this.mySelectedXuxe = xuxe;
    
    // Si l'amic no té Xuxemons sans, donem un error amigable
    if (this.friendXuxemons.length === 0) {
      alert("Aquest amic no té cap Xuxemon sa per lluitar avui! 😢");
      this.mySelectedXuxe = null;
      return;
    }
    
    const randomIndex = Math.floor(Math.random() * this.friendXuxemons.length);
    this.friendSelectedXuxe = this.friendXuxemons[randomIndex];
  }

  startBattle() {
    if (!this.mySelectedXuxe || !this.friendSelectedXuxe) return;
    
    this.battleStarted = true;
    this.isRolling = true;

    // Simulem els daus amb el temps especificat al Nivell 6
    setTimeout(() => {
      this.isRolling = false;
      this.calculateWinner();
    }, 3000);
  }

  calculateWinner() {
    const myRoll = Math.floor(Math.random() * 6) + 1;
    const friendRoll = Math.floor(Math.random() * 6) + 1;

    const mySizeMod = this.getSizeMod(this.mySelectedXuxe.size);
    const friendSizeMod = this.getSizeMod(this.friendSelectedXuxe.size);

    const myTypeMod = this.getTypeMod(this.mySelectedXuxe.type, this.friendSelectedXuxe.type);
    const friendTypeMod = this.getTypeMod(this.friendSelectedXuxe.type, this.mySelectedXuxe.type);

    const myTotal = myRoll + mySizeMod + myTypeMod;
    const friendTotal = friendRoll + friendSizeMod + friendTypeMod;

    let winnerId: number | null = null;
    let loserPivotId: number | null = null;
    let message = '';
    let isVictory = false;

    if (myTotal > friendTotal) {
      winnerId = this.myId;
      loserPivotId = this.friendSelectedXuxe.pivot_id;
      message = `Has guanyat! 🏆 Has robat el Xuxemon ${this.friendSelectedXuxe.name}.`;
      isVictory = true;
    } else if (friendTotal > myTotal) {
      winnerId = this.friendId;
      loserPivotId = this.mySelectedXuxe.pivot_id;
      message = `Has perdut! 💀 El rival t'ha robat a ${this.mySelectedXuxe.name}.`;
      isVictory = false;
    } else {
      message = `Empat èpic! ⚔️ Cap Xuxemon ha estat robat.`;
    }

    this.battleResult = { myRoll, myTotal, friendRoll, friendTotal, message, isVictory, isTie: myTotal === friendTotal };

    if (winnerId && loserPivotId) {
      this.battleService.transferXuxemon(winnerId, loserPivotId).subscribe({
        error: (err) => console.error("Error al transferir: ", err)
      });
    }
  }

  getSizeMod(size: string): number {
    if (size === 'Mitja') return 1;
    if (size === 'Gran') return 2;
    return 0; // Petit
  }

  getTypeMod(myType: string, enemyType: string): number {
    if (myType === 'Aigua' && enemyType === 'Terra') return 1;
    if (myType === 'Terra' && enemyType === 'Aire') return 1;
    if (myType === 'Aire' && enemyType === 'Aigua') return 1;
    
    // Desavantatge
    if (myType === 'Aigua' && enemyType === 'Aire') return -1;
    if (myType === 'Terra' && enemyType === 'Aigua') return -1;
    if (myType === 'Aire' && enemyType === 'Terra') return -1;
    
    return 0; 
  }

  exitBattle() {
    this.router.navigate(['/friends']);
  }
}