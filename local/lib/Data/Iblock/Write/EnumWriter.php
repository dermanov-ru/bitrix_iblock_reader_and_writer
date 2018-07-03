<?php
/**
 * Created by PhpStorm.
 * User: dev@dermanov.ru
 * Date: 03.07.2018
 * Time: 14:30
 *
 *
 */


namespace Data\Iblock\Read;


class EnumWriter
{
    /**
     * ¬озвращает конструкцию, которую нужно установить в качестве значени€,
     * если нужно обнулить множественное св-во типа "список" или "файл".
     * */
    public static function emptyValueOfMultiEnumAndFileProperty()
    {
        return array("VALUE" => "", "DESCRIPTION" => "", "DELETE" => "Y");
    }
}