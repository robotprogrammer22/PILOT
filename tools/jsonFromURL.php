<?php

	class jsonFromURL
	{
		//var $json_array;
		//var $result_number;		
	  var $json;

	  function __construct()
	  {
	    $this->json = json_decode(file_get_contents("https://pdsimage2.wr.usgs.gov/POW/UPC/volume_summary.json"));
	  }


		function arrayFromJSON ()
		{
		  $this->json = json_decode(file_get_contents("https://pdsimage2.wr.usgs.gov/POW/UPC/volume_summary.json"));
			/*
			// code from multiDBQueryResultArray
			$multiResult = array();
			$multiTotal = 0;
			foreach ($this->targetDBs as $tVal) {
				$this->_query($query, $tVal);
				$multiResult = array_merge($multiResult, $this->getResultArray());
				$multiTotal = $multiTotal + $this->total;
			}
			$this->total = $multiTotal;
			return($multiResult);
			*/
			
			
			// this will return the names, maybe make another array of just the objects? or just use the json-> function?
			// ORDER BY system, targetname, mission, instrument, start_date
				// this is what get stats does, should I try to do something similar?
			// make a filter method that goes through the whole list and then finds the one for that system? or target name?
			$json_array = array();
			$result_total = 0;
			//foreach($this->json->json_agg as $value)

			//print_r($this->json[0]->json_agg);
			$t_array = $this->json[0]->json_agg;

			foreach($t_array as $value)
			{
			  $element_array = array('instrumentid'=>$value->instrumentid, 'targetid'=>$value->targetid, 'targetname'=>$value->targetname, 'system'=>$value->system, 'instrument'=>$value->instrument, 'mission'=>$value->mission, 'spacecraft'=>$value->spacecraft, 'displayname'=> $value->displayname, 'start_date'=>$value->start_date, 'stop_date'=>$value->stop_date, "last_published"=>$value->publish_date, 'bands'=>1, 'total'=>$value->image_count, 'errors'=>0);
			  array_push($json_array, $element_array);
			}
			//print_r($json_array);
			return($json_array);

		}

		function getJSON($object_array)
		{
		  return(json_encode($object_array));
		}
	}

?>
