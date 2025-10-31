<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MatJeninStudio\ContactApprovable\Models\Approval;
use MatJeninStudio\ContactApprovable\Models\ApprovalRecord;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\MatJeninStudio\ContactApprovable\Models\ApprovalRecord>
 */
class ApprovalRecordFactory extends Factory
{
    protected $model = ApprovalRecord::class;

    public function definition(): array
    {
        $userModel = config('contact-approvable.user_model', 'App\\Models\\User');

        return [
            'approval_id' => Approval::factory(),
            'user_id' => method_exists($userModel, 'factory') ? $userModel::factory() : 1,
            'is_approved' => fake()->boolean(),
            'comment' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the record is an approval.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
            'comment' => fake()->optional()->sentence(),
        ]);
    }

    /**
     * Indicate that the record is a rejection.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
            'comment' => fake()->sentence(), // Rejections should have a comment
        ]);
    }
}
