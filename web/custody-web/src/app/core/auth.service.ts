import { Injectable } from '@angular/core';
import { Observable, tap } from 'rxjs';
import { ApiService } from './api.service';

export interface MeResponse {
  success: boolean;
  data: { user: { id: number; email: string; role: string; name: string } };
}

@Injectable({ providedIn: 'root' })
export class AuthService {
  user: MeResponse['data']['user'] | null = null;

  constructor(private api: ApiService) {}

  login(email: string, password: string) {
    return this.api.post('/auth/login', { email, password }).pipe(
      tap(() => {
        // After login, load identity
      })
    );
  }

  me(): Observable<MeResponse> {
    return this.api.get<MeResponse>('/auth/me').pipe(
      tap(res => (this.user = res.data.user))
    );
  }

  logout() {
    return this.api.post('/auth/logout', {}).pipe(
      tap(() => (this.user = null))
    );
  }
}
