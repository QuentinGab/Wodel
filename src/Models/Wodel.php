<?php

declare(strict_types=1);

namespace QuentinGab\Wodel\Models;

use QuentinGab\Wodel\Collection;

class Wodel extends Base
{

    protected $fillable = [];
    public $acf_fields = [];

    protected $post_type = 'page';

    public function __construct($array = null)
    {
        if (isset($array['id'])) {
            return static::find($array['id']);
        }

        if ($array) {
            $this->fill($array);
        }
    }

    public function __get($property)
    {
        if (method_exists(__CLASS__, $property)) {
            return $this->$property();
        }

        return null;
    }

    public static function current()
    {
        if (get_the_ID()) {
            return static::find(get_the_ID());
        }
        return false;
    }

    public static function type($post_type)
    {
        $instance = new static();
        $instance->post_type = $post_type;
        return $instance;
    }

    public static function find($id)
    {
        return (new static())->_find($id);
    }

    public static function first()
    {
        return (new static())->_first();
    }

    public static function where($array)
    {
        return (new static())->_where($array);
    }

    public static function all()
    {
        return (new static())->_all();
    }

    public function _find($id)
    {

        $post = get_post($id);
        if (!$post || $post->post_type !== $this->post_type) {
            return false;
        }

        $post = $post->to_array();

        foreach ($post as $key => $value) {
            $this->{$key} = $value;
        }

        $acf = get_fields($id);
        if ($acf) {
            $this->acf_fields = array_keys($acf);
            foreach ($acf as $key => $value) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    public function refresh()
    {
        $post = get_post($this->ID);
        if (!$post || $post->post_type !== $this->post_type) {
            return false;
        }

        $post = $post->to_array();

        foreach ($post as $key => $value) {
            $this->{$key} = $value;
        }

        $acf = get_fields($this->ID);
        if ($acf) {
            $this->acf_fields = array_keys($acf);
            foreach ($acf as $key => $value) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    public function _first()
    {
        return $this->_where(['numberposts' => 1])->first();
    }

    public function _where($array)
    {

        $collection = new Collection();

        $args = array(
            'numberposts' => $array['numberposts'] ?? -1,
            'post_type' => $array['post_type'] ?? $this->post_type,
            'post_status' => $array['post_status'] ?? 'publish',
            'author' => $array['author'] ?? null,
            'orderby' => $array['orderby'] ?? 'date',
            'order' => $array['order'] ?? 'DESC',
            'category__in' => $array['category__in'] ?? null,
            'category__not_in' => $array['category__not_in'] ?? null,
            'category_name' => $array['category_name'] ?? null,
            'cat' => $array['cat'] ?? null,
            'category__and' => $array['category__and'] ?? null,
            'meta_query' => $array['meta_query'] ?? null,
            'meta_key' => $array['meta_key'] ?? null,
            'meta_value' => $array['meta_value'] ?? null,
            'meta_type' => $array['meta_type'] ?? null,
            'name' => $array['name'] ?? null,
            'offset' => $array['offset'] ?? null,
            'include' => $array['include'] ?? null,
            'tax_query' => $array['tax_query'] ?? null,
            'post__in' => $array['post__in'] ?? null,
            'fields' => 'ids',
        );

        // dd($args);

        $posts = get_posts($args);

        foreach ($posts as $id) {
            $collection->add(static::find($id));
        }

        return $collection->unique();
    }

    public function _all()
    {
        $collection = new Collection();

        $args = array(
            'numberposts' => -1,
            'post_type' => $this->post_type,
            'post_status' => 'publish',
            'fields' => 'ids',
        );

        $posts = get_posts($args);

        foreach ($posts as $id) {
            $collection->add(static::find($id));
        }

        return $collection->unique();
    }

    public function save()
    {

        $postarr = [
            'ID' => $this->ID ?? 0,
            'post_author' => $this->post_author ?? get_current_user_id(),
            'post_content' => $this->post_content ?? '',
            'post_title' => $this->post_title ?? '',
            'post_excerpt' => $this->post_excerpt ?? '',
            'post_status' => $this->post_status ?? 'draft',
            'post_type' => $this->post_type ?? 'post',
            'comment_status' => $this->comment_status ?? '',
            'post_password' => $this->post_password ?? '',
            'post_parent' => $this->post_parent ?? 0,
        ];

        $post_id =  wp_insert_post($postarr);

        if ($post_id && !$this->ID) {
            //stored
            $this->ID = $post_id;
            $this->refresh();
        }


        if ($this->ID) {
            foreach ($this->acf_fields as $key) {
                update_field($key, $this->{$key}, $this->ID);
            }
        }

        return $post_id;
    }

    public function acf_update_field($key, $value)
    {
        if(!$this->ID){
            return false;
        }
        return update_field($key, $value,$this->ID);
    }

    public function update($array)
    {
        $this->fill($array);
        $this->save();
        return $this;
    }

    public function fresh()
    {
        return $this->_find($this->ID);
    }

    //Getter

    public function content()
    {
        return apply_filters('the_content', $this->post_content);
    }

    public function permalink()
    {
        return get_post_permalink($this->ID);
    }

    public function has_image()
    {
        if ($this->{$this->field_image} || has_post_thumbnail($this->ID)) {
            return true;
        }
        return false;
    }

    public function image($array = [])
    {
        $args = array_merge([
            'id' => null,
            'class' => 'img-fluid',
            'size' => 'my_large'
        ], $array);

        if (!is_null($args['id']) && $args['id']) {

            return wp_get_attachment_image($args['id'], $args['size'], false, ['class' => $args['class']]);
        } else if (has_post_thumbnail($this->ID)) {

            return wp_get_attachment_image(get_post_thumbnail_id($this->ID), $args['size'], false, ['class' => $args['class']]);
        } else {
            return null;
        }
    }

    public function image_url($array = [])
    {
        $args = array_merge([
            'id' => null,
            'class' => 'img-fluid',
            'size' => 'my_large'
        ], $array);

        if (!is_null($args['id']) && $args['id']) {

            return wp_get_attachment_image_url($args['id'], $args['size'], false, ['class' => $args['class']]);
        } else if (has_post_thumbnail($this->ID)) {

            return wp_get_attachment_image_url(get_post_thumbnail_id($this->ID), $args['size'], false, ['class' => $args['class']]);
        } else {
            return null;
        }
    }

    public function __toString()
    {
        return strval($this->ID);
    }
}
