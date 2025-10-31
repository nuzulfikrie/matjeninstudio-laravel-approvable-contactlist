<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MatJeninStudio\ContactApprovable\Models\Contact;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\MatJeninStudio\ContactApprovable\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' Approvers',
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the contact is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
