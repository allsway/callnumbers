<?php

function create_set()
{
  $set_json['name'] = 'Sarina test creation of set';
  $set_json['description'] = 'Sarina is testing sets';
  $set_json['type'] = array('value' => 'ITEMIZED', 'desc' => 'Itemized');
  $set_json['content'] = array('value' => 'IEP', 'desc' => 'Physical Titles');
  $set_json['status'] = array('value' => 'ACTIVE', 'desc' => 'Active');
  $json = json_encode($set_json);
  return $json;
}

function post_set($url,$json)
{
  $curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
	$response = curl_exec($curl);
	curl_close($curl);
	return $response;
}


  /*
    Strip all spaces from call number
    Compare call number strings to see if they are equal
  */
  function compare_callnumbers($holding_callnumber,$bib_callnumber)
  {
    $holding_callnumber = str_replace(' ', '', $holding_callnumber);
    $bib_callnumber = str_replace(' ' , '', $bib_callnumber);
    if ($holding_callnumber == $bib_callnumber)
    {
      return 1;
    }
    else
    {
      return 0;
    }
  }

  /*
    Read in bib xml file
  */

  $file = $argv[1];
  $xml = simplexml_load_file($file);

  /*
    Look at each holding-enhanced bib record
  */
  $i = 0;
  foreach ($xml->record as $record)
  {
    $holding_ids = $record->xpath('datafield[@tag=852]/subfield[@code="8"]');
    $holding_callnumber = $record->xpath('datafield[@tag=852]/subfield[@code="h"]');
    $holding_callnumber_i = $record->xpath('datafield[@tag=852]/subfield[@code="i"]');
    # enter call number heirarchy choices
    $bib_callnumber_050_a = $record->xpath('datafield[@tag=050]/subfield[@code="a"]');
    $bib_callnumber_050_b = $record->xpath('datafield[@tag=050]/subfield[@code="b"]');
    $bib_callnumber_090_a = $record->xpath('datafield[@tag=090]/subfield[@code="a"]');
    $bib_callnumber_090_b = $record->xpath('datafield[@tag=090]/subfield[@code="b"]');
    $bib_callnumber_099_a = $record->xpath('datafield[@tag=099]/subfield[@code="a"]');
    $bib_callnumber_099_b = $record->xpath('datafield[@tag=099]/subfield[@code="b"]');
    $bib_callnumber_086_a = $record->xpath('datafield[@tag=086]/subfield[@code="a"]');
    $bib_callnumber_086_b = $record->xpath('datafield[@tag=086]/subfield[@code="b"]');
    $bib_callnumber_055_a = $record->xpath('datafield[@tag=055]/subfield[@code="a"]');
    $bib_callnumber_055_b = $record->xpath('datafield[@tag=055]/subfield[@code="b"]');



    if(isset($bib_callnumber_050_a[0]))
    {
      $bib_callnumber_a = $bib_callnumber_050_a[0].'';
      if(isset($bib_callnumber_050_b[0]))
      {
        $bib_callnumber_b = $bib_callnumber_050_b[0].'';
      }
    }
    else if(isset($bib_callnumber_090_a[0]))
    {
      $bib_callnumber_a = $bib_callnumber_090_a[0].'';
      if(isset($bib_callnumber_090_b[0]))
      {
        $bib_callnumber_b = $bib_callnumber_090_b[0].'';
      }
    }
    else if(isset($bib_callnumber_099_a[0]))
    {
      $bib_callnumber_a = $bib_callnumber_099_a[0].'';
      if(isset($bib_callnumber_099_b[0]))
      {
        $bib_callnumber_b = $bib_callnumber_099_b[0].'';
      }
    }
    else if(isset($bib_callnumber_086[0]))
    {
      $bib_callnumber_a = $bib_callnumber_086_a[0].'';
      if(isset($bib_callnumber_086_b[0]))
      {
        $bib_callnumber_b = $bib_callnumber_086_b[0].'';
      }
    }
    else if(isset($bib_callnumber_055_a[0]))
    {
      $bib_callnumber_a = $bib_callnumber_055_a[0].'';
      if(isset($bib_callnumber_055_b[0]))
      {
        $bib_callnumber_b = $bib_callnumber_055_b[0].'';
      }
    }
    else
    {
      $bib_callnumber_a = NULL;
      $bib_callnumber_b = NULL;
    }
    $ids_array = [];
    foreach ($holding_ids as $holding_id)
    {
      /*
        If holding call number exists, and bib call number doesn't
          Do nothing
        If bib call number exists and holding call number doesn't
          Bring down from the bib
        If both
          If call number in bib matches holding call number
            bring down call number from bib
        If call number in bib doesn't match holdings call number
            do nothing
        if neither
          do nothing
      */
      $total_bib = "";
      $total_holding = "";
      if(isset($holding_callnumber[0]) && !isset($bib_callnumber_a))
      {
        if (isset($holding_callnumber_i[0]))
        {
          $total_holding = $holding_callnumber[0] . $holding_callnumber_i[0];
        }
        else
        {
          $total_holding = $holding_callnumber[0];
        }
        $result = 0;

      }
      else if(isset($bib_callnumber_a) && !isset($holding_callnumber[0]))
      {
        if (isset ($bib_callnumber_b))
        {
          $total_bib = $bib_callnumber_a . $bib_callnumber_b;
        }
        else
        {
          $total_bib = $bib_callnumber_a;
        }
        $result = 1;
      }
      else if(isset($bib_callnumber_a)  && isset($holding_callnumber[0]))
      {
        if (isset($holding_callnumber_i[0]))
        {
          $total_holding = $holding_callnumber[0] . $holding_callnumber_i[0];
        }
        else
        {
          $total_holding = $holding_callnumber[0];
        }
        if (isset ($bib_callnumber_b))
        {
          $total_bib = $bib_callnumber_a . $bib_callnumber_b;
        }
        else
        {
          $total_bib = $bib_callnumber_a;
        }
        $result = compare_callnumbers($total_holding,$total_bib);

      }
      else
      {
        $result = 0;
      }
      echo $holding_id . ',' . $total_holding . ',' . $total_bib . ',' . $result . PHP_EOL;

    }
    if ($result == 1)
    {
      $id_array[$i] = array ('id' =>$holding_id.'');
      $i++;
    }
  }


$ini_array = parse_ini_file("config.ini");
$apikey = $ini_array['apikey'];
$baseurl = $ini_array['baseurl'];
$url =  $baseurl . '/almaws/v1/conf/sets?apikey=' . $apikey .'&format=json';
echo $url . PHP_EOL;

# Creates a set once for the successful holdings
$json = create_set($id_array);
$response = post_set($url, $json);
$response_array = json_decode($response,true);
$set_id = $response_array['id'];
var_dump($response);
echo $set_id;
$new_url = $baseurl . '/almaws/v1/conf/sets/' . $set_id . '?apikey' . $apikey . '&op=add_members';


# "members": {"member":[{"id":"2310019640002914","description":"","link":""}]},
if(isset($set_id))
{
  for ($i = 0; $i < count($id_array); $i+=100)
  {

  }
}

?>
