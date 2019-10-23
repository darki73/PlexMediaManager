<?php namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractMediaModel
 * @package App\Models
 */
abstract class AbstractMediaModel extends Model {

    /**
     * Model Translation Class
     * @var string|null
     */
    protected ?string $translationClass = null;

    /**
     * Get translations for the model
     * @param bool $removeLocaleReference
     * @return array
     */
    public function getModelTranslations(bool $removeLocaleReference = false) : array {
        $title = $this->title;
        $overview = $this->overview;
        $translations = [
            'title'     =>  [],
            'overview'  =>  []
        ];

        /**
         * @var Model $translationsModel
         */
        $translationsModel = (new $this->translationClass)->find($this->id);
        if ($translationsModel !== null) {
            $rawTranslations = Arr::except($translationsModel->toArray(), ['id', 'created_at', 'updated_at']);
            foreach ($rawTranslations as $column => $value) {
                [$key, $locale, $field] = explode('_', $column);
                $locale = trim($locale);
                $value = trim($value);
                $translations[$field][$locale] = [
                    $key            =>  $locale,
                    'value'         =>  $value
                ];
            }
        }

        foreach ($translations as $field => $data) {
            foreach ($data as $locale => $value) {
                if ($value['value'] === null || strlen($value['value']) < 2) {
                    if ($field === 'title') {
                        $translations[$field][$locale]['value'] = $title;
                    } else if ($field === 'overview') {
                        $translations[$field][$locale]['value'] = $overview;
                    }
                }
            }
        }

        if ($removeLocaleReference) {
            $translations['title'] = array_values($translations['title']);
            $translations['overview'] = array_values($translations['overview']);
        }

        return $translations;
    }

}
