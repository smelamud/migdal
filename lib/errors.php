<?php
# @(#) $Id$

define('EUM_OK',100);
define('EUM_PASSWORD_LEN',101);
define('EUM_PASSWORD_DIFF',102);
define('EUM_LOGIN_ABSENT',103);
define('EUM_LOGIN_EXISTS',104);
define('EUM_GENDER',105);
define('EUM_BIRTHDAY',106);
define('EUM_NO_EDIT',107);
define('EUM_NAME_ABSENT',108);
define('EUM_SURNAME_ABSENT',109);
define('EUM_EMAIL_ABSENT',1010);
define('EUM_NOT_EMAIL',1011);
define('EUM_DISABLED',1012);

define('EL_INVALID',110);
define('EL_OK',111);
define('EL_NO_COOKIES',112);

define('ET_OK',120);
define('ET_NAME_ABSENT',121);
define('ET_DESCRIPTION_ABSENT',122);
define('ET_NO_EDIT',123);
define('ET_IDENT_UNIQUE',124);
define('ET_NO_UP',125);
define('ET_LOOP_UP',126);
define('ET_NO_USER',127);
define('ET_NO_GROUP',128);
define('ET_BAD_PERMS',129);
define('ET_NO_APPEND',1210);

define('EP_OK',130);
define('EP_NO_EDIT',131);
define('EP_BODY_ABSENT',132);
define('EP_SUBJECT_ABSENT',133);
define('EP_TOPIC_ABSENT',134);
define('EP_NO_TOPIC',135);
define('EP_NO_PERSONAL',136);
define('EP_IMAGE_ABSENT',137);
define('EP_NO_IMAGE',138);
define('EP_LARGE_BODY_ABSENT',139);
define('EP_IDENT_ABSENT',1310);
define('EP_IDENT_UNIQUE',1311);
define('EP_INVALID_GRP',1312);
define('EP_AUTHOR_ABSENT',1313);
define('EP_SOURCE_ABSENT',1314);
define('EP_TOPIC_ACCESS',1315);
define('EP_URL_ABSENT',1316);
define('EP_INDEX1_ABSENT',1317);
define('EP_NO_UP',1318);
define('EP_LOOP_UP',1319);
define('EP_LANG_ABSENT',1320);
define('EP_UP_APPEND',1321);

define('EMH_OK',140);
define('EMH_NO_MODERATE',141);
define('EMH_NO_MESSAGE',142);

define('EC_OK',150);
define('EC_NO_SEND',151);
define('EC_NO_EDIT',152);
define('EC_SUBJECT_ABSENT',153);
define('EC_BODY_ABSENT',154);
define('EC_NO_TYPE',155);
define('EC_LOGIN_ASSIGN',156);
define('EC_NO_ASSIGN',157);
define('EC_NO_COMPLAIN',158);
define('EC_NO_AUTO',159);

define('EECA_OK',160);
define('EECA_NO_ACTION',161);
define('EECA_NO_COMPLAIN',162);
define('EECA_NO_EXEC',163);

define('EUC_OK',170);
define('EUC_NO_USER',171);

define('ECHP_OK',180);
define('ECHP_NO_PERSON',181);
define('ECHP_NO_ADD',182);

define('EIU_OK',190);
define('EIU_IMAGE_LARGE',191);
define('EIU_UNKNOWN_IMAGE',192);
define('EIU_UNKNOWN_THUMBNAIL',193);

define('EFA_OK',200);
define('EFA_NO_SEND',201);
define('EFA_NO_EDIT',202);
define('EFA_BODY_ABSENT',203);
define('EFA_IMAGE_ABSENT',204);
define('EFA_NO_IMAGE',205);
define('EFA_NO_UP',206);

define('EMI_OK',210);
define('EMI_NO_EDIT',211);
define('EMI_NAME_ABSENT',212);
define('EMI_IDENT_ABSENT',213);
define('EMI_IDENT_UNIQUE',214);

