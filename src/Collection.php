<?php

declare(strict_types=1);

namespace QuentinGab\Wodel;

class Collection implements \Countable, \Iterator, \ArrayAccess
{

    private $items = [];
    private $position = 0;

    public function __construct($array = null)
    {

        if (is_array($array)) {
            foreach ($array as $item) {
                $this->offsetSet('', $item);
            }
        } else if (!is_null($array)) {
            $item = $array;
            $this->offsetSet('', $item);
        }
    }

    public function count()
    {
        return count($this->items);
    }

    public function isEmpty()
    {
        return $this->count() == 0;
    }

    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    //Iterator
    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->items[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->items[$this->position]);
    }

    //ArrayAccess

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {

        if (empty($offset)) { //this happens when you do $collection[] = 1;
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public static function collect($array)
    {
        return new self($array);
    }

    public function _collect($array)
    {

        if ($array instanceof Collection) {
            foreach ($array->toArray() as $item) {
                $this->offsetSet('', $item);
            }
        } else if (is_array($array)) {
            foreach ($array as $item) {
                $this->offsetSet('', $item);
            }
        } else {
            $item = $array;
            $this->offsetSet('', $item);
        }

        return $this;
    }

    public function add($array)
    {
        return $this->_collect($array);
    }

    public function first()
    {
        if (count($this->items) > 0) {
            return $this->items[0];
        }
        return false;
    }

    public function filter($fun)
    {
        return new Collection(array_values(array_filter($this->items, $fun)));
    }

    public function where($args)
    {
        return new Collection(array_filter($this->items, function ($item) use ($args) {

            foreach ($args as $key => $value) {
                if ($item->{$key} != $value) {
                    return false;
                }
            }

            return true;
        }));
    }

    public function map($fun)
    {
        return new Collection(array_map($fun, $this->items));
    }

    public function sort($fun)
    {
        usort($this->items, $fun);
        return $this;
    }

    public function toDeepArray()
    {
        $this->items = array_map(function ($el) {
            return $el->toArray();
        }, $this->items);

        return $this;
    }



    public function unique()
    {
        return self::collect(array_unique($this->items));
    }

    public function toArray()
    {
        return $this->items;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
