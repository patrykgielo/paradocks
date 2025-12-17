<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePage extends Model
{
    /**
     * Table name (singular, not plural).
     */
    protected $table = 'home_page';

    /**
     * Disable auto-increment (singleton with fixed id=1).
     */
    public $incrementing = false;

    /**
     * Fillable fields.
     */
    protected $fillable = [
        'sections',
        'seo_title',
        'seo_description',
        'seo_image',
    ];

    /**
     * Casts for type safety.
     */
    protected $casts = [
        'sections' => 'array',
    ];

    /**
     * Get singleton instance (always id=1).
     */
    public static function getInstance(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    /**
     * Override save to enforce singleton pattern.
     */
    public function save(array $options = []): bool
    {
        // Always force id=1
        $this->id = 1;

        return parent::save($options);
    }

    /**
     * Get sections by type.
     *
     * @param  string  $type  Section type (e.g., 'hero', 'content_grid')
     */
    public function getSectionsByType(string $type): array
    {
        return collect($this->sections ?? [])
            ->filter(fn ($section) => $section['type'] === $type)
            ->all();
    }

    /**
     * Check if a section type exists.
     */
    public function hasSection(string $type): bool
    {
        return collect($this->sections ?? [])
            ->contains(fn ($section) => $section['type'] === $type);
    }

    /**
     * Get effective SEO title (fallback to app name).
     */
    public function getEffectiveSeoTitle(): string
    {
        return $this->seo_title ?? config('app.name').' - Profesjonalny Detailing';
    }

    /**
     * Get effective SEO description (fallback to default).
     */
    public function getEffectiveSeoDescription(): string
    {
        return $this->seo_description
            ?? 'Profesjonalny detailing samochodowy. Rezerwuj online, płać po usłudze. Gwarancja satysfakcji.';
    }
}
