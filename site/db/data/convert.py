#!/usr/bin/python3

import sys
import csv

def null_time(time):
    if time == '0000-00-00 00:00:00':
        return 'NULL'
    else:
        return time

def null_id(id):
    if id == '0':
        return 'NULL'
    else:
        return id

def convert_users(row):
    if row['birthday'] != '1900-01-01':
        birth = row['birthday'].split('-')
    else:
        birth = (0, 0, 0)
    if row['gender'] == 'mine':
        gender = 0
    else:
        gender = 1
    return [
        row['id'],
        row['login'],
        row['password'],
        row['name'],
        row['jewish_name'],
        row['surname'],
        row['info'],
        row['info_xml'],
        row['created'],
        row['modified'],
        null_time(row['last_online']),
        row['confirm_deadline'],
        row['confirm_code'],
        row['email'],
        row['hide_email'],
        row['email_disabled'],
        row['shames'],
        row['guest'],
        row['rights'],
        row['hidden'],
        row['no_login'],
        row['has_personal'],
        row['settings'],
        gender,
        birth[2],
        birth[1],
        birth[0]
    ]

def convert_entries(row):
    if row['group_id'] == '172':
        row['group_id'] = row['user_id']
    if row['user_id'] == '165':
        row['creator_id'] = '165'
        row['modifier_id'] = 'NULL'
    row['subject'] = row['subject'].replace('&#171;', '\u00ab')\
                                   .replace('&#187;', '\u00bb')\
                                   .replace('&#x00ab;', '\u00ab')\
                                   .replace('&#x00bb;', '\u00bb')\
                                   .replace('&#8212;', '\u2014')\
                                   .replace('&#x2014;', '\u2014')\
                                   .replace('&#x2013;', '\u2013')\
                                   .replace('&#8220;', '\u201c')\
                                   .replace('&#8221;', '\u201d')\
                                   .replace('&#x201c;', '\u201c')\
                                   .replace('&#x201d;', '\u201d')\
                                   .replace('&#x2026;', '\u2026')\
                                   .replace('&#x2019;', '\u2019')\
                                   .replace('&#x201e;', '\u201e')\
                                   .replace('&#x0453;', '\u0453')\
                                   .replace('&#x0403;', '\u0403')\
                                   .replace('&#x05de;', '\u05de')\
                                   .replace('&#x05ea;', '\u05ea')\
                                   .replace('&#x05d9;', '\u05d9')\
                                   .replace('&#x2116;', '\u2116')\
                                   .replace('&#8470;', '\u2116')
    return [
        row['id'],
        row['ident'],
        row['entry'],
        null_id(row['up']),
        row['track'],
        row['catalog'],
        null_id(row['parent_id']),
        null_id(row['orig_id']),
        null_id(row['current_id']),
        row['grp'],
        null_id(row['person_id']),
        row['guest_login'],
        null_id(row['user_id']),
        null_id(row['group_id']),
        row['perms'],
        row['disabled'],
        row['subject'],
        row['lang'],
        row['author'],
        row['author_xml'],
        row['source'],
        row['source_xml'],
        row['title'],
        row['title_xml'],
        row['comment0'],
        row['comment0_xml'],
        row['comment1'],
        row['comment1_xml'],
        row['url'],
        row['url_domain'],
        null_time(row['url_check']),
        null_time(row['url_check_success']),
        row['body'],
        row['body_xml'],
        row['body_format'],
        row['has_large_body'],
        row['large_body'],
        row['large_body_xml'],
        row['large_body_format'],
        row['large_body_filename'],
        row['priority'],
        row['index0'],
        row['index1'],
        row['index2'],
        row['set0'],
        row['set0_index'],
        row['set1'],
        row['set1_index'],
        row['vote'],
        row['vote_count'],
        row['rating'],
        null_time(row['sent']),
        row['created'],
        row['modified'],
        null_time(row['accessed']),
        null_id(row['creator_id']),
        null_id(row['modifier_id']),
        row['modbits'],
        row['answers'],
        null_time(row['last_answer']),
        null_id(row['last_answer_id']),
        null_id(row['last_answer_user_id']),
        row['last_answer_guest_login'],
        row['small_image'],
        row['small_image_x'],
        row['small_image_y'],
        row['small_image_format'],
        row['large_image'],
        row['large_image_x'],
        row['large_image_y'],
        row['large_image_size'],
        row['large_image_format'],
        row['large_image_filename']
    ]

csv.field_size_limit(300000)
table_name = sys.argv[1]
with open(table_name + '.csv', 'r') as infile:
    with open(table_name + '.converted.csv', 'w') as outfile:
        reader = csv.DictReader(infile)
        writer = csv.writer(outfile)
        try:
            converter = globals()['convert_' + table_name]
        except KeyError:
            converter = None
        for row in reader:
            if converter is not None:
                result = converter(row)
            else:
                result = row.values()
            writer.writerow(result)
