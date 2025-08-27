<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Translation
 *
 * Represents a localized text value for a given translation key and locale.
 * This maps to the `translations` table and stores the most up-to-date value
 * along with its moderation status.
 *
 * @property int $id
 * @property int $translation_key_id
 * @property int $locale_id
 * @property string $value
 * @property string $status
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read TranslationKey $key
 * @property-read Locale $locale
 */
class Translation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'translation_key_id',
        'locale_id',
        'value',
        'status',
    ];

    /**
     * Get the parent translation key.
     *
     * @return BelongsTo
     */
    public function key(): BelongsTo
    {
        return $this->belongsTo(TranslationKey::class, 'translation_key_id');
    }

    /**
     * Get the locale of this translation.
     *
     * @return BelongsTo
     */
    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }
}


