import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../core/api.service';

type Bar = {
  id: number;
  serial: string;
  status: 'AVAILABLE' | 'RESERVED' | 'WITHDRAWN';
  weight_kg: string;
  vault: string | null;
  reserved_by_withdrawal_id: number | null;
  metal?: { id: number; code: string };
};

@Component({
  selector: 'app-institutional-bars',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './institutional-bars.html',
  styleUrls: ['./institutional-bars.scss'],
})
export class InstitutionalBars implements OnInit {
  available: Bar[] = [];
  all: Bar[] = [];
  selected = new Set<number>();

  loading = false;
  error: string | null = null;

  submitting = false;
  submitError: string | null = null;
  submitSuccess: string | null = null;

  constructor(private api: ApiService) {}

  ngOnInit(): void {
    this.refresh();
  }

  refresh() {
    this.loading = true;
    this.error = null;

    this.api.get<{ success: boolean; data: Bar[] }>('/bars/me/available').subscribe({
      next: (res) => (this.available = res.data),
      error: () => (this.error = 'Failed to load available bars'),
    });

    this.api.get<{ success: boolean; data: Bar[] }>('/bars/me').subscribe({
      next: (res) => {
        this.all = res.data;
        this.loading = false;
      },
      error: () => {
        this.error = 'Failed to load bars';
        this.loading = false;
      },
    });
  }

  toggle(barId: number) {
    if (this.selected.has(barId)) this.selected.delete(barId);
    else this.selected.add(barId);
  }

  isSelected(barId: number) {
    return this.selected.has(barId);
  }

  private selectedBars(): Bar[] {
    const ids = Array.from(this.selected);
    return this.available.filter(b => ids.includes(b.id));
  }

  selectedTotalKg(): string {
    const total = this.selectedBars().reduce((sum, b) => sum + Number(b.weight_kg), 0);
    return total.toFixed(6);
  }

  submitAllocatedWithdrawal() {
    this.submitError = null;
    this.submitSuccess = null;

    const bars = this.selectedBars();
    if (bars.length === 0) {
      this.submitError = 'Select at least one AVAILABLE bar.';
      return;
    }

    const metalCode = bars[0].metal?.code ?? 'GOLD';
    if (!bars.every(b => (b.metal?.code ?? metalCode) === metalCode)) {
      this.submitError = 'Selected bars must be the same metal.';
      return;
    }

    this.submitting = true;

    this.api.post<{ success: boolean; data: any }>('/withdrawals/request-allocated', {
      metal_code: metalCode,
      bar_ids: bars.map(b => b.id),
    }).subscribe({
      next: (res) => {
        this.submitSuccess = `Requested: ${res.data.reference} (status ${res.data.status})`;
        this.selected.clear();
        this.refresh();
      },
      error: (err) => {
        this.submitError = err?.error?.message ?? 'Failed to request allocated withdrawal.';
      },
      complete: () => (this.submitting = false),
    });
  }
}
