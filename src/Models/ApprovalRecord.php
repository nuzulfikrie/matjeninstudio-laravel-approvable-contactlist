<?php

declare(strict_types=1);

namespace MatJeninStudio\ContactApprovable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MatJeninStudio\ContactApprovable\Database\Factories\ApprovalRecordFactory;

class ApprovalRecord extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ApprovalRecordFactory::new();
    }

    protected $fillable = [
        'approval_id',
        'user_id',
        'is_approved',
        'comment',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /**
     * Get the table name from config.
     */
    public function getTable(): string
    {
        return config('contact-approvable.table_names.approval_records', 'approval_records');
    }

    /**
     * Get the approval that owns this record.
     */
    public function approval(): BelongsTo
    {
        return $this->belongsTo(Approval::class);
    }

    /**
     * Get the user who made this record.
     */
    public function user(): BelongsTo
    {
        $userModel = config('contact-approvable.user_model', 'App\\Models\\User');

        return $this->belongsTo($userModel);
    }
}

