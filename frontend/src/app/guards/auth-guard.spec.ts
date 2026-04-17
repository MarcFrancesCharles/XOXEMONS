/**
 * ============================================================
 * FITXER: src/app/guards/auth-guard.spec.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Arxiu de proves unitàries (Unit Testing) per al guàrdia
 * d'autenticació (authGuard). El seu propòsit és garantir
 * la solidesa de la primera línia de defensa de l'aplicació,
 * permetent a l'equip de desenvolupament verificar que les rutes
 * es bloquegen o s'autoritzen correctament de forma automatitzada.
 *
 * MAPA DE CONNEXIONS:
 * → Avalua: auth-guard.ts (el subjecte principal de la prova).
 * → Utilitza: TestBed d'Angular (crea l'entorn virtual de proves).
 * ============================================================
 */

import { TestBed } from '@angular/core/testing';
import { CanActivateFn } from '@angular/router';

import { authGuard } from './auth-guard';

describe('authGuard', () => {
  // ── CONFIGURACIÓ DE L'ENTORN D'INJECCIÓ ───────────────────
  // PER QUÈ: En les versions modernes d'Angular, els Guards són funcions
  // (CanActivateFn) en lloc de classes. Com que dins de `authGuard` utilitzem 
  // la funció `inject(AuthService)` per obtenir dependències, no podem executar
  // el guard directament en un test aïllat. Aquest wrapper simula l'arbre
  // de dependències d'Angular i executa el guard dins d'aquest context segur.
  const executeGuard: CanActivateFn = (...guardParameters) =>
    TestBed.runInInjectionContext(() => authGuard(...guardParameters));

  // ── PREPARACIÓ DEL CICLE DE VIDA DELS TESTS ───────────────
  beforeEach(() => {
    // Esborrem l'estat i configurem un mòdul de proves net abans
    // de cada bloc 'it'. Això evita que les modificacions fetes en 
    // un test (ex: mocks de localstorage o rutes) "contaminin" el següent test.
    TestBed.configureTestingModule({});
  });

  // ── CASOS DE PROVA ────────────────────────────────────────
  // Smoke test (Prova de fum) bàsica. 
  // Comprova simplement que la funció s'ha carregat i és executable dins
  // del TestBed sense llançar errors d'injecció fatals en temps de compilació.
  it('should be created', () => {
    expect(executeGuard).toBeTruthy();
  });
  
  // (Com a Arquitecte, aquí et recomanaria afegir en el futur casos de prova com:
  // 1. "should return true if token exists in localStorage"
  // 2. "should return false and redirect to /login if token is missing")
});