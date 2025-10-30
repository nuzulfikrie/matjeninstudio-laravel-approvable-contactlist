<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Interface Route
    |--------------------------------------------------------------------------
    |
    | This is the URI path where the admin interface will be accessible.
    | You can customize this to match your application's routing structure.
    |
    */
    'route' => env('CONTACT_APPROVABLE_ROUTE', 'contact-approvable'),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be applied to all admin interface routes.
    | You can customize this to add authentication, authorization, etc.
    |
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Database Table Names
    |--------------------------------------------------------------------------
    |
    | Customize the table names used by the package. This is useful if you
    | need to avoid naming conflicts or follow specific naming conventions.
    |
    */
    'table_names' => [
        'contacts' => 'contacts',
        'contact_user' => 'contact_user',
        'approvals' => 'approvals',
        'approval_records' => 'approval_records',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure how notifications are sent to approvers and other users.
    |
    */
    'notifications' => [
        'enabled' => env('CONTACT_APPROVABLE_NOTIFICATIONS_ENABLED', true),

        // Available channels: mail, database, slack, etc.
        'channels' => ['mail', 'database'],

        // Queue notifications for better performance
        'queue' => env('CONTACT_APPROVABLE_NOTIFICATIONS_QUEUE', true),

        // Queue name for notifications
        'queue_name' => env('CONTACT_APPROVABLE_NOTIFICATIONS_QUEUE_NAME', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Approval Settings
    |--------------------------------------------------------------------------
    |
    | Configure automatic approval behavior. When enabled, approvals can be
    | automatically processed based on the defined threshold.
    |
    */
    'auto_approve' => [
        // Enable or disable auto-approval
        'enabled' => env('CONTACT_APPROVABLE_AUTO_APPROVE_ENABLED', false),

        // Number of approvals needed before auto-approval
        'threshold' => env('CONTACT_APPROVABLE_AUTO_APPROVE_THRESHOLD', 1),

        // Percentage of approvers needed (alternative to threshold)
        // Set to null to use threshold instead
        'percentage' => env('CONTACT_APPROVABLE_AUTO_APPROVE_PERCENTAGE', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval Deadline Settings
    |--------------------------------------------------------------------------
    |
    | Configure deadline behavior for approval requests.
    |
    */
    'deadline' => [
        // Enable deadline feature
        'enabled' => env('CONTACT_APPROVABLE_DEADLINE_ENABLED', false),

        // Default deadline in days
        'default_days' => env('CONTACT_APPROVABLE_DEADLINE_DAYS', 7),

        // Send reminder before deadline (in days)
        'reminder_days' => env('CONTACT_APPROVABLE_DEADLINE_REMINDER_DAYS', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model used by your application. This is typically the default
    | Laravel User model, but you can customize it if needed.
    |
    */
    'user_model' => env('CONTACT_APPROVABLE_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Admin Interface Settings
    |--------------------------------------------------------------------------
    |
    | Configure the appearance and behavior of the admin interface.
    |
    */
    'admin' => [
        // Items per page in list views
        'per_page' => env('CONTACT_APPROVABLE_ADMIN_PER_PAGE', 15),

        // Enable real-time updates (requires Livewire or similar)
        'real_time' => env('CONTACT_APPROVABLE_ADMIN_REAL_TIME', false),

        // Brand name displayed in the admin interface
        'brand' => env('CONTACT_APPROVABLE_ADMIN_BRAND', 'Contact Approvable'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    |
    | Configure which events should be dispatched by the package.
    |
    */
    'events' => [
        'enabled' => true,

        // Specific events to enable/disable
        'dispatch' => [
            'approval_requested' => true,
            'approval_approved' => true,
            'approval_rejected' => true,
            'contact_created' => true,
            'contact_updated' => true,
            'contact_deleted' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging behavior for the package.
    |
    */
    'logging' => [
        'enabled' => env('CONTACT_APPROVABLE_LOGGING_ENABLED', true),
        'channel' => env('CONTACT_APPROVABLE_LOGGING_CHANNEL', 'stack'),
    ],
];
