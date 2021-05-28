<?php

if (!function_exists('property_path_exists')) {
    function property_path_exists($object, $property_path)
    {
        $path_components = explode('.', $property_path);

        if (count($path_components) == 1) {
            return property_exists($object, $property_path);
        } else {
            return (
                property_exists($object, $path_components[0]) &&
                property_path_exists($object->{array_shift($path_components)}, implode('.', $path_components))
            );
        }
    }
}

if (!function_exists('property_path_exists_throw')) {
    function property_path_exists_throw($object, $property_path)
    {
        if (property_path_exists($object, $property_path)) {
            return true;
        } else {
            throw new \Exception('Mission Error');
        }
    }
}
