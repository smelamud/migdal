<?php
# @(#) $Id$

class DataObject
{

function __construct($row)
{
foreach($row as $var => $value)
       $this->$var=$value;
}

}
?>
