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


class EnumReader
{
    public static function getEnumIdByXmlIdMulti( $items, $iblockId, $enumCode )
    {
        // обнуляем св-во с типов "список"
        // если передать пустой массив - он просто игнорируется ядром
        if (!$items)
            return EnumWriter::emptyValueOfMultiEnumAndFileProperty();
        
        $enumDictionary = self::getEnumValues($iblockId, $enumCode, "XML_ID");
        
        $result = [];
        
        foreach ( $items as $item ) {
            $result[] = $enumDictionary[ $item ]["ID"];
        }
        
        return $result;
    }
    
    public static function getEnumValues( $IBLOCK_ID, $ENUM_CODE, $primaryKey = "ID" )
    {
        if (!$IBLOCK_ID)
            throw new \Exception ( '$IBLOCK_ID can not be void' );
        
        if (!$ENUM_CODE)
            throw new \Exception ( '$ENUM_CODE can not be void' );
        
        $result = array();
        
        $res = \CIBlockPropertyEnum::GetList(array(
            "SORT" => "ASC",
            "NAME" => "ASC",
        ), array(
            "IBLOCK_ID" => $IBLOCK_ID,
            "CODE"      => $ENUM_CODE,
        ));
        
        while ($ob = $res->Fetch())
            $result[ $ob[ $primaryKey ] ] = $ob;
        
        return $result;
    }
}