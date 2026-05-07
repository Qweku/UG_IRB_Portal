# UG IRB Portal Migration to Laravel: Architectural Blueprint & Migration Strategy

## Executive Summary

This document presents a comprehensive architectural blueprint for migrating the existing procedural PHP-based UG IRB Portal to a modern, scalable Laravel ecosystem. The current system employs a custom routing mechanism with role-based access control for managing research applications across three user types: applicants, reviewers, and administrators.

The proposed architecture adopts **Domain-Driven Design (DDD)** principles with bounded contexts to ensure scalability, testability, and clear separation of concerns. The migration will transform monolithic procedural code into a structured, maintainable Laravel application while preserving all existing functionality.

## 1. Architectural Design Pattern Strategy

### Recommended Pattern: Domain-Driven Design (DDD) with Service-Repository Pattern

**Justification:**
- **Scalability**: DDD's bounded contexts align perfectly with the application's distinct domains (Application Submission, Review Management, Administration, User Management)
- **Testability**: Domain logic encapsulation enables comprehensive unit testing independent of framework concerns
- **Separation of Concerns**: Clear boundaries between domain logic, infrastructure, and presentation layers
- **Maintainability**: The current procedural code lacks structure; DDD provides a framework for organized growth

**DDD Bounded Contexts Identified:**
1. **Application Submission Domain** - Handles research application workflows for different applicant types
2. **Review Management Domain** - Manages IRB review processes, meetings, and decisions
3. **Administration Domain** - System configuration, user management, and reporting
4. **User Management Domain** - Authentication, authorization, and user profiles

**Service-Repository Pattern Integration:**
- **Services**: Contain domain logic, business rules, and orchestrate complex operations
- **Repositories**: Abstract data access, enabling framework-agnostic domain models
- **Actions**: Handle command-based operations for better testability and reusability

## 2. Data Transformation & Modeling Strategy

### Legacy Database Analysis

The current system uses multiple interconnected tables including:
- `users` / `applicant_users` - User management with role differentiation
- `applications` / `studies` - Research application data with status tracking
- `application_reviews` - Review workflow management
- Supporting tables: `contacts`, `institutions`, `irb_meetings`, `study_personnel`, etc.

### Migration Strategy

**Phase 1: Schema Migration**
```bash
php artisan make:migration create_legacy_users_table
php artisan make:migration create_legacy_applications_table
php artisan make:migration create_legacy_reviews_table
# ... additional migrations for all legacy tables
```

**Data Integrity Preservation:**
- Use Laravel Migrations with foreign key constraints
- Implement database seeders for reference data (review types, risk categories, etc.)
- Create data migration scripts to transform legacy relationships

**Eloquent Model Mapping:**
```php
// Domain/User/Models/User.php
class User extends Authenticatable
{
    protected $fillable = ['email', 'password', 'role'];

    public function applicantProfile()
    {
        return $this->hasOne(ApplicantProfile::class);
    }
}

// Domain/Application/Models/Application.php
class Application extends Model
{
    protected $fillable = ['protocol_number', 'study_title', 'status'];

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
```

**Testing Strategy:**
- **Factories**: Create comprehensive factories for all entities
- **Seeders**: Populate test environments with realistic data
- **Feature Tests**: Test complete workflows (application submission → review → approval)

## 3. Advanced Directory & File Structure

### Proposed Laravel Structure with DDD

