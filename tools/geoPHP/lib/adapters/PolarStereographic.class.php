<?php

/**
 * Convert from Global (lan/lon) to Polar Stereographic (meters)
 */

class PolarStereographic
{

  //latlon to polar stereo meters
  public function read($geometry, $radius=0, $pole='north') {
    return $this->geometryConvertPS($geometry, $radius, $pole, 'read');
  }

  //polar stereo meters to latlon
  public function write($geometry, $radius=0, $pole='north') {
    return $this->geometryConvertPS($geometry, $radius, $pole, 'write');
  }

  //
  protected function geometryConvertPS($geometry, $radius, $pole, $dir) {
    $type = strtolower($geometry->getGeomType());
    switch ($type) {
    case 'point':
      if ($dir == 'read') {
	  return $this->latLonToPolarMeters($geometry, $radius, $pole); 
      } else {
	  return $this->polarMetersToLatLon($geometry, $radius, $pole); 
      }
      break;
    case 'linestring':
    case 'polygon':
    case 'multipoint':
    case 'multilinestring':
    case 'multipolygon':
    case 'geometrycollection':
      return $this->collectionConvertPS($geometry, $radius, $pole, $dir);
    break;
    }
  }

   
  //
  public function collectionConvertPS($geometry, $radius, $pole, $dir) {
    $psArray = array();
    foreach ($geometry->getComponents() as $comp) {
      $newPoint = $this->geometryConvertPS($comp, $radius, $pole, $dir);
      $psArray[] = $newPoint;
    }
    $type = strtolower($geometry->getGeomType());
    $ps = new $type($psArray);
    return $ps;
  }


  //
  protected function polarMetersToLatLon ($point, $radius, $pole) {
    $R = ($radius * 1000);
    $x = $point->getX();
    $y = $point->getY();

    switch ($pole) {
    case 'north':
      $clat = 90;
      $lonRadians = atan2($x, -$y);
      break;
    case 'south':
      $clat = -90;
      $lonRadians = atan2($x, $y);
      break;
    }
  
    // Compute LAT
    $clatRadians = $clat * (pi()/180);
    $p = sqrt(pow($x,2) + pow($y,2));
    $c = 2 * atan($p/(2 * $R));
    $latRadians = asin(cos($c) * sin($clatRadians)); 
    $lat = $latRadians * 180/pi();

    // Compute LON
    $lon = $lonRadians * 180/pi();
    if ($lon < 0) {
      $lon = $lon + 360;
    }

    $psPoint = new Point($lon, $lat);
    return $psPoint;
  }
  
  
  //
  protected function latLonToPolarMeters($point, $radius, $pole) {
    $R = ($radius * 1000);
    $x = $point->getX();
    $y = $point->getY();
    $lonRadians = ($x) * pi() / 180;
    $latRadians = ($y) * pi() / 180;

    
    if ($pole == 'north') {
      $psX = (2 * $R * tan(pi() / 4 - $latRadians / 2) * sin($lonRadians));
      $psY = (-2 * $R * tan(pi() / 4 - $latRadians / 2) * cos($lonRadians));
    } else {
      $psX = 2 * $R * tan (pi() / 4 + $latRadians / 2) * sin($lonRadians);
      $psY = 2 * $R * tan (pi() / 4 + $latRadians / 2) * cos($lonRadians);
    }

    $psPoint = new Point($psX, $psY);
    return $psPoint;
  }
  
}

?>