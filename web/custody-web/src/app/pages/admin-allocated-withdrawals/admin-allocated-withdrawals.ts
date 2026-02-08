import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
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
  imports: [CommonModule, FormsModule],
  templateUrl: './admin-allocated-withdrawals.html',
  styleUrls: ['./admin-allocated-withdrawals.scss'],
})
export class AdminAllocatedWithdrawals implements OnInit {
  loading = false;
  error: string | null = null;
  message: string | null = null;

  items: Withdrawal[] = [];
  actingId: number | null = null;

  // Inline reject UI state
  rejectOpenId: number | null = null;
  rejectReason = '';

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

  toggleReject(id: number) {
    // toggle open/close
    if (this.rejectOpenId === id) {
      this.cancelReject();
      return;
    }
    this.rejectOpenId = id;
    this.rejectReason = '';
    this.error = null;
    this.message = null;
  }

  cancelReject() {
    this.rejectOpenId = null;
    this.rejectReason = '';
  }

  reject(w: Withdrawal, reason: string) {
    this.error = null;
    this.message = null;

    const trimmed = (reason ?? '').trim();
    if (trimmed.length < 5) {
      this.error = 'Reject reason must be at least 5 characters.';
      return;
    }

    this.actingId = w.id;

    this.api.post<{ success: boolean; data: Withdrawal }>(`/withdrawals/${w.id}/reject`, {
      reason: trimmed,
    }).subscribe({
      next: (res) => {
        this.message = `Rejected: ${res.data.reference}`;
        this.actingId = null;
        this.cancelReject();
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
