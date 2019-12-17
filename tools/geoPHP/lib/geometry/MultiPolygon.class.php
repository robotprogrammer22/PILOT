<?php
/**
 * MultiPolygon: A collection of Polygons
 */
class MultiPolygon extends Collection 
{
  protected $geom_type = 'MultiPolygon';


  public function haversineArea($radius = 6378)  { //radius in km
    
    $area=0;
    $polys = $this->getComponents();
    foreach ($polys as $poly) {
      $area += $poly->haversineArea($radius);  
    }
    return $area;
  }

  //
  public function geodesicArea($radius = 6378)  { //radius in km
    $area=0;
    $polys = $this->getComponents();
    foreach ($polys as $poly) {
      $area += $poly->geodesicArea($radius);  
    }
    return $area;
  }


  public function geodesicCentroid($radius) {
    return ($this->centroid());
  }

}




