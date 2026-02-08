import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../core/api.service';

type Withdrawal = {
  id: number;
  reference: string;
  storage_type: string;
  status: string;
  quantity_kg: string;
  created_at: string;
  meta: any;
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
  withdrawals: Withdrawal[] = [];

  constructor(private api: ApiService) {}

  ngOnInit(): void {
    this.refresh();
  }

  refresh() {
    this.loading = true;
    this.error = null;

    this.api.get<{ success: boolean; data: Withdrawal[] }>('/withdrawals/me').subscribe({
      next: (res) => {
        this.withdrawals = res.data
          .filter(w => w.storage_type === 'ALLOCATED')
          .sort((a, b) => b.id - a.id);
        this.loading = false;
      },
      error: () => {
        this.error = 'Failed to load withdrawals.';
        this.loading = false;
      },
    });
  }
}
