<?php
# @(#) $Id$

require_once('lib/iterator.php');

$langCodes=array('ru' => '�������',
                 'en' => '����������',
		 'he' => '�����',
		 'uk' => '����������',
		 'be' => '�����������',
		 'yi' => '����',
		 'de' => '��������');

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
      extends AssocArrayIterator
{

function LangIterator()
{
global $langCodes;

parent::AssocArrayIterator($langCodes,'LangInfo');
}

}
?>
