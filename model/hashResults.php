<?php
 
/*

  hash results - so-so hashing algorithms by mbailen@usgs.gov

*/

require_once(dirname(__FILE__) . '/../tools/databaseClass.php' );



class hashResults {

  //result variables
  var $model; //reference to model
  var $db;
  var $maxHashKeys;
  var $avgHashKeys;
  var $safeHashAmount;
  var $upcCrossingDateline;
  var $storeFootprintAmount;

  //stored results
  var $hashArray;
  var $footprints;


  function __construct($model)  {

    //set up model
    $this->SORT_FLAG = SORT_REGULAR;
    $this->model = $model;
    $this->upcCrossingDateline = $this->model->upcCrossingDateline;
    $this->avgHashKeys = 9;
    $this->safeHashAmount = 200; //on the high end... 
    $countCheck = round($this->model->total/75);
    $this->maxHashKeys = ($countCheck > 25) ? $countCheck : 25;
    $config = new Config();
    $this->storeFootprintAmount = $config->maxQueryRender; 

    //need db class to parse results
    $this->db = new DatabasePG();
    $this->db->setResult($this->model->result);

    $this->hashArray = array();
  }


  // get new hash
  function get() {
    
    $hash = false;

    //no hash - NEW PILOT DOES NOT HASH
    if (!$hash) {
       while ($row = $this->db->getResultRow()) {
	$this->hashArray[] = $row;
       }
    } else {

      //hash - switch according to groupby method
      switch ($this->model->groupBy) {
      case 'instrument':
	$this->getByInstrument();
	break;
      case 'productid':
	$this->getByProductId($this->model->groupBy);
	break;
      case 'starttime':
	$this->getByDate($this->model->groupBy);
	break;
      default:
	$this->getByConstraint();
	break;
      }
      //$this->subHashOptimize();
    }

    return($this->hashArray);
  }


  function getByInstrument() {

    while ($row = $this->db->getResultRow()) {

	//simple - one bucket per instrument
	if (!isset($this->hashArray[$row['displayname']])) {
	  $this->hashArray[$row['displayname']]['count'] = 1;
	  $this->hashArray[$row['displayname']]['subHash'] = array();
	  $this->hashArray[$row['displayname']]['footprints'] = array();
	} else {
	  $this->hashArray[$row['displayname']]['count']++;
	}
	$fIndex = $this->hashArray[$row['displayname']]['count'];
	$this->hashArray[$row['displayname']]['footprints'][$fIndex] = $row;
	$this->hashArray[$row['displayname']]['footprints'][$fIndex]['subHash'] = $this->subHashByProductId($row['displayname'], $fIndex);
	
    }
  }


  function getByDate() {

    while ($row = $this->db->getResultRow()) {

      //one bucket per month
      $currentMonth = substr($row['starttime'],0,7);
      if (!isset($this->hashArray[$currentMonth])) {
	$this->hashArray[$currentMonth]['count'] = 1;
	$this->hashArray[$currentMonth]['subHash'] = array();
	$this->hashArray[$currentMonth]['footprints'] = array();
      } else {
	$this->hashArray[$currentMonth]['count']++;
      }
      $fIndex = $this->hashArray[$currentMonth]['count'];
      $this->hashArray[$currentMonth]['footprints'][$fIndex] = $row;
      $this->hashArray[$currentMonth]['footprints'][$fIndex]['subHash'] = $this->subHashByProductId($currentMonth, $fIndex);

    }
  }



