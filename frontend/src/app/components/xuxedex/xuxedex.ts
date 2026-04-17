/**
 * ============================================================
 * FITXER: src/app/components/xuxedex/xuxedex.ts
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 * Actua com el centre de comandament (Hub) de les criatures de
 * l'usuari. És un component d'alta interactivitat que permet
 * visualitzar la col·lecció completa, aplicar filtres de cerca en
 * memòria per rendiment, i obrir modals per interactuar amb els
 * Xuxemons (alimentar per evolucionar o vacunar per curar malalties).
 *
 * MAPA DE CONNEXIONS:
 * → Serveis: XuxemonService (gestió de criatures i interaccions) 
 * i InventoryService (lectura de la motxilla).
 * → Mòduls: FormsModule és clau aquí per enllaçar els filtres
 * (ngModel) i reaccionar als canvis de l'usuari a l'instant.
 * → Interfície: Utilitza @HostListener per interceptar esdeveniments
 * globals del teclat (accessibilitat).
 * ============================================================
 */

import { Component, OnInit, inject, HostListener } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms'; 
import { XuxemonService } from '../../services/xuxemon.service';
import { InventoryService } from '../../services/inventory.service';

@Component({
  selector: 'app-xuxedex',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './xuxedex.html',
  styleUrl: './xuxedex.css'
})
export class Xuxedex implements OnInit {
  // ── INJECCIÓ DE DEPENDÈNCIES ──────────────────────────────
  private xuxemonService = inject(XuxemonService);
  private inventoryService = inject(InventoryService);
  
  // ── ESTAT: DADES ORIGINALS I FILTRADES ────────────────────
  // Mantenim l'array original intacte com a font de veritat (Cache local).
  xuxemons: any[] = [];
  
  // Array derivat que s'exposa al template HTML. 
  // PER QUÈ: Filtrar sobre una còpia evita haver de fer peticions GET
  // al servidor cada cop que l'usuari canvia el desplegable, estalviant
  // ample de banda i oferint una experiència instantània.
  filteredXuxemons: any[] = [];
  
  // Emmagatzematge categoritzat de l'inventari per alimentar els formularis dels modals
  inventoryXuxes: any[] = [];
  inventoryVaccines: any[] = [];

  // Variables lligades amb [(ngModel)] als `<select>` de l'HTML
  filterType: string = '';
  filterSize: string = '';

  // ── ESTAT: CONTROLS DELS MODALS ───────────────────────────
  selectedXuxemon: any = null;
  selectedXuxeId: number | null = null;
  isModalOpen: boolean = false;
  evolutionPreview: boolean = false; // Flag per a orquestrar animacions CSS al DOM

  isVaccinateModalOpen: boolean = false;
  selectedVaccineId: number | null = null;

  // ── CICLE DE VIDA I REACTIVITAT ───────────────────────────
  ngOnInit() {
    // 1. Disparem les peticions HTTP inicials per hidratar els BehaviorSubjects
    this.xuxemonService.loadXuxedex().subscribe();
    this.inventoryService.loadInventory().subscribe();

    // 2. Subscripció a l'estat global dels Xuxemons
    this.xuxemonService.xuxedex$.subscribe(data => {
      this.xuxemons = data;
      // Sempre que les dades originals canvien (ex: després d'evolucionar),
      // forcem un recàlcul dels filtres per mantenir la coherència visual.
      this.applyFilters(); 
    });

    // 3. Subscripció a l'inventari global (Motxilla)
    this.inventoryService.inventory$.subscribe(items => {
      // Filtrem i descartem objectes esgotats (quantity > 0) per no mostrar 
      // opcions inservibles als desplegables d'alimentació/vacunació.
      this.inventoryXuxes = items.filter(item => item.type === 'xuxe' && item.pivot.quantity > 0);
      this.inventoryVaccines = items.filter(item => item.type === 'vacuna' && item.pivot.quantity > 0);
    });
  }

  // ── LÒGICA DE NEGOCI: FILTRATGE ───────────────────────────
  applyFilters() {
    // Avalua cada Xuxemon contra els criteris seleccionats.
    // L'ús d'operadors ternaris garanteix que si un filtre està buit (''), 
    // es considera 'true' (passa el filtre), actuant com un "Mostrar-ho tot".
    this.filteredXuxemons = this.xuxemons.filter(xuxe => {
      const matchType = this.filterType ? xuxe.type === this.filterType : true;
      const matchSize = this.filterSize ? xuxe.size === this.filterSize : true;
      
      return matchType && matchSize;
    });
  }
  