```
app/
├── Console/                    # Artisan commands
├── Domain/                     # Domain layer (DDD)
│   ├── Application/           # Application Submission Domain
│   │   ├── Actions/           # Command handlers
│   │   │   ├── SubmitApplication.php
│   │   │   ├── UpdateApplicationStatus.php
│   │   │   └── AssignReviewer.php
│   │   ├── DTOs/              # Data Transfer Objects
│   │   │   ├── ApplicationData.php
│   │   │   └── ReviewData.php
│   │   ├── Models/            # Domain models
│   │   │   ├── Application.php
│   │   │   ├── Review.php
│   │   │   └── Study.php
│   │   ├── Repositories/      # Data abstraction
│   │   │   ├── ApplicationRepository.php
│   │   │   └── ReviewRepository.php
│   │   └── Services/          # Domain services
│   │       ├── ApplicationService.php
│   │       └── ReviewAssignmentService.php
│   ├── Review/                # Review Management Domain
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   ├── Models/
│   │   ├── Repositories/
│   │   └── Services/
│   ├── Administration/        # Administration Domain
│   └── User/                  # User Management Domain
├── Http/
│   ├── Controllers/           # Web controllers
│   │   ├── Api/               # API controllers
│   │   └── Web/               # Web controllers
│   ├── Middleware/            # Custom middleware
│   ├── Requests/              # Form request validation
│   │   ├── ApplicationSubmissionRequest.php
│   │   └── UserRegistrationRequest.php
│   └── Resources/             # API resources
├── Infrastructure/            # Infrastructure layer
│   ├── Database/              # Migrations, seeders, factories
│   ├── Services/              # External integrations
│   └── Repositories/          # Repository implementations
├── Jobs/                      # Queue jobs
├── Events/                    # Event classes
├── Listeners/                 # Event listeners
└── Policies/                  # Authorization policies
```

### Key Design Decisions

**DTOs for Type Safety:**
```php
// Domain/Application/DTOs/ApplicationData.php
class ApplicationData extends DataTransferObject
{
    public string $protocol_number;
    public string $study_title;
    public string $applicant_type;
    public ?int $institution_id;
    public array $personnel;
    public array $documents;
}
```

**Action Classes for Encapsulated Logic:**
```php
// Domain/Application/Actions/SubmitApplication.php
class SubmitApplication
{
    public function __construct(
        private ApplicationRepository $repository,
        private ValidationService $validator
    ) {}

    public function execute(ApplicationData $data): Application
    {
        // Domain logic here
        $this->validator->validateApplication($data);
        return $this->repository->create($data);
    }
}
```

## 4. Logic Refactoring & Component Mapping

### Business Logic Migration Strategy

**Current Procedural Code → Laravel Components:**

| Legacy Component | Laravel Component | Example |
|------------------|-------------------|---------|
| `user/handlers/register.php` | `Domain/User/Actions/RegisterUser.php` + `UserController@register` | User registration with validation |
| Inline business logic in pages | Service classes | Application status updates |
| Direct database queries in helpers.php | Repository pattern | Data retrieval abstraction |
| Session-based auth checks | Middleware + Policies | Role-based access control |

**Request Handling Transformation:**
- **Legacy**: Direct POST handling in handler files
- **Laravel**: Form Request classes with validation rules
- **Example**: `RegisterUserRequest.php` with comprehensive validation

**Asynchronous Operations:**
- **Legacy**: Synchronous processing
- **Laravel**: Queue jobs for email notifications, report generation
- **Example**: `SendApplicationNotification` job for status updates

## 5. Phased Implementation Roadmap

### Phase 1: Foundation & Schema (2-3 weeks)
**Objectives:** Establish Laravel foundation and migrate database schema
- Set up Laravel project structure
- Create migrations for all legacy tables
- Implement basic Eloquent models
- Set up authentication scaffolding
- Configure environment and dependencies

**Deliverables:**
- Functional Laravel installation
- Complete database schema migration
- Basic user authentication
- Seeders for reference data

### Phase 2: Core Domain Logic (4-5 weeks)
**Objectives:** Implement domain services and core business logic
- Create domain models and repositories
- Implement service layer for business rules
- Build action classes for command handling
- Develop comprehensive test suite
- Set up API endpoints for AJAX operations

**Deliverables:**
- Domain layer implementation
- Repository pattern abstraction
- Unit and feature tests (80% coverage)
- API-first backend services

