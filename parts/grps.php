<?php
# @(#) $Id$

require_once('lib/grps.php');

$grpTitles=array(GRP_FORUMS  => '������',
                 GRP_NEWS    => '�������',
		 GRP_GALLERY => '�������');
$grpOneTitles=array(GRP_FORUMS  => '�����',
                    GRP_NEWS    => '�������',
	   	    GRP_GALLERY => '�������');
$grpItemTitles=array(GRP_FORUMS  => '���������',
                     GRP_NEWS    => '�������',
		     GRP_GALLERY => '��������');
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
