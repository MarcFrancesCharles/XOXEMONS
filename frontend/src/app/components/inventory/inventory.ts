import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { InventoryService } from '../../services/inventory.service';

@Component({
  selector: 'app-inventory',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './inventory.html',
  styleUrl: './inventory.css'
})
export class Inventory implements OnInit {
  private inventoryService = inject(InventoryService);
  
  // Array fix de 20 posicions (null significa espai buit)
  slots: any[] = new Array(20).fill(null);

  ngOnInit() {
    // 1. Demanem les dades al backend
    this.inventoryService.loadInventory().subscribe();

    // 2. Ens subscrivim al BehaviorSubject (reactivitat pura)
    this.inventoryService.inventory$.subscribe(items => {
      this.calculateSlots(items);
    });
  }

  // Algoritme per omplir els 20 espais
  calculateSlots(items: any[]) {
    this.slots = new Array(20).fill(null); // Reiniciem la motxilla
    let currentSlot = 0;

    for (let item of items) {
      let qty = item.pivot.quantity; // La quantitat que té l'usuari (ve de la BBDD)

      if (item.is_stackable) {
        // Objectes apilables (màxim 5 per espai)
        while (qty > 0 && currentSlot < 20) {
          let chunk = qty > 5 ? 5 : qty;
          this.slots[currentSlot] = { ...item, displayQuantity: chunk };
          qty -= chunk;
          currentSlot++;
        }
      } else {
        // Objectes no apilables (ocupen 1 espai cadascun)
        while (qty > 0 && currentSlot < 20) {
          this.slots[currentSlot] = { ...item, displayQuantity: 1 };
          qty--;
          currentSlot++;
        }
      }
    }
  }
}