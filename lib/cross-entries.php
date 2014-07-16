<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');

require_once('conf/cross-entries.php');

const LINKT_NONE = 0;

class CrossEntry
        extends DataObject {

    protected $id = 0;
    protected $source_name = null;
    protected $source_id = null;
    protected $link_type = LINKT_NONE;
    protected $peer_name = null;
    protected $peer_id = null;
    protected $peer_path = '';
    protected $peer_subject = '';
    protected $peer_icon = '';

    public function __construct(array $row = array()) {
        parent::__construct($row);
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getSourceName() {
        return $this->source_name;
    }

    public function setSourceName($source_name) {
        $this->source_name = $source_name;
    }

    public function getSourceId() {
        return $this->source_id;
    }

    public function setSourceId($source_id) {
        $this->source_id = $source_id;
    }

    public function getLinkType() {
        return $this->link_type;
    }

    public function setLinkType($link_type) {
        $this->link_type = $link_type;
    }

    public function getPeerName() {
        return $this->peer_name;
    }

    public function setPeerName($peer_name) {
        $this->peer_name = $peer_name;
    }

    public function getPeerId() {
        return $this->peer_id;
    }

    public function setPeerId($peer_id) {
        $this->peer_id = $peer_id;
    }

    public function getPeerPath() {
        return $this->peer_path;
    }

    public function setPeerPath($peer_path) {
        $this->peer_path = $peer_path;
    }

    public function getPeerSubject() {
        return $this->peer_subject;
    }

    public function setPeerSubject($peer_subject) {
        $this->peer_subject = $peer_subject;
    }

    public function getPeerIcon() {
        return $this->peer_icon;
    }

    public function setPeerIcon($peer_icon) {
        $this->peer_icon = $peer_icon;
    }

}

class CrossEntryIterator
        extends SelectIterator {

    public function __construct($source_name = '', $source_id = 0,
                                $link_type = LINKT_NONE) {
        $filter = '';
        if ($source_name != '')
            $filter .= " and source_name='$source_name'";
        if ($source_id > 0)
            $filter .= " and source_id=$source_id";
        if ($link_type != LINKT_NONE)
            $filter .= " and link_type=$link_type";
        parent::__construct(
                'CrossEntry',
                "select id,source_name,source_id,link_type,peer_name,peer_id,
                        peer_path,peer_subject,peer_icon
                 from cross_entries
                 where 1 $filter
                 order by peer_icon,peer_subject");
    }

}

function storeCrossEntry(CrossEntry $cross) {
    $vars = array('source_name' => $cross->getSourceName(),
                  'source_id' => $cross->getSourceId(),
                  'link_type' => $cross->getLinkType(),
                  'peer_name' => $cross->getPeerName(),
                  'peer_id' => $cross->getPeerId(),
                  'peer_path' => $cross->getPeerPath(),
                  'peer_subject' => $cross->getPeerSubject(),
                  'peer_icon' => $cross->getPeerIcon());
    if ($cross->getId()) {
        $result = sql(sqlUpdate('cross_entries',
                                $vars,
                                array('id' => $cross->getId())),
                      __FUNCTION__, 'update');
    } else {
        $result = sql(sqlInsert('cross_entries',
                                $vars),
                      __FUNCTION__,'insert');
        $cross->setId(sql_insert_id());
    }
    return $result;
}

function deleteCrossEntry($id) {
    sql("delete
         from cross_entries
         where id=$id",
        __FUNCTION__);
}
?>
