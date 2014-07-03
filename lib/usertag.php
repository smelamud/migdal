<?php
# @(#) $Id$

require_once('lib/dataobject.php');

class UserTag
        extends DataObject {

    protected $login;
    protected $gender;
    protected $email;
    protected $hide_email;
    protected $user_hidden;
    protected $user_guest;
    protected $guest_login = '';

    public function __construct(array $row) {
        $this->gender = 'mine';
        parent::__construct($row);
    }

    public function getLogin() {
        return $this->login;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function getUserName() {
        return $this->getLogin();
    }

    public function getUserFolder() {
        return c_ascii($this->getLogin()) ? $this->getLogin()
                                          : $this->getUserId()
                                          /*FIXME where defined?*/;
    }

    public function isMan() {
        return $this->gender == 'mine' || $this->gender == '';
    }

    public function isWoman() {
        return $this->gender == 'femine';
    }

    public function getGender() {
        return $this->gender;
    }

    public function getGenderIndex() {
        return $this->isMan() ? 1 : 2;
    }

    public function getEmail() {
        return $this->email;
    }

    public function isHideEmail() {
        return $this->hide_email;
    }

    public function isEmailVisible() {
        return $this->email != '' && !$this->hide_email;
    }

    public function isUserHidden() {
        return $this->user_hidden ? 1 : 0;
    }

    public function isUserAdminHidden() {
        return $this->user_hidden > 1 ? 1 : 0;
    }

    public function isUserVisible() {
        global $userAdminUsers;

        return !$this->isUserHidden()
               || ($userAdminUsers && !$this->isUserAdminHidden());
    }

    public function isUserGuest() {
        return $this->user_guest ? 1 : 0;
    }

    public function getGuestLogin() {
        return $this->guest_login;
    }

    public function setGuestLogin($guest_login) {
        $this->guest_login = $guest_login;
    }

}
?>
