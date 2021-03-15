<?php

namespace Mon\Oversight\inc;

class Helper
{
    /**
     * Converts a single object into an array with key => value pairs
     * or iterates over an array of objects and converting the nested
     * objects into arrays with key => value pairs.
     *
     * @param object | object[] $target
     * @return array | array[]
     */
    public static function castToArray($target)
    {
        $response = array();
        if (is_object($target)) {
            return Helper::objToArray($target);
        }


        foreach ($target as $stdClassObject) {
            $response[] = Helper::objToArray($stdClassObject);
        }

        return $response;
    }

    /**
     * Converts an object into an array of key => value pairs.
     *
     * @param object $obj
     * @return array
     */
    public static function objToArray($obj)
    {
        $classArray = array();

        foreach ($obj as $key => $value) {
            $classArray = array_merge($classArray, array($key => $value));
        }

        return $classArray;
    }

}