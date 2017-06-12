#!/usr/bin/python3

import sys
import csv

def convert_users(row):
    if row['birthday'] != '1900-01-01':
        birth = row['birthday'].split('-')
    else:
        birth = (0, 0, 0)
    if row['gender'] == 'mine':
        gender = 0
    else:
        gender = 1
    if row['last_online'] == '0000-00-00 00:00:00':
        last_online = 'NULL'
    else:
        last_online = row['last_online']
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
        last_online,
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

table_name = sys.argv[1]
with open(table_name + '.csv', 'r') as infile:
    with open(table_name + '.converted.csv', 'w') as outfile:
        reader = csv.DictReader(infile)
        writer = csv.writer(outfile)
        converter = globals()['convert_' + table_name]
        for row in reader:
            if converter is not None:
                result = converter(row)
            else:
                result = row.values()
            writer.writerow(result)
