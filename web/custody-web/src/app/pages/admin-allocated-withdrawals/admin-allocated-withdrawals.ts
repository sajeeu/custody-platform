import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../core/api.service';

type Bar = { id: number; serial: string; weight_kg: string; vault: string | null; status: string };
type Withdrawal = {
  id: number;
  reference: string;
  status: string;
  storage_type: string;
  quantity_kg: string;
  created_at: string;
  bars?: Bar[];
  meta?: any;
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
  items: Withdrawal[] = [];

  actionMsg: string | null = null;

  constructor(private api: ApiService) {}

  ngOnInit(): void {
    this.refresh();
  }

  refresh() {
    this.loading = true;
    this.error = null;
    this.actionMsg = null;

    this.api.get<{ success: boolean; data: Withdrawal[] }>('/admin/withdrawals/allocated?status=PENDING').subscribe({
      next: (res) => {
        this.items = res.data;
        this.loading = false;
      },
      error: (err) => {
        this.error = err?.error?.message ?? 'Failed to load allocated withdrawal queue.';
        this.loading = false;
      },
    });
  }

  approve(id: number) {
    this.actionMsg = null;
    this.api.post<{ success: boolean; data: any }>(`/withdrawals/${id}/approve`, {}).subscribe({
      next: (res) => {
        this.actionMsg = `Approved ${res.data.reference} (COMPLETED)`;
        this.refresh();
      },
      error: (err) => {
        this.error = err?.error?.message ?? 'Approve failed.';
      },
    });
  }

  reject(id: number) {
    const reason = window.prompt('Reject reason (min 5 chars):');
    if (!reason || reason.trim().length < 5) return;

    this.actionMsg = null;
    this.api.post<{ success: boolean; data: any }>(`/withdrawals/${id}/reject`, { reason }).subscribe({
      next: (res) => {
        this.actionMsg = `Rejected ${res.data.reference}`;
        this.refresh();
      },
      error: (err) => {
        this.error = err?.error?.message ?? 'Reject failed.';
      },
    });
  }
}
