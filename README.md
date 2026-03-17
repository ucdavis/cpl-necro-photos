# Necropsy Photos

A full-stack necropsy photo management application.

- **Frontend:** React + TypeScript SPA for browsing, uploading, and managing photos.
- **Backend:** PHP REST API for photo metadata and storage management.

## 📁 Repository layout

- `frontend/` — UI client (Vite + React)
- `backend/` — PHP API (MVC-style)

## 🚀 Quick start

1. Start the backend (see `backend/README.md`):
   - Install PHP dependencies with Composer
   - Configure `.env`
   - Serve `backend/public` via a web server (Apache, Nginx, PHP built-in server)

2. Start the frontend (see `frontend/README.md`):
   - Install dependencies with npm/yarn
   - Configure `src/.env` or `.env.production`
   - Run the dev server

## 📌 Docs

- [Frontend README](frontend/README.md)
- [Backend README](backend/README.md)

---

## 🧩 Notes

- The backend API is the source of truth for photo data; the frontend consumes it.
- Configure the frontend to point at the backend API using `VITE_API_BASE_URL`.
