<?php

  /*
     datafiles_metaHelper - helper for datafiles_meta table

   */

require_once(dirname(__FILE__) . '/../tools/databaseClass.php' );
require_once(dirname(__FILE__) . '/keywords_metaHelper.php' );
require_once(dirname(__FILE__) . '/bands_helper.php');

class DatafilesHelper {

  var $db;
  var $target;
  //var $datafilesTable='datafiles_w_footprints';
  var $datafilesTable='datafiles';
  var $record;
  var $basicKeys = array('upcid', 'productid', 'edr_source', 'isisid', 'instrumentid', 'footprint', 'targetname', 'instrument', 'displayname');
  var $csvKeys;


  function DatafilesHelper($target="") {

    $this->db = new DatabasePG($target);
    $this->target = $target;
  }

  function _getRecordFromId($type, $id) {

    $keywordsHelper = new KeywordsHelper($this->target);
    $geoTypeId = $keywordsHelper->getTypeIdFromKeyword('isisfootprint');

    $query = 'SELECT d.upcid, d.productid, d.edr_source, d.isisid, d.instrumentid,' .
      'ST_AsText(g.value) As footprint, ' . 
      't.targetname, i.instrument, i.displayname FROM ' . $this->datafilesTable . ' d ' .
      "LEFT JOIN targets_meta t USING (targetid) " .
      'LEFT JOIN meta_geometry AS g ON (d.upcid = g.upcid) AND g.typeid=' . $geoTypeId . ' ' . 
      "JOIN instruments_meta i USING (instrumentid) " .
      "WHERE d." . $type . "='" . $id ."'";
    $this->db->query($query);
    $this->record = $this->db->getResultRow();
    return($this->record);
  }


  function getBasicRecord($id, $type) {

    $id = pg_escape_string($id);
    //get basic record
    if (empty($this->record)) {
      switch($type) {
	case 'productId':
	  return($this->_getRecordFromId("productid",$id));
	  break;
	case 'isisId':
	  return($this->_getRecordFromId("isisid",$id));
	  break;
	case 'edrSource':
	  return($this->_getRecordFromId("edr_source",$id));
	  break;
	case 'upcId':
	  return($this->_getRecordFromId("upcid",$id));
	  break;
        default:
	  return null;
      }
    }

  }


  function getCompleteRecord($id, $type) {

    $this->getBasicRecord($id, $type);

    //get keywords for intrument
    if (!$this->record['instrumentid']) {return null;}
    $keywordsHelper = new KeywordsHelper($this->target);
    $keywordArray = $keywordsHelper->getKeywordsFromInstrumentId($this->record['instrumentid']);

    //pull all meta data for record
    $metaTables = array('boolean'=>'boolean','integer'=>'integer','precision'=>'double','string'=>'string','time'=>'time');
    //key is table suffix, value is typename is keywords table...
    foreach($metaTables as $mKey => $mVal) {

      $query = 'SELECT * FROM meta_' . $mKey . ' ' .
	"WHERE upcid = '" . $this->record['upcid'] . "'";
      $this->db->query($query);
      $meta_results = $this->db->getResultArray();
      
      //loop through results and find matching keyword - (NxNxlogN - but very small arrays)
      foreach ($meta_results as $mmVal) {
	foreach ($keywordArray as $kVal) {
	  if (($kVal['datatype'] == $mVal) && ($kVal['typeid'] == $mmVal['typeid'])) {
	    //add meta data to record
	    //$this->record[$kVal['typename'] . '(' . $kVal['datatype'] . ')'] = $mmVal['value'];
	    $this->record[ucwords($kVal['displayname'])] = $mmVal['value'];
	    continue;
	  }
  	}//foreach
      }//foreach

    }//foreach

    $bandsHelper = new BandsHelper($this->target);
    $this->record['Filters'] = $bandsHelper->getFilters($this->record['upcid']);

    //image url replace
    $config = new Config(); 
    $thumbnailURL = $config->thumbnailURL;
    if (isset($this->record['Thumbnail Image']) && ($this->record['Thumbnail Image'] != '')) {
      $this->record['Thumbnail Image'] = str_replace('$thumbnail_server',$thumbnailURL, $this->record['Thumbnail Image']);
      $this->record['Full Size Image'] = str_replace('$thumbnail_server',$thumbnailURL, $this->record['Full Size Image']);
    }

    ksort($this->record);
    return($this->record);
  }