  function getByProductId($keyField) {
    //
    // no sub-hashing
    $healthyNumberOfHashKeysStart = 2; 
    $healthyNumberOfHashKeysEnd = 30;

    $currentKey = '';
    while ($row = $this->db->getResultRow()) {
      //smart-enuff fifo string hasher based on product id
      if ($currentKey == '') {
	//initial hash index equals full length -1 or 10, whatever is less
	$hashStartIndex = strlen($row[$keyField]) - 2;
	if ($hashStartIndex > 10) {$hashStartIndex = 10;}
      } else {
	//find first diff char
	for($i=0;$i<$hashStartIndex;$i++) {
	  if (substr($row[$keyField],$i,1) != substr($currentKey,$i,1)) {break;}
	}
	if (($i > 0) && ($i < $hashStartIndex) && (count($this->hashArray) > $healthyNumberOfHashKeysEnd)) {
	  //remake hash
	  $newHashArray = array();
	  //$newFootprints = $this->footprints;
	  foreach ($this->hashArray as $hKey => $hVal) {
	    $newKey = substr($hKey,0,$i);
	    $newHashArray[$newKey]['count'] = (empty($newHashArray[$newKey])) ? $hVal['count'] : intval($newHashArray[$newKey]['count']) + $hVal['count']; 
	    //move footprint rows
	    if (isset($newHashArray[$newKey]['footprints'])) {
	      //merge and renumber.. tweak increment?
	      $newHashArray[$newKey]['footprints'] = array_merge($newHashArray[$newKey]['footprints'], $hVal['footprints']); 
	    } else {
	      $newHashArray[$newKey]['footprints'] = $hVal['footprints']; 
	    }

	  }
	  //make sure hash didn't do too good of job.. 
	  if ((count($newHashArray) > $healthyNumberOfHashKeysStart) && (count($newHashArray) < $healthyNumberOfHashKeysEnd)) {
	    $hashStartIndex = $i;
	    $this->hashArray = $newHashArray;
	  }
	}
      }
      //increment hashArray
      $currentKey = substr($row[$keyField], 0, $hashStartIndex);	  
      $this->hashArray[$currentKey]['count'] = (empty($this->hashArray[$currentKey])) ? 1 : (intval($this->hashArray[$currentKey]['count']) + 1);
      //add row
      $fIndex = $this->hashArray[$currentKey]['count'];
      if (!isset($this->hashArray[$currentKey]['footprints'])) {
	$this->hashArray[$currentKey]['footprints']= array();
      }
      $this->hashArray[$currentKey]['footprints'][$fIndex] = $row;
    }
  }


