<?php

/*
 *    
 */



class Logging {

  var $filename;
  var $verbosity;


  function Logging($verbosity=1) {
    $this->filename = dirname(__FILE__) . '/../log/pilot.log';
    $this->verbosity = $verbosity;
  }


  function add($text, $verbosity) {

    if ($verbosity > $this->verbosity) {return(FALSE);}

    $now = date('Y-m-d h:i:s A');
    if (isset($_SERVER['REMOTE_USER'])) {
      $user = $_SERVER['REMOTE_USER'];
    } else if (isset($_SERVER['HTTP_COOKIE'])) {
      preg_match('/(?:\[username\]=)(.+?);/',$_SERVER['HTTP_COOKIE'], $matches);
      $user= (isset($matches[1])) ? $matches[1] : 'unknown';
    } else {
      $user = 'unknown';
    }

    $fp = fopen($this->filename, 'a');
    $rc = fwrite($fp, $now . ': ' . $user . ': ' . $text . "\r\n");
    fclose($fp);

    return($rc);
  }

}

?>