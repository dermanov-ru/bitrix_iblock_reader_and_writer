<?php
/*
 * Created by PhpStorm.
 * Date: 05.10.2017
 * 
 * @author dev@dermanov.ru
 */


namespace Data\Iblock\Query;


class Section extends Engine
{
    
    public function fetchAll(  )
    {
        $this->dbres = $dbres = \CIBlockSection::GetList($this->getSort(), $this->getFilter(), false, $this->getSelect(), $this->getPageNav());
        
        // TODO
        // Устанавливает шаблоны путей для элементов, разделов и списка элементов вместо тех которые указаны в настройках информационного блока
        /* чтобы ссылки с товаров вели на данный раздел */
        //$res->SetUrlTemplates( $params["detail_page_url_template"], $params["section_page_url_template"] );
        
        $result = array();
        
        while($ob = $dbres->GetNext(true, false)) {
            $result[ $ob["ID"] ] = $ob;
        }
        
        $this->resultArray = $result;
        
        return $result;
    }
    
    public function addSelectProp( $prop )
    {
        $result = $this->getSelect();
        
        $result[] = $prop;
        
        $this->select = $result;
    }
    
    public function getGroupedByLevel(  )
    {
        $result = array();
        
        $items = $this->getResultArray();
    
        foreach ( $items as $item ) {
            $result[ $item["DEPTH_LEVEL"] ][ $item["ID"] ] = $item;
        }
        
        return $result;
    }
    
    public function filterResultByLevel( $level, $parentId = false )
    {
        if (!$level)
            throw new \Exception ( '$level can not be void' );
        
        $result = array();
        
        $items = $this->getResultArray();
    
        foreach ( $items as $item ) {
            if ($parentId && $item["IBLOCK_SECTION_ID"] != $parentId)
                continue;
                
            if ($item["DEPTH_LEVEL"] == $level)
                $result[ $item["ID"] ] = $item;
        }
        
        return $result;
    }
    
    public function filterRootSections( )
    {
        $result = $this->filterResultByLevel(1);
        
        return $result;
    }
}