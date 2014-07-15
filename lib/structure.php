<?php
# @(#) $Id$

require_once('lib/iterator.php');
require_once('lib/redirs.php');
require_once('lib/utils.php');
require_once('lib/uri.php');
require_once('lib/ident.php');
require_once('lib/entries.php');
require_once('lib/cross-entries.php');
require_once('lib/text.php');
require_once('lib/cache.php');

require_once('conf/titles.php');
require_once('conf/scripts.php');
require_once('conf/traps.php');
require_once('conf/structure.php');

function idOrCatalog($id, $catalog) {
    $id = normalizePath($id, true, SLASH_NO, SLASH_NO);
    $pos = strrpos($id, '/');
    if ($pos !== false)
        $id = substr($id, $pos + 1);
    if (isId($id))
        return (int)$id;
    $catalog = strtr($catalog, '.', '/');
    $catalog = normalizePath($catalog, true, SLASH_NO, SLASH_NO);
    $catalog = strtr($catalog, '/', '.');
    return idByIdent($catalog);
}

function isEntryInGrp($id, $grp) {
    $grp = grpArray($grp);
    $egrp = getGrpByEntryId($id);
    if ($egrp <= 0 || !in_array($egrp, $grp)) {
        $egrps = getGrpsByEntryId($id);
        foreach ($egrps as $egrp)
            if (in_array($egrp, $grp))
                return true;
        return false;
    } else
        return true;
}

function getParentLocationInfo($path, $redirid) {
    if ($redirid != 0) {
        $redir = getRedirById($redirid);
        $parts = parse_url($redir->getURI());
        $info = getLocationInfo($parts['path'], $redir->getUp());
        $info->setRedir($redir);
        return $info;
    } else
        return getLocationInfo($path, 0);
}

class LocationInfo {

    private $path;
    private $script;
    private $args = array();
    private $ids = array();
    private $redir = null;
    private $title = 'Untitled';
    private $titleRelative = 'Untitled';
    private $titleFull = 'Untitled';
    private $parent = null;
    private $child = null;
    private $orig = null;
    private $linkName = '';
    private $linkId = 0;
    private $linkTitle = '';
    private $linkIcon = '';

    public function __construct() {
    }

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getScript() {
        return $this->script;
    }

    public function setScript($script) {
        $this->script = $script;
    }

    public function getArgs() {
        return $this->args;
    }

    public function setArgs($args) {
        $this->args = $args;
    }

    public function getArg($name) {
        return isset($this->args[$name]) ? $this->args[$name] : '';
    }

    public function getIds() {
        return $this->ids;
    }

    public function setIds($ids) {
        $this->ids = $ids;
    }

    public function getId($name) {
        return isset($this->ids[$name]) ? $this->ids[$name] : '';
    }

    public function origHasIds($ids) {
        $orig = $this->getOrig();
        foreach ($ids as $id)
            if (!isset($orig->ids[$id]))
                return false;
        return true;
    }

    public function getRedir() {
        return $this->redir;
    }

    public function setRedir($redir) {
        $this->redir = $redir;
    }

    public function getURI() {
        if ($this->redir != null)
            return $this->redir->getURI();
        else
            if ($this->child == null)
                return $_SERVER['REQUEST_URI'];
            else
                return $this->path;
    }

    public function getRedirId() {
        if ($this->redir != null)
            return $this->redir->getId();
        else
            return 0;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getTitleRelative() {
        return $this->titleRelative;
    }

    public function setTitleRelative($title) {
        $this->titleRelative = $title;
    }

    public function getTitleFull() {
        return $this->titleFull;
    }

    public function setTitleFull($title) {
        $this->titleFull = $title;
    }

    public function getParent() {
        return $this->parent;
    }

    public function setParent(LocationInfo $parent) {
        $this->parent = $parent;
        if ($parent != null)
            $this->parent->child = $this;
    }

    public function getParentURI() {
        if ($this->parent != null)
            return $this->parent->getURI();
        else
            return '';
    }

    public function getChild() {
        return $this->child;
    }

    public function getOrig() {
        return $this->orig;
    }

    public function setOrig(LocationInfo $orig) {
        $this->orig = $orig;
    }

    public function getLinkName() {
        return $this->linkName;
    }

    public function setLinkName($linkName) {
        $this->linkName = $linkName;
    }

    public function getLinkId() {
        return $this->linkId;
    }

    public function setLinkId($linkId) {
        $this->linkId = $linkId;
    }

    public function getLinkTitle() {
        return $this->linkTitle;
    }

    public function setLinkTitle($linkTitle) {
        $this->linkTitle = $linkTitle;
    }

    public function getLinkIcon() {
        return $this->linkIcon;
    }

    public function setLinkIcon($linkIcon) {
        $this->linkIcon = $linkIcon;
    }

    public function getRoot() {
        if ($this->parent == null)
            return $this;
        else
            return $this->parent->getRoot();
    }

}

class LocationIterator
        extends MIterator
        implements Countable {

    use CountableIterator;

    private $offset;
    private $cursor;
    private $itemCount;

    public function __construct($offset = 0) {
        parent::__construct();
        $this->offset = $offset;
        $this->cursor = null;
        $this->itemCount = -1;
    }

    private function getBeginning() {
        global $LocationInfo;

        $beginning = $LocationInfo->getRoot();
        $this->itemCount = 0;
        for ($i = 0; $i < $this->offset && $beginning != null; $i++)
            $beginning = $beginning->getChild();
        for ($p = $beginning; $p != null; $p = $p->getChild())
            $this->itemCount++;
        return $beginning;
    }

    public function current() {
        return $this->cursor;
    }

    public function next() {
        parent::next();
        if ($this->cursor != null)
            $this->cursor = $this->cursor->getChild();
    }

    public function rewind() {
        parent::rewind();
        $this->cursor = $this->getBeginning();
    }

    public function valid() {
        return $this->cursor != null;
    }

    public function count() {
        if ($this->itemCount < 0)
            $this->getBeginning();
        return $this->itemCount;
    }

}
?>
