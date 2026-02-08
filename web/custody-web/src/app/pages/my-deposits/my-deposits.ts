import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../core/api.service';

@Component({
  standalone: true,
  imports: [CommonModule],
  templateUrl: './my-deposits.html',
  styleUrls: ['./my-deposits.scss'],
})
export class MyDeposits implements OnInit {
  deposits: any[] = [];
  error: string | null = null;
  message: string | null = null;
  loading = false;

  constructor(private api: ApiService) {}

  ngOnInit() {
    // First attempt
    this.load();

    // Fallback attempt after session is definitely ready
    setTimeout(() => {
      if (!this.error && !this.deposits.length && !this.loading) {
        this.load();
      }
    }, 800);
  }

  refresh() {
    this.load(true);
  }

  load(showMessage = false) {
    this.loading = true;
    this.error = null;
    if (showMessage) this.message = null;

    this.api.get<any>('/deposits/me').subscribe({
      next: (res) => {
        this.deposits = Array.isArray(res?.data) ? res.data : [];
        this.loading = false;
        if (showMessage) this.message = 'Updated.';
      },
      error: (err) => {
        this.error = err?.error?.message ?? 'Failed to load deposits.';
        this.loading = false;
      },
    });
  }
}
