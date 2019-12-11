<?php

  /*
     targets_metaHelper - helper for targets_meta table

   */

require_once(dirname(__FILE__) . '/../tools/databaseClass.php' );
require_once(dirname(__FILE__) . '/instruments_metaHelper.php' );


class TargetsHelper {

  var $targetArray;


  function getIdFromName($name) {

    $db = new DatabasePG();
    $query = 'SELECT targetid FROM targets_meta ' .
      "WHERE targetname = '" . strtoupper($name) . "'";
    $db->query($query);
    $results = $db->getResultRow();
    return($results['targetid']);
  }


  function getNameFromId($id) {

    $db = new DatabasePG();
    $query = 'SELECT targetname FROM targets_meta ' .
      "WHERE targetid = '" . $id . "'";
    $db->query($query);
    $results = $db->getResultRow();
    return($results['targetname']);
  }


  function getArray() {

    $targetArray = array();
    $db = new DatabasePG();
    $query = 'SELECT * ' .
      'FROM targets_meta t ' .
      'ORDER BY t.targetname';
    $db->query($query);
    $instrumentHelper = new InstrumentsHelper();
    while($row = $db->getResultRow()) {
      $row['instruments'] = $instrumentHelper->getInstrumentArrayFromTargetId($row['targetid']);
      $targetArray[$row['targetid']] = $row;
    }
    return($targetArray);
  }


  function getArrayForChooser($name) {

    $targetArray = array();
    $db = new DatabasePG();
    $query = 'SELECT * ' .
      'FROM targets_meta t ' .
      "WHERE (UPPER(targetname)=UPPER('" . $name . "')) OR (UPPER(system)=UPPER('" . $name . "')) " .
      'ORDER BY t.targetname';
    $db->query($query);
    $instrumentHelper = new InstrumentsHelper();
    while($row = $db->getResultRow()) {
      $row['instruments'] = $instrumentHelper->getInstrumentArrayFromTargetId($row['targetid']);
      $targetArray[$row['targetid']] = $row;
    }
    return($targetArray);
  }


  function getRowByName($name) {

    $targetArray = array();
    $db = new DatabasePG();
    $query = 'SELECT * ' .
      'FROM targets_meta t ' .
      "WHERE UPPER(targetname)=UPPER('" . $name . "') " .
      'ORDER BY t.targetname';
    $db->query($query);
    $row = $db->getResultRow();
    return($row);
  }


  function getValidTargets($targets) {

    $validTargets = array();
    foreach($targets as $tVal) {
      if (isset($tVal['instruments']) && !empty($tVal['instruments'])) {
	if (!in_array($tVal['targetname'], $validTargets)) {
	  $validTargets[] = $tVal['targetname'];
	}
	if (!in_array($tVal['system'], $validTargets)) {
	  $validTargets[] = $tVal['system'];
	}
      }
    }
    return(implode(",",$validTargets));
  }

}


?>