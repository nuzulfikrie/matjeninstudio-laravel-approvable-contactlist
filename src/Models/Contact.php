<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MatJeninStudio\ContactApprovable\Database\Factories\ContactFactory;

class Contact extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ContactFactory::new();
    }

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the table name from config.
     */
    public function getTable(): string
    {
        return config('contact-approvable.table_names.contacts', 'contacts');
    }

    /**
     * Get all users associated with this contact.
     */
    public function users(): BelongsToMany
    {
        $userModel = config('contact-approvable.user_model', 'App\\Models\\User');
        $tableName = config('contact-approvable.table_names.contact_user', 'contact_user');

        return $this->belongsToMany($userModel, $tableName)
            ->withPivot('is_approver')
            ->withTimestamps();
    }

    /**
     * Get only the approvers for this contact.
     */
    public function approvers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_approver', true);
    }

    /**
     * Get all approvals for this contact.
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    /**
     * Scope a query to only include active contacts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive contacts.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
