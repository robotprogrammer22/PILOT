<?php

/**
 * Polygon: A polygon is a plane figure that is bounded by a closed path, 
 * composed of a finite sequence of straight line segments
 */
class Polygon extends Collection
{
  protected $geom_type = 'Polygon';
  
  public function area($exterior_only = FALSE, $signed = FALSE) {
    if ($this->isEmpty()) return 0;
    
    if ($this->geos() && $exterior_only == FALSE) {
      return $this->geos()->area();
    }
    
    $exterior_ring = $this->components[0];
    $pts = $exterior_ring->getComponents();
    
    $c = count($pts);
    if((int)$c == '0') return NULL;
    $a = '0';
    foreach($pts as $k => $p){
      $j = ($k + 1) % $c;
      $a = $a + ($p->getX() * $pts[$j]->getY()) - ($p->getY() * $pts[$j]->getX());
    }
    
    if ($signed) $area = ($a / 2);
    else $area = abs(($a / 2));
    
    if ($exterior_only == TRUE) {
      return $area;
    }
    foreach ($this->components as $delta => $component) {
      if ($delta != 0) {
        $inner_poly = new Polygon(array($component));
        $area -= $inner_poly->area();
      }
    }
    return $area;
  }
  
  public function centroid() {
    if ($this->isEmpty()) return NULL;
    
    if ($this->geos()) {
      return geoPHP::geosToGeometry($this->geos()->centroid());
    }
    
    $exterior_ring = $this->components[0];
    $pts = $exterior_ring->getComponents();
    
    $c = count($pts);
    if((int)$c == '0') return NULL;
    $cn = array('x' => '0', 'y' => '0');
    $a = $this->area(TRUE, TRUE);
    
    // If this is a polygon with no area. Just return the first point.
    if ($a == 0) {
      return $this->exteriorRing()->pointN(1);
    }
    
    foreach($pts as $k => $p){
      $j = ($k + 1) % $c;
      $P = ($p->getX() * $pts[$j]->getY()) - ($p->getY() * $pts[$j]->getX());
      $cn['x'] = $cn['x'] + ($p->getX() + $pts[$j]->getX()) * $P;
      $cn['y'] = $cn['y'] + ($p->getY() + $pts[$j]->getY()) * $P;
    }
    
    $cn['x'] = $cn['x'] / ( 6 * $a);
    $cn['y'] = $cn['y'] / ( 6 * $a);
    
    $centroid = new Point($cn['x'], $cn['y']);
    return $centroid;
  }


  public function geodesicCentroid($radius) {
    if ($this->isEmpty()) return NULL;
    
    if ($this->geos()) {
      return geoPHP::geosToGeometry($this->geos()->centroid());
    }

    if (strtolower($this->getGeomType()) == 'multipolygon') {
      return ($this->centroid());
    }
    
    //check if above 70/below -70 degrees
    $bbox = $this->getBBox();
    $switchToMeters = false;
    if (($bbox['maxy'] > 70) && ($bbox['miny'] > 70)) {
      $pole = 'north';
      $switchToMeters = true;
    }
    if (($bbox['maxy'] < -70) && ($bbox['miny'] < -70)) {
      $pole = 'south';
      $switchToMeters = true;
    }
    if ($switchToMeters) {
      $transform = new PolarStereographic();
      $mPoly = $transform->read($this, $radius, $pole); 
      $mCentroid = $mPoly->centroid();
      $centroid = $transform->write($mCentroid, $radius, $pole); 
    } else {
      $centroid = $this->centroid();
    }

    return $centroid;
  }

	/**
	 * Find the outermost point from the centroid
	 *
	 * @returns Point The outermost point
	 */
  public function outermostPoint() {
		$centroid = $this->getCentroid();

		$max = array('length' => 0, 'point' => null);

		foreach($this->getPoints() as $point) {
			$lineString = new LineString(array($centroid, $point));

			if($lineString->length() > $max['length']) {
				$max['length'] = $lineString->length();
				$max['point'] = $point;
			}
		}

		return $max['point'];
  }

