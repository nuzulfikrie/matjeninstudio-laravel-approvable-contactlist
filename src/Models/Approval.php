<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use MatJeninStudio\ContactApprovable\Database\Factories\ApprovalFactory;

class Approval extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ApprovalFactory::new();
    }

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'contact_id',
    ];

    /**
     * Get the table name from config.
     */
    public function getTable(): string
    {
        return config('contact-approvable.table_names.approvals', 'approvals');
    }

    /**
     * Get the parent approvable model.
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the contact that owns this approval.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get all approval records for this approval.
     */
    public function records(): HasMany
    {
        return $this->hasMany(ApprovalRecord::class);
    }

    /**
     * Check if approval is pending (no records yet).
     */
    public function isPending(): bool
    {
        return $this->records()->count() === 0;
    }

    /**
     * Check if approval has been approved.
     */
    public function isApproved(): bool
    {
        return $this->records()
            ->where('is_approved', true)
            ->exists();
    }

    /**
     * Check if approval has been rejected.
     */
    public function isRejected(): bool
    {
        return $this->records()
            ->where('is_approved', false)
            ->exists();
    }

    /**
     * Get approval status as string.
     */
    public function getStatusAttribute(): string
    {
        if ($this->isPending()) {
            return 'pending';
        }

        if ($this->isApproved()) {
            return 'approved';
        }

        if ($this->isRejected()) {
            return 'rejected';
        }

        return 'unknown';
    }

    /**
     * Scope a query to only include pending approvals.
     */
    public function scopePending($query)
    {
        return $query->doesntHave('records');
    }

    /**
     * Scope a query to only include approved approvals.
     */
    public function scopeApproved($query)
    {
        return $query->whereHas('records', function ($q) {
            $q->where('is_approved', true);
        });
    }

    /**
     * Scope a query to only include rejected approvals.
     */
    public function scopeRejected($query)
    {
        return $query->whereHas('records', function ($q) {
            $q->where('is_approved', false);
        });
    }
}

