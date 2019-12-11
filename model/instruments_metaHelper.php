<?php

  /*
     instruments_metaHelper - helper for instruments_meta table

   */

require_once(dirname(__FILE__) . '/../tools/databaseClass.php' );


class InstrumentsHelper {

  var $instrumentTargetArray; //array mapping instruments to targets
  var $db;
  var $target;
  
  function __construct($target="") {
    $this->target = $target;
    $this->db = new DatabasePG($target);
  }
  

  function getIdFromName($name) {

    $query = 'SELECT instrumentid FROM instruments_meta ' .
      "WHERE instrument = '" . $name . "'";
    $this->db->query($query);
    $results = $this->db->getResultRow();
    return($results['instrumentid']);
  }


  function getIdFromDisplayName($displayname) {

    $query = 'SELECT instrumentid FROM instruments_meta ' .
      "WHERE displayname = '" . $displayname . "'";
    $this->db->query($query);
    $results = $this->db->getResultRow();
    return($results['instrumentid']);
  }


  function fillInstrumentTargetArray() {

    $this->instrumentTargetArray = array();
    $query = "SELECT d.instrumentid, d.targetid, t.targetname, i.instrument, i.mission, i.spacecraft, i.displayname, count(i.instrumentid) as image_count " .
      "FROM datafiles d, " . 
      "targets_meta t, instruments_meta i " .
      "WHERE t.targetid = d.targetid " .
      "AND i.instrumentid = d.instrumentid " . 
      "GROUP BY d.instrumentid, i.instrument, i.mission, i.spacecraft, d.targetid, t.targetname, i.displayname ORDER BY i.spacecraft, i.mission, i.displayname";
    die($query);

    $this->db->query($query);
    $this->instrumentTargetArray = $this->db->getResultArray();
    return($this->instrumentTargetArray);
  }


  function getInstrumentArrayFromTargetId($targetId) {

    if (empty($this->instrumentTargetArray)) {
      $this->fillInstrumentTargetArray();
    }

    $instrumentArray = array();
    foreach($this->instrumentTargetArray as $iVal) {
      if ($iVal['targetid'] == $targetId) {
	$instrumentArray[] = $iVal;
      }
    }

    return($instrumentArray);

  }


  function getInstrumentTable() {

    //require_once(dirname(__FILE__) . '/../tools/json.php' );

    $query = 'SELECT i.*, ip.processtype, ip.comments FROM instruments_meta i ' .
      "LEFT JOIN instrument_process ip USING (instrumentid)";
    $this->db->query($query);
    $results = $this->db->getResultArray();
    //$JSONtable = json_encode($table);
    foreach ($results as $rVal) {
      $table[$rVal['instrumentid']] = $rVal;
    }
    return($table);
  }


  function getMissionLinks() {

    $url="http://pds-imaging.jpl.nasa.gov/portal/";
    $missionLinks = array(
		   "Galileo Orbiter" => $url . 'galileo_mission.html',
		   "Messenger" => $url . 'messenger_mission.html',
		   "Voyager" => $url . 'voyager_mission.html',
		   "Chandrayaan-1 Orbiter" => $url . 'chandrayaan-1_mission.html',
		   "Dawn" => 'http://sbn.psi.edu/pds/archive/dawn.html',
		   "Mars Global Surveyor" => $url . 'mgs_mission.html',
		   "Viking" => $url . 'vikingo_mission.html',
		   "Odyssey" => $url . 'odyssey_mission.html',
		   "Mariner 10" => $url . 'mariner10_mission.html',
		   "Cassini" => $url . 'cassini_mission.html',
		   "Mars Express" => $url . 'mex_mission.html',
		   "Lunar Reconnaissance Orbiter" => $url . 'lro_mission.html',
		   "Clementine" => $url . 'clementine_mission.html',
		   "Mars Reconnaissance Orbiter" => $url . 'mro_mission.html',
		   );
    return(json_encode($missionLinks));
  }


  function canProcess($processType, $id) {

    $query = 'SELECT i.* FROM instrument_process i ' .
      "WHERE i.instrumentid = " . $id . " " .
      "AND i.processtype = '" . $processType . "'"; 
    $this->db->query($query);
    $results = $this->db->getResultRow();
    return(!empty($results));
  }

}


?>