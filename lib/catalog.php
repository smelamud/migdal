<?php
# @(#) $Id$

require_once('lib/track.php');

function catalog($id,$ident,$prev='')
{
if($ident!='')
  {
  $catalog=strtr($ident,'.','/');
  if($catalog{0}=='/')
    $catalog=substr($catalog,1);
  if($catalog{strlen($catalog)-1}!='/')
    $catalog.='/';
  return $catalog;
  }
else
  {
  if($prev!='' && $prev{strlen($prev)-1}!='/')
    $prev.='/';
  $id=(int)$id;
  return "$prev$id/";
  }
}

function catalogById($id)
{
if($id<=0)
  return '';
if(hasCachedValue('catalog','entries',$id))
  return getCachedValue('catalog','entries',$id);
$result=sql("select catalog
	     from entries
	     where id=$id",
	    __FUNCTION__);
$catalog=mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
setCachedValue('catalog','entries',$id,$catalog);
return $catalog;
}

function updateCatalogById($id,$catalog)
{
return sql("update entries
	    set catalog='$catalog'
	    where id=$id",
	   __FUNCTION__);
}

function updateCatalogs($id,$journalize=true)
{
$filter=$id>0 ? "track like '%".track($id)."%'" : '1';
$result=sql("select id,ident,up
	     from entries
	     where $filter or catalog=''
	     order by track",
	    __FUNCTION__);
$catalogs=array();
while($row=mysql_fetch_assoc($result))
     {
     if(!isset($catalogs[$row['up']]))
       $catalogs[$row['up']]=catalogById($row['up']);
     $catalogs[$row['id']]=catalog($row['id'],$row['ident'],
                                   $catalogs[$row['up']]);
     updateCatalogById($row['id'],$catalogs[$row['id']]);
     }
if($journalize)
  journal("catalogs entries ".journalVar('entries',$id));
}
?>
