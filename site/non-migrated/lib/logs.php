<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/ip.php');
require_once('lib/bug.php');
require_once('lib/sql.php');

function logEvent($event, $body) {
    global $disableStatistics;

    if ($disableStatistics)
        return;
    $eventS = addslashes($event);
    $ip = IPToInteger($_SERVER['REMOTE_ADDR']);
    $bodyS = addslashes($body);
    sql("insert into logs(event,ip,body)
         values('1:$eventS',$ip,'$bodyS')",
        __FUNCTION__,'','',false);
}

class LogLine
        extends DataObject {

    private $id;
    private $event;
    private $sent;
    private $ip;
    private $body;

    public function __construct(array $row) {
        parent::__construct($row);
    }

    public function getId() {
        return $this->id;
    }

    public function getEvent() {
        return $this->event;
    }

    public function getSent() {
        return $this->sent;
    }

    public function getIP() {
        return $this->ip;
    }

    public function getBody() {
        return $this->body;
    }

}

class LogIterator
        extends SelectIterator {

    public function __construct($from = 0, $limit = 0) {
        $limiter = $limit <= 0 ? '' : "limit $limit";
        parent::__construct(
            'LogLine',
            "select id,event,unix_timestamp(sent) as sent,ip,body
             from logs
             where unix_timestamp(sent)>$from
             $limiter");
    }

}

function purgeLogs() {
    global $statisticsTimeout;

    $now = sqlNow();
    sql("delete
         from logs
         where sent+interval $statisticsTimeout day<'$now'",
        __FUNCTION__, 'delete');
    sql("optimize table logs",
        __FUNCTION__, 'optimize');
}
?>
