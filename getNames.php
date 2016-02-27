<?php
session_start();


$profiles_raw = shell_exec('netsh wlan show profiles');

preg_match_all("/(All|Current) User Profile +: (.+)/", $profiles_raw, $profiles);
array_shift($profiles);

$_SESSION['profiles'] = array();

$scopes = $profiles[0];
$ssids  = $profiles[1];

for($i = 0 ; $i < count($scopes); $i++){
  
  $_SESSION['profiles'][$ssids[$i]] = array(
  
    'SSID' => $ssids[$i],
    'scope' => $scopes[$i],
  
  );

}

echo json_encode($_SESSION['profiles']);

?>