### Phase 3: Interface & Integration (3-4 weeks)
**Objectives:** Migrate frontend interfaces and integrate with new backend
- Transform legacy pages to Blade templates
- Implement controllers for web routes
- Migrate JavaScript functionality to modern approaches
- Set up frontend build process (Vite)
- Implement role-based middleware

**Deliverables:**
- Responsive web interface
- Complete routing system
- Frontend-backend integration
- User acceptance testing environment

### Phase 4: Quality Assurance & Optimization (2-3 weeks)
**Objectives:** Performance tuning and production readiness
- Implement caching strategies
- Set up queue management for heavy operations
- Performance optimization and monitoring
- Security hardening and penetration testing
- Documentation and deployment preparation

**Deliverables:**
- Production-ready application
- Performance benchmarks
- Security audit results
- Deployment documentation

## 6. Security, Authorization, and Performance Optimization

### Security Implementation
**Authentication:**
- Laravel Sanctum for API authentication
- Session-based auth for web interface
- Multi-factor authentication for admin accounts

**Authorization:**
```php
// App/Policies/ApplicationPolicy.php
class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        return $user->id === $application->applicant_id ||
               $user->hasRole(['admin', 'super_admin']);
    }

    public function review(User $user, Application $application): bool
    {
        return $user->hasRole(['admin', 'super_admin', 'reviewer']);
    }
}
```

### Performance Optimization
**Eager Loading Strategy:**
```php
// Prevent N+1 queries in application listings
$applications = Application::with([
    'applicant:id,full_name,email',
    'reviews.reviewer:id,full_name',
    'institution:id,institution_name'
])->paginate(20);
```

**Caching Implementation:**
- Redis for session storage and general caching
- Cache application metadata and reference data
- Queue critical path operations (report generation, email sending)

**Queue Management:**
- Separate queues for different operation types
- Priority queues for urgent notifications
- Failed job monitoring and retries

## Feature Mapping & Migration Strategy

Based on codebase analysis, here are the discrete functional features and their restructuring:

### 1. User Management Feature
**Legacy:** `user/handlers/register.php`, session-based auth in `index.php`
**Laravel Mapping:**
- `Domain/User/Actions/RegisterUser.php`
- `Domain/User/Actions/AuthenticateUser.php`
- `App/Http/Controllers/AuthController.php`
- `App/Policies/UserPolicy.php`

### 2. Application Submission Feature
**Legacy:** `applicant/pages/student_application.php`, multi-step forms
**Laravel Mapping:**
- `Domain/Application/Actions/SubmitApplication.php`
- `Domain/Application/Services/ApplicationWorkflowService.php`
- `App/Http/Controllers/ApplicationController.php`
- `App/Http/Requests/SubmitApplicationRequest.php`

### 3. Review Management Feature
**Legacy:** `reviewer/pages/review_detail.php`, assignment logic
**Laravel Mapping:**
- `Domain/Review/Actions/AssignReviewer.php`
- `Domain/Review/Actions/SubmitReview.php`
- `Domain/Review/Services/ReviewAssignmentService.php`
- `App/Http/Controllers/ReviewController.php`

### 4. Administration Dashboard Feature
**Legacy:** `dashboard/applications_content.php`, various admin functions
**Laravel Mapping:**
- `Domain/Administration/Actions/UpdateSystemSettings.php`
- `Domain/Administration/Services/ReportingService.php`
- `App/Http/Controllers/Admin/DashboardController.php`
- `App/Http/Controllers/Admin/UserManagementController.php`

### 5. Document Management Feature
**Legacy:** File upload handlers, document storage
**Laravel Mapping:**
- `Domain/Application/Actions/UploadDocument.php`
- `Infrastructure/Services/DocumentStorageService.php`
- `App/Http/Controllers/DocumentController.php`

This architectural blueprint provides a clear path forward for modernizing the UG IRB Portal while maintaining all existing functionality and preparing for future scalability. The DDD approach ensures the codebase remains maintainable and testable as the system grows.