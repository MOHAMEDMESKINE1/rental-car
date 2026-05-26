# 🚗 AutoRental — Car Rental Management System

> Laravel 12 · Vue 3 · Inertia.js 2 · Spatie Permissions · Spatie Media Library · Tailwind CSS 4

---

## 📋 Features

| Module | Details |
|---|---|
| **Dashboard** | KPI cards, revenue charts (ApexCharts), fleet status donut, overdue alerts |
| **Fleet Management** | Vehicles CRUD + photos (Spatie Media), categories, status machine |
| **Reservations** | Multi-step form, live price calculator, promo code validation |
| **Rentals** | Reservation → Rental conversion, return flow, overdue detection |
| **Customers** | Profile + documents upload, blacklist management |
| **Branches** | Multi-location support with GPS coords |
| **Insurance Plans** | CDW / SCDW / TP / Full with deductibles |
| **Extra Services** | GPS, child seat, WiFi, etc. per-day or one-time |
| **Promotions** | Percentage/fixed promo codes with validity and max-uses |
| **Maintenance** | Schedule, track, complete maintenance per vehicle |
| **Damages** | Report, link to rental, customer liability |
| **Invoices & Payments** | Auto-generated invoices, multi-payment method tracking |
| **Reports** | Revenue by month/category/branch, top vehicles, top customers |
| **User Management** | Roles: admin, manager, agent, customer (Spatie Permissions) |

---

## ⚙️ Requirements

- PHP 8.2+
- Composer
- Node.js 20+
- MySQL 8+

---

## 🚀 Installation

### 1. Clone & install PHP deps
```bash
git clone <repo-url> rental-car
cd rental-car
composer install
```

### 2. Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
DB_DATABASE=rental_car
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Database
```bash
php artisan migrate
php artisan db:seed
```

### 4. Storage
```bash
php artisan storage:link
```

### 5. Install JS deps & build
```bash
npm install
npm run dev        # Development
# or
npm run build      # Production
```

### 6. Run
```bash
php artisan serve
```

Open **http://localhost:8000**

---

## 🔑 Default Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@rental.com | password |
| Manager | manager@rental.com | password |
| Agent | agent@rental.com | password |

---

## 🗂️ Project Structure

```
app/
├── Http/
│   ├── Controllers/         # DashboardController, VehicleController, RentalController ...
│   └── Requests/            # Form validation (VehicleRequest, ReservationRequest ...)
├── Models/                  # Vehicle, Customer, Rental, Reservation ...
├── Services/
│   ├── PricingService.php   # Dynamic pricing calculation
│   ├── AvailabilityService.php  # Vehicle availability check
│   └── RentalService.php    # Rental lifecycle management
└── Policies/                # Spatie permission-based policies

database/
├── migrations/              # 17 migration files
└── seeders/DatabaseSeeder.php  # Roles, permissions, demo data

resources/js/
├── Layouts/AppLayout.vue    # Main sidebar layout
└── Pages/
    ├── Dashboard/Index.vue
    ├── Vehicles/            # Index, Form, Categories, Show
    ├── Customers/           # Index, Form, Show
    ├── Reservations/        # Index, Form, Show
    ├── Rentals/             # Index, Show, Return, Create
    ├── Branches/
    ├── Maintenance/
    ├── Reports/
    └── Settings/            # Users, Promotions
```

---

## 🔐 Roles & Permissions

| Permission | Admin | Manager | Agent | Customer |
|---|---|---|---|---|
| Manage vehicles | ✅ | ✅ | 👁️ | ❌ |
| Manage customers | ✅ | ✅ | ✅ | ❌ |
| Create reservations | ✅ | ✅ | ✅ | ❌ |
| Process rentals | ✅ | ✅ | ✅ | ❌ |
| View reports | ✅ | ✅ | ❌ | ❌ |
| Manage users | ✅ | ❌ | ❌ | ❌ |
| Manage settings | ✅ | ✅ | ❌ | ❌ |

---

## 🗃️ Database Schema (20 tables)

`users` · `customers` · `branches` · `vehicle_categories` · `vehicles`
`insurance_plans` · `extra_services` · `promotions`
`reservations` · `reservation_extra_services`
`rentals` · `rental_extra_services` · `additional_drivers`
`vehicle_inspections` · `damages` · `maintenance`
`invoices` · `payments` · `reviews`
+ Spatie: `roles` · `permissions` · `model_has_roles` · `media`

---

## 📦 Key Packages

| Package | Purpose |
|---|---|
| `spatie/laravel-permission` | Role-based access control |
| `spatie/laravel-medialibrary` | File/image uploads for vehicles, customers, inspections |
| `spatie/laravel-query-builder` | Filterable, sortable API endpoints |
| `inertiajs/inertia-laravel` | SPA without building an API |
| `vue3-apexcharts` | Revenue & fleet charts |
| `lucide-vue-next` | Icon set |

---

## 🛡️ Business Logic Highlights

- **Availability check** — excludes vehicles with overlapping reservations or active rentals
- **Live pricing** — AJAX calculation updates total in real-time on reservation form  
- **Auto-charges on return** — late return days, extra KM beyond free allowance, fuel refill
- **Status machines** — vehicles: `available → reserved → rented → maintenance → retired`; rentals: `active → overdue → completed`
- **Invoice auto-generation** — created automatically when rental is completed
- **Promo validation** — checks date range, max uses, and minimum rental days

---

*Built with ❤️ — Laravel 12 + Vue 3 + Inertia.js 2*
