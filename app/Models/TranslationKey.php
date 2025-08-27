<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Class TranslationKey
 *
 * Canonical identifier for a piece of translatable text.
 * Stores optional human-readable description and relationships to tags and translations.
 *
 * @property int $id
 * @property string $key_name
 * @property string|null $description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Tag> $tags
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Translation> $translations
 */
class TranslationKey extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'key_name',
        'description',
    ];

    /**
     * Tags associated with this translation key.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'translation_key_tags');
    }

    /**
     * List of localized translations for this key.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    /**
     * Scope to filter by locale.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLocale($query, string $localeCode)
    {
        return $query->join('translations', 'translation_keys.id', '=', 'translations.translation_key_id')
            ->join('locales', 'translations.locale_id', '=', 'locales.id')
            ->where('locales.code', $localeCode);
    }

    /**
     * Scope to filter by tags.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithTags($query, array $tagNames)
    {
        return $query->whereHas('tags', function ($q) use ($tagNames) {
            $q->whereIn('name', $tagNames);
        });
    }

    /**
     * Get export data for a specific locale with optional tag filtering.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getExportData(string $localeCode, array $tagNames = [])
    {
        // Use ultra-fast raw SQL with EXISTS for maximum performance
        $sql = '
            SELECT 
                tk.key_name,
                t.value
            FROM translation_keys tk
            INNER JOIN translations t ON tk.id = t.translation_key_id
            INNER JOIN locales l ON t.locale_id = l.id
            WHERE l.code = ?
        ';

        $params = [$localeCode];

        if (! empty($tagNames)) {
            $placeholders = str_repeat('?,', count($tagNames) - 1).'?';
            $sql .= "
                AND EXISTS (
                    SELECT 1
                    FROM translation_key_tags tkt
                    INNER JOIN tags tag ON tkt.tag_id = tag.id
                    WHERE tkt.translation_key_id = tk.id
                    AND tag.name IN ($placeholders)
                )
            ";
            $params = array_merge($params, $tagNames);
        }

        $sql .= ' ORDER BY tk.id';

        return DB::select($sql, $params);
    }

    /**
     * Stream export data for a specific locale with optional tag filtering.
     *
     * @return \Generator
     */
    public static function streamExportData(string $localeCode, array $tagNames = [], int $chunkSize = 1000)
    {
        // Use ultra-fast raw SQL with EXISTS and LIMIT/OFFSET for streaming
        $sql = '
            SELECT 
                tk.key_name,
                t.value
            FROM translation_keys tk
            INNER JOIN translations t ON tk.id = t.translation_key_id
            INNER JOIN locales l ON t.locale_id = l.id
            WHERE l.code = ?
        ';

        $params = [$localeCode];

        if (! empty($tagNames)) {
            $placeholders = str_repeat('?,', count($tagNames) - 1).'?';
            $sql .= "
                AND EXISTS (
                    SELECT 1
                    FROM translation_key_tags tkt
                    INNER JOIN tags tag ON tkt.tag_id = tag.id
                    WHERE tkt.translation_key_id = tk.id
                    AND tag.name IN ($placeholders)
                )
            ";
            $params = array_merge($params, $tagNames);
        }

        $sql .= ' ORDER BY tk.id LIMIT ? OFFSET ?';

        $offset = 0;
        do {
            $chunkParams = array_merge($params, [$chunkSize, $offset]);
            $results = DB::select($sql, $chunkParams);

            foreach ($results as $row) {
                yield $row->key_name => $row->value ?? '';
            }

            $offset += $chunkSize;
        } while (count($results) === $chunkSize);
    }
}
