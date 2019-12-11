<?php

  /*
   * rss proxy
   */

require_once(dirname(__FILE__) . '/../configure.php' );

date_default_timezone_set('America/Phoenix');

  $config = new Config();
  $rssURL = $config->rssURL;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $rssURL);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

$content = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo $content;
?>