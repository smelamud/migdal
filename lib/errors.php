<?php
# @(#) $Id$

define('EUM_OK',100);
define('EUM_STORE_SQL',101);
define('EUM_PASSWORD_LEN',102);
define('EUM_PASSWORD_DIFF',103);
define('EUM_LOGIN_ABSENT',104);
define('EUM_LOGIN_EXISTS',105);
define('EUM_GENDER',106);
define('EUM_BIRTHDAY',107);
define('EUM_NO_EDIT',108);
define('EUM_PRECONFIRM_SQL',109);
define('EUM_NAME_ABSENT',1010);
define('EUM_SURNAME_ABSENT',1011);
define('EUM_EMAIL_ABSENT',1012);
define('EUM_NOT_EMAIL',1013);
define('EUM_DISABLED',1014);

define('EL_INVALID',110);
define('EL_OK',111);
define('EL_NO_COOKIES',112);

define('ET_OK',120);
define('ET_NAME_ABSENT',121);
define('ET_DESCRIPTION_ABSENT',122);
define('ET_NO_EDIT',123);
define('ET_STORE_SQL',124);
define('ET_IDENT_UNIQUE',125);
define('ET_NO_UP',126);
define('ET_LOOP_UP',127);
define('ET_TRACK_SQL',128);
define('ET_MUST_ALLOW',129);
define('ET_NO_USER',1210);

define('EP_OK',130);
define('EP_NO_EDIT',131);
define('EP_BODY_ABSENT',132);
define('EP_SUBJECT_ABSENT',133);
define('EP_STORE_SQL',134);
define('EP_TOPIC_ABSENT',135);
define('EP_NO_TOPIC',136);
define('EP_NO_PERSONAL',137);
define('EP_NO_SEND',138);
define('EP_IMAGE_ABSENT',139);
define('EP_NO_IMAGE',1310);
define('EP_LARGE_BODY_ABSENT',1311);
define('EP_TITLE_SQL',1312);
define('EP_DISABLE_SQL',1313);
define('EP_IDENT_ABSENT',1314);
define('EP_IDENT_UNIQUE',1315);
define('EP_INVALID_GRP',1316);
define('EP_AUTHOR_ABSENT',1317);
define('EP_SOURCE_ABSENT',1318);
define('EP_OWNED_TOPIC',1319);
define('EP_URL_ABSENT',1320);
define('EP_INDEX1_ABSENT',1321);
define('EP_NO_UP',1322);
define('EP_LOOP_UP',1323);
define('EP_TRACK_SQL',1324);

define('EMH_OK',140);
define('EMH_NO_MODERATE',141);
define('EMH_NO_MESSAGE',142);

define('EC_OK',150);
define('EC_NO_SEND',151);
define('EC_NO_EDIT',152);
define('EC_STORE_SQL',153);
define('EC_SUBJECT_ABSENT',154);
define('EC_BODY_ABSENT',155);
define('EC_NO_TYPE',156);
define('EC_LOGIN_ASSIGN',157);
define('EC_NO_ASSIGN',158);
define('EC_NO_COMPLAIN',159);
define('EC_SQL_ASSIGN',1510);

define('EECA_OK',160);
define('EECA_NO_ACTION',161);
define('EECA_NO_COMPLAIN',162);
define('EECA_NO_EXEC',163);
define('EECA_SQL_FORUM',164);

define('EUC_OK',170);
define('EUC_SQL_SELECT',171);
define('EUC_NO_USER',172);
define('EUC_SQL_CONFIRM',173);

define('ECHP_OK',180);
define('ECHP_NO_PERSON',181);
define('ECHP_SQL_INSERT',182);
define('ECHP_NO_ADD',183);

define('EIU_OK',190);
define('EIU_IMAGE_LARGE',191);
define('EIU_UNKNOWN_IMAGE',192);
define('EIU_IMAGE_SQL',193);
define('EIU_UNKNOWN_THUMBNAIL',194);

define('EFA_OK',200);
define('EFA_NO_SEND',201);
define('EFA_NO_EDIT',202);
define('EFA_BODY_ABSENT',203);
define('EFA_IMAGE_ABSENT',204);
define('EFA_NO_IMAGE',205);
define('EFA_NO_UP',206);
define('EFA_STORE_SQL',207);

define('EMI_OK',210);
define('EMI_NO_EDIT',211);
define('EMI_NAME_ABSENT',212);
define('EMI_IDENT_ABSENT',213);
define('EMI_IDENT_UNIQUE',214);
define('EMI_STORE_SQL',215);

define('EMIX_OK',220);
define('EMIX_NO_EDIT',221);
define('EMIX_NO_FIRST',222);
define('EMIX_NO_SECOND',223);
define('EMIX_SQL_FIRST',224);
define('EMIX_SQL_SECOND',225);

define('EMID_OK',230);
define('EMID_NO_EDIT',231);
define('EMID_NO_ITEM',232);
define('EMID_SQL',233);

define('ELIM_OK',240);
define('ELIM_NO_EDIT',241);
define('ELIM_DELETE_SQL',242);
define('ELIM_IMAGE_ABSENT',243);
define('ELIM_SET_SQL',244);
define('ELIM_SETID_SQL',245);

define('ELII_OK',250);
define('ELII_NO_EDIT',251);
define('ELII_MESSAGE_ABSENT',252);
define('ELII_IMAGE_ABSENT',253);
define('ELII_STORE_SQL',254);

define('ELID_OK',260);
define('ELID_NO_EDIT',261);
define('ELID_DELETE_SQL',262);

define('ECAM_OK',270);
define('ECAM_NO_EDIT',271);
define('ECAM_NO_TYPE',272);
define('ECAM_NAME_ABSENT',273);
define('ECAM_TEXT_ABSENT',274);
define('ECAM_NO_SCRIPT',275);
define('ECAM_ILLEGAL_SCRIPT',276);
define('ECAM_STORE_SQL',277);

define('ECAD_OK',280);
define('ECAD_NO_EDIT',281);
define('ECAD_NO_ACTION',282);
define('ECAD_DELETE_SQL',283);

define('EUL_OK',290);
define('EUL_LARGE',291);

define('EUS_OK',300);
define('EUS_NO_SWITCH',301);
define('EUS_NO_USER',302);
define('EUS_SQL',303);

define('ELO_OK',310);
define('ELO_SQL_GET',311);
define('ELO_SQL_SWITCH',312);
define('ELO_SQL_DROP',313);

define('EMR_OK',320);
define('EMR_NO_RENEW',321);
define('EMR_SQL',322);
define('EMR_NO_MESSAGE',323);

define('EO_OK',330);
define('EO_NO_REORDER',331);
define('EO_DUPS',332);
define('EO_SQL',333);
define('EO_NO_ARTICLE',334);

define('EV_OK',340);
define('EV_NO_POSTING',341);
define('EV_ALREADY_VOTED',342);
define('EV_SQL_VOTES',343);
define('EV_SQL_POSTINGS',344);

define('EDM_OK',350);
define('EDM_NO_CHANGE',351);
define('EDM_NO_POSTING',352);
?>
