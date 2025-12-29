<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'version',
        'build_number',
        'download_url',
        'release_notes',
        'is_mandatory',
        'is_active',
    ];

    protected $casts = [
        'build_number' => 'integer',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the latest active version
     */
    public static function getLatest(): ?self
    {
        return self::where('is_active', true)
            ->orderByDesc('build_number')
            ->first();
    }

    /**
     * Set this version as active and deactivate others
     */
    public function setAsActive(): void
    {
        self::where('id', '!=', $this->id)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }
}
