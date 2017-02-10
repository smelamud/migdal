<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/uri.php');
require_once('lib/post.php');

httpRequestString('okdir');
httpRequestString('faildir');
httpRequestIntegerArray('topic_id');
httpRequestInteger('offset');
httpRequestIntegerArray('grp');
httpRequestInteger('index1');
httpRequestInteger('use_index1');
httpRequestInteger('shadows');

$remove = array(
    'okdir',
    'faildir',
    'offset'
);
$recursive = array();
for ($n = 0; $n < count($topic_id); $n++) {
    $rname = "recursive($n)";
    httpRequestInteger($rname);
    $recursive[] = $Args[$rname] != 0 ? 1 : 0;
    $remove[] = $rname;
}

header(
    'Location: '.
    remakeMakeURI(
        $okdir,
        $Args,
        $remove,
        array('recursive' => $recursive)
    )
);
?>
