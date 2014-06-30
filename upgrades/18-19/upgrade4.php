<?php
# @(#) $Id: daily.php 2762 2014-06-16 10:51:04Z balu $

require_once('conf/migdal.conf');
$debug=true;

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/sql.php');
require_once('lib/session.php');
require_once('lib/text-any.php');

dbOpen();
session(getShamesId());

$result = sql("select id,title
               from entries
               where title<>''",
              __FUNCTION__);
while ($row = mysql_fetch_assoc($result)) {
    echo "{$row['id']}\n";
    $title_xml = sqlValue(anyToXML($row['title'], TF_PLAIN, MTEXT_LINE));
    sql("update entries
         set title_xml=$title_xml
         where id={$row['id']}",
        __FUNCTION__);
}
dbClose();
?>
