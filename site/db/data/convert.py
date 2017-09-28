#!/usr/bin/python3

import sys
import csv

entry_grps = {}

def read_entry_grps():
    global entry_grps

    with open('entry_grps.csv', 'r') as infile:
        reader = csv.DictReader(infile)
        for row in reader:
            id = row['entry_id']
            grp = int(row['grp'])
            if id not in entry_grps:
                entry_grps[id] = grp
            else:
                entry_grps[id] |= grp

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

def html_entities(s):
    return s.replace('&#171;', '\u00ab')\
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

def convert_users(row):
    if row['birthday'] != '1900-01-01':
        birth = row['birthday'].split('-')
    else:
        birth = (0, 0, 0)
    if row['gender'] == 'mine':
        gender = 0
    else:
        gender = 1
    if row['jewish_name'] != row['name']:
        jewish_name = row['jewish_name']
    else:
        jewish_name = ''
    return [
        row['id'],
        row['login'],
        row['password'],
        row['name'],
        jewish_name,
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
    global entry_grps

    if row['group_id'] == '172':
        row['group_id'] = row['user_id']
    if row['user_id'] == '165':
        row['creator_id'] = '165'
        row['modifier_id'] = 'NULL'
    if row['id'] in entry_grps:
        row['grp'] = str(entry_grps[row['id']])
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
        html_entities(row['subject']),
        row['lang'],
        html_entities(row['author']),
        row['author_xml'],
        html_entities(row['source']),
        row['source_xml'],
        html_entities(row['title']),
        row['title_xml'],
        html_entities(row['comment0']),
        row['comment0_xml'],
        html_entities(row['comment1']),
        row['comment1_xml'],
        row['url'],
        row['url_domain'],
        null_time(row['url_check']),
        null_time(row['url_check_success']),
        html_entities(row['body']),
        row['body_xml'],
        row['body_format'],
        row['has_large_body'],
        html_entities(row['large_body']),
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
        null_id(row['small_image']),
        row['small_image_x'],
        row['small_image_y'],
        row['small_image_format'],
        null_id(row['large_image']),
        row['large_image_x'],
        row['large_image_y'],
        row['large_image_size'],
        row['large_image_format'],
        row['large_image_filename']
    ]

def convert_cross_entries(row):
    return [
        row['id'],
        row['source_name'],
        row['source_id'],
        row['link_type'],
        row['peer_name'],
        row['peer_id'],
        row['peer_path'],
        row['peer_subject'],
        row['peer_icon']
    ]

def convert_image_files(row):
    return [
        row['id'],
        row['mime_type'],
        row['size_x'],
        row['size_y'],
        row['file_size'],
        null_time(row['created']),
        null_time(row['accessed']),
    ]

def convert_image_file_transforms(row):
    return [
        row['id'],
        row['dest_id'],
        row['orig_id'],
        row['transform'],
        row['size_x'],
        row['size_y'],
    ]

csv.field_size_limit(300000)
read_entry_grps()
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
