# Laravel Contact Approvable

<p align="center">
<a href="https://packagist.org/packages/matjeninstudio/laravel-contact-approvable"><img src="https://img.shields.io/packagist/v/matjeninstudio/laravel-contact-approvable.svg?style=flat-square" alt="Latest Version on Packagist"></a>
<a href="https://github.com/nuzulfikrie/laravel-contact-approvable/actions"><img src="https://img.shields.io/github/actions/workflow/status/nuzulfikrie/laravel-contact-approvable/tests.yml?branch=main&style=flat-square" alt="GitHub Tests Action Status"></a>
<a href="https://packagist.org/packages/matjeninstudio/laravel-contact-approvable"><img src="https://img.shields.io/packagist/dt/matjeninstudio/laravel-contact-approvable.svg?style=flat-square" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/matjeninstudio/laravel-contact-approvable"><img src="https://img.shields.io/packagist/l/matjeninstudio/laravel-contact-approvable.svg?style=flat-square" alt="License"></a>
</p>

A powerful Laravel package that brings enterprise-grade approval workflows to your application. Manage contacts, approval requests, and audit trails with polymorphic relationships, real-time notifications, and a beautiful admin interface inspired by Laravel Telescope.

## ✨ Features

- 🔄 **Polymorphic Approval Workflows** - Attach approval workflows to any Eloquent model
- 👥 **Contact Management** - Create and manage contacts with user relationships
- 📝 **Approval Records & Audit Trails** - Track every approval decision with comments
- 🔔 **Multi-Channel Notifications** - Email, database, and custom notification channels
- ⚡ **Auto-Approval Support** - Configure threshold-based or percentage-based auto-approvals
- ⏰ **Deadline Management** - Set approval deadlines with automated reminders
- 🎨 **Admin Interface** - Telescope-like UI for monitoring approval workflows
- 🎯 **Event-Driven Architecture** - Comprehensive events for all approval lifecycle stages
- 🔧 **Highly Configurable** - Extensive configuration options for every feature
- 🧪 **Fully Tested** - Comprehensive test coverage with Pest PHP

## 📋 Requirements

- PHP 8.4 or higher
- Laravel 12.0 or higher

## 📦 Installation

Install the package via Composer:

```bash
composer require matjeninstudio/laravel-contact-approvable
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="MatJeninStudio\ContactApprovable\ContactApprovableServiceProvider" --tag="config"
```

Publish and run the migrations:

```bash
php artisan vendor:publish --provider="MatJeninStudio\ContactApprovable\ContactApprovableServiceProvider" --tag="migrations"
php artisan migrate
```

## 🚀 Quick Start

### 1. Add the Trait to Your Model

```php
use Illuminate\Database\Eloquent\Model;
use MatJeninStudio\ContactApprovable\Traits\Approvable;

class Document extends Model
{
    use Approvable;

    // Your model code...
}
```

### 2. Create a Contact and Request Approval

```php
// Create a contact with approvers
$contact = $document->createContact(
    name: 'Legal Department',
    users: [$legalUser1, $legalUser2],
    markAsApprover: true
);

// Request approval
$approval = $document->requestApproval($contact);
```

### 3. Approve or Reject

```php
use MatJeninStudio\ContactApprovable\Models\ApprovalRecord;

// Approve with comment
ApprovalRecord::create([
    'approval_id' => $approval->id,
    'user_id' => $legalUser1->id,
    'is_approved' => true,
    'comment' => 'Document reviewed and approved.',
]);

// Check approval status
$status = $document->getApprovalStatus(); // 'pending', 'approved', or 'rejected'
```

## 📖 Usage Guide

### Working with Contacts

Contacts are groups of users that can approve requests. Each contact can have multiple users, and users can be designated as approvers.

#### Create a Contact

```php
// Simple contact creation
$contact = $model->createContact('Finance Team');

// With users and approvers
$contact = $model->createContact(
    name: 'Executive Board',
    users: [$ceo, $cfo, $cto],
    isActive: true,
    markAsApprover: true
);

// With mixed user types
$contact = $model->createContact(
    name: 'HR Department',
    users: [1, 2, $userModel], // User IDs or models
    markAsApprover: true
);
```

#### Manage Contact Users

```php
// Attach users to existing contact
$contact = $model->attachUsersToContact($contact, [$user1, $user2], markAsApprover: true);

// Sync users (removes users not in the list)
$contact = $model->syncContactUsers($contact, [$user1, $user3]);

// Detach specific users
$contact = $model->detachUsersFromContact($contact, [$user1]);

// Detach all users
$contact = $model->detachUsersFromContact($contact);

// Update approver status
$contact = $model->updateContactUserApproverStatus($contact, $user1, isApprover: true);
```

### Approval Workflows

#### Request Approval

```php
// Request approval from a contact
$approval = $document->requestApproval($contact);

// Request with contact ID
$approval = $document->requestApproval(contactId: 1);

// Check for pending approvals (prevents duplicates)
$pendingApproval = $document->hasPendingApproval();
```

#### Process Approvals

```php
use MatJeninStudio\ContactApprovable\Models\ApprovalRecord;

// Approve
ApprovalRecord::create([
    'approval_id' => $approval->id,
    'user_id' => auth()->id(),
    'is_approved' => true,
    'comment' => 'Looks good to me!',
]);

// Reject
ApprovalRecord::create([
    'approval_id' => $approval->id,
    'user_id' => auth()->id(),
    'is_approved' => false,
    'comment' => 'Please revise section 3.',
]);
```

#### Query Approval Status

