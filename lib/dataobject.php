<?php
# @(#) $Id$

class DataObject
{

function DataObject($row)
{
foreach($row as $var => $value)
       $this->$var=$value;
}

}
?>
