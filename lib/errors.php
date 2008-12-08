<?php
# @(#) $Id$

define('EG_OK',0);

define('EUM_PASSWORD_LEN',100);
define('EUM_PASSWORD_DIFF',101);
define('EUM_LOGIN_ABSENT',102);
define('EUM_LOGIN_EXISTS',103);
define('EUM_GENDER',104);
define('EUM_BIRTHDAY',105);
define('EUM_NO_EDIT',106);
define('EUM_NAME_ABSENT',107);
define('EUM_SURNAME_ABSENT',108);
define('EUM_EMAIL_ABSENT',109);
define('EUM_NOT_EMAIL',1010);
define('EUM_DISABLED',1011);
define('EUM_CAPTCHA_ABSENT',1012);
define('EUM_CAPTCHA',1013);

define('EL_INVALID',110);
define('EL_NO_COOKIES',111);
define('EL_GUEST_LOGIN_ABSENT',112);

define('ET_NAME_ABSENT',120);
define('ET_DESCRIPTION_ABSENT',121);
define('ET_NO_EDIT',122);
define('ET_IDENT_UNIQUE',123);
define('ET_NO_USER',124);
define('ET_NO_GROUP',125);
define('ET_BAD_PERMS',126);
define('ET_NO_APPEND',127);

define('EP_NO_EDIT',130);
define('EP_BODY_ABSENT',131);
define('EP_SUBJECT_ABSENT',132);
define('EP_TOPIC_ABSENT',133);
define('EP_NO_PERSON',134);
define('EP_IMAGE_ABSENT',135);
define('EP_NO_IMAGE',136);
define('EP_LARGE_BODY_ABSENT',137);
define('EP_IDENT_ABSENT',138);
define('EP_IDENT_UNIQUE',139);
define('EP_INVALID_GRP',1310);
define('EP_AUTHOR_ABSENT',1311);
define('EP_SOURCE_ABSENT',1312);
define('EP_TOPIC_ACCESS',1313);
define('EP_URL_ABSENT',1314);
define('EP_INDEX1_ABSENT',1315);
define('EP_LANG_ABSENT',1316);
define('EP_UP_APPEND',1317);
define('EP_LARGE_IMAGE_EXACT',1318);
define('EP_LARGE_IMAGE_EXACT_X',1319);
define('EP_LARGE_IMAGE_EXACT_Y',1320);
define('EP_LARGE_IMAGE_MAX',1321);
define('EP_LARGE_IMAGE_MAX_X',1322);
define('EP_LARGE_IMAGE_MAX_Y',1323);
define('EP_SMALL_IMAGE_EXACT',1324);
define('EP_SMALL_IMAGE_EXACT_X',1325);
define('EP_SMALL_IMAGE_EXACT_Y',1326);
define('EP_SMALL_IMAGE_MAX',1327);
define('EP_SMALL_IMAGE_MAX_X',1328);
define('EP_SMALL_IMAGE_MAX_Y',1329);
define('EP_CAPTCHA_ABSENT',1330);
define('EP_CAPTCHA',1331);
define('EP_SPAM',1332);

define('EMH_NO_MODERATE',140);
define('EMH_NO_ENTRY',141);

define('EC_NO_SEND',150);
define('EC_NO_EDIT',151);
define('EC_SUBJECT_ABSENT',152);
define('EC_BODY_ABSENT',153);
define('EC_LOGIN_ASSIGN',154);
define('EC_NO_ASSIGN',155);
define('EC_NO_COMPLAIN',156);
define('EC_NO_AUTO',157);
define('EC_CAPTCHA_ABSENT',158);
define('EC_CAPTCHA',159);

define('EECA_NO_ACTION',160);
define('EECA_NO_COMPLAIN',161);
define('EECA_NO_EXEC',162);

define('EUC_NO_USER',170);
define('EUC_ALREADY_CONFIRMED',171);

define('ECHP_NO_PERSON',180);
define('ECHP_NO_ADD',181);

define('EIU_IMAGE_LARGE',190);
define('EIU_UNKNOWN_IMAGE',191);
define('EIU_UNKNOWN_THUMBNAIL',192);
define('EIU_THUMBNAIL_LARGE',193);

