<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Reimbursement extends Model
{
    use HasFactory;

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = ['image_url'];

    /**
     * Boot the model - cache invalidation on changes.
     */
    protected static function booted(): void
    {
        static::saved(function ($reimbursement) {
            \Illuminate\Support\Facades\Cache::forget("user_{$reimbursement->user_id}_pending_summary");
        });

        static::deleted(function ($reimbursement) {
            \Illuminate\Support\Facades\Cache::forget("user_{$reimbursement->user_id}_pending_summary");
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'client_id',
        'category_id',
        'category_name',
        'amount',
        'transaction_date',
        'note',
        'image_path',
        'status',
        'report_id',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_IN_REPORT = 'in_report';
    const STATUS_PAID = 'paid';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    /**
     * Get the user that owns the reimbursement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client for the reimbursement.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the category for the reimbursement.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the report for the reimbursement.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        // If already a full URL (Cloudinary), return as-is
        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        try {
            return Storage::url($this->image_path);
        } catch (\Exception $e) {
            // Fallback: construct Cloudinary URL manually
            $cloudName = config('filesystems.disks.cloudinary.cloud_name');
            if ($cloudName) {
            return "https://res.cloudinary.com/{$cloudName}/image/upload/{$this->image_path}";
            }
            return null;
        }
    }

    /**
     * Get the display category name.
     * Returns category relationship name if exists, otherwise custom category_name.
     */
    public function getCategoryDisplayNameAttribute(): ?string
    {
        if ($this->category_id && $this->category) {
            return $this->category->name;
        }
        
        return $this->category_name;
    }


    /**
     * Scope a query to only include pending reimbursements.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include reimbursements not in a report.
     */
    public function scopeNotInReport($query)
    {
        return $query->whereNull('report_id');
    }
}
