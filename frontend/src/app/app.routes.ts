/**
 * ============================================================
 * FITXER: src/app/app.routes.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Defineix el mapa de navegació complet de l'aplicació SPA.
 *   Cada entrada de la llista routes associa una URL amb el
 *   component que s'ha de renderitzar. És el "directori de
 *   pàgines" del frontend: si una ruta no és aquí, no existeix.
 *
 * MAPA DE CONNEXIONS:
 *   → Consumit per: app.config.ts (via provideRouter)
 *   → Protegit per: authGuard (guards/auth-guard.ts) → verifica token JWT
 *   → Protegit per: adminGuard (guards/admin.guard.ts) → verifica rol 'robot'
 *   → Renderitza: Login, Register, Main, Profile, Xuxedex, Inventory,
 *     Admin, Friends, Chat, Battle (un component per ruta)
 *   → Les rutes amb paràmetre (:friendId) passen l'ID a través
 *     de ActivatedRoute als components Chat i Battle.
 * ============================================================
 */

import { Routes } from '@angular/router';
import { Login } from './components/login/login';
import { Register } from './components/register/register';
import { Main } from './components/main/main';
import { Profile } from './components/profile/profile';
import { Xuxedex } from './components/xuxedex/xuxedex';
import { Inventory } from './components/inventory/inventory';
import { Admin } from './components/admin/admin';
import { authGuard } from './guards/auth-guard';
import { adminGuard } from './guards/admin.guard';
import { Friends } from './components/friends/friends';
import { Chat } from './components/chat/chat';
import { Battle } from './components/battle/battle';

export const routes: Routes = [
  // Redirecció per defecte: URL arrel redirigeix sempre al login.
  // pathMatch: 'full' evita que coincideixi amb qualsevol URL que
  // comenci per '' (és a dir, totes).
  { path: '', redirectTo: '/login', pathMatch: 'full' },

  // Rutes PÚBLIQUES: accessibles sense token JWT.
  { path: 'login', component: Login },
  { path: 'register', component: Register },

  // Rutes PROTEGIDES: authGuard comprova que hi hagi token al localStorage.
  // Si no n'hi ha, redirigeix a /login automàticament.
  { path: 'main', component: Main, canActivate: [authGuard] },
  { path: 'profile', component: Profile, canActivate: [authGuard] },
  { path: 'xuxedex', component: Xuxedex, canActivate: [authGuard] },
  { path: 'inventory', component: Inventory, canActivate: [authGuard] },

  // Ruta de l'admin: té DOBLE protecció.
  // authGuard → verifica que hi ha sessió activa.
  // adminGuard → verifica que l'usuari té el rol 'robot'.
  // Si és un jugador normal, el redirigeix a /main.
  { path: 'admin', component: Admin, canActivate: [authGuard, adminGuard] },

  { path: 'friends', component: Friends, canActivate: [authGuard] },

  // Rutes amb paràmetre dinàmic: :friendId és l'ID de l'amic.
  // El component llegirà aquest ID via ActivatedRoute per fer les
  // peticions API correctes (missatges del xat, Xuxemons de batalla).
  { path: 'chat/:friendId', component: Chat, canActivate: [authGuard] },
  { path: 'battle/:friendId', component: Battle, canActivate: [authGuard] },

  // Wildcard: qualsevol URL no reconeguda va al login.
  // Evita pàgines en blanc o errors 404 a l'usuari.
  { path: '**', redirectTo: '/login' }
];