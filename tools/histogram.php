<?php

require_once(dirname(__FILE__) . '/../model/targets_metaHelper.php');
require_once(dirname(__FILE__) . '/../model/keywords_metaHelper.php');

// put new functions from model/histogram_helper here
// need to correctly format the array, like the json file one
// might need to create an object
// which data source do I need to use?

class histogram
{

  function __construct($currentJson)
  {
    $this->allJson = $currentJson;
  }


  function getData()
  {

  }

  function getDate()
  {
    die($this->allJson);
  }
}

?>