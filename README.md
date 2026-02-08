Project Overview

This repository contains a custody-style platform with:
Angular (SPA) frontend for institutional users to submit deposits, view deposits, view allocated bars, and submit withdrawals.
Laravel API backend with role-based access control.
Admin UI limited to Allocated Withdrawal Queue (approve/reject).


Key flows implemented:

Login (cookie/session based)
Deposit request (create)
View my deposits
View allocated bars + select bars for allocated withdrawal
View my withdrawals
Admin allocated withdrawal queue (PENDING) + approve/reject


Tech Stack

Frontend: Angular
Backend: Laravel
DB: MySQL (XAMPP)


Setup Instructions

1) Backend (Laravel API)
Install PHP + Composer (or use XAMPP PHP).

From the backend folder:
composer install
cp .env.example .env
php artisan key:generate


Configure DB in .env (MySQL via XAMPP):
DB_HOST=127.0.0.1
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD= (blank, unless you set one)

Run migrations:
php artisan migrate

Start API:
php artisan serve

API base: http://127.0.0.1:8000

2) Frontend (Angular SPA)

From the frontend folder:

npm install
ng serve

Frontend base: http://localhost:4200


Assumptions / Architecture Decisions

Cookie/session auth is used for SPA authentication. Angular requests include credentials.
Role-based access:
ADMIN: access only to Allocated Withdrawal Queue page + relevant admin endpoints.
INSTITUTIONAL: access to deposits, bars, withdrawals.
Allocated withdrawal workflow reserves bars immediately when the request is created to prevent double allocation.
Admin approval finalizes withdrawal and marks bars as withdrawn; rejection releases reserved bars back to available.


Example API Calls

Base URL: http://127.0.0.1:8000/api

Auth
POST /auth/login
POST /auth/logout
GET /auth/me

Deposits (Institutional)
POST /deposits
GET /deposits/me

Bars (Institutional)
GET /bars/me
GET /bars/me/available

Withdrawals (Institutional)
GET /withdrawals/me
POST /withdrawals/allocated (allocated withdrawal request based on selected bar IDs)

Admin (Allocated Withdrawal Queue)
GET /admin/withdrawals/allocated?status=PENDING
POST /withdrawals/{id}/approve
POST /withdrawals/{id}/reject

Edge cases + how the design handles them

Approve/Reject called on non-PENDING withdrawal
Handling: backend enforces state transition rules; returns a clear error and makes no changes.
UI handling: admin buttons are only shown/enabled for PENDING.

Two withdrawal requests try to reserve the same bars
Handling: bar reservation happens inside a DB transaction; bars can only be reserved if currently AVAILABLE.
If conflict: request fails with an error (“bar not available”) and no partial updates.

Reject must release reserved bars
Handling: rejection updates withdrawal status and resets reserved bars back to AVAILABLE in the same transaction.
Prevents “stuck” reserved inventory.

User/account missing
Handling: /me endpoints return success:true, data: [] when no account is found (instead of crashing).
UI stays stable and shows empty state.

Auth/cookie/CORS/CSRF issues for SPA
Handling: configured SPA auth with credentials + correct CORS origin (not * when credentials are used).
Prevents 401/419 loops while keeping security checks intact.

Missing required DB fields (e.g., deposit reference)
Handling: server-side validation (and/or server-generated reference) prevents SQL errors like “no default value”.
Ensures API responds with a controlled validation message.

Frontend refresh race after login
Handling: frontend uses a safe re-fetch pattern after initial load; backend returns consistent session-based auth.