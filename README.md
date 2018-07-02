# О проекте
Данный проект представляет собой ООП конструктор запросов для выборки данных из инфоблоков: разделов и элементов. 

Новым ядром D7 стало намного приятнее пользоваться для построения запросов на выборку данных из инфоблоков. Однако, ровно до тех пор, пока дело не касается выборки свойств и фильтрации по свойствам.
Описывать каждый раз таблицу со свойствами, чтобы присоединить ее на лету не всегда хочется. 

При этом, старое API продолжает работать и вполне справляется со своей задачей выборки и фильтрации даных из инфоблоков.  
Остается только сделать удобным процесс формирования запроса на выборку данных.

Еще приходится учитывать, что выборка множественых свойств из инфоблоков 1.0 ведет к декартову произведению и дублированию данных.
Поэтому, в этом случае, свойства нужно получать отдельно от элементов.

# Возможности
Возможности ООП конструкторва почти не отличаются от стандартных возможностей старого API для выборки данных из инфоблоков, то есть метода GetList классов \CIblockElement и \CIblockSection. 

- указать поля для выборки
- указать свойства для выборки
- указать сортировку
- указать лимит и оффсет 
- задать фильтр
- получить все строки разом
- выборка хранится в объекте запроса 

По мере необходимости - можно будет прикрутить группировку.  

# Установка
1. Положить файлы проекта в папку `/local/`
2. Подключить PSR-4 автозагрузчик для папки `/local/lib/`, например в файле init.php
```
<?php
/*
 * PSR-4 classes autoloader for folder /local/lib/
 * */
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    
    $path = $_SERVER["DOCUMENT_ROOT"] .'/local/lib/'.$class.'.php';
    
    if (is_readable($path)) {
        require_once $path;
    }
});
```

# Примеры
## Выборка элементов
```
<?php
$elementQueryEngine = new \Data\Iblock\Query\Element( $iblockId );

// если инфоблок версии 1.0 - нужно вызвать метод явно.
// $elementQueryEngine->setIblockEngineVersion1();

// если инфоблок версии 2.0 - можно не вызывать метод явно, это поведение по умолчанию
// $elementQueryEngine->setIblockEngineVersion2();

$elementQueryEngine
    ->addFilter("ACTIVE", "Y");
    ->addFilter("CODE", $arParams["ROUT_VALUES"]["ELEMENT_CODE"]);

$elementQueryEngine
    ->addSelectField("NAME");
    ->addSelectField("DETAIL_PICTURE");
    ->addSelectField("DETAIL_TEXT");

$elementQueryEngine
    ->addSelectProp("ARTICUL");    // single prop
    ->addSelectProp("MORE_PHOTO"); // multiple prop
    ->addSelectProp("MATERIAL");   // multiple prop

$items = $elementQueryEngine->fetchAll();
```
## Выборка разделов
```
<?php
sectionQueryEngine = new \Data\Iblock\Query\Section( $iblockId );

sectionQueryEngine
    ->addFilter("ACTIVE", "Y");
    ->addFilter("CODE", $arParams["ROUT_VALUES"]["SECTION_CODE"]);

sectionQueryEngine
    ->addSelectField("NAME");
    ->addSelectField("PICTURE");
    ->addSelectField("DESCRIPTION");

sectionQueryEngine
    ->addSelectProp("UF_CUSTOM_NAME");    
    ->addSelectProp("UF_ADDITIONAL_DESC");    
    ->addSelectProp("UF_MORE_PHOTO"); 

$sections = sectionQueryEngine->fetchAll();
```