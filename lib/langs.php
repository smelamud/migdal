<?php
# @(#) $Id$

require_once('lib/iterator.php');

$langCodes=array('ru' => 'Русский',
                 'en' => 'Английский',
		 'he' => 'Иврит',
		 'uk' => 'Украинский',
		 'be' => 'Белорусский',
		 'yi' => 'Идиш',
		 'de' => 'Немецкий');

class LangInfo
{
var $code;
var $name;

function __construct($code,$name)
{
$this->code=$code;
$this->name=$name;
}

function getCode()
{
return $this->code;
}

function getName()
{
return $this->name;
}

}

class LangIterator
      extends AssocArrayIterator
{

function __construct()
{
global $langCodes;

parent::__construct($langCodes,'LangInfo');
}

}
?>
