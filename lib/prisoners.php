<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/dataobject.php');
require_once('lib/limitselect.php');
require_once('lib/utils.php');
require_once('lib/bug.php');
require_once('lib/alphabet.php');
require_once('lib/sort.php');
require_once('lib/sql.php');

class Prisoner
        extends DataObject {

    protected $id = 0;
    protected $name = '';
    protected $name_russian = '';
    protected $location = '';
    protected $ghetto_name = '';
    protected $sender_name = '';
    protected $sum = 0;
    protected $search_data = '';

    public function __construct(array $row) {
        parent::__construct($row);
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getNameRussian() {
        return $this->name_russian;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getGhettoName() {
        return $this->ghetto_name;
    }

    public function getSenderName() {
        return $this->sender_name;
    }

    public function getSum() {
        return $this->sum;
    }

    public function getSearchData() {
        return $this->search_data;
    }

}

class PrisonerListIterator
        extends SelectIterator {

    public function __construct($prefix, $sort = SORT_NAME) {
        $sortFields = array(SORT_NAME         => 'name',
                            SORT_NAME_RUSSIAN => 'name_russian',
                            SORT_LOCATION     => 'location',
                            SORT_GHETTO_NAME  => 'ghetto_name',
                            SORT_SENDER_NAME  => 'sender_name');
        if ($prefix != '') {
            $prefixS = addslashes($prefix);
            $sortField = @$sortFields[$sort] != '' ? $sortFields[$sort]
                                                   : 'name';
            $fieldFilter = "$sortField like '$prefixS%'";
        } else
            $fieldFilter = '';
        $order = getOrderBy($sort, $sortFields);
        parent::__construct(
            'Prisoner',
            "select id,name,name_russian,location,ghetto_name,sender_name,sum,
                    search_data
             from prisoners
             where $fieldFilter
             $order");
    }

}

class PrisonerAlphabetIterator
        extends AlphabetIterator {

    public function __construct($limit = 0, $sort = SORT_NAME) {
        $fields = array(SORT_NAME         => 'name',
                        SORT_NAME_RUSSIAN => 'name_russian',
                        SORT_LOCATION     => 'location',
                        SORT_GHETTO_NAME  => 'ghetto_name',
                        SORT_SENDER_NAME  => 'sender_name');
        $field = @$fields[$sort] != '' ? $fields[$sort] : 'name';
        $order = getOrderBy($sort, $fields);
        parent::__construct("select left($field,@len@) as letter,1 as count
                             from prisoners
                             where $field<>'' and $field like '@prefix@%'
                             $order", $limit);
    }

}

function getPrisonersSummary() {
    $result = sql("select count(*)
                   from prisoners",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}
?>
