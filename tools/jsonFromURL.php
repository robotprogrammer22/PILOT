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



		function SortBySystem()
		{
		  // orders the array sent back for stats, which hopefully should fix stuff not showing up
		  $planet_moon_array = array();
		  $this->json = json_decode(file_get_contents("https://pdsimage2.wr.usgs.gov/POW/UPC/volume_summary.json"));
		  $object_array = $this->json[0]->json_agg;
		  //print_r($object_array);
		  foreach($object_array as $value)
		  {
		    //print_r($value->targetname);
		    //if (!in_array($value->targetname, $planet_moon_array))
		    if (!in_array($value->system, $planet_moon_array))
		    {
		      //array_push($planet_moon_array, $value->targetname);
		      array_push($planet_moon_array, $value->system);
		    }
		  }

		  sort($planet_moon_array);
		  //print_r($planet_moon_array);

		  $sorted_array = array();
		  foreach($planet_moon_array as $object)
		  {
		    foreach($object_array as $value)
		    {
		      //if ($value->targetname == $object)
		      if ($value->system == $object)
		      {
			array_push($sorted_array, $value);
		      }
		    }
		  }

		  return $sorted_array;
		}

		//function sortByTarget($currentArraySort, $originalArray)
		//{
		  // the current array sort is for the previous sort that was done, 
		  // do I even need to do this?
		//}

		//function sort($array)
		//{

		//}


		function orderedArray()
		{
		  $sorted_planet_array = array();
		  $sorted_planet_array = $this->sortBySystem();
		  // need to make the original array or get it as a parameter
		  //$this->sortByTarget($sorted_planet_array, $originalArray);
		  return $sorted_planet_array;
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

			$planet_order_array = $this->orderedArray();
			$planet_objects = json_decode(json_encode($planet_order_array));
			//print_r($planet_order_array);

			//foreach($t_array as $value)
			foreach($planet_objects as $value)
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