  function getByConstraint() {

    $scaleDivider = 10;
    require_once(dirname(__FILE__) . '/keywords_metaHelper.php' );

    $maxAngle=''; $minAngle=''; 
    while ($row = $this->db->getResultRow()) {

      $key = $this->model->groupBy;
      //$key = KeywordsHelper::getSpecificKeyword($this->model->groupBy,'center');
      //print('key is ' . $key);
      //smart-enuff fifo number hasher
      $minAngle = ($minAngle==='') ? floor($row[$key]) : $minAngle; 
      $maxAngle = ($maxAngle==='') ? $minAngle+1 : $maxAngle; 
      $angleScaler = ($maxAngle - $minAngle)/$scaleDivider;
      //print('minAngle:' . $minAngle . '<br/>');
      //print('maxAngle:' . $maxAngle . '<br/>');
      //print('$row[$key]:' . $row[$key] . '<br/>');
      if (($row[$key] < $minAngle) || ($row[$key] > $maxAngle)) {
	//rescale the hash (must contain all old buckets... so scale by **2)
	if ($row[$key] < $minAngle) {
	  //rescale down
	  for($j=1;((($minAngle - (pow(2,$j)*$angleScaler)) <= $row[$key]) && $j < 15);$j++) {}
	  $angleScaler = pow(2,$j)*$angleScaler;
	  $minAngle = $maxAngle - ($angleScaler*$scaleDivider);
	} else {
	  //rescale up
	  for($j=1;((($maxAngle + (pow(2,$j)*$angleScaler)) <= $row[$key]) && $j < 15);$j++) {}
	  $angleScaler = pow(2,$j)*$angleScaler;
	  $maxAngle = $minAngle + ($angleScaler*$scaleDivider);
	}
	$newHashArray = array();
	//$newFootprints = $this->footprints;
	//loop through old hash
	foreach ($this->hashArray as $hKey => $hVal) {
	  list($oldMin,$oldMax) = explode(' - ',$hKey);
	  for($i=0;$i<$scaleDivider;$i++) {
	    $newMin = $minAngle + ($i * $angleScaler);
	    $newMax = $minAngle + (($i+1) * $angleScaler);
	    $newKey = $newMin . ' - ' . $newMax;
	    if (($oldMin >= $newMin) && ($oldMax <= $newMax)) {
	      $newHashArray[$newKey]['count'] = (empty($newHashArray[$newKey])) ? $hVal['count'] : $newHashArray[$newKey]['count'] + $hVal['count']; 		

	      //move footprint rows
	      if (isset($newHashArray[$newKey]['footprints'])) {
		//merge and renumber.. tweak increment?
		$newHashArray[$newKey]['footprints'] = array_merge($newHashArray[$newKey]['footprints'], $hVal['footprints']); 
	      } else {
		$newHashArray[$newKey]['footprints'] = $hVal['footprints']; 
	      }

	      //redo subhash
	      //if ($hVal['subHash']) {
	      //$newHashArray[$newKey]['subHash'] = (empty($newHashArray[$newKey]['subHash'])) ? $hVal['subHash'] : $this->subHashMerge($newHashArray[$newKey]['subHash'],$hVal['subHash']); 		
	      //}
	      break;
	    }
	  }

	  //redo footprint array
	  //foreach ($this->footprints as $fKey => $fVal) {
	  //  if ($fVal['hash'] == $hKey) {
	  //    $newFootprints[$fKey]['hash'] = $newKey;
	  //  }
	  //}
	}
	$this->hashArray = $newHashArray;
	//$this->footprints = $newFootprints;
      }
      //add new hit
      for($i=0;$i<$scaleDivider;$i++) {
	$newMin = $minAngle + ($i * $angleScaler);
	$newMax = $minAngle + (($i+1) * $angleScaler);
	$currentKey = $newMin . ' - ' . $newMax;
	if (($newMin <= $row[$key]) && ($row[$key] <= $newMax)) {
	  $this->hashArray[$currentKey]['count'] = (isset($this->hashArray[$currentKey])) ? $this->hashArray[$currentKey]['count']+1 : 1;

	  //add row
	  $fIndex = $this->hashArray[$currentKey]['count'];
	  if (!isset($this->hashArray[$currentKey]['footprints'])) {
	    $this->hashArray[$currentKey]['footprints']= array();
	  }
	  $this->hashArray[$currentKey]['footprints'][$fIndex] = $row;

	  //if (!isset($this->hashArray[$currentKey]['subHash'])) {
	  //  $this->hashArray[$currentKey]['subHash'] = array();
	  //}
	  $currentSubKey = $this->hashArray[$currentKey]['count'] -1;
	  //print("ADDING " . $currentKey . ": " . $angleScaler . "<br/>");
	  break;
	}
      }
      //print_r($this->hashArray);
      //$this->hashArray[$currentKey]['subHash'] = $this->subHashByProductId($this->hashArray[$currentKey]['subHash'],$row['productid']);
      
      //save footprint
      //$this->storeFootprint($currentKey, $this->hashArray[$currentKey]['subHash'], $row);
    }
  }

