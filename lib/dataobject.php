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

function getWorldVarValues()
{
$vals=array();
foreach($this->getWorldVars() as $var)
       $vals[$var]=$this->$var;
return $vals;
}

function getAdminVarValues()
{
$vals=array();
foreach($this->getAdminVars() as $var)
       $vals[$var]=$this->$var;
return $vals;
}

function getNormal($isAdmin=false)
{
$normal=$this->getWorldVarValues();
if($isAdmin)
  $normal=array_merge($normal,$this->getAdminVarValues());
return $normal;
}

}
?>
