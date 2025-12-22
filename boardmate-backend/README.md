# Boardmate Backend API

Laravel 12.0 RESTful API backend for the Boardmate boarding house management system.

## 🚀 Quick Start

### Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### Database Setup

```bash
# Configure .env with your database credentials
php artisan migrate
php artisan db:seed  # Optional: seed with sample data
```

### Run Development Server

```bash
php artisan serve
```

API will be available at: `http://localhost:8000/api`

## 📚 API Documentation

All API endpoints are documented in `routes/api.php` with CURL examples. The API uses Laravel Sanctum for authentication.

### Authentication

- `POST /api/admin/login` - Admin login
- `POST /api/admin/register` - Admin registration
- `POST /api/boarder/login` - Boarder login
- `POST /api/boarder/register` - Boarder registration

### Protected Routes

All routes except authentication and public browsing require a Sanctum token in the `Authorization: Bearer {token}` header.

## 🗄️ Database

The application uses migrations for database schema management. Run migrations with:

```bash
php artisan migrate
```

## 🔐 Security

- Laravel Sanctum for API authentication
- Password hashing with bcrypt
- Input validation via Request classes
- Role-based authorization
- SQL injection prevention via Eloquent ORM

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/     # API controllers
│   └── Requests/       # Form request validation
├── Models/              # Eloquent models
└── Providers/          # Service providers

database/
├── migrations/          # Database migrations
└── seeders/            # Database seeders

routes/
└── api.php             # API routes
```

## 🧪 Testing

```bash
php artisan test
```

## 📖 Documentation

For complete API documentation and project details, see the root `README.md` and `PROJECT_DOCUMENTATION.md`.
