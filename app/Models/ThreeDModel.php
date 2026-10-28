<?php

namespace App\Models;

use App\Enums\ThreeDModelType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * An equipment model of the 3D library (inverters, batteries, charge controllers, breakers …).
 * Files live in database/models3d and are loaded by `php artisan models3d:import`.
 * Sections and the site background reference a model as "library:{slug}".
 */
class ThreeDModel extends Model
{
    use HasFactory, HasTranslations;

    public const KEY_PREFIX = 'library:';

    public array $translatable = ['name'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => ThreeDModelType::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('type')->orderBy('sort_order')->orderBy('id');
    }

    /** The value stored on a section / in the site settings. */
    public function key(): string
    {
        return self::KEY_PREFIX.$this->slug;
    }

    public static function slugFromKey(?string $key): ?string
    {
        return is_string($key) && str_starts_with($key, self::KEY_PREFIX) ? substr($key, strlen(self::KEY_PREFIX)) : null;
    }

    /** The active model a "library:{slug}" key points to, if any. */
    public static function fromKey(?string $key): ?self
    {
        $slug = self::slugFromKey($key);

        return $slug ? static::query()->active()->where('slug', $slug)->first() : null;
    }

    public function fileUrl(): string
    {
        return Storage::disk('public')->url($this->file);
    }

    public function previewUrl(): ?string
    {
        return $this->preview ? Storage::disk('public')->url($this->preview) : null;
    }

    public function label(): string
    {
        return trim(($this->brand ? $this->brand.' · ' : '').$this->name);
    }
}
