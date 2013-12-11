<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/sql.php');

function tmpTextSave($text) {
    $textS = addslashes($text);
    sql("insert into tmp_texts(value)
         values('$textS')", __FUNCTION__);
    return sql_insert_id();
}

function tmpTextRestore($id) {
    $result = sql("select value
                   from tmp_texts
                   where id=$id", __FUNCTION__, 'restore');
    sql("update tmp_texts
         set last_access=null
         where id=$id", __FUNCTION__, 'timestamp');
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : '';
}

function deleteObsoleteTmpTexts() {
    global $tmpTextTimeout;
    
    $now = sqlNow();
    sql("delete from tmp_texts
         where last_access+interval $tmpTextTimeout hour<'$now'", __FUNCTION__);
    sql('optimize table tmp_texts', __FUNCTION__, 'optimize');
}
?>

