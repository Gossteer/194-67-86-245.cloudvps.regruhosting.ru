<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stichoza\GoogleTranslate\GoogleTranslate;

/**
 * Class CityTranslate
 * @package App
 */
class CityTranslate extends Model
{
    /**
     * Выбор таблицы
     *
     * @var string
     */
    protected $table = 'cities_translations';

    /**
     * Атрибуты, которые можно назначать массово назначать
     *
     * @var array
     */
    protected $fillable = [
        'word', 'translation'
    ];

    /**
     * Перевод
     *
     * @param string $word
     * @return string|null $translation
     */
    public static function translate($word): ?string
    {
        $tr = new GoogleTranslate('ru');
        $model = self::where(['word' => $word])->first() ?? new self();
        if ($model->exists) {
            $translation = $model->translation;
        } else {
            $translation = $tr->translate($word);
            $model->word = $word;
            $model->translation = $translation;
            $model->save();
        }

        return $translation;
    }
}
