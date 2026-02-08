import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../core/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './login.html',
  styleUrls: ['./login.scss'],
})
export class Login {
  email = 'admin@example.com';
  password = 'Password123!';
  loading = false;
  error: string | null = null;

  constructor(private auth: AuthService, private router: Router) {}

  submit() {
    this.loading = true;
    this.error = null;

    this.auth.login(this.email, this.password).subscribe({
      next: () => {
        this.auth.me().subscribe({
          next: (res) => {
            const role = res.data.user.role;

            if (role === 'ADMIN') {
              this.router.navigate(['/admin/allocated-withdrawals']);
            } else {
              this.router.navigate(['/institutional/bars']);
            }
          },
          error: () => {
            this.error = 'Login succeeded but failed to load profile.';
            this.loading = false;
          },
        });
      },
      error: (err) => {
        this.error = err?.error?.message ?? 'Login failed';
        this.loading = false;
      },
    });
  }
}
