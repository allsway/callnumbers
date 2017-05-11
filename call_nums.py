#!/usr/bin/python
import requests
import sys
import csv
import configparser
import logging
import collections
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

#
def update_xml(holding,results):
    marc_852 = holding.find('record/datafield[@tag="852"]')
    subfield_h = marc_852.find('subfield[@code="h"]')
    subfield_h.text = results['bib_subfield_a']
    subfield_i = ET.SubElement(marc_852,'subfield')
    subfield_i.set("code","i")
    subfield_i.text = results['bib_subfield_b']
    return holding
#
def put_holding_xml(holding,url):
    headers = {"Content-Type": "application/xml"}
	r = requests.put(url,data=ET.tostring(holding),headers=headers)
	print (r.content)

# returns elementtree element for given call number and subfield
def get_subfield(record,marc_tag,subfield):
    return record.find('record/datafield[@tag="'+ marc_tag + '"]/subfield[@code="' + subfield + '"]')

# compares bib record and holding record call numbers
def compare_call_nums(bib,holding):
    if(bib.lower() == holding.lower()):
        return 1
    else:
        return 0

# if there's already a $i, no need to check bib record call number.  Otherwise, return holding call number $h
def get_holding_call_num(holding):
    if get_subfield(holding,'852','i'):
        return False
    else
        return get_subfield(holding,'852','h')

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
    for call_num in call_num_array:
        if match_found == 0:
            if get_subfield(bib,call_num,'a'):
                bib_subfield_a = get_subfield(bib,call_num,'a')
                if get_subfield(bib,call_num,'b'):
                    bib_subfield_b = get_subfield(bib,call_num,'b')
                    total_bib = bib_subfield_a.text.strip() + bib_subfield_b.text.strip()
                    match_found = compare_call_nums(total_bib,holding_call_num.strip())
    return_array['bib_subfield_a'] = bib_subfield_a
    return_array['bib_subfield_b'] = bib_subfield_b
    return_array['match'] = match_found
    return return_array

# parses bib record xml file
def read_results(results):
    bib = ET.parse(results)
    bib_id = bib.find('record/controlfield[@tag=001]').text
    for holding in bib.findall('record/datafield[@tag=852]')
        holding_id = holding.find('subfield[@code=8]')
        if holding_id:
            url = return_url(bib_id,holding_id)
            holding = get_holding_xml(url)
            holding_call_num = get_holding_call_num(holding)
            if holding_call_num:
                results = check_matching_callnum(bib,holding_call_num)
                if results['match'] == 1:
                    holding = update_xml(holding,results)
                    put_holding_xml(url,holding)
        finally:
            f.close()


logging.basicConfig(filename='status.log',level=logging.DEBUG)
config = configparser.ConfigParser()
config.read(sys.argv[1])
results = sys.argv[2]
read_results(results)
