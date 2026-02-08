import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../core/api.service';
import { finalize } from 'rxjs/operators';

@Component({
  standalone: true,
  imports: [CommonModule],
  templateUrl: './my-deposits.html',
})
export class MyDeposits implements OnInit {
  deposits: any[] = [];
  loading = true;
  error: string | null = null;

  constructor(private api: ApiService) {}

ngOnInit() {
  this.loading = true;
  this.error = null;

  // Failsafe: never stay stuck on Loading forever
  const timer = setTimeout(() => {
    if (this.loading) {
      this.loading = false;
      this.error = 'Request did not finish (frontend timeout).';
    }
  }, 5000);

  this.api.get<any>('/deposits/me').subscribe({
    next: (res) => {
      clearTimeout(timer);
      this.deposits = res?.data ?? [];
      this.loading = false;
    },
    error: (err) => {
      clearTimeout(timer);
      this.error = err?.error?.message ?? 'Failed to load deposits.';
      this.loading = false;
    },
  });
}

}