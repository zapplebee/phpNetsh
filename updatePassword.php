<?php
if($_SERVER['HTTP_HOST'] !== "127.0.0.1"){
    header("HTTP/1.1 403 Unauthorized" );
    exit;
}
header('Content-Type: application/json');
session_start();


function jecho($array){

  echo json_encode($array);

}

function printProfile($profile){

  unset($profile['fulltext']);
  unset($profile['filepath']);
  echo json_encode($profile);

}

function updatePassword($SSID,$password){

  $password = htmlspecialchars($password, ENT_XML1, 'UTF-8');
  $_SESSION['profiles'][$SSID]['fulltext'] = preg_replace("/<keyMaterial>(.*)<\/keyMaterial>/", "<keyMaterial>".$password."</keyMaterial>", $_SESSION['profiles'][$SSID]['fulltext']);
  file_put_contents($_SESSION['profiles'][$SSID]['filepath'],$_SESSION['profiles'][$SSID]['fulltext']);
  
  $output = shell_exec('Netsh WLAN add profile filename="'.$_SESSION['profiles'][$SSID]['filepath'].'" user=' . strtolower($_SESSION['profiles'][$SSID]['scope']));

  if($output !== "Profile ".$_SESSION['profiles'][$SSID]['SSID']." is added on interface Wi-Fi.\n"){
    throw new Exception($output);
  } else {
    echo json_encode(array('success' => str_replace("added", "updated", $output)));
  }

}
   
try {
  
  
  //Create an array of filepaths that will be deleted in the finally statement
  $filepaths = array();
  
  // Ensure correct variables are posted
  if(!isset($_POST['SSID'])){
    throw new Exception('Incomplete Form Data: SSID Not Set');
  }
  
  $SSID = $_POST['SSID'];

  //Ensure SSID exists
  if(!isset($_SESSION['profiles'][$SSID])){
    throw new Exception('SSID Not Found');
  }
  
  //PHP is drunk and returns bad paths for sys_get_temp_dir() -- like 'C:\PROGRA~2\EASYPH~1.1VC\\binaries\tmp' -- which is not useful.
  //Instead, create a temp file, get its directory, and add this temp file to the filepaths array (which will be deleted in the finally statement).
  $filepaths[] = tempnam(false,false);
  $directory = dirname($filepaths[0]);
  
  //Instead of using the ($SSID|$_POST['SSID']) variable, use the SSID from within the selected profile to ensure that no
  //  user input is entered directly into the shell_exec.
  $output = shell_exec('Netsh WLAN export profile "'.$_SESSION['profiles'][$SSID]['SSID'].'" key=clear folder="'.$directory.'"');
  
  //Try to match the filename of the newly exported file.
  preg_match("/Interface profile \".+\" is saved in file \"(.+)\"/", $output, $profileFile);
  
  //If filename is not found
  if(count($profileFile) !== 2){
    throw new Exception('Error exporting profile');
  }
  
  $filepaths[] = $profileFile[1];
  $_SESSION['profiles'][$SSID]['fulltext'] = file_get_contents($profileFile[1]);
  $_SESSION['profiles'][$SSID]['filepath'] = $profileFile[1];
  

  //Try to match the keyMaterial aka the current password  
  preg_match_all("/<keyMaterial>(.+)<\/keyMaterial>/", $_SESSION['profiles'][$SSID]['fulltext'], $keyMaterial);
  
  //If no password is found
  if(count($keyMaterial) !== 2 || !isset($keyMaterial[1][0])){
    throw new Exception('No password saved for this profile');
  }
  
  $_SESSION['profiles'][$SSID]['keyMaterial'] = $keyMaterial[1][0];
  
  //Try to match the authentication method 
  preg_match_all("/<authentication>(.+)<\/authentication>/", $_SESSION['profiles'][$SSID]['fulltext'], $authentication);
  
  //If no authentication is found
  if(count($authentication) !== 2 || !isset($authentication[1][0])){
    throw new Exception('No authentication for this profile');
  }
  $_SESSION['profiles'][$SSID]['authentication'] = $authentication[1][0];
  
  
  if(isset($_POST['password'])){
    updatePassword($SSID,$_POST['password']);
  } else {
    printProfile($_SESSION['profiles'][$SSID]);
  }
  
  
} catch (Exception $e){
  
  echo json_encode(array('error'=>$e->getMessage()));
  
} finally {
  
  foreach ($filepaths as $file){
    
    unlink($file);
    
  }
  
}
   
?>