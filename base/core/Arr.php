<?php

namespace core;

/**
 * Array class
 *
 * @author kohei okuda
 */
class Arr {

    public static $delimiter = '.';


    public static function get(array $array, $path, $default = null, $delimiter = null)
    {
        if (array_key_exists($path, $array))
        {
            // No need to do extra processing
            return $array[$path];
        }

        if ($delimiter === null)
        {
            $delimiter = Arr::$delimiter;
        }

        // Split the keys by delimiter
        $keys = explode($delimiter, $path);

        $temp = $array;

        foreach ($keys as $key)
        {
            if (filter_var($key, FILTER_VALIDATE_INT))
            {
                // Make the key an integer
                $key = (int) $key;
            }

            if (isset($temp[$key]))
            {
                $temp = $temp[$key];
            }
            else
            {
                $temp = false;
            }
        }

        return $temp ?: $default;
    }

    public static function set(array &$array, string $path, $value, $delimiter = null)
    {
        if ($delimiter === null)
        {
            // Use the default delimiter
            $delimiter = Arr::$delimiter;
        }

        $keys = explode($delimiter, $path);

        foreach ($keys as $key)
        {
            if (filter_var($key, FILTER_VALIDATE_INT))
            {
                // Make the key an integer
                $key = (int) $key;
            }

            if (!isset($array[$key]))
            {
                $array[$key] = null;
            }

            $array = & $array[$key];
        }

        $array = $value;
    }

    public static function delete(array &$array, $path, $delimiter = null)
    {
        if ($delimiter === null)
        {
            // Use the default delimiter
            $delimiter = static::$delimiter;
        }

        if (is_array($path))
        {
            foreach ($path as $key)
            {
                $return = static::delete($array, $key);
                if (!$return)
                {
                    return false;
                }
            }
        }
        else
        {
            $segments = explode($delimiter, $path);
            if (count($segments) === 1)
            {
                $delete_key = reset($segments);
                if (filter_var($delete_key, FILTER_VALIDATE_INT))
                {
                    // Make the key an integer
                    $delete_key = (int) $delete_key;
                }

                if (!static::keyExist($array, $delete_key))
                {
                    return false;
                }

                unset($array[$delete_key]);
            }
            else
            {
                $top_key = array_shift($segments);
                if (filter_var($top_key, FILTER_VALIDATE_INT))
                {
                    // Make the key an integer
                    $top_key = (int) $top_key;
                }

                $after_key = implode($delimiter, $segments);
                return static::delete($array[$top_key], $after_key);
            }
        }

        return true;
    }

    public static function pluck(array $array, $key, $index_key = null)
    {
        return array_column($array, $key, $index_key);
    }

    public static function toArr($obj)
    {
        return json_decode(json_encode($obj), 1);
    }

    public static function sort(array $array, $key, $direction = 'asc', $type = SORT_NUMERIC)
    {
        if ($direction == 'asc')
        {
            $direction = SORT_ASC;
        }
        elseif ($direction == 'desc')
        {
            $direction = SORT_DESC;
        }

        return $result = array_multisort(array_column($array, $key), $direction, $type, $array);
    }

    public static function map(array $array, callable $callbacks, $targets = null)
    {
        foreach ($array as $key => $val)
        {
            if (is_array($val))
            {
                $array[$key] = Arr::map($array[$key], $callbacks, $targets);
            }
            else
            {
                if (is_null($targets) OR ( !is_null($targets) AND ( $targets === $key OR ( is_array($targets) AND in_array($key, $targets)))))
                {
                    if (is_array($callbacks))
                    {
                        foreach ($callbacks as $callback)
                        {
                            $array[$key] = call_user_func($callback, $array[$key], $key);
                        }
                    }
                    else
                    {
                        $array[$key] = call_user_func($callbacks, $array[$key], $key);
                    }
                }
            }
        }

        return $array;
    }

    public static function keyExist($array, $key)
    {
        return array_key_exists($key, $array);
    }

    /**
     * Rotete array
     * 
     * @param array $array
     *      array in array
     * @param string $keyname
     * @return array
     */
    public static function rotete(array $array, $keyname = 'id')
    {
        $result = array();

        foreach ($array as $key => $value)
        {
            foreach ($value as $k => $v)
            {
                $result[$keyname . $k][$key] = $v;
            }
        }

        return $result;
    }

    /**
     * Tests if an array is associative or not.
     *
     *     // Returns true
     *     Arr::is_assoc(array('username' => 'john.doe'));
     *
     *     // Returns false
     *     Arr::is_assoc('foo', 'bar');
     *
     * @param   array   $array  array to check
     * @return  boolean
     */
    public static function isAssoc(array $array)
    {
        // Keys of the array
        $keys = array_keys($array);

        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }

    /**
     * Test if a value is an array with an additional check for array-like objects.
     *
     *     // Returns true
     *     Arr::is_array(array());
     *     Arr::is_array(new ArrayObject);
     *
     *     // Returns false
     *     Arr::is_array(false);
     *     Arr::is_array('not an array!');
     *     Arr::is_array(Database::instance());
     *
     * @param   mixed   $value  value to check
     * @return  boolean
     */
    public static function isArray($value)
    {
        if (is_array($value))
        {
            // Definitely an array
            return true;
        }
        else
        {
            // Possibly a Traversable object, functionally the same as an array
            return (is_object($value) AND $value instanceof Traversable);
        }
    }

    /**
     * Fill an array with a range of numbers.
     *
     *     // Fill an array with values 5, 10, 15, 20
     *     $values = Arr::range(5, 20);
     *
     * @param   integer $step   stepping
     * @param   integer $max    ending number
     * @return  array
     */
    public static function range($step = 10, $max = 100)
    {
        if ($step < 1)
        {
            return array();
        }

        $array = array();
        for ($i = $step; $i <= $max; $i += $step)
        {
            $array[$i] = $i;
        }

        return $array;
    }

