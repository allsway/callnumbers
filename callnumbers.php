<?php

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

  foreach ($xml->record as $record)
  {

    $holding_ids = $record->xpath('datafield[@tag=852]/subfield[@code="8"]');
    $holding_callnumber = $record->xpath('datafield[@tag=852]/subfield[@code="h"]');
    $holding_callnumber_i = $record->xpath('datafield[@tag=852]/subfield[@code="i"]');
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
  }


?>
