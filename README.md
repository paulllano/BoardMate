# Boardmate - Boarding House Management System

A comprehensive web application for managing boarding houses, boarders, contracts, payments, applications, and reviews. Built with Laravel (backend) and Nuxt.js (frontend).

## 🚀 Features

- **Dual Authentication System**: Separate login/registration for admins (property owners) and boarders (residents)
- **Boarding House Management**: Complete CRUD operations for properties with detailed information
- **Application System**: Boarders can apply to boarding houses with a two-step transfer approval process
- **Contract Management**: Create and manage rental agreements with automatic status tracking
- **Payment Tracking**: Record and approve payments with multiple methods (Cash, GCash) and admin approval workflow
- **Soft Delete System**: Archive contracts and payments instead of permanent deletion, with restore functionality
- **Review & Rating System**: Boarders can leave reviews and ratings (1-5 stars) for boarding houses
- **Service Management**: Admins can add and manage additional services offered by boarding houses
- **Dashboard Analytics**: Role-specific dashboards showing statistics and pending items

## 🛠️ Technology Stack

**Frontend:**
- Nuxt.js 4.2.1
- Vue.js 3.5.25
- Tailwind CSS 4.1.17
- Font Awesome 6.7.2

**Backend:**
- Laravel 12.0
- PHP 8.2+
- Laravel Sanctum (API Authentication)
- MySQL/PostgreSQL (configurable)

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer (PHP dependency manager)
- Node.js 16 or higher
- npm (Node package manager)
- MySQL/PostgreSQL (or SQLite for development)
- Git

## 🔧 Installation

### 1. Clone the repository
```bash
git clone [your-repo-url]
cd BOARDMATE\ \(REAL\)
```

### 2. Backend Setup (Laravel)

```bash
cd boardmate-backend
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Configure Environment Variables

Edit `.env` file in `boardmate-backend` directory:

```env
APP_NAME=Boardmate
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=boardmate
DB_USERNAME=root
DB_PASSWORD=

# Or use SQLite for development:
# DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite
```

### 4. Set up the Database

```bash
# Create database (if using MySQL)
mysql -u root -p
CREATE DATABASE boardmate;

# Run migrations
php artisan migrate

# (Optional) Seed database with sample data
php artisan db:seed
```

### 5. Frontend Setup (Nuxt.js)

```bash
cd ../boardmate-frontend
npm install
```

### 6. Configure Frontend API Endpoint

Create or edit `.env` file in `boardmate-frontend` directory:

```env
NUXT_PUBLIC_API_BASE_URL=http://localhost:8000/api
```

## 🏃 Running the Application

### Start Backend Server

```bash
cd boardmate-backend
php artisan serve
```

Backend will run on: `http://localhost:8000`

### Start Frontend Development Server

```bash
cd boardmate-frontend
npm run dev
```

Frontend will run on: `http://localhost:3000` (or another port if 3000 is occupied)

### Access the Application

1. Open your browser and navigate to: `http://localhost:3000`
2. You will see the role selection page
3. Choose "Admin" or "Boarder" to proceed to login

**Note:** Make sure both servers are running simultaneously for the application to work properly.

## 📁 Project Structure

```
BOARDMATE (REAL)/
├── boardmate-backend/          # Laravel API backend
│   ├── app/
│   │   ├── Http/Controllers/   # API controllers
│   │   ├── Models/             # Eloquent models
│   │   └── ...
│   ├── database/
│   │   ├── migrations/         # Database migrations
│   │   └── seeders/           # Database seeders
│   ├── routes/
│   │   └── api.php            # API routes
│   └── ...
│
├── boardmate-frontend/         # Nuxt.js frontend
│   ├── components/            # Vue components
│   ├── pages/                # Application pages
│   ├── nuxt.config.ts        # Nuxt configuration
│   └── ...
│
└── PROJECT_DOCUMENTATION.md   # Complete project documentation
```

## 🔐 Authentication

The application uses Laravel Sanctum for API token-based authentication. Users must:
1. Select their role (Admin or Boarder) on the login page
2. Enter their email and password
3. Receive a Sanctum token for subsequent API requests
4. Include the token in the `Authorization: Bearer {token}` header for protected routes

## 📚 API Documentation

All API endpoints are documented in `boardmate-backend/routes/api.php` with CURL examples. Key endpoints include:

- `POST /api/admin/login` - Admin authentication
- `POST /api/boarder/login` - Boarder authentication
- `GET /api/boarding-houses` - List boarding houses
- `GET /api/applications` - List applications
- `POST /api/applications` - Create application
- `GET /api/contracts` - List contracts
- `GET /api/payments` - List payments
- `POST /api/payments` - Create payment
- And more...

## 👥 User Roles

### Admin (Property Owner)
- Manage boarding houses
- Review and approve/reject applications
- Create and manage contracts
- Approve/reject payments
- Manage services
- View reviews and statistics

### Boarder (Resident)
- Browse available boarding houses
- Apply to boarding houses
- View contracts and payment history
- Submit payments
- Leave reviews and ratings
- Manage profile

## 🔒 Security Features

- Password hashing using bcrypt
- Laravel Sanctum token-based authentication
- Role-based access control (RBAC)
- Input validation on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection via Vue.js template escaping
- Authorization checks on all protected routes

## 📝 Database

The application uses a relational database with the following main entities:
- `admins` - Property owners and staff
- `boarding_houses` - Properties
- `boarders` - Residents
- `contracts` - Rental agreements
- `payments` - Payment records
- `applications` - Boarder applications
- `reviews` - Reviews and ratings
- `services` - Additional services
- `deleted_contracts` - Archived contracts
- `deleted_payments` - Archived payments

See `PROJECT_DOCUMENTATION.md` for complete ERD and database schema details.

## 🧪 Testing

The application includes test cases for:
- Authentication (admin and boarder login)
- Application submission and approval
- Contract creation and management
- Payment submission and approval
- Transfer application workflow
- Soft delete and restore functionality

See `PROJECT_DOCUMENTATION.md` for detailed test cases.

## 📖 Documentation

For complete project documentation including:
- Detailed feature descriptions
- Database architecture (ERD)
- API endpoint documentation
- User stories and use cases
- UI/UX design specifications
- Security measures
- Installation instructions

See `PROJECT_DOCUMENTATION.md`

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👤 Author

[Your Name]

## 🙏 Acknowledgments

- Laravel framework
- Nuxt.js framework
- Tailwind CSS
- Font Awesome
- All contributors and users

---

