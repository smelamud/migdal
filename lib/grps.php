<?php
# @(#) $Id$

require_once('lib/utils.php');
require_once('lib/array.php');
require_once('conf/grps.php');

function grpArray($grp) {
    global $grpGroups;

    if (is_array($grp))
        return $grp;
    if ($grp === GRP_NONE)
        return array();
    if (is_numeric("0$grp")) // �������� ����...
        return array($grp);
    return isset($grpGroups[$grp]) ? $grpGroups[$grp] : array();
}

function grpJoin($grp1, $grp2) {
    $grp = grpArray($grp1);
    $grp2 = grpArray($grp2);
    foreach ($grp2 as $g)
        if (!in_array($g, $grp))
            $grp[] = $g;
    return $grp;
}

function grpDiff($grp1, $grp2) {
    return array_diff(grpArray($grp1), grpArray($grp2));
}

function isGrpValid($grp) {
    return !is_array($grp) && in_array($grp, grpArray(GRP_ALL));
}

function grpFilter($grp, $field = 'grp', $prefix = '') {
    if ($grp === GRP_NONE)
        return 0;
    if ($grp === GRP_ALL)
        return 1;
    $grp = grpArray($grp);
    if ($prefix != '' && substr($prefix, -1) != '.')
        $prefix .= '.';
    $conds = array();
    foreach($grp as $i)
        $conds[] = "${prefix}$field=$i";
    if (count($conds) == 0)
        return '1';
    else
        return '('.join(' or ', $conds).')';
}

class GrpEditor {

    private $ident;
    private $title;
    private $comment;
    private $mandatory;
    private $style;
    private $base;

    private $imageExactX = 0;
    private $imageExactY = 0;
    private $imageMaxX = 0;
    private $imageMaxY = 0;
    private $thumbExactX = 0;
    private $thumbExactY = 0;
    private $thumbMaxX = 0;
    private $thumbMaxY = 0;

    public function __construct(array $props) {
        foreach($props as $key => $value)
            $this->$key = $value;
    }

    public function getIdent() {
        return $this->ident;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getComment() {
        return $this->comment;
    }

    public function isMandatory() {
        return $this->mandatory;
    }

    public function getStyle() {
        return $this->style;
    }

    public function getImageStyle() {
        @list($thumbFlag, $imageFlag) = explode('-', $this->getStyle());
        return isset($imageFlag) && $imageFlag != '' ? $imageFlag : 'manual';
    }

    public function getThumbnailStyle() {
        @list($thumbFlag, $imageFlag) = explode('-', $this->getStyle());
        return isset($thumbFlag) && $thumbFlag != '' ? $thumbFlag : 'auto';
    }

    public function getBase() {
        return $this->base;
    }

    public function getImageExactX() {
        return $this->imageExactX;
    }

    public function getImageExactY() {
        return $this->imageExactY;
    }

    public function getImageMaxX() {
        return $this->imageMaxX;
    }

    public function getImageMaxY() {
        return $this->imageMaxY;
    }

    public function getThumbExactX() {
        return $this->thumbExactX;
    }

    public function getThumbExactY() {
        return $this->thumbExactY;
    }

    public function getThumbMaxX() {
        return $this->thumbMaxX;
    }

    public function getThumbMaxY() {
        return $this->thumbMaxY;
    }

}

function grpEditor($grp = GRP_NONE, $inverse = false) {
    global $grpGetGrpEditor;

    if (!$inverse) {
        return isset($grpGetGrpEditor[$grp])
               ? $grpGetGrpEditor[$grp] : $grpGetGrpEditor[GRP_NONE];
    } else {
        if ($grp == GRP_NONE || !isset($grpGetGrpEditor[$grp]))
            return array();
        $remove = array();
        foreach ($grpGetGrpEditor[$grp] as $item)
            if ($item['ident'] != '')
                $remove[$item['ident']] = true;
        $editor = array();
        foreach ($grpGetGrpEditor[GRP_NONE] as $item)
            if ($item['ident'] != '' && !isset($remove[$item['ident']]))
                $editor[] = $item;
        return $editor;
    }
}

class GrpEditorIterator
        extends MArrayIterator {

    public function __construct($grp = GRP_NONE, $inverse = false) {
        parent::__construct(grpEditor($grp, $inverse));
    }

    protected function create($key, $value) {
        return new GrpEditor($value);
    }

}
?>
