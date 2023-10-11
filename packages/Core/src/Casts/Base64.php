<?php

namespace Core\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Base64 implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        return base64_decode($value);
    }

    /**
     * Prepare the given value for storage.
     * @param $model
     * @param string $key
     * @param array $value
     * @param array $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        return base64_encode($value);
    }
}
