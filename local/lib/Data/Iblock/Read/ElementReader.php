<?php
/*
 * Created by PhpStorm.
 * Date: 05.10.2017
 * 
 * @author dev@dermanov.ru
 */


namespace Data\Iblock\Read;


class ElementReader extends Engine
{
    /**
     * Iblock engine version: 1.0 or 2.0
     * */
    protected $iblockEngineVersion = 2;
    protected $selectProps = [];
    
    public function fetchAll(  )
    {
        $this->dbres = $dbres = \CIBlockElement::GetList($this->getSort(), $this->getFilter(), false, $this->getPageNav(), $this->getSelect());
        
        // TODO
        // Устанавливает шаблоны путей для элементов, разделов и списка элементов вместо тех которые указаны в настройках информационного блока
        /* чтобы ссылки с товаров вели на данный раздел */
        //$res->SetUrlTemplates( $params["detail_page_url_template"], $params["section_page_url_template"] );
        
        $result = array();
        
        while($ob = $dbres->GetNext(true, false)) {
            $result[ $ob["ID"] ] = $ob;
        }
    
        // Fill props for iblock 1.0 engine
        if ($this->isIblockEngineVersion1())
            $this->fillProps($result);
        
        $this->resultArray = $result;
        
        return $result;
    }
    
    public function addSelectProp( $prop )
    {
        $result = $this->getSelectProps();
        
        $result[] = "PROPERTY_" . $prop;
        
        $this->setSelectProps($result);
    }
    
    /**
     * @return
     * */
    public function filterResultBySection( $sectionId)
    {
        $resultItems = array();
        
        $items = $this->getResultArray();
        
        foreach ( $items as $item ) {
            if ($item["IBLOCK_SECTION_ID"] == $sectionId)
                $resultItems[ $item["ID"] ] = $item;
        }
        
        return $resultItems;
    }
    
    public function isIblockEngineVersion1()
    {
        return $this->iblockEngineVersion == 1;
    }
    
    public function isIblockEngineVersion2()
    {
        return $this->iblockEngineVersion == 2;
    }
    
    public function setIblockEngineVersion1(  )
    {
        $this->iblockEngineVersion = 1;
    }
    
    public function setIblockEngineVersion2(  )
    {
        $this->iblockEngineVersion = 2;
    }
    
    /**
     * @return array
     */
    public function getSelectProps()
    {
        return $this->selectProps;
    }
    
    /**
     * @param array $selectProps - [ "PROPERTY_XXX", "PROPERTY_YYY", ... ]
     */
    public function setSelectProps( $selectProps )
    {
        $this->selectProps = $selectProps;
    }
    
    public function getSelect()
    {
        $fields = parent::getSelect();
        $props = $this->getSelectProps();
    
        if ($this->isIblockEngineVersion2()){
            $result = array_merge($fields, $props);
        } else {
            $result = $fields;
        }
        
        return $result;
    }
    
    /**
     * Fill props for all items from getlist result.
     *
     * use only with iblock engine 1.0
     *
     * @param array $result - array with getlist result items
     * */
    protected function fillProps( &$result = [])
    {
        $selectProps = $this->getSelectProps();
    
        foreach ( $selectProps as $selectProp ) {
            foreach ( $result as &$item ) {
                // TODO store clean codes :)
                $selectPropCode = str_replace("PROPERTY_", "", $selectProp);
                $this->fillProp( $selectPropCode, $item);
            }
        }
    }
    
    /**
     * Fill prop for one item from getlist result.
     *
     * use only with iblock engine 1.0
     * @param array $item - array with one item from getlist result. Must contain ID.
     * */
    protected function fillProp( $selectPropCode, &$item)
    {
        $productId = $item["ID"];
        
        if (!$productId)
            throw new \Exception ( '$productId can not be empty' );
        
        $res = \CIBlockElement::GetProperty($this->getIblockId(), $productId, "sort", "asc", array("CODE" => $selectPropCode));
    
        while ($ob = $res->Fetch())
        {
            // iblock 2.0 style
            $prop = "PROPERTY_" . $ob['CODE'] . "_VALUE";
            
            if (!$ob['VALUE']) {
                $item[ $prop ] = false;
                continue;
            }
            
            if ($ob['MULTIPLE'] == "Y") {
                $item[ $prop ][] = $ob['VALUE'];
            } else {
                $item[ $prop ] = $ob['VALUE'];
            }
        }
    }
    
    public static function getElementIdByXmlId( $xmlId, $iblockId )
    {
        $query = new ElementReader($iblockId);
        $query->addFilter("XML_ID", $xmlId);
        $result = $query->fetchAll();
        $item = current($result);
        
        return $item["ID"];
    }
    
    public static function getElementIdByXmlIdMulti( $xmlId, $iblockId )
    {
        // обнуляем множ св-во с типов "привязка к элементам"
        // если передать пустой массив - он просто игнорируется ядром
        if (!$xmlId)
            return false;
        
        $query = new ElementReader($iblockId);
        $query->addFilter("XML_ID", $xmlId);
        $items = $query->fetchAll();
        
        $result = [];
        
        foreach ( $items as $item ) {
            $result[] = $item["ID"];
        }
        
        return $result;
    }
}