<?php
# @(#) $Id$

class DataObject
{

function DataObject($row)
{
reset($row);
while(list($var,$value)=each($row))
     $this->$var=$value;
}

function getWorldVars()
{
return array();
}

function getAdminVars()
{
return array();
}

function collectVars($vars)
{
$vals=array();
foreach($vars as $var)
       $vals[$var]=$this->$var;
return $vals;
}

function getNormal($isAdmin=false)
{
$normal=$this->collectVars($this->getWorldVars());
if($isAdmin)
  $normal=array_merge($normal,$this->collectVars($this->getAdminVars()));
return $normal;
}

}
?>
