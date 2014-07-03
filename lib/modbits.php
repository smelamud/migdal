<?php
# @(#) $Id$

require_once('lib/bug.php');
require_once('lib/sql.php');
require_once('lib/entries.php');

// For regular postings
const MOD_NONE = 0x0000;
const MOD_MODERATE = 0x0001;
const MOD_HTML = 0x0002; # deprecated
const MOD_EDIT = 0x0004;
const MOD_ATTENTION = 0x0008;
const MOD_MULTIPART = 0x0010;
const MOD_ALL = 0x001f;

const MOD_HIDDEN = -1;
const MOD_DISABLED = -2;
const MOD_DELETE = -3;

$modbitLetters = array('M', 'H' /* deprecated */, 'E', 'S', 'L');
$modbitNames = array('Модерировать',
                     'HTML',  # deprecated
                     'Редактировать',
                     'Особо проверить',
                     'Многостраничное');

// For topics
const MODT_NONE = 0x0000;
const MODT_PREMODERATE = 0x0001;
const MODT_MODERATE = 0x0002;
const MODT_EDIT = 0x0004;
const MODT_ROOT = 0x0008;
const MODT_TRANSPARENT = 0x0010;
const MODT_ALL = 0x001f;

$modbitTNames = array('Премодерировать',
                      'Модерировать',
                      'Редактировать',
                      'Корневая',
                      'Прозрачная');

class Modbit {

    private $bit;
    private $letter;
    private $name;

    public function __construct($bit, $letter, $name) {
        $this->bit = $bit;
        $this->letter = $letter;
        $this->name = $name;
    }

    public function getBit() {
        return $this->bit;
    }

    public function getLetter() {
        return $this->letter;
    }

    public function getName() {
        return $this->name;
    }

}

class ModbitIterator
        extends MIterator {

    private $bit, $lbbit;
    private $max;
    private $letters,$names;

    public function __construct($max, $letters, $names) {
        parent::__construct();
        $this->max = $max;
        $this->letters = $letters;
        $this->names = $names;
    }

    public function current() {
        return new Modbit(
                $this->bit,
                $this->letters != null ? $this->letters[$this->lbbit] : '',
                $this->names[$this->lbbit]);
    }

    public function next() {
        parent::next();
        $this->bit *= 2;
        $this->lbbit++;
    }

    public function rewind() {
        parent::rewind();
        $this->bit = 1;
        $this->lbbit = 0;
    }

    public function valid() {
        return $this->bit <= $this->max;
    }

}

class PostingModbitIterator
        extends ModbitIterator {

    public function __construct() {
        global $modbitLetters, $modbitNames;

        parent::__construct(MOD_ALL, $modbitLetters, $modbitNames);
    }

}

class TopicModbitIterator
        extends ModbitIterator {

    public function __construct() {
        global $modbitTNames;

        parent::__construct(MODT_ALL, null, $modbitTNames);
    }

}

function getModbitsByEntryId($id)
{
$result=sql("select modbits
	     from entries
	     where id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function setModbitsByEntryId($id,$bits)
{
sql("update entries
     set modbits=modbits | $bits
     where id=$id",
    __FUNCTION__);
incContentVersionsByEntryId($id);
}

function resetModbitsByEntryId($id,$bits)
{
sql("update entries
     set modbits=modbits & ~$bits
     where id=$id",
    __FUNCTION__);
incContentVersionsByEntryId($id);
}

function assignModbitsByEntryId($id,$bits)
{
sql("update entries
     set modbits=$bits
     where id=$id",
    __FUNCTION__);
incContentVersionsByEntryId($id);
}
?>
