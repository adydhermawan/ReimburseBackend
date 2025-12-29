<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['period_label', 'total_entries'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'period_start',
        'period_end',
        'total_amount',
        'entry_count',
        'status',
        'pdf_path',
        'payment_date',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_GENERATED = 'generated';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PAID = 'paid';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'payment_date' => 'date',
            'total_amount' => 'decimal:2',
            'entry_count' => 'integer',
        ];
    }

    /**
     * Get the user that owns the report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reimbursements for the report.
     */
    public function reimbursements(): HasMany
    {
        return $this->hasMany(Reimbursement::class);
    }

    /**
     * Get the PDF download URL.
     */
    public function getPdfUrlAttribute(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }

        return asset('storage/' . $this->pdf_path);
    }

    /**
     * Calculate total from related reimbursements.
     */
    public function calculateTotal(): float
    {
        return $this->reimbursements()->sum('amount');
    }

    /**
     * Get the period label (e.g., "December 2024").
     */
    public function getPeriodLabelAttribute(): string
    {
        return $this->period_start->format('F Y');
    }

    /**
     * Get the total entries count (alias for entry_count).
     */
    public function getTotalEntriesAttribute(): int
    {
        return $this->entry_count ?? 0;
    }
}
