<?php

/*
 *    
 */

require_once(dirname(__FILE__) . '/../configure.php' );


class NomenclatureHelper {

  var $nomenclatureURL;
  var $nomenclatureLocalFile;


  function NomenclatureHelper() {

    $config = new Config();
    $this->nomenclatureURL = $config->nomenclatureURL;
  }


  function getFeatureTypes($target) {

    $handle = @fopen($this->nomenclatureURL . '/stats/' . strtoupper($target), "rb");
    if (!$handle) {return (null);}

    $contents = stream_get_contents($handle);
    fclose($handle);

    $xml = simplexml_load_string($contents); 
    return ($xml);
  }


  function getFeatureNames($target, $featureType) {

    $handle = @fopen($this->nomenclatureURL . '/SearchResults?target=' . urlencode(strtoupper($target)) . '&displayType=JSON&featureType=' . urlencode($featureType), "rb");
    if (!$handle) {return (null);}

    $contents = stream_get_contents($handle);
    fclose($handle);

    return ($contents);
  }


  function getFeatureLatLon($featureId) {

    $handle = @fopen($this->nomenclatureURL . '/Feature/' . urlencode($featureId) . '.js', "rb");
    if (!$handle) {return (null);}

    $contents = stream_get_contents($handle);
    fclose($handle);

    return ($contents);
  }



}

?>
