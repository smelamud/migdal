<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/sql.php');

class UserGroup
        extends DataObject {

    protected $user_id;
    protected $group_id;
    protected $user_name;
    protected $group_name;

    public function __construct(array $row) {
        parent::__construct($row);
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getGroupId() {
        return $this->group_id;
    }

    public function getUserName() {
        return $this->user_name;
    }

    public function getGroupName() {
        return $this->group_name;
    }

}

class GroupsIterator
        extends SelectIterator {

    public function __construct() {
        parent::__construct(
            'UserGroup',
            'select user_id,group_id,users.login as user_name,
                    gusers.login as group_name
             from groups
                  left join users
                       on user_id=users.id
                  left join users as gusers
                       on group_id=gusers.id
             order by gusers.login,users.login');
    }

}

function isUserInGroup($user_id, $group_id) {
    if ($user_id == $group_id)
        return true;
    $result = sql("select user_id
                   from groups
                   where user_id=$user_id and group_id=$group_id",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0;
}

function addUserGroup($user_id, $group_id) {
    if (isUserInGroup($user_id, $group_id))
        return;
    sql("insert into groups(user_id,group_id)
         values($user_id,$group_id)",
        __FUNCTION__);
}

function delUserGroup($user_id, $group_id) {
    sql("delete from groups
         where user_id=$user_id and group_id=$group_id",
        __FUNCTION__);
}
?>