define('EF_NO_SEND',200);
define('EF_NO_EDIT',201);
define('EF_NO_PARENT',202);
define('EF_UP_APPEND',203);
define('EF_BODY_ABSENT',204);
define('EF_IMAGE_ABSENT',205);
define('EF_NO_IMAGE',206);
define('EF_SUBJECT_ABSENT',207);
define('EF_AUTHOR_ABSENT',208);
define('EF_CAPTCHA_ABSENT',209);
define('EF_CAPTCHA',2010);

define('EMI_NO_EDIT',210);
define('EMI_NAME_ABSENT',211);
define('EMI_IDENT_ABSENT',212);
define('EMI_IDENT_UNIQUE',213);

define('EMIX_NO_EDIT',220);
define('EMIX_NO_FIRST',221);
define('EMIX_NO_SECOND',222);

define('EMID_NO_EDIT',230);
define('EMID_NO_ITEM',231);

define('ELIM_NO_EDIT',240);
define('ELIM_NO_POSTING',241);
define('ELIM_POSTING_APPEND',242);
define('ELIM_IMAGE_ABSENT',243);
define('ELIM_POSTING_WRITE',244);

define('ELID_NO_EDIT',260);
define('ELID_POSTING_APPEND',261);

define('ECAM_NO_EDIT',270);
define('ECAM_NAME_ABSENT',271);
define('ECAM_TEXT_ABSENT',272);
define('ECAM_NO_SCRIPT',273);

define('ECAD_NO_EDIT',280);
define('ECAD_NO_ACTION',281);

define('EUL_LARGE',290);
define('EUL_UNKNOWN_FORMAT',291);

define('EUS_NO_SWITCH',300);
define('EUS_NO_USER',301);

define('EMR_NO_RENEW',310);
define('EMR_NO_ENTRY',311);

define('EO_NO_REORDER',320);
define('EO_DUPS',321);
define('EO_NO_ENTRY',322);
define('EO_LITTLE',323);

define('EV_NO_POSTING',330);
define('EV_ALREADY_VOTED',331);
define('EV_INVALID_VOTE',332);

define('EDM_NO_CHANGE',340);
define('EDM_NO_POSTING',341);

define('EPL_NO_LOGIN',350);
define('EPL_NOT_CONFIRMED',351);

define('ECEA_NO_ADD',360);
define('ECEA_ENTRY_ABSENT',361);
define('ECEA_NO_SOURCE',362);
define('ECEA_NO_PATH',363);
define('ECEA_PATH_ABSENT',364);
define('ECEA_NO_LINKING',365);

define('ETLD_NO_DEL',370);

define('ESP_NO_SHADOW',380);
define('ESP_POSTING_ABSENT',381);

define('ETD_DEST_ABSENT',390);
define('ETD_NO_DELETE',391);
define('ETD_TOPIC_ABSENT',392);
define('ETD_DEST_ACCESS',393);

define('EH_NO_MODIFY',400);

define('EPD_NO_DELETE',410);
define('EPD_POSTING_ABSENT',411);

define('EGA_USER_EMPTY',420);
define('EGA_GROUP_EMPTY',421);
define('EGA_NO_USER',422);
define('EGA_NO_GROUP',423);
define('EGA_NO_ADD',424);

define('EGD_NO_USER',430);
define('EGD_NO_GROUP',431);
define('EGD_NO_DELETE',432);

define('ECHM_NO_CHMOD',440);
define('ECHM_USER_EMPTY',441);
define('ECHM_NO_USER',442);
define('ECHM_GROUP_EMPTY',443);
define('ECHM_NO_GROUP',444);
define('ECHM_BAD_PERMS',445);

define('ETO_NO_REORDER',450);
define('ETO_DUPS',451);
define('ETO_NO_TOPIC',452);

define('EMO_NO_MODERATE',470);
define('EMO_NO_ENTRY',471);
define('EMO_CANNOT_DELETE',472);

define('EMS_NO_HIDE',480);
define('EMS_NO_ENTRY',481);

define('EPC_NO_COPY',490);
define('EPC_NO_POSTING',491);

define('EVH_NO_PARENT',500);
define('EVH_NO_UP',501);
define('EVH_NOT_UP_UNDER_PARENT',502);
define('EVH_LOOP',503);
define('EVH_INCORRECT',504);

define('ESM_SEND',510);
define('ESM_NO_USER',511);
?>
