<?php namespace App\Classes\Media\Processor\Type;

use App\Classes\TheMovieDB\Processor\AbstractProcessor;
use Illuminate\Support\Arr;

/**
 * Class AbstractType
 * @package App\Classes\Media\Processor\Type
 */
abstract class AbstractType {

    /**
     * The Movie DB media processor instance
     * @var AbstractProcessor|null
     */
    protected $entity = null;

    /**
     * Database model for specified type
     * @var string|null
     */
    protected $model = null;

    /**
     * Entities which do not belong to main model
     * @var array
     */
    protected $separateEntities = [];

    /**
     * Main database model
     * @var array
     */
    protected $mainModel = [];

    /**
     * Additional database models
     * @var array
     */
    protected $additionalModels = [];

    /**
     * AbstractType constructor.
     * @param AbstractProcessor $processor
     */
    public function __construct(AbstractProcessor $processor) {
        $this->entity = $processor;
        $this
            ->extractSeparateEntities()
            ->mergeGenres()
            ->executeTypeSpecificMethods()
            ->sequelizeMainModelFields()
            ->sequelizeAdditionalModelsFields()
            ->createOrUpdateMainModel()
            ->createOrUpdateAdditionalModels();
    }

    /**
     * Execute methods which are type specific and may require additional processing
     * @return AbstractType|static|self|$this
     */
    abstract protected function executeTypeSpecificMethods() : self;

    /**
     * Merge specified entity from separate entities
     * @param string $targetEntity
     * @param string $saveAs
     * @return AbstractType|static|self|$this
     */
    protected function mergeSeparateEntities(string $targetEntity, string $saveAs) : self {
        foreach ($this->separateEntities as $entity => $model) {
            if (false !== stripos($entity, $targetEntity)) {
                $elements = $this->additionalModels[$model];
                if ($elements !== null) {
                    foreach ($elements as $element) {
                        $this->mainModel[$saveAs][] = $element['id'];
                    }
                } else {
                    $this->mainModel[$saveAs] = null;
                }
                break;
            }
        }
        return $this;
    }

    /**
     * Extract separate entities from the main model
     * @return AbstractType|static|self|$this
     */
    private function extractSeparateEntities() : self {
        $array = $this->entity->toArray();

        foreach ($this->separateEntities as $entity => $model) {
            if (array_key_exists($entity, $array)) {
                $this->additionalModels[$model] = $array[$entity];
                unset($array[$entity]);
            }
        }

        $this->mainModel = $array;
        return $this;
    }

    /**
     * Merge genres with the main model
     * @return AbstractType|static|self|$this
     */
    private function mergeGenres() : self {
        foreach ($this->separateEntities as $entity => $model) {
            if (false !== stripos($entity, 'genres')) {
                $genres = $this->additionalModels[$model];
                if ($genres !== null) {
                    foreach ($genres as $genre) {
                        $this->mainModel['genres'][] = $genre['id'];
                    }
                }
                break;
            }
        }
        return $this;
    }

    /**
     * Convert main model array columns to columns defined in the model
     * @return AbstractType|static|self|$this
     */
    private function sequelizeMainModelFields() : self {
        foreach ($this->mainModel as $column => $value) {
            $newColumn = camel_to_underscore($column);
            if (is_string($value) && $value === '') {
                $value = null;
            }
            $this->mainModel[$newColumn] = $value;
            if ($newColumn !== $column) {
                unset($this->mainModel[$column]);
            }
        }
        return $this;
    }

    /**
     * Convert addition models array columns to columns defined in respective models
     * @return AbstractType|static|self|$this
     */
    private function sequelizeAdditionalModelsFields() : self {
        foreach ($this->additionalModels as $className => $elements) {
            if ($elements !== null) {
                foreach ($elements as $index => $element) {
                    foreach ($element as $column => $value) {
                        $newColumn = camel_to_underscore($column);
                        if (is_string($value) && $value === '') {
                            $value = null;
                        }
                        $this->additionalModels[$className][$index][$newColumn] = $value;
                        if ($newColumn !== $column) {
                            unset($this->mainModel[$column]);
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Create or update main model data
     * @return AbstractType|static|self|$this
     */
    private function createOrUpdateMainModel() : self {
        $model = new $this->model;
        $array = $this->mainModel;
        $entity = $model->where('id', '=', $array['id'])->first();
        if ($entity !== null) {
            unset($array['id']);
            $entity->update($array);
        } else {
            $model->create($this->mainModel);
        }

        return $this;
    }

    /**
     * Create or update data for additional models
     * @return AbstractType|static|self|$this
     */
    private function createOrUpdateAdditionalModels() : self {
        foreach ($this->additionalModels as $className => $elements) {
            if ($elements !== null) {
                foreach ($elements as $element) {
                    $class = new $className;
                    if ($class->where('id', '=', $element['id'])->exists()) {
                        $fetchedElement = $class->where('id', '=', $element['id'])->first();
                        $localElement = array_filter(Arr::except($fetchedElement->toArray(), [
                            'id',
                            'episodes',
                            'created_at',
                            'updated_at'
                        ]));
                        $remoteElement = array_filter(Arr::except($element, [
                            'id'
                        ]));
                        $difference = array_diff($remoteElement, $localElement);
                        if (\count($difference) > 0) {
                            $fetchedElement->update($difference);
                        }

                    } else {
                        $class->create($element);
                    }
                }
            }
        }
        return $this;
    }

}
