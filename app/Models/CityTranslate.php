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
     * @var string
     */
    protected $table = 'cities_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'word', 'translation'
    ];

    public static function translate($word)
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

