<?php
/*
 * Created by PhpStorm.
 * Date: 05.10.2017
 * 
 * @author dev@dermanov.ru
 */


namespace Data\Iblock\Read;


use Bitrix\Main\Loader;

abstract class Engine
{
    protected
        $select = [],
        $sort = false,
        $filter = [],
        $limit = 9999,
        $offset = 1
    ;
    
    /** @var \CIBlockResult $dbres */
    protected $dbres;
    protected $resultArray;
    
    protected $iblockId;
    
    /**
     * Engine constructor.
     */
    public function __construct($iblockId)
    {
        if (!$iblockId)
            throw new \Exception ( '$iblockId can not be void' );
        
        Loader::includeModule("iblock");
        
        $this->iblockId = $iblockId;
    }
    
    public static function findIblockId( $type, $code )
    {
        if (!$type)
            throw new \Exception ( '$type can not be void' );
        
        if (!$code)
            throw new \Exception ( '$code can not be void' );
    
        Loader::includeModule("iblock");
        
        /*
         * TODO cache results
         * */
        
        $iblock = \CIBlock::GetList(false, array(
            "TYPE" => $type,
            "CODE" => $code,
        ))->Fetch();
        
        $iblockId = $iblock["ID"];
    
        if (!$iblockId) {
            throw new \Exception ( "cant find iblock [$type:$code] " );
        }
        
        return $iblockId;
    }
    
    abstract public function fetchAll(  );
    
    /**
     * Сначала должен быть вызван метод fetchAll().
     *
     * Здесь хранится полученный результат в виде массива.
     */
    public function getResultArray()
    {
        return $this->resultArray;
    }
    
    /**
     * @return mixed
     */
    public function getSelect()
    {
        $result = $this->select;
        
        if (!is_array($result))
            $result = array();
        
        if (!in_array("ID", $result))
            $result[] = "ID";
        
        if (!in_array("IBLOCK_ID", $result))
            $result[] = "IBLOCK_ID";
        
        return $result;
    }
    
    /**
     * @return mixed
     */
    public function getFilter()
    {
        $result = $this->filter;
    
        if (!is_array($result))
            $result = array();
    
        $result["IBLOCK_ID"] = $this->getIblockId();
        
        // TODO ?
        //$arFilter["ACTIVE"]    = "Y";
        
        return $result;
    }
    
    /**
     * @param mixed $select
     */
    public function setSelect( $select )
    {
        $this->select = $select;
    }
    
    /**
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort ? $this->sort : array("SORT" => "ASC", "NAME" => "ASC");
    }
    
    /**
     * @param mixed $sort
     */
    public function setSort( $sort )
    {
        $this->sort = $sort;
    }
    
    /**
     * @param mixed $filter
     */
    public function addFilter( $field, $filter )
    {
        $this->filter[$field] = $filter;
    }
    
    /**
     * @param mixed $filter
     */
    public function setFilter( $filter )
    {
        $this->filter = $filter;
    }
    
    /**
     * @return mixed
     */
    public function getLimitPerPage()
    {
        return $this->limit;
    }
    
    /**
     * @param mixed $limit
     */
    public function setLimitPerPage( $limit )
    {
        $this->limit = $limit;
    }
    
    /**
     * @return mixed
     */
    public function getPageOffset()
    {
        return $this->offset;
    }
    
    /**
     * @return mixed
     */
    public function getTotalCount()
    {
        $this->dbres->SelectedRowsCount(); // общее число выбранных элементов
        
        return $this->offset;
    }
    
    /**
     * @return mixed
     */
    public function GetPageNavString($tplName = "", $component = false)
    {
        $navComponentObject = false;
        $result = $this->dbres->GetPageNavStringEx($navComponentObject, "", $tplName, false, $component);
        
        return $result;
    }
    
    /**
     * @param mixed $offset
     */
    public function setPageOffset( $offset )
    {
        $this->offset = $offset;
    }
    
    /**
     */
    protected function getPageNav(  )
    {
        $result = array(
            "nPageSize" => $this->getLimitPerPage(),
            "iNumPage" => $this->getPageOffset(),
            
            /*
             * Защита подставленного вручную параметра постранички ?SHOWALL_1=1
             * который может уронить проект.
             * */
            "bShowAll" => false,
        );
    
        return $result;
    }
    
    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }
    
    /**
     * @return mixed
     */
    public function getIblockId()
    {
        return $this->iblockId;
    }
    
    public function addSelectField( $field )
    {
        $result = $this->getSelect();
        
        $result[] = $field;
        
        $this->select = $result;
    }
    
    /**
     * @return []
     * */
    public function get(  )
    {
        $items = $this->getResultArray();
        
        return $items;
    }
    
}