# Task Management Application

A single-page application built with Laravel 11 and Vue 3, demonstrating modern web development practices and clean architecture.

## Technical Stack

### Backend
- Laravel 11
- Laravel Sanctum for SPA Authentication
- MySQL/SQLite for database
- RESTful API architecture

### Frontend
- Vue 3 with Composition API
- TypeScript for type safety
- Pinia for state management
- Vue Router for routing
- Tailwind CSS with shadcn/ui components
- Vite for build tooling

## Architecture Overview

### Backend Architecture
- Request/Response pattern with form requests for validation
- Resource classes for API responses
- Service layer for business logic
- Repository pattern for data access
- Policy-based authorisation
- Query builder pattern for complex filters

### Frontend Architecture
- Component-based architecture with Vue 3
- Composables for reusable logic
- Type-safe store management
- Reactive state management
- Client-side form validation
- Custom hooks for data fetching

## Key Features

- SPA Authentication flow
- Task CRUD operations
- Advanced filtering and sorting
- Deadline tracking
- Category management
- Form validation
- Toast notifications
- Responsive design

## Local Development Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+ (optional - can use SQLite)

### Initial Setup

1. Clone the repository:
```bash
git clone [repository-url]
cd [repository-name]
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
yarn
```

4. Environment setup:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure database in `.env`:
```
DB_CONNECTION=sqlite  # or mysql
DB_DATABASE=storage/database.sqlite  # for SQLite
```

6. Run migrations and seed data:
```bash
php artisan migrate --seed
```

### Development Server

1. Start Laravel development server:
```bash
php artisan serve
```

2. Start Vite development server:
```bash
yarn dev
```

The application will be available at `http://localhost:8000`

### Default Test Account
```
Email: test@example.com
Password: password
```

## API Endpoints

### Authentication
```
POST   /api/register     # Register new user
POST   /api/login        # User login
POST   /api/logout       # User logout
GET    /api/user         # Get authenticated user
```

### Tasks
```
GET    /api/tasks        # List tasks (with filters)
POST   /api/tasks        # Create task
GET    /api/tasks/{id}   # Get task
PUT    /api/tasks/{id}   # Update task
DELETE /api/tasks/{id}   # Delete task
```

## Task Filtering

The tasks endpoint supports several query parameters:

```typescript
interface TaskFilters {
  search?: string;          // Search in title and description
  category?: 'work' | 'personal' | 'urgent';
  completed?: boolean;
  deadline?: 'overdue' | 'today' | 'week' | 'month';
  sort_by?: string;         // deadline|title
  sort_direction?: 'asc' | 'desc';
}
```

## Code Organisation

```
app/
├── Http/
│   ├── Controllers/        # Request handlers
│   └── Requests/          # Form request validation
├── Models/                # Eloquent models
└── Policies/             # Authorisation policies

resources/
├── components/           # Vue components
├── composables/         # Vue composables
├── services/            # API Service layer
├── views/               # Page components
├── stores/             # Pinia stores
└── types/              # TypeScript types
```

## Design Decisions

1. **SPA Architecture**: Full SPA implementation with Laravel Sanctum for seamless authentication and better user experience.

2. **TypeScript**: Used for enhanced type safety and better developer experience.

3. **Composables**: Extracted reusable logic into composables for better code organisation.

4. **Form Requests**: Dedicated request classes for robust validation and clean controllers.

5. **Task Filtering**: Implemented complex filtering system using query builder pattern.

## Testing

### Setup

```
1.mysql -u your_username -p -e "CREATE DATABASE your-project-test;"
2. Configure .env.testing (if it doesn't exist, copy .env.example to .env.testing)
3.php artisan migrate --seed --env=testing
```

Backend tests can be run with:
```bash
composer test
```

Frontend tests can be run with:
```bash
yarn test
```

## Additional Notes

- The application uses Laravel Sanctum's SPA authentication
- CSRF protection is enabled
- All API endpoints are protected by authentication except login/register
- Task ownership is enforced through policies
- Validation errors are handled consistently across the application