  // ── LÒGICA DE NEGOCI: ALIMENTACIÓ I EVOLUCIÓ ──────────────
  openFeedModal(xuxemon: any) {
    this.selectedXuxemon = xuxemon;
    this.selectedXuxeId = null;
    this.evolutionPreview = false;
    this.isModalOpen = true;
  }

  closeModal() {
    this.isModalOpen = false;
    this.selectedXuxemon = null;
  }

  // Càlcul de feedback per a l'usuari: Quantes xuxes falten?
  // PER QUÈ: Fer aquest càlcul al frontend (tot i que el backend té la veritat absoluta)
  // permet donar feedback visual instantani dins del modal abans de fer cap petició.
  getXuxesToEvolve(): number {
    if (!this.selectedXuxemon) return 0;
    
    const food = this.selectedXuxemon.pivot.food_eaten || 0;
    let required = 0;
    
    // Regles de negoci base de les evolucions
    if (this.selectedXuxemon.size === 'Petit') required = 3;
    if (this.selectedXuxemon.size === 'Mitja') required = 5;

    // Modificador de dificultat dinàmic per malaltia
    if (this.selectedXuxemon.pivot.disease === 'Bajón de azúcar') {
      required += 2;
    }

    if (required === 0) return 0; // Talla Gran (top level) ja no evoluciona
    return Math.max(0, required - food);
  }

  feed() {
    if (!this.selectedXuxemon || !this.selectedXuxeId) return;

    this.xuxemonService.feedXuxemon(this.selectedXuxemon.pivot.id, this.selectedXuxeId).subscribe({
      next: (response) => {
        if (response.evolved) {
          // Bloquegem el tancament automàtic per donar temps a l'animació
          // d'evolució (CSS) a reproduir-se, millorant el 'game feel'.
          this.evolutionPreview = true;
          setTimeout(() => {
            this.closeModal();
            alert('🎉 ' + response.message);
          }, 1500); 
        } else {
          this.closeModal();
          // Casuística: El xuxemon ha menjat però s'ha posat malalt.
          if (response.disease) {
            alert('🦠 Oh no! El teu Xuxemon ha contret una malaltia: ' + response.disease);
          }
        }
        // Després de qualsevol acció de consum, demanem al servei de l'inventari
        // que refresqui les dades perquè la motxilla global s'actualitzi automàticament.
        this.inventoryService.loadInventory().subscribe();
      },
      error: (err) => {
        alert('Error: ' + err.error.message);
      }
    });
  }

  // ── LÒGICA DE NEGOCI: VACUNACIÓ ───────────────────────────
  openVaccinateModal(xuxemon: any) {
    this.selectedXuxemon = xuxemon;
    this.selectedVaccineId = null;
    this.isVaccinateModalOpen = true;
  }

  closeVaccinateModal() {
    this.isVaccinateModalOpen = false;
    this.selectedXuxemon = null;
  }

  vaccinate() {
    if (!this.selectedXuxemon || !this.selectedVaccineId) return;

    this.xuxemonService.vaccinateXuxemon(this.selectedXuxemon.pivot.id, this.selectedVaccineId).subscribe({
      next: (response) => {
        alert('✨ ' + response.message);
        this.closeVaccinateModal();
        this.inventoryService.loadInventory().subscribe();
      },
      error: (err) => {
        alert('Error: ' + err.error.message);
      }
    });
  }

  // ── EXPERIÈNCIA D'USUARI (UX) AVANÇADA ────────────────────
  // Escolta l'esdeveniment físic de la tecla 'Escape' a nivell de document.
  // PER QUÈ: Els usuaris d'escriptori esperen poder tancar les finestres modals 
  // prement ESC en lloc de buscar la 'X' amb el ratolí. Això millora dràsticament l'accessibilitat.
  @HostListener('document:keydown.escape')
  handleKeyboardEvent() {
    if (this.isModalOpen) {
      this.closeModal();
    }
    if (this.isVaccinateModalOpen) {
      this.closeVaccinateModal();
    }
  }
}