<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/usertag.php');
require_once('lib/limitselect.php');
require_once('lib/calendar.php');
require_once('lib/utils.php');
require_once('lib/bug.php');
require_once('lib/tmptexts.php');
require_once('lib/calendar.php');
require_once('lib/random.php');
require_once('lib/text.php');
require_once('lib/text-any.php');
require_once('lib/alphabet.php');
require_once('lib/sort.php');
require_once('lib/sql.php');
require_once('lib/charsets.php');
require_once('lib/mtext-html.php');
require_once('lib/ctypes.php');
require_once('lib/time.php');
require_once('lib/fuzzy-calendar.php');

const USR_NONE = 0;
const USR_MIGDAL_STUDENT = 0x0001;
const USR_REBE = 0x0004;
const USR_ADMIN_USERS = 0x0008;
const USR_ADMIN_TOPICS = 0x0010;
const USR_MODERATOR = 0x0040;
const USR_ADMIN_DOMAIN = 0x0100;

define('USR_USER', USR_MIGDAL_STUDENT);
define('USR_ADMIN', USR_REBE | USR_ADMIN_USERS | USR_ADMIN_TOPICS |
                    USR_MODERATOR | USR_ADMIN_DOMAIN);

class User
        extends UserTag {

    protected $id = 0;
    protected $password = '';
    protected $dup_password = '';
    protected $name = '';
    protected $jewish_name = '';
    protected $surname = '';
    protected $info = '';
    protected $info_xml = '';
    protected $birthday = '1970-01-01';
    protected $created = 0;
    protected $modified = 0;
    protected $rights = 0;
    protected $last_online = 0;
    protected $last_minutes = 0;
    protected $icq = '';
    protected $email_disabled = 0;
    protected $shames = 0;
    protected $hidden = 0;
    protected $online = 0;
    protected $no_login = 0;
    protected $has_personal = 0;
    protected $confirm_code = '';
    protected $confirmed = 0;
    protected $confirm_days = 0;
    protected $last_message = 0;

    public function __construct(array $row = array()) {
        parent::__construct($row);
    }

    public function setup(array $vars) {
        global $tfUser;

        if (!isset($vars['edittag']) || !$vars['edittag'])
            return;
        $this->login = $vars['new_login'];
        $this->password = $vars['new_password'];
        $this->dup_password = $vars['dup_password'];
        $this->name = $vars['name'];
        $this->jewish_name = $vars['jewish_name'];
        $this->surname = $vars['surname'];
        $this->gender = $vars['gender'];
        $this->rights = disjunct($vars['rights']);
        $this->info = $vars['info'];
        $this->info_xml = anyToXML($this->info, $tfUser, MTEXT_SHORT);
        $this->email = $vars['email'];
        $this->hide_email = $vars['hide_email'];
        $this->icq = $vars['icq'];
        $this->hidden = $vars['hidden'];
        $this->no_login = $vars['no_login'];
        $this->has_personal = $vars['has_personal'];
        $birth_year = $vars['birth_year'];
        if ($birth_year < 20)
            $birth_year = 2000 + $birth_year;
        elseif ($birth_year < 100)
            $birth_year = 1900 + $birth_year;
        $this->birthday = sprintf('%04u-%02u-%02u', $birth_year,
                                  $vars['birth_month'], $vars['birth_day']);
        $this->email_disabled = $vars['email_enabled'] ? 0 : 1;
    }

    public function isEditable() {
        global $userId, $userAdminUsers;

        return $this->id == 0 || $this->id == $userId || $userAdminUsers;
    }

    public function getId() {
        return $this->id;
    }

    // Used by UserTag::getUserFolder()
    public function getUserId() {
        return $this->getId();
    }

    public function getPassword() {
        return $this->password;
    }

    public function getDupPassword() {
        return $this->dup_password;
    }

    public function getFolder() {
        return $this->getUserFolder();
    }

    public function getName() {
        return $this->name;
    }

    public function getJewishName() {
        return $this->jewish_name;
    }

    public function getSurname() {
        return $this->surname;
    }

    public function getFullName() {
        if ($this->jewish_name != '')
            $fullName = "$this->jewish_name ($this->name)";
        else
            $fullName = $this->name;
        if ($this->surname != '')
            $fullName .= " $this->surname";
        return $fullName;
    }

    public function getFullNameCivil() {
        if ($this->jewish_name != '')
            $fullName = "$this->name ($this->jewish_name)";
        else
            $fullName = $this->name;
        if ($this->surname != '')
            $fullName .= " $this->surname";
        return $fullName;
    }

    public function getFullNameSurname() {
        if ($this->surname != '')
            $fullName = $this->surname;
        else
            $fullName = '';
        if ($this->jewish_name != '')
            $fullName .= " $this->jewish_name ($this->name)";
        else
            $fullName .= " $this->name";
        return $fullName;
    }

    public function getInfo() {
        return $this->info;
    }

    public function getInfoXML() {
        return $this->info_xml;
    }

    public function getInfoHTML() {
        return mtextToHTML($this->getInfoXML(), MTEXT_SHORT);
    }

    public function getAge() {
        $bt = explode('-', $this->birthday);
        $t = getdate();
        $age = getCalendarAge($bt[1], $bt[2], $bt[0],
                              $t['mon'], $t['mday'], $t['year']);
        return $age < 100 ? $age : '-';
    }

    public function getBirthday() {
        return $this->birthday;
    }

    public function getRussianBirthday() {
        $bt = explode('-', $this->birthday);
        return $bt[2].' '.getRussianMonth((int)$bt[1]).' '.$bt[0];
    }

    public function getJewishBirthday() {
        $bt = explode('-', $this->birthday);
        return getJewishFromDate($bt[1], $bt[2], $bt[0]);
    }

    public function getDayOfBirth() {
        $bt = explode('-', $this->birthday);
        return $bt[2] ? $bt[2] : 1;
    }

    public function getMonthOfBirth() {
        $bt = explode('-', $this->birthday);
        return $bt[1] ? $bt[1] : 1;
    }

    public function isMonthOfBirth($month) {
        return $this->getMonthOfBirth() == $month;
    }

    public function getYearOfBirth() {
        $bt = explode('-', $this->birthday);
        return $bt[0] ? $bt[0] : '1900';
    }

    public function getCreated() {
        return strtotime($this->created);
    }

    public function getModified() {
        return strtotime($this->modified);
    }

    public function getRights() {
        return $this->rights;
    }

    public function hasRight($right) {
        return ($this->rights & $right) != 0;
    }

    public function isMigdalStudent() {
        return $this->hasRight(USR_MIGDAL_STUDENT);
    }

    public function setRights($rights) {
        $this->rights = $rights;
    }

    public function getICQ() {
        return $this->icq;
    }

    public function getICQStatusImage() {
        $icqH = htmlspecialchars($this->icq, ENT_QUOTES);
        return $icqH
               ? "<img src=\"http://web.icq.com/whitepages/online?icq=$icqH&img=5\">"
               : '';
    }

    public function isEmailDisabled() {
        return $this->email_disabled;
    }

    public function isEmailEnabled() {
        return $this->email_disabled == 0;
    }

    public function isOnline() {
        return $this->online != 0;
    }

    public function isTooOld() {
        return ourtime() - $this->getLastOnline() > 10 * 365 * 24 * 60 * 60;
    }

    public function getLastOnline() {
        return strtotime($this->last_online);
    }

    public function getFuzzyLastOnline() {
        return formatFuzzyTimeElapsed($this->getLastOnline());
    }

    public function getLastMinutes() {
        return $this->last_minutes;
    }

    public function isAdminUsers() {
        return $this->hasRight(USR_ADMIN_USERS);
    }

    public function isAdminTopics() {
        return $this->hasRight(USR_ADMIN_TOPICS);
    }

    public function isModerator() {
        return $this->hasRight(USR_MODERATOR);
    }

    public function isAdminDomain() {
        return $this->hasRight(USR_ADMIN_DOMAIN);
    }

    public function getHidden() {
        return $this->hidden;
    }

    public function isHidden() {
        return $this->hidden ? 1 : 0;
    }

    // Override UserTag method
    public function isUserHidden() {
        return $this->isHidden();
    }

    public function isAdminHidden() {
        return $this->hidden > 1 ? 1 : 0;
    }

    // Override UserTag method
    public function isUserAdminHidden() {
        return $this->isAdminHidden();
    }

    public function isVisible() {
        global $userAdminUsers;

        return !$this->isHidden()
               || ($userAdminUsers && !$this->isAdminHidden());
    }

    public function isNoLogin() {
        return $this->no_login;
    }

    public function isHasPersonal() {
        return $this->has_personal;
    }

    public function getConfirmCode() {
        return $this->confirm_code;
    }

    public function isConfirmed() {
        return $this->confirmed;
    }

    public function getConfirmDays() {
        return $this->confirm_days;
    }

    public function getLastMessage()
    {
    return !empty($this->last_message) ? strtotime($this->last_message) : 0;
    }

    public function getRank() {
        if ($this->isAdminUsers())
            return 'Вебмастер';
        if ($this->isModerator())
            return 'Модератор';
        if ($this->isMigdalStudent())
            return 'Мигдалевец';
        return '';
    }

    public function getInfoOrRankHTML() {
        return $this->getInfo() != '' ? $this->getInfoHTML() : $this->getRank();
    }

}

