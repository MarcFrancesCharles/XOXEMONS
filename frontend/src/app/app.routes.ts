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

export const routes: Routes = [
  { path: '', redirectTo: '/login', pathMatch: 'full' },
  { path: 'login', component: Login },
  { path: 'register', component: Register },
  { path: 'main', component: Main, canActivate: [authGuard] },
  { path: 'profile', component: Profile, canActivate: [authGuard] },
  { path: 'xuxedex', component: Xuxedex, canActivate: [authGuard] },
  { path: 'inventory', component: Inventory, canActivate: [authGuard] },
  { path: 'admin', component: Admin, canActivate: [authGuard, adminGuard] }, // Faltarà l'AdminGuard específic
  { path: '**', redirectTo: '/login' }
];