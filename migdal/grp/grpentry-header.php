<?php
require_once('lib/entries.php');
require_once('grp/grps.php');

class GrpEntry
      extends Entry
{

function GrpEntry($row)
{
$this->grp=GRP_NONE;
$this->grps=array();
parent::Entry($row);
}

