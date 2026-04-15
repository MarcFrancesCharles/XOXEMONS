import { Component, OnInit, OnDestroy, AfterViewChecked, ElementRef, ViewChild, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { ChatService } from '../../services/chat.service';
import { AuthService } from '../../services/auth'; // Per saber el nostre ID

@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './chat.html',
  styleUrl: './chat.css'
})
export class Chat implements OnInit, AfterViewChecked {
  private chatService = inject(ChatService);
  private route = inject(ActivatedRoute);
  private authService = inject(AuthService);
  private pollingInterval: any; // <-- NOVA VARIABLE PER GUARDAR L'INTERVAL

  friendId!: number;
  myId!: number;
  messages: any[] = [];
  newMessage: string = '';

  @ViewChild('chatScroll') private chatScrollContainer!: ElementRef;

  ngOnInit() {
    this.friendId = Number(this.route.snapshot.paramMap.get('friendId'));
    this.authService.getProfile().subscribe(user => this.myId = user.id);

    // 1. Carreguem l'historial just al entrar
    this.chatService.loadMessages(this.friendId).subscribe();
    this.chatService.messages$.subscribe(msgs => this.messages = msgs);

    // 2. POLLING: Demanem els missatges cada 2 segons (2000ms) perquè sigui "instantani"
    this.pollingInterval = setInterval(() => {
      this.chatService.loadMessages(this.friendId).subscribe();
    }, 2000);
  }

  // 3. ATENCIÓ: Quan tanquem el xat o canviem de pàgina, matem l'interval!
  ngOnDestroy() {
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval);
    }
  }

  // Fa scroll cap a baix automàticament
  ngAfterViewChecked() {
    this.scrollToBottom();
  }

  scrollToBottom(): void {
    try {
      this.chatScrollContainer.nativeElement.scrollTop = this.chatScrollContainer.nativeElement.scrollHeight;
    } catch(err) { }
  }

  sendMessage() {
    if (!this.newMessage.trim()) return;

    const content = this.newMessage;
    this.newMessage = ''; // Netegem l'input ràpidament perquè sembli instantani

    this.chatService.sendMessage(this.friendId, content).subscribe({
      next: () => {
        // Eliminem la simulació. El missatge ja s'ha afegit a la pantalla des del servei!
      },
      error: (err) => alert('Error enviant: ' + err.error.message)
    });
  }

  // Funció per calcular el temps relatiu ("fa 5 min")
  timeAgo(dateString: string): string {
    const messageDate = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - messageDate.getTime();
    const diffMins = Math.floor(diffMs / 60000);

    if (diffMins < 1) return 'Ara mateix';
    if (diffMins < 60) return `Fa ${diffMins} min`;
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `Fa ${diffHours} h`;
    return messageDate.toLocaleDateString();
  }
}