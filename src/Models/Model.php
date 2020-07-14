<?php
declare (strict_types = 1);
namespace QuentinGab\Wodel\Models;
use QuentinGab\Wodel\Collection;

class Model extends Base
{
    protected $table;

    protected $primary_key = "id";

    protected $fillable = [];

    protected $casts = [
        "id" => 'int',
    ];

    public function _find($id)
    {
        if (!is_numeric($id)) {
            return false;
        }

        global $wpdb;
        $table_name = $this->table;
        $key = $this->primary_key;

        $db = $wpdb->get_row("SELECT * FROM $table_name WHERE $key = $id", ARRAY_A);
        if (empty($db)) {
            return false;
        }

        $this->fill($db);

        return $this;
    }

    public function _where($args = [])
    {
        global $wpdb;

        $table_name = $this->table;
        $where = self::prepare_where($args);

        $db = $wpdb->get_results("SELECT * FROM $table_name WHERE $where", ARRAY_A);

        $collection = new Collection();
        foreach ($db as $row) {
            $item = new static($row);
            $collection->_collect($item);
        }
        return $collection;
    }

    public function _all()
    {
        global $wpdb;
        $table_name = $this->table;
        $db = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

        $collection = new Collection();
        foreach ($db as $row) {
            $item = new static($row);
            $collection->_collect($item);
        }
        return $collection;
    }


    public function save()
    {
        global $wpdb;

        if ($this->has_primary_key()) {
            return $this->_update();
        }

        return $this->_create();
    }

    public function _create()
    {
        global $wpdb;
        $item = self::get_fillable($this->toArray(), $this->fillable);
        $formats = array_map(function ($field) {return (is_int($field) ? '%d' : '%s');}, $item);

        $saved = $wpdb->insert(
            $this->table,
            $item,
            $formats
        );

        $this->{$this->primary_key} = $wpdb->insert_id;

        return $saved >= 0 ? true : false;
    }

    public function _update()
    {
        global $wpdb;

        $item = self::get_fillable($this->toArray(), $this->fillable);
        $formats = array_map(function ($field) {return (is_int($field) ? '%d' : '%s');}, $item);
        $saved = $wpdb->update(
            $this->table,
            $item,
            array($this->primary_key => $this->get_primary_key()),
            $formats,
            array('%d')
        );

        return $saved >= 0 ? true : false;

    }

    private static function prepare_where($arr)
    {
        $res = "";
        $i = 0;
        foreach ($arr as $key => $value) {
            if (is_string($value)) {
                $res .= $key . " = '" . $value . "'";
            } else {
                $res .= $key . " = " . $value;
            }
            if ($i < count($arr) - 1) {
                $res .= " AND ";
            }
            $i += 1;
        }

        return $res;

    }

    public static function find($id)
    {
        return (new static())->_find($id);
    }

    public static function where($args)
    {
        return (new static())->_where($args);
    }

    public static function all()
    {
        return (new static())->_all();
    }

    

    public static function get_fillable($array, $fillable)
    {
        return array_filter(
            $array,
            function ($key) use ($fillable) {
                return in_array($key, $fillable);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private function get_primary_key()
    {
        return $this->{$this->primary_key};
    }

    private function has_primary_key()
    {
        return property_exists($this, $this->primary_key) and $this->get_primary_key() != null;
    }

}
