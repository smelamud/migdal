<?php
# @(#) $Id$

require_once('lib/sql.php');
require_once('lib/ident.php');

function getOldId($entry_id, $table_name) {
    $result = sql("select old_id
                   from old_ids
                   where table_name='$table_name' and entry_id=$entry_id",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}

function getNewId($table_name, $old_id) {
    $idFilter = isId($old_id) ? "and old_id=$old_id"
                              : "and old_ident='$old_id'";
    $result = sql("select entry_id
                   from old_ids
                   where table_name='$table_name' $idFilter",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}

function putOldId($entry_id, $table_name, $old_id, $old_ident = '') {
    $new_id = getNewId($table_name, $old_id);
    if ($new_id == 0) {
        $oi = $old_ident == '' ? 'NULL' : "'$old_ident'";
        sql("insert into old_ids(table_name,old_id,old_ident,entry_id)
             values('$table_name',$old_id,$oi,$entry_id)",
            __FUNCTION__);
    } else
        echo "Duplicate id for $table_name($old_id): $new_id and $entry_id\n";
}

class OldId
        extends DataObject {

    protected $table_name;
    protected $old_id;
    protected $old_ident;
    protected $entry_id;

    public function __construct(array $row) {
        parent::__construct($row);
    }

    public function getTableName() {
        return $this->table_name;
    }

    public function getOldId() {
        return $this->old_id;
    }

    public function getOldIdent() {
        return $this->old_ident;
    }

    public function getEntryId() {
        return $this->entry_id;
    }

}

class OldIdsIterator
        extends SelectIterator {

    public function __construct() {
        parent::__construct('OldId',
                            'select table_name,old_id,old_ident,entry_id
                             from old_ids');
    }

}
?>
