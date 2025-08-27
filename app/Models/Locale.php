<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Locale
 *
 * Represents an application language/locale (e.g., en, fr).
 *
 * @property int $id
 * @property string $code
 * @property string $name
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Translation> $translations
 */
class Locale extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Translations written for this locale.
     *
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }
}
