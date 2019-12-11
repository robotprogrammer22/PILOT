<?php

/*
 *    
 */


class upcFileClass {

  var $fileArray;


  function upcFileClass() {

  }


  function getUploadedFile($name) {
    $this->fileArray = $_FILES[$name];
  }


  function getWKTFromMapTemplateFile() {

    $keyInfoArray = array('LongitudeDirection','LongitudeDomain','LatitudeType','MinimumLatitude','MaximumLatitude','MinimumLongitude','MaximumLongitude');
    $boundingBox = array();
    $mapInfo = array();

    //open and loop through file
    $handle = @fopen($this->fileArray['tmp_name'], "r");
    if ($handle) {
      while (!feof($handle)) {
        $buffer = fgets($handle, 4096);
	if (strpos($buffer,'=') !== FALSE) {
	  list($key,$value) = explode('=',$buffer);
	  if (in_array(trim($key), $keyInfoArray)) {
	    $boundingBox[trim($key)] = trim($value);
	  }
	}
      }
      fclose($handle);
    }

    //generate mapInfo array
    if (!empty($boundingBox)) {
      $mapInfo['wkt']='POLYGON((' . $boundingBox['MaximumLongitude'] . ' ' . $boundingBox['MaximumLatitude'] . ',' .
	$boundingBox['MinimumLongitude'] . ' ' . $boundingBox['MaximumLatitude'] . ',' .
	$boundingBox['MinimumLongitude'] . ' ' . $boundingBox['MinimumLatitude'] . ',' .
	$boundingBox['MaximumLongitude'] . ' ' . $boundingBox['MinimumLatitude'] . ',' .
	$boundingBox['MaximumLongitude'] . ' ' . $boundingBox['MaximumLatitude'] . '))';
      $mapInfo['longitudeDirection'] = $boundingBox['LongitudeDirection'];
      $mapInfo['longitudeDomain'] = $boundingBox['LongitudeDomain'];
      $mapInfo['latitudeType'] = $boundingBox['LatitudeType'];
    }

    return($mapInfo);
  }


}

