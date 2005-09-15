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

}
?>
