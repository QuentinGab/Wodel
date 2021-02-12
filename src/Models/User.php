<?php

declare(strict_types=1);

namespace QuentinGab\Wodel\Models;

use Illuminate\Support\Collection;
use QuentinGab\Wodel\Models\Base;

use WP_User;

class User extends Base
{

    protected $fillable = [
        'first_name',
        'last_name',
        'user_nicename',
        'user_email',
        'user_pass',
    ];

    protected $meta = [
        'first_name',
        'last_name',
    ];

    protected $casts = [
        'ID' => 'int',
    ];

    public $wp_user;

    public static function all()
    {
        $args = array(
            'fields' => 'all',
        );

        $wp_users = get_users($args);

        $users = array_map(function ($wp_user) {
            $user = new static();
            $user->fill($wp_user->to_array(), true);
            $user->fill($user->get_meta(), true);
            return $user;
        }, $wp_users);
        return new Collection($users);
    }

    public static function find($id)
    {
        return (new static())->_find($id);
    }

    public function _find($id)
    {
        $this->wp_user = new WP_User($id);

        if (!$this->wp_user) {
            return false;
        }
        $this->fill($this->wp_user->to_array(), true);
        $this->fill($this->get_meta(), true);

        return $this;
    }

    public function permissions()
    {
        return $this->wp_user->allcaps;
    }

    public function roles()
    {
        return $this->wp_user->roles;
    }

    public function can($roleOrPermission)
    {
        return in_array($roleOrPermission, $this->roles());
    }

    public function get_meta()
    {
        $data = [];
        foreach ($this->meta as $key) {
            if ($this->wp_user->has_prop($key)) {
                $data[$key] = $this->wp_user->get($key);
            } else {
                $data[$key] = null;
            }
        }
        return $data;
    }

    public static function current()
    {
        if (is_user_logged_in()) {
            return static::find(get_current_user_id());
        }
        return false;
    }

    public function _exists()
    {
        return !!email_exists($this->user_email);
    }

    public static function exists($email)
    {
        return !!email_exists($email);
    }

    public function save()
    {
        if ($this->_exists()) {
            return $this->_update();
        } else {
            return $this->_create();
        }
    }

    public function _update()
    {
        foreach ($this->meta as $key) {
            update_user_meta($this->ID, $key, $this->{$key});
        }

        return wp_update_user($this->userFields());
    }

    public function _create()
    {
        $data = $this->userFields();
        $data['user_login'] = $this->user_email;
        $data['user_pass'] = $this->user_pass;
        $user_id = wp_insert_user($data);

        if (!is_wp_error($user_id)) {
            $this->ID = $user_id;
        } else {
            return false;
        }

        foreach ($this->meta as $key) {
            update_user_meta($this->ID, $key, $this->{$key});
        }

        return true;
    }

    public function delete()
    {
        return wp_delete_user($this->ID);
    }

    public function updatePassword($password)
    {

        if (wp_check_password($password, $this->user_pass, $this->ID)) {
            return false;
        }

        wp_set_password($password, $this->ID);
        return true;
    }

    public function fresh()
    {
        return $this->_find($this->ID);
    }

    public function toArray()
    {
        return [
            'ID' => $this->ID,
            'user_email' => $this->user_email,
            'user_login' => $this->user_login,
            'user_nicename' => $this->user_nicename,
            'display_name' => $this->display_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
        ];
    }

    public function userFields()
    {
        return [
            'ID' => $this->ID,
            'user_email' => $this->user_email,
            'user_pass' => $this->user_pass,
            'user_login' => $this->user_login,
            'user_nicename' => $this->user_nicename,
            'display_name' => $this->display_name,
        ];
    }
}
