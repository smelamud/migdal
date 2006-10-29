<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errors.php');
require_once('lib/text.php');
require_once('lib/text-any.php');

function uploadLargeBody(&$posting,$del)
{
global $tmpDir,$maxLargeText;

if($del)
  $posting->large_body='';
if(!isset($_FILES['large_body_file']))
  return EG_OK;
$file=$_FILES['large_body_file'];
if($file['tmp_name']=='' || !is_uploaded_file($file['tmp_name'])
   || filesize($file['tmp_name'])!=$file['size'])
  return EG_OK;
if($file['size']>$maxLargeText)
  return EUL_LARGE;

$tmpname=tempnam($tmpDir,'mig-');
if(!move_uploaded_file($file['tmp_name'],$tmpname))
  return EG_OK;
$fd=fopen($tmpname,'r');
$posting->has_large_body=1;
$posting->large_body_filename=$file['name'];
$text=fread($fd,$maxLargeText);
$posting->large_body=convertUploadedText($text);
$posting->large_body_xml=anyToXML($posting->large_body,
                                  $posting->large_body_format,MTEXT_LONG);
fclose($fd);
unlink($tmpname);

return EG_OK;
}
?>
