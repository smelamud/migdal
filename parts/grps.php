<?php
# @(#) $Id$

require_once('lib/grps.php');

$grpTitles=array(GRP_FORUMS  => 'Форумы',
                 GRP_NEWS    => 'Новости',
		 GRP_GALLERY => 'Галерея');
$grpOneTitles=array(GRP_FORUMS  => 'Форум',
                    GRP_NEWS    => 'Новости',
	   	    GRP_GALLERY => 'Галерея');
$grpItemTitles=array(GRP_FORUMS  => 'сообщение',
                     GRP_NEWS    => 'новость',
		     GRP_GALLERY => 'картинку');
$grpIdents=array(GRP_FORUMS  => 'forums',
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
?>
