import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class ChatService {
  private apiUrl = 'http://localhost:8000/api/chat';
  private http = inject(HttpClient);

  // Subject per mantenir l'estat dels missatges en temps real
  private messagesSubject = new BehaviorSubject<any[]>([]);
  public messages$ = this.messagesSubject.asObservable();

  // Carregar historial de la base de dades
  loadMessages(friendId: number): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/${friendId}`).pipe(
      tap(messages => this.messagesSubject.next(messages))
    );
  }

  // Enviar missatge real al backend
  sendMessage(friendId: number, content: string): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/${friendId}`, { content }).pipe(
      tap((res) => {
        // Afegim el missatge enviat a la llista actual sense recarregar-ho tot
        const currentMessages = this.messagesSubject.getValue();
        this.messagesSubject.next([...currentMessages, res.data]);
      })
    );
  }

}