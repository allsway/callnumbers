#!/usr/bin/python
import requests
import sys
import csv
import configparser
import logging
import xml.etree.ElementTree as ET

# Returns the API key
def get_key():
    return config.get('Params', 'apikey')

# Returns the Alma API base URL
def get_base_url():
    return config.get('Params', 'baseurl')

# returns holding url
def return_url(bib_id,holding_id):
    url = get_base_url() + '/almaws/v1/bibs/' + bib_id + '/holdings/' + holding_id + '?apikey=' + get_key()
    return url

# returns holding xml
def get_holding_xml(url):
    response = requests.get(url)
    if response.status_code != 200:
        return None
    holding = ET.fromstring(response.content)
    return holding

# Updates the holding record 852$h and $i fields
def update_xml(holding,results):
    marc_852 = holding.find('record/datafield[@tag="852"]')
    subfield_h = marc_852.find('subfield[@code="h"]')
    subfield_h.text = results['bib_subfield_a']
    subfield_i = ET.SubElement(marc_852, 'subfield')
    subfield_i.set("code","i")
    subfield_i.text = results['bib_subfield_b']
#    marc_852.append(subfield_i)
    print(ET.tostring(holding))

# Places put request for holdign with updated 852 field
def put_holding_xml(url,holding):
    headers = {"Content-Type": "application/xml"}
    r = requests.put(url, data=ET.tostring(holding), headers=headers)
    print (r.content)

# returns elementtree element for given call number and subfield
def get_subfield(record,marc_tag,subfield):
    return record.find('datafield[@tag="'+ marc_tag + '"]/subfield[@code="' + subfield + '"]')

def get_holding_subfield(record,marc_tag,subfield):
    return record.find('record/datafield[@tag="'+ marc_tag + '"]/subfield[@code="' + subfield + '"]')


# compares bib record and holding record call numbers
def compare_call_nums(bib,holding):
    if(bib.replace(' ', '').lower() == holding.replace(' ', '').lower()):
        return 1
    else:
        return 0

# if there's already a $i, no need to check bib record call number.  Otherwise, return holding call number $h
def get_holding_call_num(holding):
    if get_holding_subfield(holding,'852','i') is not None:
        return False
    else:
        return get_holding_subfield(holding,'852','h')

"""
Iterates through bib call number tags
returns dict with call number subfields if match is found
"""
def check_matching_callnum(bib,holding_call_num):
    call_num_array = ['050','090','099','086','055']
    match_found = 0
    return_array = {}
    return_array['match'] = 0
    bib_subfield_a = ''
    bib_subfield_b = ''
    print (bib)
    for call_num in call_num_array:
        print ('bib: ' + call_num)
        if match_found == 0:
            if get_subfield(bib,call_num,'a') is not None:
                bib_subfield_a = get_subfield(bib,call_num,'a').text
                if get_subfield(bib,call_num,'b') is not None:
                    bib_subfield_b = get_subfield(bib,call_num,'b').text
                    total_bib = bib_subfield_a.strip() + bib_subfield_b.strip()
                    print (total_bib)
                    match_found = compare_call_nums(total_bib,holding_call_num.strip())
    return_array['bib_subfield_a'] = bib_subfield_a
    return_array['bib_subfield_b'] = bib_subfield_b
    return_array['match'] = match_found
    print (return_array)
    return return_array

# parses bib record xml file
def read_results(results):
    f = open('matchresults.csv', 'wt')
    try:
        writer = csv.writer(f)
        bibs = ET.parse(results)
        for bib in bibs.findall('record'):
            bib_id = bib.find('controlfield[@tag="001"]').text
            for holding in bib.findall('datafield[@tag="852"]'):
                holding_id = holding.find('subfield[@code="8"]').text
                print (holding_id)
                if holding_id is not None:
                    url = return_url(bib_id,holding_id)
                    print (url)
                    holding = get_holding_xml(url)
                    if holding:
                        holding_call_num = get_holding_call_num(holding)
                        print (holding_call_num)
                        if holding_call_num is not False:
                            results = check_matching_callnum(bib,holding_call_num.text)
                            print (results)
                            if results['match'] == 1:
                                update_xml(holding,results)
                                print(ET.tostring(holding))
                                put_holding_xml(url,holding)
                                string = results['match'] + ',' + bib_id,holding_id + ',' +  holding_call_num.text + ',' + results['bib_subfield_a'] + ' ' +  results['bib_subfield_b']
                        else:
                            logging.info('Subfield $i already exists for: ' + url)
                            string = results['match'] + ',' + bib_id,holding_id + ',' +  holding_call_num.text + ',' + results['bib_subfield_a'] + ' ' +  results['bib_subfield_b']
                        writer.writerow(string)
    finally:
        f.close()



logging.basicConfig(filename='status.log',level=logging.DEBUG)
config = configparser.ConfigParser()
config.read(sys.argv[1])
bib_records = sys.argv[2]
read_results(bib_records)
