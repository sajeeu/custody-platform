import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../core/api.service';

type Withdrawal = {
  id: number;
  reference: string;
  status: string;
  storage_type: string;
  quantity_kg: string;
  created_at: string;
  meta?: any;
};

@Component({
  selector: 'app-institutional-withdrawals',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './institutional-withdrawals.html',
  styleUrls: ['./institutional-withdrawals.scss'],
})
export class InstitutionalWithdrawals implements OnInit {
  loading = false;
  error: string | null = null;

  items: Withdrawal[] = [];

  constructor(private api: ApiService) {}

  ngOnInit(): void {
    this.refresh();
  }

  refresh() {
    this.loading = true;
    this.error = null;

    this.api.get<{ success: boolean; data: Withdrawal[] }>('/withdrawals/me').subscribe({
      next: (res) => {
        this.items = res.data ?? [];
        this.loading = false;
      },
      error: (err) => {
        this.error = err?.error?.message ?? 'Failed to load your withdrawals.';
        this.loading = false;
      },
    });
  }

  barIdsLabel(w: Withdrawal): string {
    const ids = w?.meta?.bar_ids;
    if (!Array.isArray(ids) || ids.length === 0) return '-';
    return ids.join(', ');
  }
}
