# Laravel Contact Approvable

A Laravel package for managing contacts and approval workflows with polymorphic relationships, audit trails, and a Telescope-like admin interface.

## Installation

You can install the package via composer:

```bash
composer require matjeninstudio/laravel-contact-approvable
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="MatJeninStudio\ContactApprovable\ContactApprovableServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
    'approvers' => [
        // Add the email addresses of the approvers here
    ],
];
```

## Usage

This package provides a trait `Approvable` that you can use in your models. This trait will automatically trigger an approval workflow when a new model is created or updated.

To use the trait, simply add it to your model:

```php
use Illuminate\Database\Eloquent\Model;
use MatJeninStudio\ContactApprovable\Traits\Approvable;

class Contact extends Model
{
    use Approvable;

    // ...
}
```

When a new contact is created, an approval request will be sent to the approvers defined in the `config/contact-approvable.php` file. The approvers can then approve or reject the request.

## Testing

```bash
composer test
```

## Development

This project uses `pest` for testing, `pint` for styling and `larastan` for static analysis. The following composer scripts are available:

- `composer analyse`: Run static analysis with `phpstan`
- `composer test`: Run tests with `pest`
- `composer test-coverage`: Run tests with `pest` and generate a coverage report
- `composer test-arch`: Run architecture tests with `pest`
- `composer format`: Format the code with `pint`
- `composer lint`: Run `pint` and `phpstan`

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
