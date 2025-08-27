<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Tag
 *
 * Categorizes translation keys for grouping/filtering (e.g., web, mobile, auth).
 *
 * @property int $id
 * @property string $name
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TranslationKey> $translationKeys
 */
class Tag extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    /**
     * Translation keys associated with this tag.
     *
     * @return BelongsToMany
     */
    public function translationKeys(): BelongsToMany
    {
        return $this->belongsToMany(TranslationKey::class, 'translation_key_tags');
    }
}
