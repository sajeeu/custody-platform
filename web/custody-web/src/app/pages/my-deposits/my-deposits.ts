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

  constructor(private api: ApiService) {}

  ngOnInit() {
    // First attempt (may race on hard refresh)
    this.load();

    // Fallback attempt after session is definitely ready
    setTimeout(() => {
      if (!this.deposits.length) {
        this.load();
      }
    }, 800);
  }

  load() {
    this.api.get<any>('/deposits/me').subscribe({
      next: (res) => {
        this.deposits = Array.isArray(res?.data) ? res.data : [];
      },
      error: (err) => {
        this.error = err?.error?.message ?? 'Failed to load deposits.';
      },
    });
  }
}

