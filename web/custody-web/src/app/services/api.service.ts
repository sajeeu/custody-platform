import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface HealthResponse {
  success: boolean;
  message: string;
}

@Injectable({
  providedIn: 'root',
})
export class ApiService {
  private baseUrl = 'http://127.0.0.1:8000';

  constructor(private http: HttpClient) {}

  health(): Observable<HealthResponse> {
    return this.http.get<HealthResponse>(`${this.baseUrl}/api/health`);
  }
}
