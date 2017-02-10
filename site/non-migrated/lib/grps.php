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
    if (is_numeric("0$grp")) // Странный глюк...
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

    private $properties = array();

    public function __construct(array $props) {
        $this->properties = /*clone*/ $props;
    }

    public function __call($name, $args) {
        if (substr($name, 0, 3) == 'get') {
            $pos = 3;
        } elseif (substr($name, 0, 2) == 'is') {
            $pos = 2;
        } else {
            trigger_error("Unknown method GrpEditor::$name()", E_USER_ERROR);
        }
        return $this->properties[lcfirst(substr($name, $pos))];
    }

    public function getImageStyle() {
        @list($thumbFlag, $imageFlag) = explode('-', $this->getStyle());
        return isset($imageFlag) && $imageFlag != '' ? $imageFlag : 'manual';
    }

    public function getThumbnailStyle() {
        @list($thumbFlag, $imageFlag) = explode('-', $this->getStyle());
        return isset($thumbFlag) && $thumbFlag != '' ? $thumbFlag : 'auto';
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
