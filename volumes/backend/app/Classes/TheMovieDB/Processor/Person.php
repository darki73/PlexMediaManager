<?php namespace App\Classes\TheMovieDB\Processor;

use Illuminate\Support\Arr;

/**
 * Class Person
 * @package App\Classes\TheMovieDB\Processor
 */
class Person {

    /**
     * Raw element data
     * @var array
     */
    protected array $rawElement = [];

    /**
     * Rename these fields
     * @var array
     */
    protected array $rename = [
        'profile_path'          =>  'photo',
        'place_of_birth'        =>  'birth_place'
    ];

    /**
     * Fields which we want to take from the raw element
     * @var array
     */
    protected array $acceptableFields = [
        'id',
        'name',
        'gender',
        'photo',
        'birthday',
        'deathday',
        'biography',
        'birth_place',
        'popularity',
        'imdb_id',
        'homepage',
        'adult',
    ];

    /**
     * Processed data array
     * @var array
     */
    protected array $processed = [];

    /**
     * AbstractPeopleProcessor constructor.
     * @param array $rawElement
     */
    public function __construct(array $rawElement) {
        $this->rawElement = $rawElement;
        $this->processData();
    }

    /**
     * "Convert" class to array
     * @param array $except
     * @return array
     */
    public function toArray(array $except = []) : array {
        return Arr::except($this->processed, $except);
    }

    /**
     * Process data and map keys to the respective database columns
     * @return Person|static|self|$this
     */
    protected function processData() : self {
        $tempArray = [];
        foreach ($this->rawElement as $key => $value) {
            if (array_key_exists($key, $this->rename)) {
                $key = $this->rename[$key];
            }
            if ($key === 'photo') {
                $value = trim(ltrim($value, '/'));
                if (strlen($value) < 3) {
                    $value = null;
                }
            }
            $tempArray[$key] = $value;
        }
        $this->processed = Arr::only($tempArray, $this->acceptableFields);
        return $this;
    }

}
