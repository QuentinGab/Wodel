<?php
declare (strict_types = 1);
namespace QuentinGab\Wodel\Models;
use QuentinGab\Wodel\Collection;

class Base
{

    protected $fillable = [];

    protected $casts = [];

    public function __construct($array = [])
    {
        $this->fill($array, true);
    }

    public function fill($array, $override_fillable = false)
    {

        if (!is_array($array)) {
            return false;
        }

        foreach ($array as $key => $value) {
            if ($override_fillable || in_array($key, $this->fillable)) {
                $this->{$key} = $this->cast_field($key, $value);
            }
        }

        return $this;

    }

    protected function cast_field($key, $value)
    {
        if (array_key_exists($key, $this->casts)) {
            $type = $this->casts[$key];
            switch ($type) {
                case 'int':
                    return intval($value);
                    break;
                case 'bool':
                    return boolval($value);
                    break;
                case 'string':
                    return strval($value);
                    break;
            }
        }
        return $value;
    }

    public function fillableToArray()
    {
        $array = $this->toArray();
        $fillable = $this->fillable;
        return array_filter(
            $array,
            function ($key) use ($fillable) {
                return in_array($key, $fillable);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    public function refresh()
    {
        $this->fill($this->fresh()->toArray());
        return $this;
    }

    public function fresh()
    {
        return $this->_find($this->id);
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

}
