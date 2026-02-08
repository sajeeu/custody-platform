import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-demo-deposit',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './demo-deposit.html',
})
export class DemoDeposit {
  metal = 'GOLD';
  quantity = 1;
  success: string | null = null;

  deposit() {
    const current = Number(localStorage.getItem('demo_balance') || '0');
    const updated = current + Number(this.quantity);

    localStorage.setItem('demo_balance', String(updated));

    this.success = `Deposited ${this.quantity} kg of ${this.metal}. Demo balance: ${updated} kg`;
  }

  get balance() {
    return localStorage.getItem('demo_balance') || '0';
  }
}
