<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use MatJeninStudio\ContactApprovable\Events\ApprovalRequestedEvent;
use MatJeninStudio\ContactApprovable\Listeners\NotifyApproversListener;
use MatJeninStudio\ContactApprovable\Services\ContactApprovableManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ContactApprovableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('contact-approvable')
            ->hasConfigFile()
            ->hasMigrations([
                'create_contacts_table',
                'create_contact_user_table',
                'create_approvals_table',
                'create_approval_records_table',
            ])
            ->hasViews()
            ->hasRoute('web');
    }

    public function packageRegistered(): void
    {
        // Register the main manager class
        $this->app->singleton(ContactApprovableManager::class, function ($app) {
            return new ContactApprovableManager();
        });

        // Bind the manager to the facade
        $this->app->alias(ContactApprovableManager::class, 'contact-approvable');
    }

    public function packageBooted(): void
    {
        // Register event listeners
        Event::listen(
            ApprovalRequestedEvent::class,
            NotifyApproversListener::class
        );

        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->package->basePath('/../config/contact-approvable.php') => config_path('contact-approvable.php'),
            ], 'contact-approvable-config');

            $this->publishes([
                $this->package->basePath('/../database/migrations') => database_path('migrations'),
            ], 'contact-approvable-migrations');

            $this->publishes([
                $this->package->basePath('/../resources/views') => resource_path('views/vendor/contact-approvable'),
            ], 'contact-approvable-views');
        }
    }
}
