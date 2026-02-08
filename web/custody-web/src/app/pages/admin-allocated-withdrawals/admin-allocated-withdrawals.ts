import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../core/api.service';

type Bar = {
  id: number;
  serial: string;
  status: string;
  weight_kg: string;
  vault: string | null;
};

type Withdrawal = {
  id: number;
  reference: string;
  status: string;
  storage_type: string;
  quantity_kg: string;
  created_at: string;
  meta?: any;
  bars?: Bar[];
};

@Component({
  selector: 'app-admin-allocated-withdrawals',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './admin-allocated-withdrawals.html',
  styleUrls: ['./admin-allocated-withdrawals.scss'],
})
export class AdminAllocatedWithdrawals implements OnInit {
  loading = false;
  error: string | null = null;
  message: string | null = null;

  items: Withdrawal[] = [];
  actingId: number | null = null;

  constructor(private api: ApiService) {}

  ngOnInit(): void {
    this.refresh();
  }

  refresh() {
    this.loading = true;
    this.error = null;
    this.message = null;

    this.api.get<{ success: boolean; data: Withdrawal[] }>(
      '/admin/withdrawals/allocated?status=PENDING'
    ).subscribe({
      next: (res) => {
        this.items = res.data ?? [];
        this.loading = false;
      },
      error: (err) => {
        this.error = err?.error?.message ?? 'Failed to load pending allocated withdrawals.';
        this.loading = false;
      },
    });
  }

  approve(w: Withdrawal) {
    this.error = null;
    this.message = null;
    this.actingId = w.id;

    this.api.post<{ success: boolean; data: Withdrawal }>(`/withdrawals/${w.id}/approve`, {}).subscribe({
      next: (res) => {
        this.message = `Approved: ${res.data.reference}`;
        this.actingId = null;
        this.refresh();
      },
      error: (err) => {
        this.error = err?.error?.message ?? 'Approve failed.';
        this.actingId = null;
      },
    });
  }

  reject(w: Withdrawal) {
    this.error = null;
    this.message = null;

    const reason = window.prompt('Reject reason (min 5 chars):');
    if (!reason || reason.trim().length < 5) return;

    this.actingId = w.id;

    this.api.post<{ success: boolean; data: Withdrawal }>(`/withdrawals/${w.id}/reject`, {
      reason: reason.trim(),
    }).subscribe({
      next: (res) => {
        this.message = `Rejected: ${res.data.reference}`;
        this.actingId = null;
        this.refresh();
      },
      error: (err) => {
        this.error = err?.error?.message ?? 'Reject failed.';
        this.actingId = null;
      },
    });
  }

  barIds(w: Withdrawal): string {
    const ids = w?.meta?.bar_ids;
    return Array.isArray(ids) && ids.length ? ids.join(', ') : '-';
  }
}
