<?php
require_once('lib/entries.php');
require_once('conf/grps.php');

class GrpEntry
      extends Entry
{

function __construct($row)
{
$this->grp=GRP_NONE;
$this->grps=array();
parent::__construct($row);
}

function getGrpImageEditor()
{
$editors=$this->getGrpEditor();
foreach($editors as $editor)
       if(isset($editor['ident']) && $editor['ident']=='image')
         return $editor;
return array();
}

