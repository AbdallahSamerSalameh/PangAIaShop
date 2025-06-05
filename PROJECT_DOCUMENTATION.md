# PangAIa Shop - Laravel 11 E-Commerce Backend Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [System Requirements](#system-requirements)
3. [Architecture Overview](#architecture-overview)
4. [Authentication & Authorization](#authentication--authorization)
5. [Database Schema](#database-schema)
6. [API Documentation](#api-documentation)
7. [Frontend Integration](#frontend-integration)
8. [Admin Panel](#admin-panel)
9. [E-Commerce Features](#e-commerce-features)
10. [Security Features](#security-features)
11. [Installation & Setup](#installation--setup)
12. [Configuration](#configuration)
13. [Development Guidelines](#development-guidelines)
14. [Testing](#testing)
15. [Deployment](#deployment)
16. [Troubleshooting](#troubleshooting)

---

## Project Overview

**PangAIa Shop** is a comprehensive e-commerce backend system built with Laravel 11, featuring a robust admin panel, multi-guard authentication, comprehensive inventory management, and full API support. The system is designed to handle both B2C and B2B e-commerce operations with advanced features like cart management, order processing, review systems, and promotional campaigns.

### Key Features
- **Dual Authentication System**: Separate authentication for customers and administrators
- **Advanced Cart Management**: Session-based cart for guests, database cart for authenticated users
- **Comprehensive Admin Panel**: Full-featured admin interface with role-based access control
- **Inventory Management**: Real-time stock tracking with automated status updates
- **Order Management**: Complete order lifecycle management with status tracking
- **Review & Rating System**: Customer feedback and moderation capabilities
- **Promotional System**: Promo codes, discounts, and marketing campaigns
- **API-First Design**: RESTful API with Sanctum authentication
- **Audit Logging**: Comprehensive activity tracking for security and compliance

---

## System Requirements

### Server Requirements
- **PHP**: 8.2 or higher
- **Composer**: Latest stable version
- **Database**: MySQL 8.0+ or SQLite
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Node.js**: 16+ (for asset compilation)
- **NPM**: 8+ (for package management)

### Laravel Dependencies
- **Laravel Framework**: ^11.31
- **Laravel Sanctum**: ^4.1 (API authentication)
- **Laravel Tinker**: ^2.9 (Debugging and testing)
- **Doctrine DBAL**: ^4.2 (Database abstraction)

### Development Dependencies
- **Laravel IDE Helper**: ^3.5 (IDE support)
- **Laravel Pint**: ^1.13 (Code formatting)
- **Laravel Sail**: ^1.26 (Docker environment)
- **PHPUnit**: ^11.0.1 (Testing framework)

---

## Architecture Overview

### MVC Structure
The application follows Laravel's MVC pattern with additional layers for business logic:

```
app/
├── Console/               # Artisan commands
├── Helpers/              # Utility functions and helpers
├── Http/
│   ├── Controllers/      # Request handling
│   │   ├── Admin/       # Admin panel controllers
│   │   ├── Frontend/    # Customer-facing controllers
│   │   └── API/         # API endpoints
│   ├── Middleware/      # Request filtering and authentication
│   └── Requests/        # Form request validation
├── Models/              # Eloquent models
├── Policies/           # Authorization policies
├── Providers/          # Service providers
├── Services/           # Business logic services
└── Traits/             # Reusable code traits
```

### Key Components

#### Models
- **User**: Customer accounts and authentication
- **Admin**: Administrator accounts with role-based access
- **Product**: Product catalog with variants and images
- **Category**: Hierarchical product categorization
- **Cart/CartItem**: Shopping cart management
- **Order/OrderItem**: Order processing and tracking
- **Inventory**: Stock management and tracking
- **Review**: Customer feedback and ratings
- **PromoCode**: Promotional campaigns and discounts

#### Services
- **GuestCartService**: Handles cart operations for non-authenticated users
- **InventoryHelper**: Manages stock levels and availability
- **SettingHelper**: Application configuration management

#### Middleware
- **AdminAuthenticate**: Admin panel access control
- **SuperAdminAccess**: Super admin role verification
- **AdminSessionValidator**: Session management for admin users
- **CheckAccountStatus**: User account status validation
- **RequireAuthForCart**: Cart authentication requirements

---

## Authentication & Authorization

### Multi-Guard Authentication System

The application implements a dual authentication system using Laravel's multi-guard feature:

#### User Guard (Customers)
- **Guard**: `web`
- **Provider**: `users`
- **Model**: `App\Models\User`
- **Features**: Registration, login, password reset, profile management

#### Admin Guard (Administrators)
- **Guard**: `admin`
- **Provider**: `admins`
- **Model**: `App\Models\Admin`
- **Features**: Role-based access, audit logging, session management

### Role-Based Access Control

#### Admin Roles
1. **Super Admin**
   - Full system access
   - Admin user management
   - Audit log access
   - System configuration
   - Vendor management

2. **Admin**
   - Product management
   - Order processing
   - Customer support
   - Inventory management
   - Report access

### Session Management
- **Admin Session Validation**: Automatic logout when navigating away from admin routes
- **Remember Me**: Persistent sessions for both user types
- **Session Security**: CSRF protection and session regeneration

---

## Database Schema

### Core Tables

#### Users Table
```sql
users (
    id,
    username,
    email,
    password,
    first_name,
    last_name,
    phone,
    street,
    city,
    state,
    postal_code,
    country,
    is_active,
    last_login,
    email_verified_at,
    created_at,
    updated_at,
    deleted_at
)
```

#### Products Table
```sql
products (
    id,
    name,
    description,
    price,
    sale_price,
    sku,
    vendor_id,
    status,
    weight,
    dimensions,
    warranty_info,
    return_policy,
    view_count,
    in_stock,
    created_by,
    updated_by,
    created_at,
    updated_at,
    deleted_at
)
```

#### Orders Table
```sql
orders (
    id,
    user_id,
    order_number,
    shipping_street,
    shipping_city,
    shipping_state,
    shipping_postal_code,
    shipping_country,
    billing_street,
    billing_city,
    billing_state,
    billing_postal_code,
    billing_country,
    total_amount,
    subtotal,
    shipping,
    discount,
    order_date,
    status,
    promo_code_id,
    expected_delivery_date,
    notes,
    admin_notes,
    handled_by,
    cancelled_at,
    deleted_at
)
```

### Relationship Tables

#### Product Categories (Many-to-Many)
```sql
product_categories (
    product_id,
    category_id,
    is_primary_category,
    added_by,
    added_at
)
```

#### Cart Items
```sql
cart_items (
    id,
    cart_id,
    product_id,
    variant_id,
    quantity,
    unit_price,
    created_at,
    updated_at
)
```

### Audit and Logging

#### Admin Audit Logs
```sql
admin_audit_logs (
    id,
    admin_id,
    action,
    resource,
    resource_id,
    description,
    ip_address,
    user_agent,
    created_at
)
```

---

## API Documentation

### Authentication Endpoints

#### User Authentication
```http
POST /api/register
POST /api/login
POST /api/logout
GET  /api/user-profile
```

#### Admin Authentication
```http
POST /api/admin/login
POST /api/admin/logout
GET  /api/admin/profile
```

### Product Endpoints

#### Public Product API
```http
GET  /api/products                    # List all products
GET  /api/products/featured           # Featured products
GET  /api/products/new-arrivals       # New arrivals
GET  /api/products/best-sellers       # Best selling products
GET  /api/products/{id}               # Product details
GET  /api/products/{id}/related       # Related products
GET  /api/products/{id}/reviews       # Product reviews
```

#### Admin Product API
```http
GET    /api/admin/products            # List products (admin)
POST   /api/admin/products            # Create product
GET    /api/admin/products/{id}       # Product details (admin)
PUT    /api/admin/products/{id}       # Update product
DELETE /api/admin/products/{id}       # Delete product
POST   /api/admin/products/{id}/images # Upload product images
```

### Cart Endpoints

#### User Cart API
```http
GET    /api/cart                      # Get cart contents
POST   /api/cart/add                  # Add item to cart
PUT    /api/cart/{item}               # Update cart item
DELETE /api/cart/{item}               # Remove cart item
DELETE /api/cart                      # Clear cart
POST   /api/cart/apply-promo          # Apply promo code
DELETE /api/cart/remove-promo         # Remove promo code
```

### Order Endpoints

#### User Orders
```http
GET  /api/orders                      # User's orders
POST /api/orders                      # Create order
GET  /api/orders/{id}                 # Order details
PUT  /api/orders/{id}/cancel          # Cancel order
```

#### Admin Orders
```http
GET  /api/admin/orders                # All orders (admin)
GET  /api/admin/orders/{id}           # Order details (admin)
PUT  /api/admin/orders/{id}/status    # Update order status
```

### Category Endpoints
```http
GET /api/categories                   # List categories
GET /api/categories/{id}              # Category details
GET /api/categories/{id}/products     # Category products
```

---

## Frontend Integration

### Web Routes Structure

#### Public Routes
```php
GET  /                                # Homepage
GET  /about                          # About page
GET  /contact                        # Contact page
GET  /shop                           # Product catalog
GET  /product/{id}                   # Product details
POST /product/{id}/review            # Submit review
```

#### Authentication Routes
```php
GET  /login                          # Login form
POST /login                          # Login process
GET  /register                       # Registration form
POST /register                       # Registration process
GET  /forgot-password               # Password reset form
POST /forgot-password               # Password reset process
POST /logout                        # Logout
```

#### User Dashboard Routes
```php
GET  /profile                        # User profile
PATCH /profile                       # Update profile
GET  /orders                         # User orders
GET  /orders/{id}                    # Order details
GET  /wishlist                       # User wishlist
```

#### Cart Routes
```php
GET  /cart                           # Cart page
POST /cart/add                       # Add to cart
POST /cart/update                    # Update cart
POST /cart/remove                    # Remove from cart
GET  /checkout                       # Checkout page
POST /checkout/process               # Process checkout
```

### Guest Cart System

For non-authenticated users, the system provides a session-based cart:

#### Guest Cart Features
- Session-based storage
- Automatic migration to user cart upon login
- Full cart functionality without registration
- Persistent across browser sessions

#### Implementation
```php
// Guest cart routes
GET  /guest/cart                     # Guest cart page
POST /guest/cart/add                 # Add to guest cart
POST /guest/cart/update              # Update guest cart
POST /guest/cart/remove              # Remove from guest cart
```

---

## Admin Panel

### Admin Routes Structure

```php
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication
    GET  /admin/login                 # Admin login
    POST /admin/login                 # Admin login process
    POST /admin/logout                # Admin logout
    
    // Dashboard
    GET  /admin/dashboard             # Admin dashboard
    
    // Profile Management
    GET  /admin/profile               # Admin profile
    PUT  /admin/profile               # Update profile
    PUT  /admin/profile/password      # Change password
    
    // Product Management
    Route::resource('products', ProductController::class);
    
    // Category Management
    Route::resource('categories', CategoryController::class);
    
    // Order Management
    Route::resource('orders', OrderController::class);
    GET  /admin/orders/pending        # Pending orders
    GET  /admin/orders/{id}/invoice   # Order invoice
    
    // Customer Management
    Route::resource('customers', CustomerController::class);
    
    // Inventory Management
    Route::resource('inventory', InventoryController::class);
    
    // Promotions
    Route::resource('promo-codes', PromotionController::class);
    
    // Reviews & Ratings
    Route::resource('reviews', ReviewController::class);
    PATCH /admin/reviews/{id}/approve  # Approve review
    PATCH /admin/reviews/{id}/reject   # Reject review
    
    // Reports
    GET  /admin/reports/sales         # Sales reports
    GET  /admin/reports/inventory     # Inventory reports
    GET  /admin/reports/customers     # Customer reports
    
    // Settings
    GET  /admin/settings              # System settings
    PUT  /admin/settings              # Update settings
    
    // Admin Management (Super Admin only)
    Route::resource('admins', AdminController::class);
    Route::resource('audit-logs', AuditLogController::class);
});
```

### Admin Features

#### Dashboard Analytics
- Sales overview and trends
- Order statistics
- Low stock alerts
- Customer metrics
- Revenue analytics

#### Product Management
- Product CRUD operations
- Category assignment
- Image management
- Inventory tracking
- Bulk operations

#### Order Management
- Order lifecycle management
- Status updates
- Invoice generation
- Shipping coordination
- Customer communication

#### Customer Management
- Customer profiles
- Order history
- Account status management
- Support ticket handling

#### Inventory Management
- Stock level monitoring
- Low stock alerts
- Automatic reorder points
- Supplier management

---

## E-Commerce Features

### Product Catalog

#### Product Structure
- **Basic Information**: Name, description, SKU, pricing
- **Categorization**: Multiple category assignment with primary category
- **Variants**: Size, color, and other attribute variations
- **Images**: Multiple product images with primary image designation
- **Inventory**: Real-time stock tracking
- **SEO**: Meta descriptions, keywords, and URL optimization

#### Category Management
- **Hierarchical Structure**: Parent-child category relationships
- **Display Order**: Custom sorting and organization
- **Status Management**: Active/inactive category control
- **Product Association**: Many-to-many product-category relationships

### Shopping Cart System

#### Dual Cart Implementation
1. **Guest Cart**: Session-based for anonymous users
2. **User Cart**: Database-persistent for authenticated users
3. **Cart Migration**: Automatic merge upon user login

#### Cart Features
- **Item Management**: Add, update, remove cart items
- **Quantity Control**: Stock-aware quantity validation
- **Price Calculation**: Dynamic pricing with taxes and discounts
- **Promo Codes**: Coupon and discount code application
- **Persistence**: Session and database storage options

### Order Processing

#### Order Lifecycle
1. **Cart Checkout**: Convert cart to order
2. **Payment Processing**: Integrate with payment gateways
3. **Order Confirmation**: Generate order number and confirmation
4. **Fulfillment**: Pick, pack, and ship process
5. **Delivery Tracking**: Status updates and tracking information
6. **Completion**: Order fulfillment and customer notification

#### Order Statuses
- `pending`: New order awaiting processing
- `processing`: Order being prepared
- `shipped`: Order dispatched to customer
- `delivered`: Order received by customer
- `completed`: Order fully processed
- `cancelled`: Order cancelled
- `refunded`: Order refunded
- `on_hold`: Order temporarily suspended

### Payment System

#### Payment Integration
- **Payment Gateways**: Stripe, PayPal, and other providers
- **Payment Methods**: Credit cards, digital wallets, bank transfers
- **Security**: PCI compliance and secure payment processing
- **Refunds**: Automated and manual refund processing

### Review & Rating System

#### Review Features
- **Star Ratings**: 1-5 star rating system
- **Written Reviews**: Detailed customer feedback
- **Moderation**: Admin approval and rejection workflow
- **Verification**: Verified purchase requirements
- **Response**: Admin and vendor response capabilities

### Promotional System

#### Promo Code Features
- **Discount Types**: Percentage and fixed amount discounts
- **Usage Limits**: Per-customer and total usage restrictions
- **Validity Periods**: Start and end date controls
- **Conditions**: Minimum order amounts and product restrictions
- **Analytics**: Usage tracking and performance metrics

---

## Security Features

### Authentication Security
- **Password Hashing**: Bcrypt encryption for all passwords
- **Session Management**: Secure session handling with regeneration
- **CSRF Protection**: Cross-site request forgery prevention
- **Rate Limiting**: Login attempt throttling
- **Remember Me**: Secure persistent login tokens

### Authorization & Access Control
- **Role-Based Access**: Admin and Super Admin role separation
- **Policy-Based Authorization**: Granular permission control
- **Resource Protection**: Middleware-based route protection
- **Audit Logging**: Comprehensive activity tracking

### Data Protection
- **Input Validation**: Request validation and sanitization
- **SQL Injection Prevention**: Eloquent ORM and prepared statements
- **XSS Protection**: Output escaping and content filtering
- **File Upload Security**: Type validation and secure storage
- **Soft Deletes**: Data preservation with logical deletion

### Admin Security Features
- **Session Validation**: Automatic logout when leaving admin area
- **Failed Login Tracking**: Brute force protection
- **IP Logging**: Access attempt tracking
- **Activity Monitoring**: Comprehensive audit trail
- **Account Locking**: Automatic account suspension

---

## Installation & Setup

### 1. Environment Setup

#### Clone Repository
```bash
git clone <repository-url>
cd PangAIaShop-BackEnd
```

#### Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

#### Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Database Setup

#### Configure Database
Edit `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pangaia_shop
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Run Migrations
```bash
# Run database migrations
php artisan migrate

# Seed initial data (optional)
php artisan db:seed
```

### 3. Storage Setup

#### Create Storage Links
```bash
# Create symbolic link for storage
php artisan storage:link
```

#### Set Permissions
```bash
# Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 4. Asset Compilation

#### Development Build
```bash
# Compile assets for development
npm run dev
```

#### Production Build
```bash
# Compile and minify for production
npm run build
```

### 5. Application Configuration

#### Queue Configuration
```bash
# Set up queue worker (for background jobs)
php artisan queue:work
```

#### Schedule Configuration
Add to crontab for scheduled tasks:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Configuration

### Environment Variables

#### Application Configuration
```env
APP_NAME="PangAIa Shop"
APP_ENV=production
APP_KEY=base64:your-app-key
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

#### Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pangaia_shop
DB_USERNAME=username
DB_PASSWORD=password
```

#### Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@pangaiashop.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### File Storage Configuration
```env
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
```

#### Session Configuration
```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
```

#### Cache Configuration
```env
CACHE_DRIVER=file
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Application Settings

#### Admin Panel Settings
- **Pagination**: Configurable page sizes (10, 15, 25, 50, 100)
- **File Uploads**: Image size limits and allowed types
- **Security**: Session timeouts and access controls
- **Notifications**: System alerts and warnings

#### E-Commerce Settings
- **Currency**: Default currency and formatting
- **Shipping**: Default rates and calculation methods
- **Tax**: Tax rates and calculation rules
- **Inventory**: Low stock thresholds and alerts

---

## Development Guidelines

### Code Standards

#### PHP Coding Standards
- Follow PSR-12 coding standards
- Use Laravel Pint for code formatting
- Implement proper DocBlocks for all methods
- Use type hints and return types

#### Laravel Best Practices
- Use Eloquent relationships instead of manual joins
- Implement Form Request validation
- Use Resource classes for API responses
- Follow single responsibility principle

#### Database Guidelines
- Use migrations for all schema changes
- Implement proper foreign key constraints
- Use soft deletes for data preservation
- Index frequently queried columns

### Testing Strategy

#### Test Types
1. **Unit Tests**: Individual component testing
2. **Feature Tests**: Application workflow testing
3. **Integration Tests**: Third-party service testing
4. **Browser Tests**: Frontend interaction testing

#### Test Coverage
- Controllers: Request handling and responses
- Models: Relationships and business logic
- Services: Complex business operations
- API: Endpoint functionality and security

### Git Workflow

#### Branch Strategy
- `main`: Production-ready code
- `develop`: Integration branch
- `feature/*`: Feature development
- `hotfix/*`: Production bug fixes

#### Commit Standards
- Use conventional commit messages
- Include issue numbers in commits
- Keep commits atomic and focused
- Write descriptive commit messages

---

## Testing

### Test Setup

#### PHPUnit Configuration
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

#### Test Environment
```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### Test Categories

#### Unit Tests
- Model relationships and methods
- Helper function testing
- Service class testing
- Validation rule testing

#### Feature Tests
- Authentication workflows
- API endpoint testing
- Admin panel functionality
- E-commerce processes

#### Browser Tests
- Frontend user interactions
- JavaScript functionality
- Form submissions
- Navigation flows

---

## Deployment

### Production Deployment

#### Server Requirements
- **PHP**: 8.2+ with required extensions
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx or Apache with PHP-FPM
- **SSL Certificate**: HTTPS encryption
- **Memory**: Minimum 512MB RAM

#### Deployment Steps

1. **Code Deployment**
```bash
# Deploy code to server
git clone <repository-url>
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

2. **Environment Setup**
```bash
# Set up environment
cp .env.example .env
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. **Database Migration**
```bash
# Run migrations
php artisan migrate --force
```

4. **Storage Setup**
```bash
# Set up storage
php artisan storage:link
chmod -R 755 storage bootstrap/cache
```

5. **Queue Setup**
```bash
# Configure queue workers
php artisan queue:restart
```

### Performance Optimization

#### Caching Strategy
- **Config Caching**: `php artisan config:cache`
- **Route Caching**: `php artisan route:cache`
- **View Caching**: `php artisan view:cache`
- **Query Caching**: Implement Redis for database caching

#### Database Optimization
- Index optimization for frequently queried columns
- Query optimization using Laravel Debugbar
- Connection pooling for high-traffic scenarios
- Database query caching

#### Asset Optimization
- Image optimization and compression
- CSS and JavaScript minification
- CDN implementation for static assets
- Browser caching headers

---

## Troubleshooting

### Common Issues

#### Authentication Issues
**Issue**: Admin cannot log in
**Solution**: 
1. Verify admin account is active: `SELECT * FROM admins WHERE email = 'admin@example.com'`
2. Check middleware configuration in `bootstrap/app.php`
3. Clear cache: `php artisan cache:clear`

#### Cart Issues
**Issue**: Items disappearing from cart
**Solution**:
1. Check session configuration in `.env`
2. Verify cart cleanup middleware isn't removing items
3. Check guest cart service implementation

#### Database Issues
**Issue**: Migration errors
**Solution**:
1. Check database connection in `.env`
2. Verify database user permissions
3. Run migrations individually: `php artisan migrate --step`

#### Performance Issues
**Issue**: Slow page loads
**Solution**:
1. Enable query caching
2. Optimize database indexes
3. Implement Redis caching
4. Use Laravel Debugbar to identify slow queries

### Debug Tools

#### Laravel Debugbar
```bash
# Install debugbar
composer require barryvdh/laravel-debugbar --dev

# Publish config
php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"
```

#### Log Monitoring
```bash
# Monitor application logs
tail -f storage/logs/laravel.log

# View specific error logs
php artisan log:show
```

#### Database Debugging
```bash
# Enable query logging
DB::enableQueryLog();
dd(DB::getQueryLog());
```

### Emergency Access

#### Admin Emergency Routes
The system includes emergency admin access routes:
- `/admin-access`: Emergency admin login options
- `/admin-test-route`: Test admin routing
- `/direct-admin-login`: Direct admin login bypass

#### Database Recovery
```sql
-- Reset admin password
UPDATE admins SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin@example.com';

-- Activate admin account
UPDATE admins SET is_active = 1 WHERE email = 'admin@example.com';
```

---

## Support & Maintenance

### Regular Maintenance Tasks

#### Daily Tasks
- Monitor error logs
- Check system performance
- Review failed jobs queue
- Monitor disk space usage

#### Weekly Tasks
- Update dependencies
- Review security logs
- Backup database
- Performance optimization

#### Monthly Tasks
- Security audit
- Database optimization
- Update documentation
- Review and update policies

### Monitoring & Alerts

#### System Monitoring
- Application performance monitoring
- Database performance tracking
- Error rate monitoring
- Resource usage alerts

#### Security Monitoring
- Failed login attempt tracking
- Unusual access pattern detection
- Admin activity monitoring
- Data breach detection

---

## Changelog & Version History

### Version 1.0 (Current)
- Initial release with full e-commerce functionality
- Multi-guard authentication system
- Comprehensive admin panel
- API-first architecture
- Advanced cart management
- Order processing system
- Review and rating system
- Promotional code system

### Planned Features
- **v1.1**: Multi-vendor marketplace support
- **v1.2**: Advanced analytics dashboard
- **v1.3**: Mobile app API enhancements
- **v1.4**: AI-powered product recommendations
- **v1.5**: Multi-language and currency support

---

## License & Credits

### License
This project is licensed under the MIT License. See the LICENSE file for details.

### Credits
- **Laravel Framework**: The PHP framework for web artisans
- **Laravel Community**: For extensive documentation and support
- **Contributors**: All developers who contributed to this project

### Third-Party Packages
- **Laravel Sanctum**: API authentication
- **Laravel IDE Helper**: Development assistance
- **Doctrine DBAL**: Database abstraction
- **Laravel Pint**: Code formatting

---

*This documentation is maintained by the PangAIa Shop development team. For questions or contributions, please contact the development team.*