  function getCSVRecord($id, $type) {
    $this->record = array();
    $this->getBasicRecord($id, $type);
    $keywordsHelper = new KeywordsHelper($this->target);
    $this->csvKeys = $this->basicKeys;

    //pull extra keys
    $doubleKeys = array('maximumemissionangle','maximumincidenceangle','maximumphaseangle','meangroundresolution','solarlongitude','subsolarazimuth','surfacearea');
    $timeKeys = array('starttime');

    //check for detached label
    $edrLabelKey = 'edr_detached_label';
    $query = 'SELECT ' . $edrLabelKey . ' FROM datafiles ' .
      "WHERE upcid = '" . $this->record['upcid'] . "'";
    $this->db->query($query);
    $row = $this->db->getResultRow();
    if ($row[$edrLabelKey] != '') {
      $this->csvKeys[] = $edrLabelKey;
      $this->record[$edrLabelKey] = $row[$edrLabelKey];
    }

    foreach ($doubleKeys as $kVal) {
      $typeid = $keywordsHelper->getTypeIdFromKeyword($kVal);
      if ($typeid > 0) {
	$this->csvKeys[] = $kVal;
	$query = 'SELECT value FROM meta_precision ' .
	  "WHERE upcid = '" . $this->record['upcid'] . "' " .
	  "AND typeid = " . $typeid;
	$this->db->query($query);
	$row = $this->db->getResultRow();
	$this->record[$kVal] = $row['value'];
      }
    }

    foreach ($timeKeys as $kVal) {
      $typeid = $keywordsHelper->getTypeIdFromKeyword($kVal);
      if ($typeid > 0) {
	$this->csvKeys[] = $kVal;
	$query = 'SELECT value FROM meta_time ' .
	  "WHERE upcid = '" . $this->record['upcid'] . "' " .
	  "AND typeid = " . $typeid;
	$this->db->query($query);
	$row = $this->db->getResultRow();
	$this->record[$kVal] = $row['value'];
      }
    }


  }


  function getUPCRecord($id, $type, $groupBy='starttime-a') {

    $this->getBasicRecord($id, $type);
    return($this->record);
  }


  function getImageFromProductId($productId, $big=false) {

    $keyword = ($big) ? 'fullimageurl' : 'thumbnailurl';

    $keywordsHelper = new KeywordsHelper($this->target);
    $typeId = $keywordsHelper->getTypeIdFromKeyword($keyword);

    //pull url from config
    $config = new Config(); 
    $thumbnailURL = $config->thumbnailURL;

    $query = 'SELECT m.value AS ' . $keyword . ' FROM ' . $this->datafilesTable . ' d ' .
      'JOIN meta_string AS m USING (upcid) ' .
      "WHERE d.productid = '" . $productId . "' " .
      "AND m.typeid='" . $typeId . "'";
    $this->db->query($query);
    $this->record = str_replace('$thumbnail_server',$thumbnailURL, $this->db->getResultRow());

    return($this->record);
  }


  function getImageFromUpcId($upcId, $big=false) {

    $keyword = ($big) ? 'fullimageurl' : 'thumbnailurl';

    $keywordsHelper = new KeywordsHelper($this->target);
    $typeId = $keywordsHelper->getTypeIdFromKeyword($keyword);

    //pull url from config
    $config = new Config(); 
    $thumbnailURL = $config->thumbnailURL;

    $query = 'SELECT m.value AS ' . $keyword . ' FROM ' . $this->datafilesTable . ' d ' .
      'JOIN meta_string AS m USING (upcid) ' .
      "WHERE d.upcid = '" . $upcId . "' " .
      "AND m.typeid='" . $typeId . "'";
    $this->db->query($query);
    $this->record = str_replace('$thumbnail_server',$thumbnailURL, $this->db->getResultRow());

    return($this->record);
  }


  function setModelFromRecord(&$model) {

    if (empty($this->record)) {
      return('');
    }
    //print_r($this->record);
    //print('target '  . $this->record['targetname']);
    $model->target = $this->record['targetname'];
  }


  function getJSONRecord() {

    if (empty($this->record)) {
      return('');
    }

    //*** output JSON data structure ***/
    require_once(dirname(__FILE__) . '/../tools/json.php' );
    $renderOutput = json_encode($this->record);
    return($renderOutput);
  }


}


?>