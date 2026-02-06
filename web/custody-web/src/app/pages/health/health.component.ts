import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService, HealthResponse } from '../../services/api.service';

import { MatCardModule } from '@angular/material/card';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

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
    this.api.health().subscribe({
      next: (res) => {
        this.data = res;
        this.loading = false;
      },
      error: (err) => {
        // Helpful message even for CORS
        this.error =
          err?.error?.message ??
          err?.message ??
          'Failed to call API (check browser console)';
        this.loading = false;
      },
    });
  }
}