  //
  //
  function subHashByProductId($currentHash, $fIndex) {

    $currentSubHash = $this->hashArray[$currentHash]['subHash'];
    $productId = $this->hashArray[$currentHash]['footprints'][$fIndex]['productid'];

    //smart-enuff fifo string hasher based on product id
    if (empty($currentSubHash)) {
      //initial hash index equals full length -1 or 10, whatever is less
      $hashStartIndex = strlen($productId) - 2;
      if ($hashStartIndex > 10) {$hashStartIndex = 10;}
      $currentKey = substr($productId,0,$hashStartIndex);
      $currentSubHash[$currentKey] =1;
    } else {
      $hashStartIndex = strlen(key($currentSubHash));
      $currentKey = substr($productId,0,$hashStartIndex);
      if (isset($currentSubHash[$currentKey])) {
	//increment existing hash
	$currentSubHash[$currentKey]++;
      } else {
	if (count($currentSubHash) <= $this->maxHashKeys) {
	  //create new hash
	  $currentSubHash[$currentKey] = 1;
	} else {
	  //remake hash - find index
	  $newIndex = 0;
	  foreach ($currentSubHash as $shKey => $shVal) {
	    for($i=1;$i<$hashStartIndex;$i++) {
	      if (substr($productId,$i,1) != substr($shKey,$i,1)) {
		if ($i < $newIndex) {break 2;}
		if ($i > $newIndex) {$newIndex = $i;}
		break;
	      }
	    }
	  }
	  //realign hash array
	  $newHashArray = array();
	  foreach ($currentSubHash as $hKey => $hVal) {
	    $newKey = substr($hKey,0,$newIndex);
	    $newHashArray[$newKey]= (empty($newHashArray[$newKey])) ? $hVal : intval($newHashArray[$newKey]) + intVal($hVal); 
	  }
	  //make sure we didn't do too good of job.. 
	  $healthyNumberOfHashKeys = 2; //need a guesstimation algorithm here.. total-hashedSoFar/count?
	  //if (count($newHashArray) > $healthyNumberOfHashKeys) {
	  //print('old count: '.count($currentSubHash).'  new count: ' . count($newHashArray).' *** ');
	  $currentSubHash = $newHashArray;
	    //}
	  //increment
	  $currentKey = substr($productId,0,$newIndex);
	  $currentSubHash[$currentKey] = (empty($currentSubHash[$currentKey])) ? 1 : (intval($currentSubHash[$currentKey])+1);

	  //fix footprints
	  foreach ($this->hashArray[$currentHash]['footprints'] as $fpKey => $fpVal) {
	    foreach ($currentSubHash as $hKey => $hVal) {
	      if (substr($fpVal['productid'],0,$newIndex) == $hKey) {
		$this->hashArray[$currentHash]['footprints'][$fpKey]['subHash'] = $hKey;
		break;
	      }
	    }
	  }//foreach

	}//else
      }//else
    }

    $this->hashArray[$currentHash]['subHash'] = $currentSubHash;
    return($currentKey);
  }

  //
  //
  function subHashMerge($hash1, $hash2) {

    //find shortest length
    $keySize1 = strlen(key($hash1));
    $keySize2 = strlen(key($hash2));
    if ($keySize1 > $keySize2) {
       $newKeySize = $keySize2;
       $baseArray =& $hash2;
       $mergeArray =& $hash1;
    } else {
       $newKeySize = $keySize1;
       $baseArray =& $hash1;
       $mergeArray =& $hash2;
    }
    //merge arrays
    foreach($mergeArray as $mKey => $mVal) {
      $currentKey = substr($mKey, 0, $newKeySize);
      if (isset($baseArray[$currentKey])) {
	$baseArray[$currentKey] += $mVal;
      } else {
	$baseArray[$currentKey] = $mVal;
      }
    }
    return($baseArray);

  }

  //
  //
  function subHashOptimize() {

    //optimize merged hash
    foreach ($this->hashArray as $hKey => $hVal) {

      $hashCount = count($hVal['subHash']);
      $newIndex = strlen(key($hVal['subHash']));
      $reHash = FALSE;
      while ($hashCount > $this->maxHashKeys) {
	$newIndex--;
	$newHashArray = array();
	foreach ($hVal['subHash'] as $bKey => $bVal) {
	  $newKey = substr($bKey,0,$newIndex);
	  $newHashArray[$newKey]= (empty($newHashArray[$newKey])) ? $bVal : intval($newHashArray[$newKey]) + intVal($bVal); 
	  //safety catch for already too large hashes and adds
	  if ($newHashArray[$newKey] > $this->safeHashAmount) {return;}
	}
	$hashCount = count($newHashArray);
	$reHash = TRUE;
	//print('count going from ' . count($baseArray) . ' to [index '. $newIndex . '] ' . count($newHashArray));
      }      
      if ($reHash) {
	$this->hashArray[$hKey]['subHash'] = $newHashArray;
      }
    }
  }


  function storeFootprint($currentKey, $subHashKey, $row) {

    //save footprints
    if ($this->storeFootprintAmount > 0) {
      foreach ($this->model->resultKeys as $rVal) {
	$this->footprints[$this->storeFootprintAmount][$rVal] = $row[$rVal]; 
      }
      $this->footprints[$this->storeFootprintAmount]['hash'] = $currentKey; 
      $this->footprints[$this->storeFootprintAmount]['subHash'] = $subHashKey; 
      $this->storeFootprintAmount--;
    }
  }

}