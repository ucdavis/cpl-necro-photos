# Necropsy Photos

A React + TypeScript single-page app for browsing and managing necropsy photos via a REST API backend.

Necropsy images are utilized for training and research purposes.

## 🚀 Features

- **Gallery view** with year filtering
- **Photo details** view with metadata
- **Upload** and **reassign** photos to another case functionality
- **Responsive UI** built with Tailwind CSS

## 🧰 Tech stack

- **React 19** + **TypeScript**
- **Vite** for fast builds and dev server
- **Tailwind CSS** for styling

## Configuration

- See `src/.env.example` and create a .env.production to define API URLs, specifically:
  - **VITE_PHOTO_URL**=https://your-url-where-photos-are-stored
  - **VITE_API_BASE_URL**=https://your-api-base-url

## Project Structure

- `src/` - application source code
  - `components/` - reusable UI components
  - `pages/` - route pages
  - `services/` - API clients and services
  - `utils/` - shared utilities and helpers

## Notes

- The app expects an API backend to be available (see `src/services` for the client configuration).
