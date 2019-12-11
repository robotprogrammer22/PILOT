<?php

date_default_timezone_set( 'America/Phoenix' );
$currentTarget = strtoupper($_REQUEST['target']);
if ($currentTarget == '') {return null;}

require_once(dirname(__FILE__) . '/../model/stats_helper.php' );
$instrumentArray = StatsHelper::getInstrumentsFromTarget($currentTarget);
$subtargetArray = StatsHelper::getSubtargetsFromTarget($currentTarget);

//XML doc
$xmlString = <<<XML
<?xml version='1.0' standalone='yes'?>
 <upc>
 </upc>
XML;
$doc = new SimpleXMLElement($xmlString);
$target = $doc->addChild("target", $currentTarget);
$instruments = $doc->addChild("instruments");
$subtargets = $doc->addChild("subtargets");

//  
$edrCount = 0;
foreach($instrumentArray as $iKey => $iVal ) {

  $instrument = $instruments->addChild("instrument");
  $iMission = $instrument->addChild("mission", $iVal['mission']);
  $iName = $instrument->addChild("name", $iVal['displayname']);
  $iStart = $instrument->addChild("start_date", date('M d Y', strtotime($iVal['start_date'])));
  $iStop = $instrument->addChild("stop_date", date('M d Y', strtotime($iVal['stop_date']))); 
  $iPub = $instrument->addChild("last_published", date('M d Y', strtotime($iVal['last_published'])));
  $iCount = $instrument->addChild("count", $iVal['image_count']);
  $edrCount += $iVal['image_count'];
}
//<total>
$total = $doc->addChild("edrCount", $edrCount);

foreach($subtargetArray as $sKey => $sVal ) {
    //<subtarget>
  $st = $subtargets->addChild("target", $sVal['targetname']);
  $edrAttr = $st->addAttribute("edrCount", $sVal['image_count']);
}

echo $doc->asXML();

?>