<?php
define('GRP_FORUMS',1);
define('GRP_NEWS',2);
define('GRP_GALLERY',4);
define('GRP_EVENTS',7);
define('GRP_ARTICLES',8);
define('GRP_ALL',15);

$grpNames=array(GRP_FORUMS   => 'forums',
                GRP_NEWS     => 'news',
                GRP_GALLERY  => 'gallery',
		GRP_ARTICLES => 'articles');

function getGrpName($grp)
{
global $grpNames;

return $grpNames[$grp];
}

$grpClassNames=array(GRP_FORUMS   => 'Forum',
                     GRP_NEWS     => 'News',
                     GRP_GALLERY  => 'Gallery',
		     GRP_ARTICLES => 'Article',
		     GRP_ALL      => 'Posting');

function getGrpClassName($grp)
{
global $grpClassNames;

$name=$grpClassNames[$grp];
return isset($name) ? $name : 'Message';
}

function getGrpValid($grp)
{
return round(exp(round(log($grp)/M_LN2)*M_LN2))==$grp;
}
?>
