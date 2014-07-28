<?php
# @(#) $Id$

require_once('lib/limitselect.php');
require_once('lib/sort.php');
require_once('lib/forums.php');
require_once('lib/postings.php');

class ForumOrPostingListIterator
        extends LimitSelectIterator {

    public function __construct($grp, $limit = 10, $offset = 0, 
                                $sort = SORT_SENT, $asGuest = true) {
        $Filter = '('.forumListFilter().')';
        $Filter .= 'or ('.postingListFilter(
                        $grp, -1, true, -1, $sort, GRP_NONE, 0, -1, 0, -1,
                        SELECT_GENERAL, MOD_NONE, -1, -1, '', false, 0,
                        $asGuest).')';
        $Order = getOrderBy($sort,
                    array(SORT_SENT  => 'entries.sent desc'));
        parent::__construct(
            'Forum',
            "select entries.id as id,entry,orig_id,grp,subject,author,
                    author_xml,source,source_xml,title,title_xml,comment0,
                    comment0_xml,comment1,comment1_xml,body,body_xml,
                    body_format,sent,answers,entries.created as created,
                    entries.modified as modified,guest_login,user_id,group_id,
                    perms,disabled,parent_id,users.login as login,
                    users.gender as gender,users.email as email,
                    users.hide_email as hide_email,users.hidden as user_hidden,
                    users.guest as user_guest
             from entries
                  left join users
                       on entries.user_id=users.id
             where $Filter
             $Order",
            $limit, $offset,
            "select count(*)
             from entries
             where $Filter");
    }

    protected function create(array $row) {
        if ($row['id'] > 0) {
            if ($row['ident'] != '')
                setCachedValue('ident', 'entries', $row['ident'], $row['id']);
            setCachedValue('track', 'entries', $row['id'], $row['track']);
            setCachedValue('catalog', 'entries', $row['id'], $row['catalog']);
        }
        if ($row['entry'] == ENT_POSTING && $row['id'] != $row['orig_id']) {
            $Select = origFields(SELECT_GENERAL);
            $From = origTables(SELECT_GENERAL);
            $Where = "entries.id={$row['orig_id']}";
            $result = sql("select $Select
                           from $From
                           where $Where",
                          __FUNCTION__, 'original');
            $orig = mysql_num_rows($result) > 0 ? mysql_fetch_assoc($result)
                                                : array();
            $row = array_merge($row, $orig);
        }
        if ($row['entry'] == ENT_FORUM)
            $row['parent_type'] = getTypeByEntryId($row['parent_id']);

        switch ($row['entry']) {
            case ENT_FORUM:
                return new Forum($row);
            case ENT_POSTING:
                return new Posting($row);
            default:
                return new Entry($row);
        }
    }

}
?>
