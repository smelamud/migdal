<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/bug.php');
require_once('lib/track.php');
require_once('lib/uri.php');
require_once('lib/utils.php');
require_once('lib/sql.php');
require_once('lib/post.php');

class Redir
        extends DataObject {

    protected $id;
    protected $up;
    protected $track;
    protected $uri;
    protected $last_access;

    public function getId() {
        return $this->id;
    }

    public function getUp() {
        return $this->up;
    }

    public function getTrack() {
        return $this->track;
    }

    public function getURI() {
        return $this->uri;
    }

}

function updateRedirectTimestamps($track) {
    foreach (explode(' ', $track) as $level) {
        $id = (int)$level;
        sql("update redirs
             set last_access=null
             where id=$id",
            __FUNCTION__);
    }
}

function redirect() {
    global $LocationInfo, $redirid, $globalid, $Args;

    httpRequestInteger('globalid');
    unset($Args['globalid']);

    if ($globalid == 0) {
        $requestURIS = addslashes($_SERVER['REQUEST_URI']);
        sql(sqlInsert('redirs',
                      array('up'    => $redirid,
                            'track' => null,
                            'uri'   => $_SERVER['REQUEST_URI'])),
            __FUNCTION__);
        $id = sql_insert_id();
        $track = track($id, trackById('redirs', $redirid));
        updateTrackById('redirs', $id, $track);
        $redir = new Redir(array('id'    => $id,
                                 'up'    => $redirid,
                                 'track' => $track,
                                 'uri'   => $_SERVER['REQUEST_URI']));
    } else {
        $redir = getRedirById($globalid);
        if ($redir->getId() == 0) {
            httpValue('globalid', 0);
            redirect();
            return;
        }
    }
    updateRedirectTimestamps($redir->getTrack());
    $LocationInfo->setRedir($redir);
}

function getRedirById($id) {
    $result = sql("select id,up,track,uri
                   from redirs
                   where id=$id",
                  __FUNCTION__);
    return new Redir(mysql_num_rows($result) > 0 ? mysql_fetch_assoc($result)
                                                 : array());
}

function redirExists($id) {
    $result = sql("select id
                   from redirs
                   where id=$id",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0;
}

function deleteObsoleteRedirs() {
    global $redirTimeout;

    $now = sqlNow();
    sql("delete from redirs
         where last_access+interval $redirTimeout hour<'$now'",
        __FUNCTION__);
    sql('optimize table redirs',
        __FUNCTION__, 'optimize');
}
?>