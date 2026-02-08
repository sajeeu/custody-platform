import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../core/api.service';

@Component({
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './deposit.html',
})
export class Deposit {
  metal = 'GOLD';
  quantity = 1;

  message: string | null = null;
  loading = false;

  constructor(private api: ApiService) {}

  submit() {
    this.loading = true;
    this.message = null;

    this.api.post('/deposits', {
      metal_code: this.metal,
      quantity_kg: this.quantity,
    }).subscribe({
      next: () => {
        this.message = 'Deposit request submitted successfully.';
        this.loading = false;
      },
      error: err => {
        this.message = err?.error?.message || 'Failed to submit deposit.';
        this.loading = false;
      },
    });
  }

  refresh() {
    // Simple, safe reset
    this.metal = 'GOLD';
    this.quantity = 1;
    this.message = null;
    this.loading = false;
  }
}

/* Explanation (end):
- refresh() only resets local form state
- no API call, no auth risk
- keeps demo fast and predictable */