  public function exteriorRing() {
    if ($this->isEmpty()) return new LineString();
    return $this->components[0];
  }
  
  public function numInteriorRings() {
    if ($this->isEmpty()) return 0;
    return $this->numGeometries()-1;
  }
  
  public function interiorRingN($n) {
    return $this->geometryN($n+1);
  }
  
  public function dimension() {
    if ($this->isEmpty()) return 0;
    return 2;
  }

  public function isSimple() {
    if ($this->geos()) {
      return $this->geos()->isSimple();
    }
    
    $segments = $this->explode();
    
    foreach ($segments as $i => $segment) {
      foreach ($segments as $j => $check_segment) {
        if ($i != $j) {
          if ($segment->lineSegmentIntersect($check_segment)) {
            return FALSE;
          }
        }
      }
    }
    return TRUE;
  }

  // BROKEN
  // tweaked from
  //http://forum.worldwindcentral.com/showthread.php?20724-A-method-to-compute-the-area-of-a-spherical-polygon
  //
  public function haversineArea($radius = 6378)  { //radius in km

    /// Haversine function : hav(x) = (1-cos(x))/2
    $haversineFunction = function($x) { return ( 1.0 - cos($x) ) / 2.0; };

    $lam1 = 0; $lam2 = 0; $beta1 =0; $beta2 = 0; $cosB1 =0; $cosB2 = 0;
    $hav = 0;
    $sum = 0;

    $points = $this->getPoints();
    for($j=0; $j<$this->numPoints()-1; $j++) {

	if( $j == 0 ) {
	    $p1 = $points[$j];
	    $p2 = $points[$j+1];
	} else {
	  $k = ( $j + 1 ) % count($points);
	  $p1 = $p2;
	  $p2 = $points[$k];
	}
	$lam1 = $p1->getX(); //lon[$j];
	$beta1 = $p1->getY(); //lat[$j];
	$lam2 = $p2->getX(); //lon[$j + 1];
	$beta2 = $p2->getY(); //lat[$j + 1];
	$cosB1 = cos( $beta1 );
	$cosB2 = cos( $beta2 );

	if( $lam1 != $lam2 ) {
	    $hav = $haversineFunction( $beta2 - $beta1 ) + 
	      $cosB1 * $cosB2 * $haversineFunction( $lam2 - $lam1 );
	    $a = 2 * asin( sqrt( $hav ) );
	    $b = pi()/ 2 - $beta2;
	    $c = pi() / 2 - $beta1;
	    $s = 0.5 * ( $a + $b + $c );
	    $t = tan( $s / 2 ) * tan( ( $s - $a ) / 2 ) *  
	      tan( ( $s - $b ) / 2 ) * tan( ( $s - $c ) / 2 );

	    $excess = abs( 4 * atan( sqrt(abs( $t ) ) ) );
	    if( $lam2 < $lam1 ) {
	      $excess = -1 * $excess;
	    }
	    $sum += $excess;
	  }
    }//for loop
    return (abs( $sum ) * $radius * $radius);
  }

  // rough WKT area guesstimator from OpenLayers 
  //
  public function geodesicArea($radius = 6378)  { //radius in km

    $area = 0.0;
    $points = $this->getPoints();

    if($this->numPoints() > 2) {
      for($i=0; $i<$this->numPoints()-1; $i++) {
	$p1 = $points[$i];
	$p2 = $points[$i+1];
	$area += deg2rad($p2->getX() - $p1->getX()) *
	  (2 + sin(deg2rad($p1->getY())) + sin(deg2rad($p2->getY())));
      }
      $area = $area * $radius * $radius / 2.0;
    }
    return $area;
  }



  // Not valid for this geometry type
  // --------------------------------
  public function length() { return NULL; }
  
}

