<?php
/**
 * LineString. A collection of Points representing a line.
 * A line can have more than one segment.
 */
class LineString extends Collection
{
  protected $geom_type = 'LineString';

  /**
   * Constructor
   *
   * @param array $points An array of at least two points with
   * which to build the LineString
   */
  public function __construct($points = array()) {
    if (count($points) == 1) {
      throw new Exception("Cannot construct a LineString with a single point");
    }

    // Call the Collection constructor to build the LineString
    parent::__construct($points);
  }

  // The boundary of a linestring is itself
  public function boundary() {
    return $this;
  }

  public function startPoint() {
    return $this->pointN(1);
  }

  public function endPoint() {
    $last_n = $this->numPoints();
    return $this->pointN($last_n);
  }

  public function isClosed() {
    return ($this->startPoint()->equals($this->endPoint()));
  }

  public function isRing() {
    return ($this->isClosed() && $this->isSimple());
  }

  public function numPoints() {
    return $this->numGeometries();
  }

  public function pointN($n) {
    return $this->geometryN($n);
  }

  public function dimension() {
    if ($this->isEmpty()) return 0;
    return 1;
  }

  public function area() {
    return 0;
  }

  public function length() {
    if ($this->geos()) {
      return $this->geos()->length();
    }
    $length = 0;
    foreach ($this->getPoints() as $delta => $point) {
      $previous_point = $this->geometryN($delta);
      if ($previous_point) {
        $length += sqrt(pow(($previous_point->getX() - $point->getX()), 2) + pow(($previous_point->getY()- $point->getY()), 2));
      }
    }
    return $length;
  }

