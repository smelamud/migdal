<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/dataobject.php');
require_once('lib/sql.php');
require_once('lib/time.php');
require_once('lib/debug-log.php');

class HTMLCacheRecord
        extends DataObject {

    protected $ident = '';
    protected $content = null;
    protected $deadline = null;
    protected $postings_version = null;
    protected $forums_version = null;
    protected $topics_version = null;

    protected $condition = true;

    public function __construct(array $row = array()) {
        parent::__construct($row);
    }

    public function getIdent() {
        return $this->ident;
    }

    public function getContent() {
        return $this->content;
    }

    public function isEmpty() {
        return empty($this->content);
    }

    public function getDeadline() {
        return $this->deadline != null ? strtotime($this->deadline) : null;
    }

    public function getPostingsVersion() {
        return $this->postings_version;
    }

    public function getForumsVersion() {
        return $this->forums_version;
    }

    public function getTopicsVersion() {
        return $this->topics_version;
    }

    public function isCondition() {
        return $this->condition;
    }

}

$htmlCacheStack = array();

function pushHTMLCacheStack($record) {
    global $htmlCacheStack;

    array_push($htmlCacheStack, $record);
}

function popHTMLCacheStack() {
    global $htmlCacheStack;

    if (count($htmlCacheStack) == 0)
        bug('&lt;/html_cache&gt; without corresponding &lt;html_cache&gt;.');
    return array_pop($htmlCacheStack);
}

function getHTMLCacheRecord($ident, $period = null, $depends = array(),
                            $condition = true) {
    global $htmlCache, $contentVersions;

    debugLog(LL_FUNCTIONS,
             'getHTMLCacheRecord(ident=%,...)',
             array($ident));

    if ($htmlCache && $condition) {
        $now = sqlNow();
        $filter = '';
        if ($period != null)
            $filter .= " and deadline>='$now'";
        foreach($depends as $dep) {
            $name = "${dep}_version";
            if (isset($contentVersions[$name]))
                $filter .= " and $name>=".$contentVersions[$name];
        }
        $result = sql("select ident,content,deadline
                       from html_cache
                       where ident='$ident' $filter",
                      __FUNCTION__);
        if (mysql_num_rows($result) > 0)
            return new HTMLCacheRecord(mysql_fetch_assoc($result));
    }

    $vars = array('ident' => $ident,
                  'deadline' => $period != null ? sqlDate(ourtime() + $period)
                                                : null,
                  'condition' => $htmlCache && $condition);
    foreach($depends as $dep) {
        $name = "${dep}_version";
        if (isset($contentVersions[$name]))
            $vars[$name] = $contentVersions[$name];
    }
    return new HTMLCacheRecord($vars);
}

function storeHTMLCacheRecord(HTMLCacheRecord $record, $content) {
    debugLog(LL_FUNCTIONS,
             'storeHTMLCacheRecord(record.ident=%,...)',
             array($record->getIdent()));

    if (!$record->isCondition())
        return;
    $vars = array('ident' => $record->getIdent(),
                  'content' => $content,
                  'deadline' => $record->getDeadline(),
                  'postings_version' => $record->getPostingsVersion(),
                  'forums_version' => $record->getForumsVersion(),
                  'topics_version' => $record->getTopicsVersion());
    sql(sqlReplace('html_cache', $vars),
        __FUNCTION__);
}

function contentVersions() {
    global $contentVersions;

    $result = sql('select postings_version,forums_version,topics_version
                   from content_versions',
                  __FUNCTION__);
    if (mysql_num_rows($result) > 0)
        $contentVersions = mysql_fetch_assoc($result);
    else
        $contentVersions = array();
}

function incContentVersions($depends) {
    global $contentVersions;

    if (!is_array($depends)) {
        incContentVersions(array($depends));
        return;
    }
    $ops = array();
    foreach ($depends as $dep) {
        $name = "${dep}_version";
        if (isset($contentVersions[$name])) {
            $contentVersions[$name]++;
            $ops[] = "$name=$name+1";
        }
    }
    if (count($ops) > 0) {
        sql('update content_versions
             set '.join(',', $ops),
            __FUNCTION__);
    }
}
?>