```php
// Get latest approval
$latestApproval = $document->latestApproval();

// Get approval status
$status = $document->getApprovalStatus(); // 'pending', 'approved', or 'rejected'

// Check specific states
$isPending = $approval->isPending();
$isApproved = $approval->isApproved();
$isRejected = $approval->isRejected();

// Query scopes
use MatJeninStudio\ContactApprovable\Models\Approval;

$pendingApprovals = Approval::pending()->get();
$approvedApprovals = Approval::approved()->get();
$rejectedApprovals = Approval::rejected()->get();
```

### Events

The package dispatches events for all key actions, allowing you to hook into the approval lifecycle:

```php
// Listen to approval events
Event::listen(ApprovalRequestedEvent::class, function ($event) {
    // $event->approval
    Log::info('Approval requested', ['approval_id' => $event->approval->id]);
});

Event::listen(ApprovalApprovedEvent::class, function ($event) {
    // $event->approvalRecord
    // Send custom notification, update related models, etc.
});

Event::listen(ApprovalRejectedEvent::class, function ($event) {
    // $event->approvalRecord
    // Handle rejection logic
});
```

**Available Events:**
- `ApprovalRequestedEvent` - When approval is requested
- `ApprovalApprovedEvent` - When approval is approved
- `ApprovalRejectedEvent` - When approval is rejected
- `ContactCreatedEvent` - When a contact is created
- `ContactUpdatedEvent` - When a contact is updated
- `ContactDeletedEvent` - When a contact is deleted

### Notifications

The package includes built-in notifications that can be sent via multiple channels:

```php
// Configure in config/contact-approvable.php
'notifications' => [
    'enabled' => true,
    'channels' => ['mail', 'database'],
    'queue' => true,
    'queue_name' => 'default',
],
```

**Built-in Notifications:**
- `ApprovalRequestedNotification` - Sent to approvers when approval is requested
- `ApprovalApprovedNotification` - Sent when approval is approved
- `ApprovalRejectedNotification` - Sent when approval is rejected

### Auto-Approval

Configure automatic approval based on threshold or percentage:

```php
// In config/contact-approvable.php
'auto_approve' => [
    'enabled' => true,

    // Approve after 2 approvals
    'threshold' => 2,

    // OR approve after 50% of approvers approve
    'percentage' => 50, // Set to null to use threshold instead
],
```

### Deadline Management

Set approval deadlines with automated reminders:

```php
'deadline' => [
    'enabled' => true,
    'default_days' => 7,        // Approval expires in 7 days
    'reminder_days' => 2,       // Send reminder 2 days before deadline
],
```

## ⚙️ Configuration

The package is highly configurable. Here are some key configuration options:

```php
// config/contact-approvable.php

return [
    // Admin interface route
    'route' => 'contact-approvable',

    // Middleware for admin routes
    'middleware' => ['web', 'auth'],

    // Customize table names
    'table_names' => [
        'contacts' => 'contacts',
        'contact_user' => 'contact_user',
        'approvals' => 'approvals',
        'approval_records' => 'approval_records',
    ],

    // Your User model
    'user_model' => 'App\\Models\\User',

    // Enable/disable specific events
    'events' => [
        'enabled' => true,
        'dispatch' => [
            'approval_requested' => true,
            'approval_approved' => true,
            'approval_rejected' => true,
        ],
    ],

    // Admin interface settings
    'admin' => [
        'per_page' => 15,
        'real_time' => false,
        'brand' => 'Contact Approvable',
    ],
];
```

## 🔧 Advanced Usage

### Custom Polymorphic Relationships

The approval system works with any Eloquent model:

```php
class Invoice extends Model
{
    use Approvable;
}

class PurchaseOrder extends Model
{
    use Approvable;
}

class Contract extends Model
{
    use Approvable;
}

// All can use the same approval workflow
$invoice->requestApproval($financeContact);
$purchaseOrder->requestApproval($procurementContact);
$contract->requestApproval($legalContact);
```

### Relationship Access

```php
// Get all approvals for a model
$approvals = $document->approvals;

// Get approval records through relationship
$approval->records;

// Get the approvable model from approval
$model = $approval->approvable;

// Get contact and users
$contact = $approval->contact;
$users = $contact->users;
$approvers = $contact->approvers; // Only users marked as approvers
```

## 🧪 Testing

The package includes comprehensive tests using Pest PHP:

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run architecture tests
composer test-arch

# Run static analysis
composer analyse

# Format code
composer format

# Run linting (format + analyse)
composer lint
```

## 📚 Use Cases

This package is perfect for:

- **Document Approval Systems** - Route documents through approval chains
- **Purchase Order Management** - Multi-level approval for procurement
- **Contract Review Workflows** - Legal and executive approvals
- **Time-off Requests** - HR approval workflows
- **Expense Approvals** - Finance department sign-offs
- **Content Publishing** - Editorial approval pipelines
- **Change Request Management** - IT change approval boards
- **Quality Assurance** - QA sign-off processes

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 🔐 Security

If you discover any security-related issues, please email nuzulfikrie@gmail.com instead of using the issue tracker.

## 📝 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 👨‍💻 Credits

- [Nuzul Fikrie](https://github.com/nuzulfikrie)
- [All Contributors](../../contributors)

## 🙏 Acknowledgments

- Inspired by Laravel Telescope's elegant UI design
- Built with [Spatie's Laravel Package Tools](https://github.com/spatie/laravel-package-tools)
- Tested with [Pest PHP](https://pestphp.com/)

---

<p align="center">
Made with ❤️ by <a href="https://github.com/nuzulfikrie">MatJenin Studio</a>
</p>
