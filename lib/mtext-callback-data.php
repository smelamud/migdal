<?php
# @(#) $Id$

class ImageCallbackData {

    private $id;
    private $par;
    private $image;
    private $align;

    public function __construct($id, $par, $image = 0, $align = '') {
        $this->id = $id;
        $this->par = $par;
        $this->image = $image;
        $this->align = $align;
    }

    public function getId() {
        return $this->id;
    }

    public function getPar() {
        return $this->par;
    }

    public function getImage() {
        return $this->image;
    }

    public function getAlign() {
        return $this->align;
    }

}

class UserNameCallbackData {

    private $guest;
    private $login;

    public function __construct($guest, $login) {
        $this->guest = $guest;
        $this->login = $login;
    }

    public function isGuest() {
        return $this->guest;
    }

    public function getLogin() {
        return $this->login;
    }

}

class IncutCallbackData {

    private $align;
    private $width;

    public function __construct($align, $width) {
        $this->align = $align;
        $this->width = $width;
    }

    public function getAlign() {
        return $this->align;
    }

    public function getWidth() {
        return $this->width;
    }

}
?>
