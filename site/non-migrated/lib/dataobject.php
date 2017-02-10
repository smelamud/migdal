<?php
# @(#) $Id$

class DataObject {

    public function __construct(array $row = array()) {
        foreach($row as $var => $value)
            $this->$var = $value;
    }

}
?>
