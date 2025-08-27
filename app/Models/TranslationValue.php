<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslationValue extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'translation_key_id',
        'locale_id',
        'value',
    ];

    public function key(): BelongsTo
    {
        return $this->belongsTo(TranslationKey::class, 'translation_key_id');
    }

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }
}
