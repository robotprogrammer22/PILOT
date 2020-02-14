<?php

	class jsonFromURL
	{
		//var $json_array;
		//var $result_number;		
	  var $json;

	  function __construct()
	  {
	    $this->json = file_get_contents("https://pdsimage2.wr.usgs.gov/POW/UPC/volume_summary.json");
	  }


		function arrayFromJSON ()
		{
		  //var $json = file_get_contents(https://pdsimage2.wr.usgs.gov/POW/UPC/volume_summary.json);
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
			foreach($this->json->json_agg as $value)
			{
			  $element_array = array('system'=>$value->system, 'targetname'=>$value->targetname, 'mission'=>$value->mission, 'instrument'=>$value->instrument, 'start_data'=>$value->start_date);
			  array_push($json_array, $element_array);
			}
			return($json_array);
		}
	}

?>
