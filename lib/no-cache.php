<?php
# @(#) $Id$

function noCacheHeaders()
{
header('Expires: '.gmdate('D, d M Y H:i:s',time()-60));
header('Last-Modified: '.gmdate('D, d M Y H:i:s'));
header('Cache-Control: no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0, s-max-age=0'); 
header('Pragma: no-cache'); 
}

function noCacheMeta()
{
?>
<meta http-equiv='Expires' content='<?php echo gmdate('D, d M Y H:i:s',time()-60) ?>'>
<meta http-equiv='Last-Modified' content='<?php echo gmdate('D, d M Y H:i:s') ?>'>
<meta http-equiv='Cache-Control'
      value='no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0, s-max-age=0'>
<meta http-equiv='Pragma' content='no-cache'>
<?php
}
?>
