<?php
require_once('lib/grps.php');

$grpTitles=array(GRP_FORUMS   => '������',
                 GRP_NEWS     => '�������',
		 GRP_GALLERY  => '�������',
		 GRP_ARTICLES => '������',
                 GRP_ALL      => '��� ���������');
$grpOneTitles=array(GRP_FORUMS   => '�����',
                    GRP_NEWS     => '�������',
	   	    GRP_GALLERY  => '�������',
		    GRP_ARTICLES => '������',
                    GRP_ALL      => '��� ���������');
$grpItemTitles=array(GRP_FORUMS   => '���������',
                     GRP_NEWS     => '�������',
		     GRP_GALLERY  => '��������',
		     GRP_ARTICLES => '������');
$grpIdents=array(GRP_FORUMS   => 'forums',
                 GRP_NEWS     => 'news',
		 GRP_GALLERY  => 'gallery',
		 GRP_ARTICLES => 'articles',
                 GRP_ALL      => 'messages');

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

function getValidItem()
{
$item=$this->getItemTitle();
return isset($item);
}

function getInvalid()
{
return !$this->getValid();
}

function getEffective()
{
return $this->ignore ? GRP_ALL : $this->grp;
}

function getIgnore()
{
return $this->ignore ? 1 : 0;
}

function getInvIgnore()
{
return $this->ignore ? 0 : 1;
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
