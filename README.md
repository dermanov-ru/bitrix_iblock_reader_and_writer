# О проекте
## ООП конструктор запросов для выборки данных из инфоблоков

Данный проект представляет собой ООП конструктор запросов для выборки данных из инфоблоков: разделов и элементов. 

Новым ядром D7 стало намного приятнее пользоваться для построения запросов на выборку данных из инфоблоков. Однако, ровно до тех пор, пока дело не касается выборки свойств и фильтрации по свойствам.
Описывать каждый раз таблицу со свойствами, чтобы присоединить ее на лету не всегда хочется. 

При этом, старое API продолжает работать и вполне справляется со своей задачей выборки и фильтрации даных из инфоблоков.  
Остается только сделать удобным процесс формирования запроса на выборку данных.

Еще приходится учитывать, что выборка множественых свойств из инфоблоков 1.0 ведет к декартову произведению и дублированию данных.
Поэтому, в этом случае, свойства нужно получать отдельно от элементов.

## Обертка для записи элементов в инфоблок

В тоже время я занимался импортом на одном проекте - задача заключалась в синхронизации сайтов-клонов. 
То есть было три сайта, два из которых копии основного. 
И была задача автоматизировать ручное наполнение контентом, чтобы можно было заполнить на одном сайте, и синхронизировать клоны.
На всех сайтах-клонах у элементов и свойств(енумов) совпадали XML_ID.
Как всегда, были заморочки с обнулением множественного св-ва типа "файл" :)
Написал обертку для записи элементов в инфоблок, сохранения внешних файлов, и выборке ID по XML_ID.

Это близкие по смыслу проекты, поэтому не стал их разделять на разные репозитории.

# Возможности
## ООП конструктор запросов для выборки данных из инфоблоков
Возможности ООП конструкторва почти не отличаются от стандартных возможностей старого API для выборки данных из инфоблоков, то есть метода GetList классов \CIblockElement и \CIblockSection. 

- указать поля для выборки
- указать свойства для выборки
- указать сортировку
- указать лимит и оффсет 
- задать фильтр
- получить все строки разом
- выборка хранится в объекте запроса 
- найти ИД иблока по коду

По мере необходимости - можно будет прикрутить группировку.  

## Обертка для записи элементов в инфоблок
- Создание и обновление элементов инфоблока
- сохранение внешних файлов в папку upload
- удобное обновление множественного св-ва типа "файл"
- пакетное получение ID по XML_ID, для елементов и енумов

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
## Чтение данных
### Выборка элементов
```
<?php
$iblockId = Engine::findIblockId($type, $code);
$elementReader = new \Data\Iblock\Read\ElementReader( $iblockId );

// если инфоблок версии 1.0 - нужно вызвать метод явно.
// $elementReader->setIblockEngineVersion1();

// если инфоблок версии 2.0 - можно не вызывать метод явно, это поведение по умолчанию
// $elementReader->setIblockEngineVersion2();

$elementReader
    ->addFilter("ACTIVE", "Y");
    ->addFilter("CODE", $arParams["ROUT_VALUES"]["ELEMENT_CODE"]);

$elementReader
    ->addSelectField("NAME");
    ->addSelectField("DETAIL_PICTURE");
    ->addSelectField("DETAIL_TEXT");

$elementReader
    ->addSelectProp("ARTICUL");    // single prop
    ->addSelectProp("MORE_PHOTO"); // multiple prop
    ->addSelectProp("MATERIAL");   // multiple prop

$items = $elementReader->fetchAll();
```
### Выборка разделов
```
<?php
$sectionReader = new \Data\Iblock\Read\SectionReader( $iblockId );

$sectionReader
    ->addFilter("ACTIVE", "Y");
    ->addFilter("CODE", $arParams["ROUT_VALUES"]["SECTION_CODE"]);

$sectionReader
    ->addSelectField("NAME");
    ->addSelectField("PICTURE");
    ->addSelectField("DESCRIPTION");

$sectionReader
    ->addSelectProp("UF_CUSTOM_NAME");    
    ->addSelectProp("UF_ADDITIONAL_DESC");    
    ->addSelectProp("UF_MORE_PHOTO"); 

$sections = $sectionReader->fetchAll();
```
## Запись данных
### Запись элемента инфоблока
```
<?php
$writer = new ElementWriter();
$iblockId = Engine::findIblockId("CATALOG", "collections");
$id = ElementReader::getElementIdByXmlId($item["XML_ID"], $iblockId);

$updateFields = [
    "DETAIL_TEXT" => $item["DETAIL_TEXT"],
    "DETAIL_TEXT_TYPE" => $item["DETAIL_TEXT_TYPE"],
    "PREVIEW_PICTURE" => $writer->saveRemoteFile($item["PREVIEW_PICTURE"], $returnArray = true),
    "DETAIL_PICTURE" => $writer->saveRemoteFile($item["DETAIL_PICTURE"], $returnArray = true),
];

$updateProps = [
    "COLOR" => EnumReader::getEnumIdByXmlIdMulti($item["PROPERTY_COLOR_VALUE"], $iblockId, "COLOR"),
    "STYLISTIC" => EnumReader::getEnumIdByXmlIdMulti($item["PROPERTY_STYLISTIC_VALUE"], $iblockId, "STYLISTIC"),
];

$updateFileProps = [
    "PHOTO" => $writer->saveRemoteFileMulti($item["PROPERTY_PHOTO_VALUE"]),
];
        
// $id = $writer->addElement($updateFields, $iblockId, $updateProps, $updateFileProps);
// or
$writer->updateElement($id, $updateFields, $iblockId, $updateProps, $updateFileProps);
```