    /**
     * Retrieves multiple paths from an array. If the path does not exist in the
     * array, the default value will be added instead.
     *
     *     // Get the values "username", "password" from $_POST
     *     $auth = Arr::extract($_POST, array('username', 'password'));
     *
     *     // Get the value "level1.level2a" from $data
     *     $data = array('level1' => array('level2a' => 'value 1', 'level2b' => 'value 2'));
     *     Arr::extract($data, array('level1.level2a', 'password'));
     *
     * @param   array  $array    array to extract paths from
     * @param   array  $paths    list of path
     * @param   mixed  $default  default value
     * @return  array
     */
    public static function extract($array, array $paths, $default = null)
    {
        $found = array();
        foreach ($paths as $path)
        {
            Arr::set_path($found, $path, Arr::path($array, $path, $default));
        }

        return $found;
    }

    /**
     * Recursively merge two or more arrays. Values in an associative array
     * overwrite previous values with the same key. Values in an indexed array
     * are appended, but only when they do not already exist in the result.
     *
     * Note that this does not work the same as [array_merge_recursive](http://php.net/array_merge_recursive)!
     *
     *     $john = array('name' => 'john', 'children' => array('fred', 'paul', 'sally', 'jane'));
     *     $mary = array('name' => 'mary', 'children' => array('jane'));
     *
     *     // John and Mary are married, merge them together
     *     $john = Arr::merge($john, $mary);
     *
     *     // The output of $john will now be:
     *     array('name' => 'mary', 'children' => array('fred', 'paul', 'sally', 'jane'))
     *
     * @param   array  $array1      initial array
     * @param   array  $array2,...  array to merge
     * @return  array
     */
    public static function merge($array1, $array2)
    {
        if (Arr::is_assoc($array2))
        {
            foreach ($array2 as $key => $value)
            {
                if (is_array($value)
                        AND isset($array1[$key])
                        AND is_array($array1[$key])
                )
                {
                    $array1[$key] = Arr::merge($array1[$key], $value);
                }
                else
                {
                    $array1[$key] = $value;
                }
            }
        }
        else
        {
            foreach ($array2 as $value)
            {
                if (!in_array($value, $array1, true))
                {
                    $array1[] = $value;
                }
            }
        }

        if (func_num_args() > 2)
        {
            foreach (array_slice(func_get_args(), 2) as $array2)
            {
                if (Arr::is_assoc($array2))
                {
                    foreach ($array2 as $key => $value)
                    {
                        if (is_array($value)
                                AND isset($array1[$key])
                                AND is_array($array1[$key])
                        )
                        {
                            $array1[$key] = Arr::merge($array1[$key], $value);
                        }
                        else
                        {
                            $array1[$key] = $value;
                        }
                    }
                }
                else
                {
                    foreach ($array2 as $value)
                    {
                        if (!in_array($value, $array1, true))
                        {
                            $array1[] = $value;
                        }
                    }
                }
            }
        }

        return $array1;
    }

    /**
     * Overwrites an array with values from input arrays.
     * Keys that do not exist in the first array will not be added!
     *
     *     $a1 = array('name' => 'john', 'mood' => 'happy', 'food' => 'bacon');
     *     $a2 = array('name' => 'jack', 'food' => 'tacos', 'drink' => 'beer');
     *
     *     // Overwrite the values of $a1 with $a2
     *     $array = Arr::overwrite($a1, $a2);
     *
     *     // The output of $array will now be:
     *     array('name' => 'jack', 'mood' => 'happy', 'food' => 'tacos')
     *
     * @param   array   $array1 master array
     * @param   array   $array2 input arrays that will overwrite existing values
     * @return  array
     */
    public static function overwrite($array1, $array2)
    {
        foreach (array_intersect_key($array2, $array1) as $key => $value)
        {
            $array1[$key] = $value;
        }

        if (func_num_args() > 2)
        {
            foreach (array_slice(func_get_args(), 2) as $array2)
            {
                foreach (array_intersect_key($array2, $array1) as $key => $value)
                {
                    $array1[$key] = $value;
                }
            }
        }

        return $array1;
    }

    /**
     * Convert a multi-dimensional array into a single-dimensional array.
     *
     *     $array = array('set' => array('one' => 'something'), 'two' => 'other');
     *
     *     // Flatten the array
     *     $array = Arr::flatten($array);
     *
     *     // The array will now be
     *     array('one' => 'something', 'two' => 'other');
     *
     * [!!] The keys of array values will be discarded.
     *
     * @param   array   $array  array to flatten
     * @return  array
     * @since   3.0.6
     */
    public static function flatten($array)
    {
        $is_assoc = Arr::is_assoc($array);

        $flat = array();
        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                $flat = array_merge($flat, Arr::flatten($value));
            }
            else
            {
                if ($is_assoc)
                {
                    $flat[$key] = $value;
                }
                else
                {
                    $flat[] = $value;
                }
            }
        }
        return $flat;
    }

    /**
     * Filters an array by an array of keys
     *
     * @param   array  $array   the array to filter.
     * @param   array  $keys    the keys to filter
     * @param   bool   $remove  if true, removes the matched elements.
     * @return  array
     */
    public static function filterKeys($array, $keys, $remove = false)
    {
        $return = array();
        foreach ($keys as $key)
        {
            if (array_key_exists($key, $array))
            {
                $remove or $return[$key] = $array[$key];
                if ($remove)
                {
                    unset($array[$key]);
                }
            }
        }
        return $remove ? $array : $return;
    }

}
