import { Routes } from '@angular/router';
import { Login } from './components/login/login';
import { Register } from './components/register/register';
import { Main } from './components/main/main';
import { Profile } from './components/profile/profile';

export const routes: Routes = [
  { path: '', redirectTo: '/login', pathMatch: 'full' },
  { path: 'login', component: Login },
  { path: 'register', component: Register },
  { path: 'main', component: Main },
  { path: 'profile', component: Profile },
  { path: '**', redirectTo: '/login' }
];