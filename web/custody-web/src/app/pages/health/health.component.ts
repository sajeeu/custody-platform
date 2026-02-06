import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService, HealthResponse } from '../../services/api.service';

import { MatCardModule } from '@angular/material/card';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-health',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatProgressSpinnerModule],
  templateUrl: './health.component.html',
  styleUrls: ['./health.component.scss'],
})
export class HealthComponent implements OnInit {
  loading = true;
  data: HealthResponse | null = null;
  error: string | null = null;

  constructor(private api: ApiService) {}

ngOnInit(): void {
  this.api
    .health()
    .pipe(finalize(() => (this.loading = false)))
    .subscribe({
      next: (res) => {
        console.log('Health response:', res);
        this.data = res;
      },
      error: (err) => {
        console.error('Health error:', err);
        this.error =
          err?.error?.message ??
          err?.message ??
          'Failed to call API (check browser console)';
      },
    });
}
}
