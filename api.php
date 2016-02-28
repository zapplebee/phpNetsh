<?php //api.php
header('Content-Type: application/json');

define('ns', "phpNetsh_");

function ob_catch($function){
  //start the buffer and call the original funciton. if it has a return value, return that. otherwise return buffer
  ob_start();
  $originalReturn = call_user_func($function);
  $r = ob_get_clean();
  if($originalReturn !== null){
    return $originalReturn;
  } else {
    return $r;
  }
}


function error($httpCode,$text){
  http_response_code($httpCode);
  throw new Exception($text);
}

function jp($array){
  echo json_encode($array);
  return $array;
}

class TempFilesHandler {
  
  private $files = array();
  private $dir;
  
  function __construct(){
    $this->files[0] = tempnam(false,false);
    $this->dir = dirname($this->files[0]); 
  }

  public function get($file){
    $this->files[] = $file;
    return file_get_contents($file);
  }
  
  public function put($fileContents){
    $file = tempnam(false,false);
    file_put_contents($file,$fileContents);
    $this->files[] = $file;
    return $file;
  }
  
  public function getDirectory(){
    return $this->dir;
  }
  
  function __destruct() {
    foreach($this->files as $f){
      unlink($f);
    }
  }
}
$f = new TempFilesHandler();

function phpNetsh_getProfiles(){
    
    $profiles_raw = shell_exec('netsh wlan show profiles');

    preg_match_all("/(All|Current) User Profile +: (.+)/", $profiles_raw, $profiles);
    array_shift($profiles);

    $r = array();

    $scopes = $profiles[0];
    $ssids  = $profiles[1];

    for($i = 0 ; $i < count($scopes); $i++){
      
      $r[$ssids[$i]] = array(
      
        'SSID' => $ssids[$i],
        'scope' => $scopes[$i],
      
      );

    }
    
    jp($r);
    return $r;
}


function phpNetsh_getProfileDetails(){

  if(!isset($_POST['SSID'])){
    error(400, 'No SSID Specified');
  }
  
  $profiles = ob_catch('phpNetsh_getProfiles');

  if(!array_key_exists($_POST['SSID'], $profiles)){

    error(400, 'No such SSID as ' . $_POST['SSID']);
  }
  
  $output = shell_exec('Netsh WLAN export profile "'.$_POST['SSID'].'" key=clear folder="'.$GLOBALS['f']->getDirectory().'"');
 
  preg_match("/Interface profile \".+\" is saved in file \"(.+)\"/", $output, $profileFile);
  if(count($profileFile) !== 2){
    error(500, 'Could not export profile');
  }
  
  $fileContents = $GLOBALS['f']->get($profileFile[1]);
  $r = array();
  
  preg_match_all("/<keyMaterial>(.+)<\/keyMaterial>/", $fileContents, $keyMaterial);

  if(count($keyMaterial) !== 2 || !isset($keyMaterial[1][0])){
    $r['password'] = false;
  }else {
    $r['password'] = $keyMaterial[1][0];
  }

  preg_match_all("/<authentication>(.+)<\/authentication>/", $fileContents, $authentication);

  if(count($authentication) !== 2 || !isset($authentication[1][0])){
    $r['auth'] = false;
  }else {
    $r['auth'] = $authentication[1][0];
  }
  
  jp($r);
  return array('profiles' => $profiles, 'fileContents' => $fileContents);
}



function phpNetsh_setProfilePassword(){


  
  if(!isset($_POST['password'])){
    error(400,"Incomplete Form Data: No Password Set");
  }
  
  $password = htmlspecialchars($_POST['password'], ENT_XML1, 'UTF-8');
  
  $t = ob_catch('phpNetsh_getProfileDetails');
  $fileContents = $t['fileContents'];
  $fileContents = preg_replace("/<keyMaterial>(.*)<\/keyMaterial>/", "<keyMaterial>".$password."</keyMaterial>", $t['fileContents']);
  $file = $GLOBALS['f']->put($fileContents);
  $output = shell_exec('Netsh WLAN add profile filename="'.$file.'" user=' . strtolower($t[$_POST['SSID']]['scope']));  
    
  if($output !== "Profile ".$_POST['SSID']." is added on interface Wi-Fi.\n"){
    throw new Exception(trim(explode("\n",$output)[0]));
  } else {
    echo json_encode(array('success' => trim(explode("\n",str_replace("added", "updated", $output))[0])));
  }

}


try{
  if(isset($_POST['action']) && function_exists(ns.$_POST['action'])){
     call_user_func(ns.$_POST['action']);
  } else {
      error(400,"Bad Request");
  }
} catch (Exception $e){
  jp(array("error" => $e->getMessage()));
}



?>