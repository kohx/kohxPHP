<?php

/**
 * AutoLoader class
 *
 * @author kohei okuda
 */
class AutoLoader {

    /**
     * Loads the given class or interface.
     *
     * @param string $load_class The name of the class to load.
     * @return void
     */
    public static function loadClass($load_class)
    {
        $namespace_full = ltrim($load_class, NS);
        $last_namespace_pos = strripos($namespace_full, NS);

        $file_name = '';
        $namespace = '';

        if ($last_namespace_pos)
        {
            $namespace = substr($namespace_full, 0, $last_namespace_pos);
            $dir_path = str_replace(NS, DS, $namespace) . DS;
            $class_name = substr($namespace_full, $last_namespace_pos + 1);
        }

        $file_name = BASE_PATH . $dir_path . str_replace('_', DS, $class_name) . EXT;

        if (!(is_file($file_name) AND is_readable($file_name)))
        {
            throw new \Exception('class not exist!');
        }

        // require class file
        require_once $file_name;
    }

}