define('EMIX_OK',220);
define('EMIX_NO_EDIT',221);
define('EMIX_NO_FIRST',222);
define('EMIX_NO_SECOND',223);

define('EMID_OK',230);
define('EMID_NO_EDIT',231);
define('EMID_NO_ITEM',232);

define('ELIM_OK',240);
define('ELIM_NO_EDIT',241);
define('ELIM_IMAGE_ABSENT',242);

define('ELII_OK',250);
define('ELII_NO_EDIT',251);
define('ELII_MESSAGE_ABSENT',252);
define('ELII_IMAGE_ABSENT',253);

define('ELID_OK',260);
define('ELID_NO_EDIT',261);

define('ECAM_OK',270);
define('ECAM_NO_EDIT',271);
define('ECAM_NO_TYPE',272);
define('ECAM_NAME_ABSENT',273);
define('ECAM_TEXT_ABSENT',274);
define('ECAM_NO_SCRIPT',275);
define('ECAM_ILLEGAL_SCRIPT',276);

define('ECAD_OK',280);
define('ECAD_NO_EDIT',281);
define('ECAD_NO_ACTION',282);

define('EUL_OK',290);
define('EUL_LARGE',291);

define('EUS_OK',300);
define('EUS_NO_SWITCH',301);
define('EUS_NO_USER',302);

define('ELO_OK',310);

define('EMR_OK',320);
define('EMR_NO_RENEW',321);
define('EMR_NO_MESSAGE',322);

define('EO_OK',330);
define('EO_NO_REORDER',331);
define('EO_DUPS',332);
define('EO_NO_ARTICLE',333);

define('EV_OK',340);
define('EV_NO_POSTING',341);
define('EV_ALREADY_VOTED',342);

define('EDM_OK',350);
define('EDM_NO_CHANGE',351);
define('EDM_NO_POSTING',352);

define('EPL_OK',360);
define('EPL_NO_LOGIN',361);

define('ETLA_OK',370);
define('ETLA_NO_ADD',371);
define('ETLA_TOPIC_ABSENT',372);

define('ETLD_OK',380);
define('ETLD_NO_DEL',381);

define('ESP_OK',390);
define('ESP_NO_SHADOW',391);
define('ESP_POSTING_ABSENT',392);

define('ETD_OK',400);
define('ETD_DEST_ABSENT',401);
define('ETD_NO_DELETE',402);
define('ETD_TOPIC_ABSENT',403);
define('ETD_DEST_ACCESS',404);

define('EH_OK',410);
define('EH_NO_MODIFY',411);

define('EPD_OK',420);
define('EPD_NO_DELETE',421);
define('EPD_POSTING_ABSENT',422);

define('EGA_OK',430);
define('EGA_USER_EMPTY',431);
define('EGA_GROUP_EMPTY',432);
define('EGA_NO_USER',433);
define('EGA_NO_GROUP',434);
define('EGA_NO_ADD',435);

define('EGD_OK',440);
define('EGD_NO_USER',441);
define('EGD_NO_GROUP',442);
define('EGD_NO_DELETE',443);

define('ECHM_OK',450);
define('ECHM_NO_USER',451);
define('ECHM_NO_GROUP',452);
define('ECHM_NO_ADD',453);
define('ECHM_BAD_PERMS',454);

define('ETO_OK',460);
define('ETO_NO_REORDER',461);
define('ETO_DUPS',462);
define('ETO_NO_TOPIC',463);

define('EMM_OK',470);
define('EMM_NO_MODERATE',471);
define('EMM_NO_MESSAGE',472);

define('EMO_OK',480);
define('EMO_NO_MODERATE',481);
define('EMO_NO_MESSAGE',482);

define('EMS_OK',490);
define('EMS_NO_HIDE',491);
define('EMS_NO_MESSAGE',492);

define('EPC_OK',500);
define('EPC_NO_COPY',501);
define('EPC_NO_POSTING',502);
?>
