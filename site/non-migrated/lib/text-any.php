<?php
# @(#) $Id$

require_once('lib/text.php');
require_once('lib/text-xml.php');
require_once('lib/text-wiki.php');

function anyToXML($s, $format, $dformat) {
    if ($format == TF_XML)
        return xmlFormatToXML($s, $format, $dformat);
    else
        return wikiToXML($s, $format, $dformat);
}
?>
