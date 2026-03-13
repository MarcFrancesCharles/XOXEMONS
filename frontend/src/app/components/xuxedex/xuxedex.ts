import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms'; // Necessari pels filtres
import { XuxemonService } from '../../services/xuxemon.service';

@Component({
  selector: 'app-xuxedex',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule], // Importem el FormsModule
  templateUrl: './xuxedex.html',
  styleUrl: './xuxedex.css'
})
export class Xuxedex implements OnInit {
  private xuxemonService = inject(XuxemonService);
  
  // Dades originals des de la BBDD
  xuxemons: any[] = [];
  
  // Dades filtrades per mostrar a l'HTML
  filteredXuxemons: any[] = [];

  // Variables per als filtres
  filterType: string = '';
  filterSize: string = '';

  ngOnInit() {
    // 1. Demanem les dades al backend
    this.xuxemonService.loadXuxedex().subscribe();

    // 2. Ens subscrivim al BehaviorSubject
    this.xuxemonService.xuxedex$.subscribe(data => {
      this.xuxemons = data;
      this.applyFilters(); // Apliquem els filtres inicials (mostra tot)
    });
  }

  // Funció que s'executa cada vegada que l'usuari toca els select de filtres
  applyFilters() {
    this.filteredXuxemons = this.xuxemons.filter(xuxe => {
      // Comprovem si coincideix el tipus (o si no hi ha cap filtre seleccionat)
      const matchType = this.filterType ? xuxe.type === this.filterType : true;
      // Comprovem si coincideix la mida (o si no hi ha cap filtre seleccionat)
      const matchSize = this.filterSize ? xuxe.size === this.filterSize : true;
      
      return matchType && matchSize;
    });
  }
}