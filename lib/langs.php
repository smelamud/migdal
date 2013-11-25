<?php
# @(#) $Id$

require_once('lib/iterator.php');

$langCodes = array('ru' => '�������',
                   'en' => '����������',
                   'he' => '�����',
                   'uk' => '����������',
                   'be' => '�����������',
                   'yi' => '����',
                   'de' => '��������');

class LangInfo {

    private $code;
    private $name;

    public function __construct($code, $name) {
        $this->code = $code;
        $this->name = $name;
    }

    public function getCode() {
        return $this->code;
    }

    public function getName() {
        return $this->name;
    }

}

class LangIterator
        extends AssocArrayIterator {

    public function __construct() {
        global $langCodes;

        parent::__construct($langCodes, 'LangInfo');
    }

}
?>
