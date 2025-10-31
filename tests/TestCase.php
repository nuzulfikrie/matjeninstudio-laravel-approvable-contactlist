<?php

namespace MatJeninStudio\ContactApprovable\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use MatJeninStudio\ContactApprovable\ContactApprovableServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'MatJeninStudio\\ContactApprovable\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ContactApprovableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // Use in-memory SQLite database for tests
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configure the test User model
        $app['config']->set('contact-approvable.user_model', 'MatJeninStudio\\ContactApprovable\\Tests\\Models\\User');

        // Run migrations
        $migrations = [
            include __DIR__.'/../database/migrations/2024_01_01_000000_create_users_table.php',
            include __DIR__.'/../database/migrations/2024_01_01_000001_create_contacts_table.php',
            include __DIR__.'/../database/migrations/2024_01_01_000002_create_contact_user_table.php',
            include __DIR__.'/../database/migrations/2024_01_01_000003_create_approvals_table.php',
            include __DIR__.'/../database/migrations/2024_01_01_000004_create_approval_records_table.php',
            include __DIR__.'/../database/migrations/2024_01_01_000005_create_documents_table.php',
        ];

        foreach ($migrations as $migration) {
            $migration->up();
        }
    }
}
