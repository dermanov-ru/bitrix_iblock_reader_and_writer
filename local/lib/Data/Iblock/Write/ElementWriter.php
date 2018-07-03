<?php
/**
 * Created by PhpStorm.
 * Date: 18.04.2018
 * Time: 21:25
 *
 * @author dev@dermanov.ru
 */


namespace Data\Iblock\Write;

use Data\Iblock\Read\EnumWriter;

class ElementWriter
{
    public function saveRemoteFileMulti( $images, $returnArray = false ){
        $result = [];
        
        foreach ( $images as $image ) {
            $result[] = $this->saveRemoteFile($image, $returnArray);
        }
        
        return $result;
    }
    
    public function saveRemoteFile( $fullPath, $returnArray = false )
    {
        $arFile = \CFile::MakeFileArray($fullPath);
        
        if ($returnArray)
            return $arFile;
        else {
            $fileId = \CFile::SaveFile($arFile, "iblock");
            return $fileId;
        }
    }
    
    public function addElement( $fields, $iblockId, $props = [], $fileProps = [] )
    {
        $obElement = new \CIBlockElement;
        $recordId = $obElement->Add($fields);
        
        $this->updateElement($recordId, false, $iblockId, $props, $fileProps);
        
        return $recordId;
    }
    
    public function updateElement( $recordId, $fields, $iblockId, $props = [], $fileProps = [] )
    {
        if ($fields) {
            $obElement = new \CIBlockElement;
            $obElement->Update($recordId, $fields);
        }
        
        /*
         * Save "file" type props
         * https://idea.1c-bitrix.ru/setpropertyvaluesex-i-svoystvo-tipa-fayl/
         * */
        foreach ( $fileProps as $code => $value ) {
            // clean old files at first.
            \CIBlockElement::SetPropertyValuesEx($recordId, $iblockId, [$code => EnumWriter::emptyValueOfMultiEnumAndFileProperty()]);
            
            \CIBlockElement::SetPropertyValues($recordId, $iblockId, $value, $code);
        }
        
        /*
         * Save other type props
         * */
        if ($props) {
            \CIBlockElement::SetPropertyValuesEx($recordId, $iblockId, $props);
        }
    }
}