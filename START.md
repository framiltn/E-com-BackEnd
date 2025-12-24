# Quick Start Guide

## Backend (Laravel)

```bash
cd BackEnd\marketplace-backend

# Install dependencies
composer install

# Setup database
php artisan migrate

# Start server
php artisan serve
```

Backend runs at: `http://localhost:8000`

---

## Frontend (Next.js)

```bash
cd FrontEnd

# Install dependencies
npm install

# Start dev server
npm run dev
```

Frontend runs at: `http://localhost:3000`

---

## Test the App

1. Open `http://localhost:3000`
2. Click "Sign Up" to register
3. Login and start shopping

---

## Default API URL

Frontend is configured to connect to: `http://localhost:8000/api`

Change in `FrontEnd\.env.local` if needed.
