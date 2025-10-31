<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MatJeninStudio\ContactApprovable\Models\Approval;
use MatJeninStudio\ContactApprovable\Models\Contact;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\MatJeninStudio\ContactApprovable\Models\Approval>
 */
class ApprovalFactory extends Factory
{
    protected $model = Approval::class;

    public function definition(): array
    {
        return [
            'approvable_type' => 'Workbench\\App\\Models\\Document',
            'approvable_id' => 1,
            'contact_id' => Contact::factory(),
        ];
    }

    /**
     * Indicate that the approval is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            // Pending approvals have no records, so no changes needed
        ]);
    }
}