  public function greatCircleLength($radius = 6378137) {
    $length = 0;
    $points = $this->getPoints();
    for($i=0; $i<$this->numPoints()-1; $i++) {
      $point = $points[$i];
      $next_point = $points[$i+1];
      if (!is_object($next_point)) {continue;}
      // Great circle method
      $lat1 = deg2rad($point->getY());
      $lat2 = deg2rad($next_point->getY());
      $lon1 = deg2rad($point->getX());
      $lon2 = deg2rad($next_point->getX());
      $dlon = $lon2 - $lon1;
      $length +=
        $radius *
          atan2(
            sqrt(
              pow(cos($lat2) * sin($dlon), 2) +
                pow(cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dlon), 2)
            )
            ,
            sin($lat1) * sin($lat2) +
              cos($lat1) * cos($lat2) * cos($dlon)
          );
    }
    // Returns length in meters.
    return $length;
  }

  public function haversineLength() {
    $degrees = 0;
    $points = $this->getPoints();
    for($i=0; $i<$this->numPoints()-1; $i++) {
      $point = $points[$i];
      $next_point = $points[$i+1];
      if (!is_object($next_point)) {continue;}
      $degree = rad2deg(
        acos(
          sin(deg2rad($point->getY())) * sin(deg2rad($next_point->getY())) +
            cos(deg2rad($point->getY())) * cos(deg2rad($next_point->getY())) *
              cos(deg2rad(abs($point->getX() - $next_point->getX())))
        )
      );
      $degrees += $degree;
    }
    // Returns degrees
    return $degrees;
  }

    /**
     * Calculate the distance between two
     * points using Vincenty's formula.
     *
     * Supply instances of the coordinate class.
     *
     * http://www.movable-type.co.uk/scripts/LatLongVincenty.html
     *
     * @param object $p1
     * @param object $p2
     * @return float
     */
    public function vincenty($radius = 6378137) {

      $distance = 0;
      $points = $this->getPoints();
      for($i=0; $i<$this->numPoints()-1; $i++) {
	$p1 = $points[$i];
	$p2 = $points[$i+1];

	$a     = $radius; //$this->majorSemiax;
	$b     = $radius; //$this->minorSemiax;
        $f     = ($a - $b) / $a;  //flattening of the ellipsoid
        $L     = deg2rad($p2->getX()) - deg2rad($p1->getX()); //$p2->longRadian - $p1->longRadian;  //difference in longitude
        $U1    = atan((1 - $f) * tan(deg2rad($p1->getY()) )); //$p1->latRadian));  //U is 'reduced latitude'
	$U2    = atan((1 - $f) * tan(deg2rad($p2->getY()) )); //$p2->latRadian));
        $sinU1 = sin($U1);
        $sinU2 = sin($U2);
        $cosU1 = cos($U1);
        $cosU2 = cos($U2);

        $lambda  = $L;
        $lambdaP = 2 * pi();
        $i = 20;

        while(abs($lambda - $lambdaP) > 1e-12 and --$i > 0) {
            $sinLambda = sin($lambda);
            $cosLambda = cos($lambda);
            $sinSigma  = sqrt(($cosU2 * $sinLambda) * ($cosU2 * $sinLambda) + ($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda) * ($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda));

            if($sinSigma == 0)
                return 0;  //co-incident points

            $cosSigma   = $sinU1 * $sinU2 + $cosU1 * $cosU2 * $cosLambda;
            $sigma      = atan2($sinSigma, $cosSigma);
            $sinAlpha   = $cosU1 * $cosU2 * $sinLambda / $sinSigma;
            $cosSqAlpha = 1 - $sinAlpha * $sinAlpha;
            $cos2SigmaM = $cosSigma - 2 * $sinU1 * $sinU2 / $cosSqAlpha;
            if(is_nan($cos2SigmaM))
                $cos2SigmaM = 0;  //equatorial line: cosSqAlpha=0 (6)
            $c = $f / 16 * $cosSqAlpha * (4 + $f * (4 - 3 * $cosSqAlpha));
            $lambdaP = $lambda;
            $lambda = $L + (1 - $c) * $f * $sinAlpha * ($sigma + $c * $sinSigma * ($cos2SigmaM + $c * $cosSigma * (-1 + 2 * $cos2SigmaM * $cos2SigmaM)));
        }

        if($i == 0)
            return false;  //formula failed to converge

        $uSq = $cosSqAlpha * ($a * $a - $b * $b) / ($b * $b);
        $A   = 1 + $uSq / 16384 * (4096 + $uSq * (-768 + $uSq * (320 - 175 * $uSq)));
        $B   = $uSq / 1024 * (256 + $uSq * (-128 + $uSq * (74 - 47 * $uSq)));
        $deltaSigma = $B * $sinSigma * ($cos2SigmaM + $B / 4 * ($cosSigma * (-1 + 2 * $cos2SigmaM * $cos2SigmaM) - $B / 6 * $cos2SigmaM * (-3 + 4 * $sinSigma * $sinSigma) * (-3 + 4 * $cos2SigmaM * $cos2SigmaM)));
        $d = $b * $A * ($sigma - $deltaSigma);

	$distance += number_format($d, 3, '.', ''); //round to 1mm precision
      }//for loop
      
      return $distance;
    }


  public function explode() {
    $parts = array();
    $points = $this->getPoints();

    foreach ($points as $i => $point) {
      if (isset($points[$i+1])) {
        $parts[] = new LineString(array($point, $points[$i+1]));
      }
    }
    return $parts;
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

  // Utility function to check if any line sigments intersect
  // Derived from http://stackoverflow.com/questions/563198/how-do-you-detect-where-two-line-segments-intersect
  public function lineSegmentIntersect($segment) {
    $p0_x = $this->startPoint()->x();
    $p0_y = $this->startPoint()->y();
    $p1_x = $this->endPoint()->x();
    $p1_y = $this->endPoint()->y();
    $p2_x = $segment->startPoint()->x();
    $p2_y = $segment->startPoint()->y();
    $p3_x = $segment->endPoint()->x();
    $p3_y = $segment->endPoint()->y();

    $s1_x = $p1_x - $p0_x;     $s1_y = $p1_y - $p0_y;
    $s2_x = $p3_x - $p2_x;     $s2_y = $p3_y - $p2_y;

    $fps = (-$s2_x * $s1_y) + ($s1_x * $s2_y);
    $fpt = (-$s2_x * $s1_y) + ($s1_x * $s2_y);

    if ($fps == 0 || $fpt == 0) {
      return FALSE;
    }

    $s = (-$s1_y * ($p0_x - $p2_x) + $s1_x * ($p0_y - $p2_y)) / $fps;
    $t = ( $s2_x * ($p0_y - $p2_y) - $s2_y * ($p0_x - $p2_x)) / $fpt;

    if ($s > 0 && $s < 1 && $t > 0 && $t < 1) {
      // Collision detected
      return TRUE;
    }
    return FALSE;
  }
}

