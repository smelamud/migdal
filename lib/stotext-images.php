<?php
# @(#) $Id$

require_once('lib/dataobject.php');

define('IPL_LEFT',1);
define('IPL_HCENTER',2);
define('IPL_RIGHT',3);
define('IPL_HORIZONTAL',3);
define('IPL_TOP',4);
define('IPL_VCENTER',8);
define('IPL_BOTTOM',12);
define('IPL_VERTICAL',12);

define('IPL_TOPLEFT',IPL_TOP|IPL_LEFT);
define('IPL_TOPCENTER',IPL_TOP|IPL_HCENTER);
define('IPL_TOPRIGHT',IPL_TOP|IPL_RIGHT);
define('IPL_CENTERLEFT',IPL_VCENTER|IPL_LEFT);
define('IPL_CENTER',IPL_VCENTER|IPL_HCENTER);
define('IPL_CENTERRIGHT',IPL_VCENTER|IPL_RIGHT);
define('IPL_BOTTOMLEFT',IPL_BOTTOM|IPL_LEFT);
define('IPL_BOTTOMCENTER',IPL_BOTTOM|IPL_HCENTER);
define('IPL_BOTTOMRIGHT',IPL_BOTTOM|IPL_RIGHT);

class StotextImage
      extends DataObject
{
var $stotext_id;
var $par;
var $image_id;
var $has_large_image;
var $title;
var $placement;

function StotextImage($row)
{
$this->placement=IPL_CENTER;
$this->DataObject($row);
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
}

function getCorrespondentVars()
{
return array('par','image_id','placement');
}

function getWorldVars()
{
return array('stotext_id','par','image_id','placement');
}

function getAdminVars()
{
return array();
}

function getJencodedVars()
{
return array('stotext_id' => 'stotexts','image_id' => 'images');
}

function store()
{
$result=mysql_query('delete from stotext_images
                     where stotext_id='.$this->stotext_id.
		   ' and par='.$this->par);
if($result)
  journal('delete from stotext_images
           where stotext_id='.journalVar('stotexts',$this->stotext_id).
	 ' and par='.$this->par);
if(!$result || $this->image_id==0)
  return $result;
$normal=$this->getNormal();
$result=mysql_query(makeInsert('stotext_images',$normal));
if($result)
  journal(makeInsert('stotext_images',
                     jencodeVars($normal,$this->getJencodedVars())));
return $result;
}

function getStotextId()
{
return $this->stotext_id;
}

function getPar()
{
return $this->par;
}

function getImageId()
{
return $this->image_id;
}

function hasLargeImage()
{
return $this->has_large_image;
}

function getPlacement()
{
return $this->placement;
}

function getTitle()
{
return $this->title;
}

function getHTMLTitle()
{
return stotextToHTML(TF_MAIL,$this->title);
}

}

function getStotextImageByParagraph($textid,$par)
{
$result=mysql_query("select stotext_id,par,image_id,placement
                     from stotext_images
                     where stotext_id=$textid and par=$par");
return new StotextImage(mysql_num_rows($result)>0
                        ? mysql_fetch_assoc($result)
                        : array('stotext_id' => $textid,
			        'par'        => $par));
}
?>