class UserListIterator
        extends SelectIterator {

    public function __construct($prefix, $sort = SORT_LOGIN,
                                $right = USR_NONE) {
        global $userAdminUsers;

        $now = sqlNow();
        $hide = $userAdminUsers ? 2 : 1;
        $sortFields = array(SORT_LOGIN       => 'login',
                            SORT_NAME        => 'name',
                            SORT_JEWISH_NAME => 'if(jewish_name<>"",jewish_name,name)',
                            SORT_SURNAME     => 'surname');
        if ($prefix != '') {
            $prefixS = addslashes($prefix);
            $sortField = @$sortFields[$sort] != '' ? $sortFields[$sort]
                                                   : 'login';
            $fieldFilter = "and ($sortField like '$prefixS%')";
        } else
            $fieldFilter = '';
        $rightFilter = $right != USR_NONE ? "and (rights & $right)<>0" : '';
        $order = getOrderBy($sort,
                            array(SORT_LOGIN       => 'login',
                                  SORT_NAME        => 'name,surname',
                                  SORT_JEWISH_NAME => 'if(jewish_name<>"",
                                                          jewish_name,name),
                                                       surname',
                                  SORT_SURNAME     => 'surname,name'));
        parent::__construct(
            'User',
            "select id,login,name,jewish_name,surname,gender,birthday,rights,
                    email,hide_email,icq,last_online,
                    if(last_online+interval 1 hour>'$now',1,0) as online,
                    floor((unix_timestamp('$now')
                           -unix_timestamp(last_online))/60) as last_minutes,
                    confirm_deadline is null as confirmed,
                    floor((unix_timestamp(confirm_deadline)
                           -unix_timestamp('$now'))/86400) as confirm_days
             from users
             where hidden<$hide $fieldFilter $rightFilter
             $order");
    }

}

