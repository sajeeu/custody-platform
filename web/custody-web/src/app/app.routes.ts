import { Routes } from '@angular/router';
import { Login } from './pages/login/login';

export const routes: Routes = [
  { path: 'login', component: Login },

  {
    path: 'institutional/bars',
    loadComponent: () =>
      import('./pages/institutional-bars/institutional-bars')
        .then(m => m.InstitutionalBars),
  },
  {
    path: 'institutional/withdrawals',
    loadComponent: () =>
      import('./pages/institutional-withdrawals/institutional-withdrawals')
        .then(m => m.InstitutionalWithdrawals),
  },
  {
    path: 'admin/allocated-withdrawals',
    loadComponent: () =>
      import('./pages/admin-allocated-withdrawals/admin-allocated-withdrawals')
        .then(m => m.AdminAllocatedWithdrawals),
  },

  { path: '', redirectTo: 'login', pathMatch: 'full' },
];
