<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/entry-types.php');
require_once('lib/usertag.php');
require_once('lib/mtext-shorten.php');
require_once('lib/image-types.php');
require_once('lib/track.php');
require_once('lib/answers.php');
require_once('lib/langs.php');
require_once('lib/text-large.php');
require_once('lib/permission-schemes.php');
require_once('lib/time.php');

$entryClassNames=array(ENT_NULL     => 'Entry',
                       ENT_POSTING  => 'Posting',
                       ENT_FORUM    => 'Forum',
                       ENT_TOPIC    => 'Topic',
                       ENT_IMAGE    => 'Image',
                       ENT_VERSION  => 'Entry');

class Entry
        extends UserTag {

    protected $id = 0;
    protected $ident = null;
    protected $entry = ENT_NULL;
    protected $up = 0;
    protected $track = '';
    protected $catalog = '';
    protected $parent_id = 0;
    protected $orig_id = 0;
    protected $grp = 0;
    protected $grps = array();
    protected $person_id = 0;
    protected $user_id = 0;
    protected $group_id = 0;
    protected $group_login = '';
    protected $perms = PERM_NONE;
    protected $perm_string = '';
    protected $disabled = 0;
    protected $subject = '';
    protected $lang = '';
    protected $author = '';
    protected $author_xml = '';
    protected $source = '';
    protected $source_xml = '';
    protected $title = '';
    protected $title_xml = '';
    protected $comment0 = '';
    protected $comment0_xml = '';
    protected $comment1 = '';
    protected $comment1_xml = '';
    protected $url = '';
    protected $url_domain = '';
    protected $url_check = 0;
    protected $url_check_success = 0;
    protected $body = '';
    protected $body_xml = '';
    protected $body_format = TF_MAIL;
    protected $has_large_body = 0;
    protected $large_body = '';
    protected $large_body_xml = '';
    protected $large_body_format = TF_PLAIN;
    protected $large_body_filename = '';
    protected $priority = 0;
    protected $index0 = 0;
    protected $index1 = 0;
    protected $index2 = 0;
    protected $vote = 0;
    protected $vote_count = 0;
    protected $rating = 0;
    protected $sent = 0;
    protected $created = 0;
    protected $modified = 0;
    protected $accessed = 0;
    protected $creator_id = 0;
    protected $modifier_id = 0;
    protected $modbits = MOD_NONE;
    protected $answers = 0;
    protected $last_answer = 0;
    protected $last_answer_id = 0;
    protected $last_answer_user_id = 0;
    protected $last_answer_guest_login = 0;
    protected $small_image = 0;
    protected $small_image_x = 0;
    protected $small_image_y = 0;
    protected $small_image_format = '';
    protected $large_image = 0;
    protected $large_image_x = 0;
    protected $large_image_y = 0;
    protected $large_image_size = 0;
    protected $large_image_format = '';
    protected $large_image_filename = '';
    protected $inserted = false;

    public function __construct(array $row = array()) {
        parent::__construct($row);
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getIdent() {
        return $this->ident;
    }

    public function getEntry() {
        return $this->entry;
    }

    public function getUpValue() {
        return $this->up;
    }

    public function setUpValue($up) {
        $this->up = $up;
    }

    public function getTrack() {
        return $this->track;
    }

    public function getTrackPath() {
        $s = '';
        foreach (explode(' ', $this->getTrack()) as $item)
            $s .= (int)$item.'/';
        return $s;
    }

    public function getParentTrackPath() {
        $s = '';
        $c = '';
        foreach(explode(' ', $this->getTrack()) as $item) {
            $s = $c;
            $c .= (int)$item.'/';
        }
        return $s;
    }

    public function setTrack($track) {
        $this->track = $track;
    }

    public function getCatalog($start = 0, $length = 0) {
        if ($start == 0 && $length == 0 || $this->catalog == '')
            return $this->catalog;
        $elements = explode('/', substr($this->catalog, 0, -1));
        $catalog = '';
        $begin = $start >= 0 ? $start : count($elements) + $start;
        $end = $length > 0 ? $begin + $length : count($elements) + $length;
        for ($i = $begin; $i < $end; $i++)
            $catalog .= $elements[$i].'/';
        return $catalog;
    }

    public function setCatalog($catalog) {
        $this->catalog = $catalog;
    }

    public function getParentId() {
        return $this->parent_id;
    }

    public function getOrigId() {
        return $this->orig_id;
    }

    public function setOrigId($orig_id) {
        $this->orig_id = $orig_id;
    }

    public function getGrp() {
        return $this->grp;
    }

    public function setGrp($grp) {
        $this->grp = $grp;
    }

    public function getGrps() {
        return $this->grps;
    }

    public function setGrps($grps) {
        $this->grps=$grps;
    }

    public function getPersonId() {
        return $this->person_id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function setUserId($id) {
        $this->user_id = $id;
    }

    public function getGroupId() {
        return $this->group_id;
    }

    public function setGroupId($id) {
        $this->group_id = $id;
    }

    public function getGroupLogin() {
        return $this->group_login;
    }

    public function setGroupLogin($group_login) {
        $this->group_login = $group_login;
    }

    public function getGroupName() {
        return $this->getGroupLogin();
    }

    public function getPerms() {
        return $this->perms;
    }

    public function getPermStringRaw() {
        return $this->perm_string;
    }

    public function getPermString() {
        return $this->perm_string != '' ? $this->perm_string
                                        : strPerms($this->getPerms());
    }

    public function isPermitted($right) {
        return isPermittedEntry($this, $right);
    }

    public function isReadable() {
        return $this->isPermitted(PERM_READ);
    }

    public function isWritable() {
        return $this->isPermitted(PERM_WRITE);
    }

    public function isAppendable() {
        return $this->isPermitted(PERM_APPEND);
    }

    public function isPostable() {
        return $this->isPermitted(PERM_POST);
    }

    public function isGuestPostable() {
        return ($this->perms & PERM_EP) != 0;
    }

    public function isHidden() {
        return ($this->perms & 0x1100) == 0;
    }

    public function isDisabled() {
        return $this->disabled;
    }

    public function setDisabled($disabled) {
        $this->disabled = $disabled;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function getLang() {
        return $this->lang;
    }

    public function getLangName() {
        global $langCodes;

        return $langCodes[$this->lang];
    }

    public function getAuthor() {
        return $this->author;
    }

    public function getAuthorXML() {
        return $this->author_xml;
    }

    public function getAuthorHTML() {
        return mtextToHTML($this->getAuthorXML(), MTEXT_LINE, $this->getId());
    }

    public function getSource() {
        return $this->source;
    }

    public function getSourceXML() {
        return $this->source_xml;
    }

    public function getSourceHTML() {
        return mtextToHTML($this->getSourceXML(), MTEXT_LINE, $this->getId());
    }

    public function getTitle() {
        return $this->title;
    }

    public function getTitleXML() {
        return $this->title_xml;
    }

    public function getTitleHTML() {
        return mtextToHTML($this->getTitleXML(), MTEXT_SHORT, $this->getId());
    }

    public function getTitleLineHTML() {
        return mtextToHTML($this->getTitleXML(), MTEXT_CONVERT | MTEXT_LINE,
                           $this->getId());
    }

    public function isTitleTiny() {
        global $tinySize, $tinySizeMinus, $tinySizePlus;

        return cleanLength($this->getTitleXML()) <= $tinySize + $tinySizePlus;
    }

    public function getTitleTiny() {
        global $tinySize, $tinySizeMinus, $tinySizePlus;

        return shortenNote($this->getTitleXML(), $tinySize, $tinySizeMinus,
                           $tinySizePlus);
    }

    public function getTitleTinyXML() {
        global $tinySize, $tinySizeMinus, $tinySizePlus;

        return shorten($this->getTitleXML(), $tinySize, $tinySizeMinus,
                       $tinySizePlus);
    }

    public function getTitleTinyHTML() {
        return mtextToHTML($this->getTitleTinyXML(), MTEXT_SHORT,
                           $this->getId());
    }

    public function isTitlePea() {
        global $peaSize, $peaSizeMinus, $peaSizePlus;

        return cleanLength($this->getTitleXML()) <= $peaSize + $peaSizePlus;
    }

    public function getTitlePea() {
        global $peaSize, $peaSizeMinus, $peaSizePlus;

        return shortenNote($this->getTitleXML(), $peaSize, $peaSizeMinus,
                           $peaSizePlus);
    }

    public function getTitlePeaXML() {
        global $peaSize, $peaSizeMinus, $peaSizePlus;

        return shorten($this->getTitleXML(), $peaSize, $peaSizeMinus,
                       $peaSizePlus);
    }

    public function getTitlePeaHTML() {
        return mtextToHTML($this->getTitlePeaXML(), MTEXT_SHORT,
                           $this->getId());
    }

    public function getComment0() {
        return $this->comment0;
    }

    public function getComment0XML() {
        return $this->comment0_xml;
    }

    public function getComment0HTML() {
        return mtextToHTML($this->getComment0XML(), MTEXT_LINE, $this->getId());
    }

    public function getComment1() {
        return $this->comment1;
    }

    public function getComment1XML() {
        return $this->comment1_xml;
    }

    public function getComment1HTML() {
        return mtextToHTML($this->getComment1XML(), MTEXT_LINE, $this->getId());
    }

    public function getURL() {
        return $this->url;
    }

    public function getURLEllip() {
        global $urlEllipSize;

        return ellipsize($this->url, $urlEllipSize);
    }

    public function setURL($url) {
        $this->url = $url;
    }

    public function getURLDomain() {
        return $this->url_domain;
    }

    public function getURLCheck() {
        return $this->url_check;
    }

    public function getURLCheckSuccess() {
        return $this->url_check_success;
    }

    public function getURLCheckFail() {
        return $this->url_check_success == 0
               ? 0 : ourtime() - $this->url_check_success;
    }

    public function getURLCheckFailDays() {
        return floor($this->getURLCheckFail() / (24 * 60 * 60));
    }

    public function getBody() {
        return $this->body;
    }

    public function getBodyXML() {
        return $this->body_xml;
    }

    public function getBodyHTML() {
        return mtextToHTML($this->getBodyXML(), MTEXT_SHORT, $this->getId());
    }

    public function getBodyNormal() {
        return shortenNote($this->getBodyXML(), 65535, 0, 0);
    }

    public function isBodyTiny() {
        global $tinySize, $tinySizeMinus, $tinySizePlus;

        return cleanLength($this->getBodyXML()) <= $tinySize + $tinySizePlus;
    }

    public function getBodyTiny() {
        global $tinySize, $tinySizeMinus, $tinySizePlus;

        return shortenNote($this->getBodyXML(), $tinySize, $tinySizeMinus,
                           $tinySizePlus);
    }

    public function getBodyTinyXML() {
        global $tinySize, $tinySizeMinus, $tinySizePlus;

        return shorten($this->getBodyXML(), $tinySize, $tinySizeMinus,
                       $tinySizePlus);
    }

    public function getBodyTinyHTML() {
        return mtextToHTML($this->getBodyTinyXML(), MTEXT_SHORT,
                           $this->getId());
    }

    public function isBodySmall() {
        global $smallSize, $smallSizeMinus, $smallSizePlus;

        return cleanLength($this->getBodyXML()) <= $smallSize + $smallSizePlus;
    }

    public function getBodySmallXML() {
        global $smallSize, $smallSizeMinus, $smallSizePlus;

        return shorten($this->getBodyXML(), $smallSize, $smallSizeMinus,
                       $smallSizePlus);
    }

    public function getBodySmallHTML() {
        return mtextToHTML($this->getBodySmallXML(), MTEXT_SHORT,
                           $this->getId());
    }

    public function isBodyMedium() {
        global $mediumSize, $mediumSizeMinus, $mediumSizePlus;

        return cleanLength($this->getBodyXML()) <= $mediumSize + $mediumSizePlus;
    }

    public function getBodyMediumXML() {
        global $mediumSize, $mediumSizeMinus, $mediumSizePlus;

        return shorten($this->getBodyXML(), $mediumSize, $mediumSizeMinus,
                       $mediumSizePlus);
    }

    public function getBodyMediumHTML() {
        return mtextToHTML($this->getBodyMediumXML(), MTEXT_SHORT,
                           $this->getId());
    }

    public function getBodyMediumNormal() {
        global $mediumSize, $mediumSizeMinus, $mediumSizePlus;

        return shortenNote($this->getBodyXML(), $mediumSize, $mediumSizeMinus,
                           $mediumSizePlus);
    }

    public function getBodyFormat() {
        return $this->body_format;
    }

    public function hasLargeBody() {
        return $this->has_large_body;
    }

    public function setHasLargeBody($has_large_body) {
        $this->has_large_body = $has_large_body;
    }

    public function getLargeBody() {
        return $this->large_body;
    }

    public function setLargeBody($large_body) {
        $this->large_body = $large_body;
    }

    public function getLargeBodyXML() {
        return $this->large_body_xml;
    }

    public function setLargeBodyXML($large_body_xml) {
        $this->large_body_xml = $large_body_xml;
    }

    public function getLargeBodyHTML() {
        return new LargeText($this->getLargeBodyXML(), $this->getId());
    }

    public function getLargeBodyFormat() {
        return $this->large_body_format;
    }

    public function getLargeBodyFilename() {
        return $this->large_body_filename;
    }

    public function setLargeBodyFilename($large_body_filename) {
        $this->large_body_filename = $large_body_filename;
    }

    public function getLargeBodySize() {
        return strlen($this->large_body);
    }

    public function getLargeBodySizeKB() {
        return (int)($this->getLargeBodySize() / 1024);
    }

    public function getPriority() {
        return $this->priority;
    }

    public function getIndex0() {
        return $this->index0;
    }

    public function setIndex0($index0) {
        $this->index0 = $index0;
    }

    public function getIndex1() {
        return $this->index1;
    }

    public function setIndex1($index1) {
        $this->index1 = $index1;
    }

    public function getIndex2() {
        return $this->index2;
    }

    public function setIndex2($index2) {
        $this->index2 = $index2;
    }

    public function getVote() {
        return $this->vote;
    }

    public function getVoteCount() {
        return $this->vote_count;
    }

    public function getRating() {
        return $this->rating;
    }

    public function getRatingString() {
        return sprintf("%1.2f", $this->getRating());
    }

    public function getRating20() {
        return (int)round($this->getRating() * 4);
    }

    public function getSent() {
        return strtotime($this->sent);
    }

    public function getCreated() {
        return strtotime($this->created);
    }

    public function getModified() {
        return strtotime($this->modified);
    }

    public function getAccessed() {
        return strtotime($this->accessed);
    }

    public function getCreatorId() {
        return $this->creator_id;
    }

    public function getModifierId() {
        return $this->modifier_id;
    }

    public function getModbits() {
        return $this->modbits;
    }

    public function setModbits($modbits) {
        $this->modbits = $modbits;
    }

    public function getAnswers() {
        return $this->answers;
    }

    public function getLastAnswer() {
        return !empty($this->last_answer) && $this->last_answer != 0
               ? strtotime($this->last_answer) : 0;
    }

    public function getLastAnswerId() {
        return $this->last_answer_id;
    }

    public function getLastAnswerUserId() {
        return $this->last_answer_user_id;
    }

    public function getLastAnswerGuestLogin() {
        return $this->last_answer_guest_login;
    }

    public function getSmallImage() {
        return $this->small_image;
    }

    public function hasSmallImage() {
        return $this->small_image != 0;
    }

    public function setSmallImage($small_image) {
        $this->small_image = $small_image;
    }

    public function getSmallImageX() {
        return $this->small_image_x;
    }

    public function setSmallImageX($small_image_x) {
        $this->small_image_x = $small_image_x;
    }

    public function getSmallImageY() {
        return $this->small_image_y;
    }

    public function setSmallImageY($small_image_y) {
        $this->small_image_y = $small_image_y;
    }

    public function getSmallImageFormat() {
        return $this->small_image_format;
    }

    public function setSmallImageFormat($small_image_format) {
        $this->small_image_format = $small_image_format;
    }

    public function getSmallImageURL() {
        return getImageURL($this->getSmallImageFormat(),
                           $this->getSmallImage());
    }

    public function getLargeImage() {
        return $this->large_image;
    }

    public function hasLargeImage() {
        return $this->large_image != 0
               && $this->small_image != $this->large_image;
    }

    public function setLargeImage($large_image) {
        $this->large_image = $large_image;
    }

    public function getLargeImageX() {
        return $this->large_image_x;
    }

    public function setLargeImageX($large_image_x) {
        $this->large_image_x = $large_image_x;
    }

    public function getLargeImageY() {
        return $this->large_image_y;
    }

    public function setLargeImageY($large_image_y) {
        $this->large_image_y = $large_image_y;
    }

    public function getLargeImageURL() {
        return getImageURL($this->getLargeImageFormat(),
                           $this->getLargeImage());
    }

    // Useful synonyms

    public function hasImage() {
        return $this->hasSmallImage();
    }

    public function getImage() {
        return $this->getLargeImage();
    }

    public function getImageX() {
        return $this->getLargeImageX();
    }

    public function getImageY() {
        return $this->getLargeImageY();
    }

    public function getImageURL() {
        return $this->getLargeImageURL();
    }

    public function getImageSize() {
        return $this->getLargeImageSize();
    }

    public function getImageSizeKB() {
        return $this->getLargeImageSizeKB();
    }

    //

    public function getLargeImageSize() {
        return $this->large_image_size;
    }

    public function getLargeImageSizeKB() {
        return (int)($this->large_image_size / 1024);
    }

    public function setLargeImageSize($large_image_size) {
        $this->large_image_size = $large_image_size;
    }

    public function getLargeImageFormat() {
        return $this->large_image_format;
    }

    public function setLargeImageFormat($large_image_format) {
        $this->large_image_format = $large_image_format;
    }

    public function getLargeImageFilename() {
        return $this->large_image_filename;
    }

    public function setLargeImageFilename($large_image_filename) {
        $this->large_image_filename = $large_image_filename;
    }

    public function isInserted() {
        return (boolean)$this->inserted;
    }

    public function getProperty($name) {
        $func = 'get'.ucfirst($name);
        return $this->$func();
    }

    public function getDateProperty($name, $format) {
        return formatAnyDate($format, $this->getProperty($name));
    }

    protected function getCompositeValue($value, $term = false) {
        if ($term || strpos($value, ' ') === false) {
            $value = preg_replace(
                '/\$\[([-\d]+)\]/e',
                '$this->getCatalog(\1,0)',
                $value
            );
            $value = preg_replace(
                '/\$\[([-\d]+),([-\d]+)\]/e',
                '$this->getCatalog(\1,\2)',
                $value
            );
            $value = preg_replace(
                '/\$\{(\w+)\}/e',
                '$this->getProperty("\1")',
                $value
            );
            $value = preg_replace(
                '/\$\{(\w+)@(\w+)\}/e',
                '$this->getDateProperty("\1","\2")',
                $value
            );
            return $value;
        } else {
            $program = preg_split('/\s+/', $value);
            $ep = 0;
            while($ep < count($program)) {
               if ($program[$ep] == 'subtree') {
                   if ($ep >= count($program) - 2)
                       break;
                   if (strpos($this->getTrack(),
                              track(idByIdent($program[$ep + 1]))) !== false)
                       return $this->getCompositeValue($program[$ep + 2], true);
                   $ep += 3;
               } elseif ($program[$ep] == 'default') {
                   if ($ep >= count($program) - 1)
                       break;
                   return $this->getCompositeValue($program[$ep + 1], true);
               } else {
                   break;
               }
            }
            return $this->getCompositeValue($value, true);
        }
    }

}

function newEntry(array $row) {
    global $entryClassNames;

    if (isset($entryClassNames[$row['entry']]))
        $className = $entryClassNames[$row['entry']];
    else
        $className = 'Entry';
    if (!class_exists($className))
        $className = 'Entry';
    return new $className($row);
}

function getGrpsByEntryId($id) {
    if (hasCachedValue('grps', 'entries', $id))
        return getCachedValue('grps', 'entries', $id);
    $result = sql("select grp
                   from entry_grps
                   where entry_id=$id",
                  __FUNCTION__);
    $grps = array();
    while (list($grp) = mysql_fetch_row($result))
        $grps[] = $grp;
    setCachedValue('grps', 'entries', $id, $grps);
    return $grps;
}

function setGrpsByEntryId($id, array $grps) {
    sql('lock tables entry_grps write',
        __FUNCTION__, 'lock');
    sql("delete
         from entry_grps
         where entry_id=$id",
        __FUNCTION__, 'delete');
    foreach ($grps as $grp) {
        sql("insert into entry_grps(entry_id,grp)
             values($id,$grp)",
            __FUNCTION__, 'insert');
        $eid = sql_insert_id();
    }
    setCachedValue('grps', 'entries', $id, $grps);
    sql('unlock tables',
        __FUNCTION__, 'unlock');
    incContentVersionsByEntryId($id);
}

function setHiddenByEntryId($id, $hidden) {
    if ($hidden)
        $op = '& ~0x1100';
    else
        $op = '| 0x1100';
    sql("update entries
         set perms=perms $op
         where id=$id",
        __FUNCTION__);
    if (getTypeByEntryId($id) == ENT_FORUM)
        answerUpdate(getParentIdByEntryId($id));
    incContentVersionsByEntryId($id);
}

function setDisabledByEntryId($id, $disabled) {
    $disabled = (int) $disabled;
    sql("update entries
         set disabled=$disabled
         where id=$id",
        __FUNCTION__);
    if (getTypeByEntryId($id) == ENT_FORUM)
        answerUpdate(getParentIdByEntryId($id));
    incContentVersionsByEntryId($id);
}

function getTypeByEntryId($id) {
    if (hasCachedValue('entry', 'entries', $id))
        return getCachedValue('entry', 'entries', $id);
    $result = sql("select entry
                   from entries
                   where id=$id",
                  __FUNCTION__);
    if (mysql_num_rows($result) > 0) {
        $type = mysql_result($result, 0, 0);
        setCachedValue('entry', 'entries', $id, $type);
        return $type;
    } else {
        return ENT_NULL;
    }
}

function getGrpByEntryId($id) {
    if (hasCachedValue('grp', 'entries', $id))
        return getCachedValue('grp', 'entries', $id);
    $result = sql("select grp
                   from entries
                   where id=$id",
                  __FUNCTION__);
    if (mysql_num_rows($result) > 0) {
        $grp = mysql_result($result, 0, 0);
        setCachedValue('grp', 'entries', $id, $grp);
        return $grp;
    } else {
        return 0;
    }
}

function getParentIdByEntryId($id) {
    $result = sql("select parent_id
                   from entries
                   where id=$id",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}

function getOrigIdByEntryId($id) {
    $result = sql("select orig_id
                   from entries
                   where id=$id",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}

function setOrigIdToEntryId(Entry $entry) {
    sql("update entries
         set orig_id=id
         where id={$entry->getId()}",
        __FUNCTION__, 'orig_id');
    $entry->setOrigId($entry->getId());
    incContentVersionsByEntryId($entry->getId());
}

function getRatingByEntryId($id) {
    $result = sql("select rating
                   from entries
                   where id=$id",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}

function validateHierarchy($parentId, $up, $entry, $id) {
    if ($parentId < 0)
        return EVH_NO_PARENT;
    if ($up < 0)
        return EVH_NO_UP;
    if ($parentId != 0 && $up == 0)
        return EVH_NOT_UP_UNDER_PARENT;
    $parentTrack = $parentId > 0 ? trackById('entries', $parentId) : '';
    if ($parentTrack === 0)
        return EVH_NO_PARENT;
    $upTrack = $up > 0 ? trackById('entries', $up) : '';
    if ($upTrack === 0)
        return EVH_NO_UP;
    if (substr($upTrack, 0, strlen($parentTrack)) != $parentTrack)
        return EVH_NOT_UP_UNDER_PARENT;
    if (strpos($upTrack,track($id)) !== false)
        return EVH_LOOP;
    $parentEntry = getTypeByEntryId($parentId);
    $upEntry = getTypeByEntryId($up);
    $correct = false;
    switch($entry) {
        case ENT_POSTING:
            $correct = $parentEntry == ENT_TOPIC
                       && ($upEntry == ENT_POSTING || $parentId == $up);
            break;
        case ENT_FORUM:
            $correct = $parentEntry == ENT_POSTING
                       && ($upEntry == ENT_FORUM || $parentId == $up);
            break;
        case ENT_TOPIC:
            $correct = $parentId == 0 && ($upEntry == ENT_TOPIC || $up == 0);
            break;
        case ENT_IMAGE:
            $correct = $parentId == 0
                       && ($upEntry == ENT_POSTING
                           || $upEntry == ENT_FORUM
                           || $upEntry == ENT_TOPIC
                           || $up == 0);
            break;
    }
    if (!$correct)
        return EVH_INCORRECT;
    return EG_OK;
}

function entryExists($entry, $id) {
    $filter = "id=$id";
    if ($entry != ENT_NULL)
        $filter .= " and entry=$entry";
    $result = sql("select id
                   from entries
                   where $filter",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0;
}

function reorderEntries(array $ids) {
    $n = 1;
    $some_id = null;
    foreach ($ids as $id) {
        $some_id = $id;
        sql("update entries
             set index0=$n
             where id=$id",
            __FUNCTION__);
        $n++;
    }
    if ($some_id != null)
        incContentVersionsByEntryId($some_id);
}

function renewEntry($id) {
    $now = sqlNow();
    sql("update entries
         set sent='$now'
         where id=$id",
        __FUNCTION__);
    incContentVersionsByEntryId($id);
}

function incContentVersionsByEntryId($id) {
    $type = getTypeByEntryId($id);
    switch ($type) {
        case ENT_POSTING:
            incContentVersions('postings');
            break;
        case ENT_FORUM:
            incContentVersions('forums');
            break;
        case ENT_TOPIC:
            incContentVersions('topics');
            break;
    }
}
?>
