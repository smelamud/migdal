<?php
require_once('lib/grps.php');
require_once('lib/utils.php');

$grpTitles=array(GRP_FORUMS   => 'Форумы',
                 GRP_NEWS     => 'Новости',
		 GRP_GALLERY  => 'Галерея',
		 GRP_ARTICLES => 'Статьи',
                 GRP_ALL      => 'Все сообщения');
$grpOneTitles=array(GRP_FORUMS   => 'Форум',
                    GRP_NEWS     => 'Новости',
	   	    GRP_GALLERY  => 'Галерея',
		    GRP_ARTICLES => 'Статьи',
                    GRP_ALL      => 'Все сообщения');
$grpItemTitles=array(GRP_FORUMS   => 'сообщение',
                     GRP_NEWS     => 'новость',
		     GRP_GALLERY  => 'картинку',
		     GRP_ARTICLES => 'статью',
		     GRP_ALL      => 'сообщение');
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
return getGrpValid($this->grp);
}

function getGrp()
{
return $this->grp;
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
