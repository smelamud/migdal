<?php
# @(#) $Id$

/*
 * ПРЕДУПРЕЖДЕНИЕ: все эти функции могут вызываться только с параметрами arg.*,
 * id.*. Поскольку эти параметры, если они отмечены как числовые, уже прошли
 * обработку, их безопасно вставлять в SQL-операторы. Для всех остальных
 * параметров обработка необходима.
 */
require_once('lib/entries.php');
require_once('lib/postings.php');

function scriptGrpDetails($id) {
    $posting = new Posting(array('id'  => $id,
                                 'grp' => getGrpByEntryId($id)));
    return $posting->getGrpDetailsScript();
}
?>
