# Alma call number updater
Compares the holding record call number to the bib record call number fields: 050, 090, 086, 099 and 055.  If the call numbers are identical and the bib record call number has a $b delimiter but the holding call number does not have a $i delimiter, updates the holding call number to the bib record call number in order to add the $i delimiter.  

#### config.txt
```[Params]
apikey = [api key] 
baseurl = https://api-na.hosted.exlibrisgroup.com
```

#### call_nums.py
Iterates through bib record call numbers and compares to holding call number to see if there is a match.  If a match exists, updates the holding record $i field and places a PUT request to the Alma holding API. Parameters:
- config.txt 
- File of bib record exported from Alma in XML format with 'Add Holdings Information' selected (bib_record_export.xml)

Run as:
```
python ./call_nums.py config.txt bib_record_export.xml
```

#### match_results.csv
Report file created by call_nums.py that contains:
```
[Match (0 or 1)][Bib record MMS ID][Holding MMS ID][Holding Call Number][Bib call number]
```
