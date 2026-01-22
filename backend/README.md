# CPL Necro Photos Backend

A refactored PHP backend API for managing necropsy photos with proper MVC architecture.

## Structure

```
backend/
├── public/              # Public entry point
│   ├── index.php       # Main entry point with routing
│   └── .htaccess       # Apache rewrite rules
├── src/                # Application source code
│   ├── Core/           # Core framework classes
│   │   ├── Router.php
│   │   ├── Controller.php
│   │   ├── Repository.php
│   │   └── Database.php
│   ├── Controllers/    # Request handlers
│   │   └── PhotoController.php
│   └── Repositories/   # Database access layer
│       └── PhotoRepository.php
├── bootstrap.php       # Application bootstrap
├── composer.json       # Dependencies
├── .env               # Environment variables (not in git)
└── .env.example       # Example environment file
```

## Setup

1. **Install dependencies:**

   ```bash
   cd backend
   composer install
   ```

2. **Configure environment:**

   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

3. **Configure web server:**
   - Point your web server document root to `backend/public/`
   - Ensure `.htaccess` is enabled (Apache) or configure URL rewriting

## API Endpoints

### Get Photos (with pagination)

```
GET /photos
Query params:
  - page: Page number (default: 1)
  - per_page: Items per page (default: 20, max: 100)
  - year: Filter by year (4-digit format, e.g., 2022)
  - search: Search by CPL number, filename, or login

Response:
{
  "data": [...],
  "pagination": {
    "total": 150,
    "per_page": 20,
    "current_page": 1,
    "last_page": 8,
    "from": 1,
    "to": 20
  }
}
```

### Get Single Photo

```
GET /photos/{id}

Response:
{
  "id": 6841,
  "cpl_num": "0047",
  "suffix": "J",
  "year": 22,
  "filename": "0047-22J-a.jpg",
  "size": 1309359,
  "date_uploaded": "2022-01-04 16:16:50",
  "login": "necropsy"
}
```

### Upload Photo

```
POST /photos/upload
Content-Type: multipart/form-data

Form data:
  - photo: File upload (required)
  - cpl_num: CPL number (required)
  - year: Year (required, 4-digit)
  - suffix: Suffix (optional)
  - login: Username (optional, default: 'system')

Response:
{
  "success": true,
  "message": "Photo uploaded successfully",
  "data": {
    "id": 123,
    "filename": "0047-22J.jpg"
  }
}
```

### Delete Photo

```
DELETE /photos/{id}

Response:
{
  "success": true,
  "message": "Photo deleted successfully"
}
```

## Environment Variables

See `.env.example` for all available configuration options:

- `DB_HOST`: Database host
- `DB_USER`: Database user
- `DB_PASSWORD`: Database password
- `DB_NAME`: Database name
- `UPLOAD_DIR`: Directory for uploaded photos
- `MAX_UPLOAD_SIZE`: Maximum upload size in bytes

## Development

The backend uses:

- **vlucas/phpdotenv** for environment variable management
- **PSR-4 autoloading** for class organization
- **MVC pattern** with Controllers and Repositories
- **Prepared statements** for SQL security
- **Pagination support** for large datasets

## Notes

- All responses are in JSON format
- CORS headers are enabled for frontend integration
- File uploads are validated for type and size
- Database connections use singleton pattern