class UserAlphabetIterator
        extends AlphabetIterator {

    public function __construct($limit = 0, $sort = SORT_LOGIN) {
        global $userAdminUsers;

        $hide = $userAdminUsers ? 2 : 1;
        $fields = array(SORT_LOGIN       => 'login',
                        SORT_NAME        => 'name',
                        SORT_JEWISH_NAME => 'if(jewish_name<>"",jewish_name,name)',
                        SORT_SURNAME     => 'surname');
        $field = @$fields[$sort] != '' ? $fields[$sort] : 'login';
        $sortFields = array(SORT_LOGIN       => 'login',
                            SORT_NAME        => 'name',
                            SORT_JEWISH_NAME => 'if(jewish_name<>"",
                                                    jewish_name,name)',
                            SORT_SURNAME     => 'surname');
        $sortField = @$sortFields[$sort] != '' ? $sortFields[$sort] : 'login';
        $order = getOrderBy($sort, $sortFields);
        parent::__construct("select left($field,@len@) as letter,1 as count
                             from users
                             where hidden<$hide and guest=0
                                   and $sortField like '@prefix@%'
                             $order",
                            $limit);
    }

}

class UsersNowIterator
        extends SelectIterator {

    public function __construct($period) {
        global $userAdminUsers;

        $now = sqlNow();
        $hide = $userAdminUsers ? 2 : 1;
        parent::__construct(
                'User',
                "select distinct users.id as id,login,gender,email,
                                 hide_email,hidden
                 from users
                      inner join sessions
                            on sessions.user_id=users.id
                 where last+interval $period minute>'$now' and hidden<$hide
                 order by last desc");
    }

}

function getUserById($id, $guest_login = '') {
    global $userAdminUsers,$userId;

    $now = sqlNow();
    $hide = $userAdminUsers ? 2 : 1;
    $result = sql("select id,login,name,jewish_name,surname,gender,info,
                          info_xml,birthday,rights,last_online,email,hide_email,
                          icq,guest as user_guest,email_disabled,hidden,
                          no_login,has_personal,
                          if(last_online+interval 1 hour>'$now',1,0) as online,
                          floor((unix_timestamp('$now')
                                 -unix_timestamp(last_online))/60) as last_minutes,
                          confirm_code,
                          confirm_deadline is null as confirmed,
                          floor((unix_timestamp(confirm_deadline)
                                 -unix_timestamp('$now'))/86400) as confirm_days
                   from users
                   where users.id=$id and (hidden<$hide or guest<>0
                                           or users.id=$userId)",
                  __FUNCTION__);
    $user = new User(mysql_num_rows($result) > 0 ? mysql_fetch_assoc($result)
                                                 : array('id' => 0));
    $user->setGuestLogin($guest_login);
    return $user;
}

function storeUser(User $user) {
    global $userAdminUsers;

    // Здесь допускается установка админских прав не админом! Проверка должна
    // производиться раньше.
    $vars = array(
        'login' => $user->getLogin(),
        'name' => $user->getName(),
        'jewish_name' => $user->getJewishName(),
        'surname' => $user->getSurname(),
        'gender' => $user->getGender(),
        'info' => $user->getInfo(),
        'info_xml' => $user->getInfoXML(),
        'birthday' => $user->getBirthday(),
        'modified' => sqlNow(),
        'rights' => $user->getRights(),
        'email' => $user->getEmail(),
        'hide_email' => $user->isHideEmail(),
        'email_disabled' => $user->isEmailDisabled(),
        'icq' => $user->getICQ()
    );
    if ($userAdminUsers)
        $vars = array_merge($vars, array(
            'hidden' => $user->getHidden(),
            'no_login' => $user->isNoLogin(),
            'has_personal' => $user->isHasPersonal()
        ));
    if (!$user->getId() || $user->getDupPassword() != '')
        $vars = array_merge($vars, array(
            'password' => md5($user->getPassword())
        ));
    if ($user->getId()) {
        $result = sql(sqlUpdate('users',
                                $vars,
                                array('id' => $user->getId())),
                      __FUNCTION__, 'update');
    } else {
        $vars['created'] = sqlNow();
        $result = sql(sqlInsert('users',
                                $vars),
                      __FUNCTION__, 'insert');
        $user->setId(sql_insert_id());
    }
    return $result;
}

function preconfirmUser($userId) {
    global $regConfirmTimeout;

    $s = '';
    for ($i = 0; $i < 20; $i++) {
        $s.=chr(random(ord('A'),ord('Z')));
    }
    $now = sqlNow();
    sql("update users
         set no_login=1,confirm_code='$s',
             confirm_deadline='$now'+interval $regConfirmTimeout day
         where id=$userId",
        __FUNCTION__);
}

function confirmUser($userId) {
    $now = sqlNow();
    sql("update users
         set no_login=0,hidden=0,confirm_deadline=null,
             last_online='$now'
         where id=$userId",
        __FUNCTION__);
}

function getUserIdByConfirmCode($confirmCode) {
    $confirmCodeS = addslashes($confirmCode);
    $result = sql("select id
                   from users
                   where confirm_code='$confirmCodeS'
                         and hidden<2",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}

function isUserConfirmed($id) {
    $result = sql("select if(confirm_deadline is null,1,0)
                   from users
                   where id=$id",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) != 0
                                       : false;
}

function deleteNonConfirmedUsers() {
    $now = sqlNow();
    sql("delete
         from users
         where confirm_deadline is not null and confirm_deadline<'$now'",
        __FUNCTION__,'delete');
    sql("optimize table users",
        __FUNCTION__,'optimize');
}

function getUserLoginById($id) {
    // Hidden users' logins must be returned, because system users must be
    // identified

    $result = sql("select login
                   from users
                   where id=$id",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}


function getUserGenderById($id) {
    global $userAdminUsers;

    $hide = $userAdminUsers ? 2 : 1;
    $result = sql("select gender
                   from users
                   where id=$id and hidden<$hide",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 'mine';
}

function getUserIdByLogin($login) {
    $loginS = addslashes($login);
    $result = sql("select id
                   from users
                   where login='$loginS'",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}

function idByLogin($login) {
    if (isId($login))
        return $login;
    if (is_null($login) || $login == '')
        return 0;
    if (hasCachedValue('login', 'users', $login))
        return getCachedValue('login', 'users', $login);
    $id = getUserIdByLogin($login);
    setCachedValue('login', 'users', $login, $id);
    return $id;
}

function getUserIdByLoginPassword($login, $password) {
    $loginS = addslashes($login);
    $passwordMD5 = md5($password);
    $result = sql("select id
                   from users
                   where login='$login' and password='$passwordMD5'
                         and no_login=0",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}

function setPasswordByUserId($id, $password) {
    $now = sqlNow();
    sql("update users
         set password=md5('$password'),modified='$now'
         where id=$id",
        __FUNCTION__);
}

function getShamesId() {
    $result = sql('select id
                   from users
                   where shames=1',
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}

function getGuestId() {
    global $allowGuests, $shortSessionTimeout, $guestLogin;

    if (!$allowGuests)
        return 0;
    $result = sql("select id
                   from users
                   where guest<>0
                   order by login
                   limit 1",
                  __FUNCTION__, 'locate_guest');
    if (mysql_num_rows($result) > 0)
        return mysql_result($result, 0, 0);
    $now = sqlNow();
    sql("insert into users(login,email_disabled,guest,hidden,no_login,created,
                           modified)
         values('$guestLogin',1,1,2,1,'$now','$now')",
        __FUNCTION__,'create');
    $id = sql_insert_id();
    return $id;
}

function updateLastOnline($userId) {
    $now = sqlNow();
    sql("update users
         set last_online='$now'
         where id=$userId",
        __FUNCTION__);
}

function personalExists($id) {
    global $userAdminUsers;

    $hide = $userAdminUsers ? 2 : 1;
    $result = sql("select id
                   from users
                   where id=$id and hidden<$hide and has_personal<>0",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0;
}

function userExists($id) {
    global $userAdminUsers;

    $hide = $userAdminUsers ? 2 : 1;
    $result = sql("select id
                   from users
                   where id=$id and hidden<$hide",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0;
}

function userLoginExists($login, $excludeId = 0) {
    $loginS = addslashes($login);
    $filter = $excludeId != 0 ? "and id<>$excludeId" : '';
    $result = sql("select id
                   from users
                   where login='$loginS' $filter",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0;
}

class UsersSummary
        extends DataObject {

    private $total;
    private $waiting;

    public function __construct($total, $waiting) {
        $this->total = $total;
        $this->waiting = $waiting;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getWaiting() {
        return $this->waiting;
    }

}

function getUsersSummary() {
    global $userAdminUsers;

    $hide = $userAdminUsers ? 2 : 1;
    $result = sql("select count(*),count(confirm_deadline)
                   from users
                   where hidden<$hide",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ?
           new UsersSummary(mysql_result($result, 0, 0)
                            - mysql_result($result, 0, 1),
                            mysql_result($result, 0, 1)) :
           new UsersSummary(0, 0);
}

function getGuestsNow($period) {
    $now = sqlNow();
    $result = sql("select count(*)
                   from sessions
                   where user_id=0 and last+interval $period minute>'$now'",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}
?>
