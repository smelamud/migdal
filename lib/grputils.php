<?php
require_once('lib/grps.php');

$grpTitles=array(GRP_ANY     => '��� ���������',
                 GRP_FORUMS  => '������',
                 GRP_NEWS    => '�������',
		 GRP_GALLERY => '�������');
$grpOneTitles=array(GRP_ANY     => '��� ���������',
                    GRP_FORUMS  => '�����',
                    GRP_NEWS    => '�������',
	   	    GRP_GALLERY => '�������');
$grpItemTitles=array(GRP_FORUMS  => '���������',
                     GRP_NEWS    => '�������',
		     GRP_GALLERY => '��������');
$grpIdents=array(GRP_ANY     => 'messages',
                 GRP_FORUMS  => 'forums',
                 GRP_NEWS    => 'news',
		 GRP_GALLERY => 'gallery');

function getGrpTitle($grp)
{
global $grpTitles;

return $grpTitles[$grp];
}

function getGrpOneTitle($grp)
{
global $grpOneTitles;

return $grpOneTitles[$grp];
}

function getGrpItemTitle($grp)
{
global $grpItemTitles;

return $grpItemTitles[$grp];
}

function getGrpIdent($grp)
{
global $grpIdents;

return $grpIdents[$grp];
}

class GrpData
{
var $grp;
var $ignore;

function GrpData($grp,$ignore=0)
{
$this->grp=$grp;
$this->ignore=$ignore;
}

function getValid()
{
$ident=$this->getIdent();
return isset($ident);
}

function getInvalid()
{
return !$this->getValid();
}

function getEffective()
{
return $this->ignore ? GRP_ANY : $this->grp;
}

function getName()
{
return getGrpName($this->grp);
}

function getTitle()
{
return getGrpTitle($this->grp);
}

function getOneTitle()
{
return getGrpOneTitle($this->grp);
}

function getItemTitle()
{
return getGrpItemTitle($this->grp);
}

function getIdent()
{
return getGrpIdent($this->grp);
}

function getClassName()
{
return getGrpClassName($this->grp);
}

}
?>
