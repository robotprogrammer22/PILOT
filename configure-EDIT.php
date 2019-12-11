<?php

  //Edit values in this file and copy to configure.php

  date_default_timezone_set( 'America/Phoenix' );

  class Config {
  
   //database for mars, deimos and phobos
   var $host_mars = '';
   var $port_mars = '';
   var $dbname_mars = 'upc_search_mars';
   var $user_mars = '';
   var $password_mars = '';

   //database for earth and moon
   var $host_moon = '';
   var $port_moon = '';
   var $dbname_moon = 'upc_search_moon';
   var $user_moon = '';
   var $password_moon = '';

   //database for all other targets
   var $host_other = '';
   var $port_other = '';
   var $dbname_other = 'upc_search_other';
   var $user_other = '';
   var $password_other = '';

    //var $maxQueryHits = 5000;
    var $maxQueryPage = 100;
    var $maxQueryDownload = 1000;
    var $maxQueryRender = 100;
    var $fullMapWKT = 'POLYGON((360 90,0 90,0 -90,360 -90,360 90))';
    var $loggingLevel = 1; //only one level now

    //USGS Astrogeology URLs
    var $thumbnailURL = 'https://upcimages.wr.usgs.gov';
    var $nomenclatureURL = 'https://planetarynames.wr.usgs.gov';
    var $rssURL = "https://astrogeology.usgs.gov/rss?category=pilot";
    var $powURL = ""; //set to empty to disable

    //jquery libs
    var $jqueryURL = "js/jquery/jquery.min.js";
    var $jqueryuiURL = "js/jquery/jquery-ui.min.js";
    var $jqueryuiCSS = "js/jquery/jquery-ui-1.10.1.custom/css/custom-theme/jquery-ui-1.10.1.custom.css";
    var $sparklineURL = "js/sparklines/jquery.sparkline.min.js";

    //map libs
    var $openLayersURL = "/astrowebmaps/js/openlayers/ol.js";
    var $openLayersCSS = "/astrowebmaps/css/main.css";
    var $openLayersCSS2 = "/astrowebmaps/js/openlayers/ol.css";
    var $atlasURL = "http://astrowebmaps.wr.usgs.gov/webmapatlas/Layers/maps.js";
    var $astroWebMapsURL = "/astrowebmaps/build/js-min/AstroWebMaps.js";

    //set alert on front page
    var $pilotAlert = "";

  }

?>
