<?php
# @(#) $Id$

require_once('lib/array.php');

function createStyleList()
{
global $styleList,$styleNames;

if(!isset($styleList))
  {
  $styleList=array(1 => 'default');
  $styleNames=array(1 => 'По умолчанию');
  }
}

function declareStyle($ident,$name)
{
global $styleList,$styleNames;

createStyleList();
if(!in_array(strtolower($ident),$styleList))
  {
  $styleList[]=strtolower($ident);
  $styleNames[]=$name;
  }
}

function getStyle($n)
{
global $styleList;

createStyleList();
return $styleList[isset($styleList[$n]) ? $n : 1];
}

class StyleNamesIterator
      extends ArrayIterator
{

function StyleNamesIterator()
{
global $styleNames;

$this->ArrayIterator($styleNames);
}

}
?>
