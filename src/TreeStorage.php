<?php

/**
 * Cozy.ValueObjects
 *
 * (c) Nestor Picado <info@nestorpicado.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cozy\ValueObjects;

use Cozy\Contracts\Arrayable;
use Cozy\Contracts\Equatable;
use InvalidArgumentException;
use stdClass;

/**
 * A Value Object that represents a flatted tree with hierarchical data.
 */
class TreeStorage implements Arrayable, Equatable
{
    public const FORMAT_RAW = 'raw';
    public const FORMAT_ARRAY = 'array';
    public const FORMAT_OBJECT = 'object';

    protected const DELIMITER = '.';
    protected array $storage = [];
    protected array $rawStorage = [];
    protected string $keyIndex;

    /**
     * Create a new instance from hierarchical data.
     *
     * @param array|object $data
     */
    public function __construct($data)
    {
        if (!is_array($data) && !is_object($data)) {
            throw new InvalidArgumentException('The given data is invalid.');
        }

        $this->processData($data);
        $this->keyIndex = '@'.implode('@@', array_keys($this->storage)).'@';
    }

    /**
     * Converts a full hierarchical data into a flat associative array and stores it.
     *
     * @param array|object $data
     * @param string       $prefix the prefix that will be added to the stored keys
     */
    protected function processData($data, string $prefix = ''): void
    {
        $data = (array)$data;
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $this->storage["{$prefix}{$key}"] = new self($value);
                $this->rawStorage["{$prefix}{$key}"] = $value;
                $this->processData($value, "{$prefix}{$key}".self::DELIMITER);
            } else {
                $this->storage["{$prefix}{$key}"] = $value;
            }
        }
    }

    /**
     * Get the value of a node identified by its key.
     *
     * @param  string     $key    key that identifies the node
     * @param  string     $format Disable if you want to get the tree node
     * @return mixed|null
     */
    public function get(string $key, $format = self::FORMAT_RAW)
    {
        if ($this->exists($key)) {
            if ($format === self::FORMAT_RAW) {
                return $this->rawStorage[$key] ?? $this->storage[$key];
            }

            if ($format === self::FORMAT_ARRAY && $this->storage[$key] instanceof self) {
                return $this->storage[$key]->toArray();
            }

            if ($format === self::FORMAT_OBJECT && $this->storage[$key] instanceof self) {
                return $this->storage[$key]->toObject();
            }

            return $this->storage[$key];
        }

        return null;
    }

    /**
     * Checks if a key exists in the storage.
     */
    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->storage);
    }

    /**
     * Get the flatted list of the stored keys.
     */
    public function list(): array
    {
        return array_keys($this->storage);
    }

    /**
     * Matches the node keys using a pattern that can contain special characters to represent a single segment/level
     * (?) or multiple ones (*).
     *
     * @param  string              $pattern        pattern to search
     * @param  bool                $only_end_nodes Enable if you want to exclude intermediate nodes with trees.
     * @param  string              $format         Select the format of the returned nodes
     * @return array|TreeStorage[]
     */
    public function match(string $pattern, bool $only_end_nodes = false, string $format = self::FORMAT_RAW): array
    {
        $pattern = '/@('.str_replace(['\*', '\?'], ['[^\@]*', '[^\@\.]+'], preg_quote($pattern, '/')).')@/i';
        $result = [];

        if (preg_match_all($pattern, $this->keyIndex, $matches) > 0) {
            foreach ($matches[1] as $key) {
                if ($only_end_nodes && $this->storage[$key] instanceof self) {
                    continue;
                }

                $result[$key] = $this->get($key, $format);
            }
        }

        return $result;
    }

    /**
     * Converts the tree into an associated array.
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->storage as $key => $value) {
            if (strpos($key, '.') === false) {
                $result[$key] = ($value instanceof self) ? $value->toArray() : $value;
            }
        }

        return $result;
    }

    /**
     * Converts the tree into an object.
     */
    public function toObject(): stdClass
    {
        $result = new stdClass();

        foreach ($this->storage as $key => $value) {
            if (strpos($key, '.') === false) {
                $result->{$key} = ($value instanceof self) ? $value->toObject() : $value;
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function equals($other): bool
    {
        if (!($other instanceof self)) {
            return false;
        }

        $a = $this->toArray();
        $b = $other->toArray();

        return $a === $b;
    }
}
