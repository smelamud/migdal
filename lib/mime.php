<?php
# @(#) $Id$

$mimeExtensions=array('image/pjpeg'     => 'jpg',
                      'image/jpeg'      => 'jpg',
		      'image/gif'       => 'gif',
		      'image/x-png'     => 'png',
		      'image/png'       => 'png',
		      'application/zip' => 'zip');

function getMimeExtension($mime_type)
{
global $mimeExtensions;

return isset($mimeExtensions[$mime_type]) ? $mimeExtensions[$mime_type] : '';
}

$mimeTypes=array('jpg' => 'image/jpeg',
                 'gif' => 'image/gif',
		 'png' => 'image/png',
		 'zip' => 'application/zip');

function getMimeType($ext)
{
global $mimeTypes;

return isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : '';
}
?>
