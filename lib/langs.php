<?php
# @(#) $Id$

require_once('lib/iterator.php');

$langCodes=array('ru' => '�������',
                 'en' => '����������',
		 'he' => '�����',
		 'uk' => '����������',
		 'be' => '�����������',
		 'yi' => '����');

class LangInfo
{
var $code;
var $name;

function LangInfo($code,$name)
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
      extends Iterator
{

function LangIterator()
{
global $langCodes;

$this->Iterator();
reset($langCodes);
}

function next()
{
global $langCodes;

Iterator::next();
$lang=key($langCodes);
next($langCodes);
return $lang ? new LangInfo($lang,$langCodes[$lang]) : 0;
}

function getCount()
{
global $langCodes;

return count($langCodes);
}

}

?>
