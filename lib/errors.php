<?php
# @(#) $Id$

const EG_OK = 0;

const EUM_PASSWORD_LEN = 100;
const EUM_PASSWORD_DIFF = 101;
const EUM_LOGIN_ABSENT = 102;
const EUM_LOGIN_EXISTS = 103;
const EUM_GENDER = 104;
const EUM_BIRTHDAY = 105;
const EUM_NO_EDIT = 106;
const EUM_NAME_ABSENT = 107;
const EUM_SURNAME_ABSENT = 108;
const EUM_EMAIL_ABSENT = 109;
const EUM_NOT_EMAIL = 1010;
const EUM_DISABLED = 1011;
const EUM_CAPTCHA_ABSENT = 1012;
const EUM_CAPTCHA = 1013;

const EL_INVALID = 110;
const EL_NO_COOKIES = 111;
const EL_GUEST_LOGIN_ABSENT = 112;

const ET_NAME_ABSENT = 120;
const ET_DESCRIPTION_ABSENT = 121;
const ET_NO_EDIT = 122;
const ET_IDENT_UNIQUE = 123;
const ET_NO_USER = 124;
const ET_NO_GROUP = 125;
const ET_BAD_PERMS = 126;
const ET_NO_APPEND = 127;

const EP_NO_EDIT = 130;
const EP_BODY_ABSENT = 131;
const EP_SUBJECT_ABSENT = 132;
const EP_TOPIC_ABSENT = 133;
const EP_NO_PERSON = 134;
const EP_IMAGE_ABSENT = 135;
const EP_LARGE_BODY_ABSENT = 137;
const EP_IDENT_ABSENT = 138;
const EP_IDENT_UNIQUE = 139;
const EP_INVALID_GRP = 1310;
const EP_AUTHOR_ABSENT = 1311;
const EP_SOURCE_ABSENT = 1312;
const EP_TOPIC_ACCESS = 1313;
const EP_URL_ABSENT = 1314;
const EP_INDEX1_ABSENT = 1315;
const EP_LANG_ABSENT = 1316;
const EP_UP_APPEND = 1317;
const EP_LARGE_IMAGE_EXACT = 1318;
const EP_LARGE_IMAGE_EXACT_X = 1319;
const EP_LARGE_IMAGE_EXACT_Y = 1320;
const EP_LARGE_IMAGE_MAX = 1321;
const EP_LARGE_IMAGE_MAX_X = 1322;
const EP_LARGE_IMAGE_MAX_Y = 1323;
const EP_SMALL_IMAGE_EXACT = 1324;
const EP_SMALL_IMAGE_EXACT_X = 1325;
const EP_SMALL_IMAGE_EXACT_Y = 1326;
const EP_SMALL_IMAGE_MAX = 1327;
const EP_SMALL_IMAGE_MAX_X = 1328;
const EP_SMALL_IMAGE_MAX_Y = 1329;
const EP_CAPTCHA_ABSENT = 1330;
const EP_CAPTCHA = 1331;
const EP_SPAM = 1332;

const EMH_NO_MODERATE = 140;
const EMH_NO_ENTRY = 141;

const EUC_NO_USER = 170;
const EUC_ALREADY_CONFIRMED = 171;

const ECHP_NO_PERSON = 180;
const ECHP_NO_ADD = 181;

const EIU_IMAGE_LARGE = 190;
const EIU_UNKNOWN_IMAGE = 191;
const EIU_UNKNOWN_THUMBNAIL = 192;
const EIU_THUMBNAIL_LARGE = 193;
const EIU_INTERNAL_ERROR = 194;
const EIU_WRONG_IMAGE_SIZE = 195;
const EIU_WRONG_THUMBNAIL_SIZE = 196;

const EF_NO_SEND = 200;
const EF_NO_EDIT = 201;
const EF_NO_PARENT = 202;
const EF_UP_APPEND = 203;
const EF_BODY_ABSENT = 204;
const EF_IMAGE_ABSENT = 205;
const EF_NO_IMAGE = 206;
const EF_SUBJECT_ABSENT = 207;
const EF_AUTHOR_ABSENT = 208;
const EF_CAPTCHA_ABSENT = 209;
const EF_CAPTCHA = 2010;

