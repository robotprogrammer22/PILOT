<?php

	class jsonFromURL
	{
		//var $json_array;
		//var $result_number;
		var $json = file_get_contents("https://pdsimage2.wr.usgs.gov/POW/UPC/volume_summary.json");
		
		function arrayFromJSON ($query)
		{
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
			$json_array = array();
			$result_total = 0;
			foreach($json->json_agg as $value)
			{
				array_push($json_array, $value->targetname);
			}
			return($json_array);
		}
	}

?>