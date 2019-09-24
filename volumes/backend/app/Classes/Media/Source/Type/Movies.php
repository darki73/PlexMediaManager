<?php namespace App\Classes\Media\Source\Type;

/**
 * Class Movies
 * @package App\Classes\Media\Source\Type
 */
class Movies extends AbstractType {

    /**
     * @inheritDoc
     * @var string
     */
    protected $source = 'movies';

    /**
     * List of processed movies
     * @var array
     */
    protected $movies = [];

    /**
     * @inheritDoc
     * @return array
     */
    public function list() : array {
        return $this->movies;
    }

    /**
     * @inheritDoc
     * @return AbstractType|static|self|$this
     */
    protected function processStorageItems(): AbstractType {
        foreach ($this->rawElements as $element) {
            $absolutePath = $this->absoluteMediaPath($element);
            $relativePath = $this->relativeMediaPath($element);
            $movieFile = str_replace($this->relativeContentPath($element) . DIRECTORY_SEPARATOR, '', $relativePath);
            $fileExtension = pathinfo($absolutePath, PATHINFO_EXTENSION);

            if (in_array(strtolower($fileExtension), $this->allowedExtensions)) {
                $movieName = str_replace('.' . $fileExtension, '', $movieFile);
                $originalName = $movieName;
                $movieYear = null;

                if (false !== strpos($movieName, '(') && false !== strpos($movieName, ')')) {
                    $movieYear = $this->extractYear($movieName);
                    $movieName = trim(str_replace('(' . $movieYear . ')', '', $movieName));
                }

                $this->movies[] = [
                    'name'          =>  $movieName,
                    'original_name' =>  $originalName,
                    'year'          =>  $movieYear,
                    'path'          =>  [
                        'absolute'  =>  $absolutePath,
                        'relative'  =>  $relativePath
                    ],
                    'drive'         =>  $this->storage->driveInformation($element['drive']),
                    'size'          =>  [
                        'exact'     =>  filesize($absolutePath),
                        'nice'      =>  $this->storage->formatBytes(filesize($absolutePath))
                    ]
                ];
            }
        }
        return $this;
    }

}
