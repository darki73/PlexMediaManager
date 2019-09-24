<?php namespace App\Classes\Media\Source\Type;

use RuntimeException;
use Illuminate\Config\Repository;
use App\Classes\Storage\PlexStorage;

/**
 * Class AbstractType
 * @package App\Classes\Media\Source\Type
 */
abstract class AbstractType {

    /**
     * Plex Storage instance
     * @var PlexStorage|null
     */
    protected $storage = null;

    /**
     * List of allowed media extensions
     * Only these extensions will be marked as valid for media files
     * @var array|Repository|mixed
     */
    protected $allowedExtensions = [];

    /**
     * Media source name
     * @var null|string
     */
    protected $source = null;

    /**
     * List of raw elements obtained from Plex Storage
     * @var array
     */
    protected $rawElements = [];

    /**
     * AbstractType constructor.
     */
    public function __construct() {
        $this->storage = new PlexStorage;
        $this->allowedExtensions = config('storage.process_only');
        $this
            ->loadStorageItems()
            ->processStorageItems();
    }

    /**
     * Get list of processed items
     * @return array
     */
    abstract public function list() : array;

    /**
     * Process raw items received from Plex Storage
     * @return AbstractType|static|self|$this
     */
    abstract protected function processStorageItems() : self;

    /**
     * Get absolute media path from the element
     * @param array $element
     * @return string
     */
    protected function absoluteMediaPath(array $element) : string {
        return $this->mediaPath($element, 'absolute');
    }

    /**
     * Get relative media path from the element
     * @param array $element
     * @return string
     */
    protected function relativeMediaPath(array $element) : string {
        return $this->mediaPath($element, 'relative');
    }

    /**
     * Get absolute content path from the element
     * @param array $element
     * @return string
     */
    protected function absoluteContentPath(array $element) : string {
        return $this->contentPath($element, 'absolute');
    }

    /**
     * Get relative content path from the element
     * @param array $element
     * @return string
     */
    protected function relativeContentPath(array $element) : string {
        return $this->contentPath($element, 'relative');
    }

    /**
     * Extract year from media name
     * @param string $name
     * @return int|null
     */
    protected function extractYear(string $name) : ?int {
        preg_match('/\((\d{4})\)/', $name, $matches);
        if (\count($matches) > 0) {
            return (int) $matches[1];
        }
        return null;
    }

    /**
     * Load items from Plex Storage
     * @return AbstractType|static|self|$this
     */
    private function loadStorageItems() : self {
        if ($this->source === null) {
            throw new RuntimeException('You have to set the source name');
        }
        $this->rawElements = $this->storage->listMediaForAllDrives($this->source);
        return $this;
    }

    /**
     * Get information for specific type inside specific type inside the element
     * @param array $element
     * @param string $path
     * @param string $type
     * @return string
     */
    private function elementPathType(array $element, string $path, string $type) : string {
        $path = strtolower(trim(str_replace('_path', '', $path)));
        return $element[$path . '_path'][$type];
    }

    /**
     * Get elements' specific media_path path type
     * @param array $element
     * @param string $type
     * @return string
     */
    private function mediaPath(array $element, string $type) : string {
        return $this->elementPathType($element, 'media', $type);
    }

    /**
     * Get elements' specific content_path path type
     * @param array $element
     * @param string $type
     * @return string
     */
    private function contentPath(array $element, string $type) : string {
        return $this->elementPathType($element, 'content', $type);
    }


}
