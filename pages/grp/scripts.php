<?php
# @(#) $Id$

require_once('lib/entries.php');
require_once('lib/postings.php');

function scriptGrpDetails($id)
{
$posting=new Posting(array('id'  => $id,
                           'grp' => getGrpByEntryId($id)));
return $posting->getGrpDetailsScript();
}
?>
