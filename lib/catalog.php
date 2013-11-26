<?php
# @(#) $Id$

require_once('lib/track.php');
require_once('lib/utils.php');
require_once('lib/uri.php');
require_once('lib/modbits.php');
require_once('lib/entry-types.php');

function catalog($id,$ident,$prev='')
{
if($ident!='')
  {
  if(substr($ident,0,5)=='post.')
    $ident=substr($ident,5);
  $pos=strrpos($ident,',');
  if($pos!==false)
    $ident=substr($ident,0,$pos);
  $catalog=strtr($ident,'.','/');
  return normalizePath($catalog,false,SLASH_NO,SLASH_YES);
  }
else
  {
  $id=(int)$id;
  if($prev=='')
    return "$id/";
  $prev=normalizePath($prev,false,SLASH_NO,SLASH_YES);
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
if(catalogById($id)!=$catalog)
  {
  sql("update entries
       set catalog='$catalog'
       where id=$id",
      __FUNCTION__);
  setCachedValue('catalog','entries',$id,$catalog);
  }
}

function updateCatalogs($trackPrefix='')
{
$filter=$trackPrefix!='' ? "track like '$trackPrefix%'" : '1';
$result=sql("select entry,id,ident,up,catalog,modbits
	     from entries
	     where $filter
	     order by track",
	    __FUNCTION__);
$catalogs=array();
while($row=mysql_fetch_assoc($result))
     {
     setCachedValue('catalog','entries',$row['id'],$row['catalog']);
     if(!isset($catalogs[$row['up']]))
       $catalogs[$row['up']]=catalogById($row['up']);
     if($row['entry']==ENT_TOPIC
        && ($row['modbits'] & (MODT_ROOT|MODT_TRANSPARENT))!=0)
       $catalogs[$row['id']]='';
     elseif($row['entry']==ENT_TOPIC && ($row['modbits'] & MODT_ROOT)!=0)
       $catalogs[$row['id']]=catalog($row['id'],$row['ident'],'');
     elseif($row['entry']==ENT_TOPIC && ($row['modbits'] & MODT_TRANSPARENT)!=0)
       $catalogs[$row['id']]=$catalogs[$row['up']];
     else
       $catalogs[$row['id']]=catalog($row['id'],$row['ident'],
				     $catalogs[$row['up']]);
     updateCatalogById($row['id'],$catalogs[$row['id']]);
     }
}
?>