const EMI_NO_EDIT = 210;
const EMI_NAME_ABSENT = 211;
const EMI_IDENT_ABSENT = 212;
const EMI_IDENT_UNIQUE = 213;

const EMIX_NO_EDIT = 220;
const EMIX_NO_FIRST = 221;
const EMIX_NO_SECOND = 222;

const EMID_NO_EDIT = 230;
const EMID_NO_ITEM = 231;

const ELIM_NO_EDIT = 240;
const ELIM_NO_POSTING = 241;
const ELIM_POSTING_APPEND = 242;
const ELIM_IMAGE_ABSENT = 243;
const ELIM_POSTING_WRITE = 244;

const ELID_NO_EDIT = 260;
const ELID_POSTING_APPEND = 261;

const ECAM_NO_EDIT = 270;
const ECAM_NAME_ABSENT = 271;
const ECAM_TEXT_ABSENT = 272;
const ECAM_NO_SCRIPT = 273;

const ECAD_NO_EDIT = 280;
const ECAD_NO_ACTION = 281;

const EUL_LARGE = 290;
const EUL_UNKNOWN_FORMAT = 291;

const EUS_NO_SWITCH = 300;
const EUS_NO_USER = 301;

const EMR_NO_RENEW = 310;
const EMR_NO_ENTRY = 311;

const EO_NO_REORDER = 320;
const EO_DUPS = 321;
const EO_NO_ENTRY = 322;
const EO_LITTLE = 323;

const EV_NO_POSTING = 330;
const EV_ALREADY_VOTED = 331;
const EV_INVALID_VOTE = 332;
const EV_NO_LOGIN = 333;

const EDM_NO_CHANGE = 340;
const EDM_NO_POSTING = 341;

const EPL_NO_LOGIN = 350;
const EPL_NOT_CONFIRMED = 351;

const ECEA_NO_ADD = 360;
const ECEA_ENTRY_ABSENT = 361;
const ECEA_NO_SOURCE = 362;
const ECEA_NO_PATH = 363;
const ECEA_PATH_ABSENT = 364;
const ECEA_NO_LINKING = 365;

const ETLD_NO_DEL = 370;

const ESP_NO_SHADOW = 380;
const ESP_POSTING_ABSENT = 381;

const ETD_DEST_ABSENT = 390;
const ETD_NO_DELETE = 391;
const ETD_TOPIC_ABSENT = 392;
const ETD_DEST_ACCESS = 393;

const EH_NO_MODIFY = 400;

const EPD_NO_DELETE = 410;
const EPD_POSTING_ABSENT = 411;

const EGA_USER_EMPTY = 420;
const EGA_GROUP_EMPTY = 421;
const EGA_NO_USER = 422;
const EGA_NO_GROUP = 423;
const EGA_NO_ADD = 424;

const EGD_NO_USER = 430;
const EGD_NO_GROUP = 431;
const EGD_NO_DELETE = 432;

const ECHM_NO_CHMOD = 440;
const ECHM_USER_EMPTY = 441;
const ECHM_NO_USER = 442;
const ECHM_GROUP_EMPTY = 443;
const ECHM_NO_GROUP = 444;
const ECHM_BAD_PERMS = 445;

const ETO_NO_REORDER = 450;
const ETO_DUPS = 451;
const ETO_NO_TOPIC = 452;

const EMO_NO_MODERATE = 470;
const EMO_NO_ENTRY = 471;
const EMO_CANNOT_DELETE = 472;

const EMS_NO_HIDE = 480;
const EMS_NO_ENTRY = 481;

const EPC_NO_COPY = 490;
const EPC_NO_POSTING = 491;

const EVH_NO_PARENT = 500;
const EVH_NO_UP = 501;
const EVH_NOT_UP_UNDER_PARENT = 502;
const EVH_LOOP = 503;
const EVH_INCORRECT = 504;

const ESM_SEND = 510;
const ESM_NO_USER = 511;
?>
