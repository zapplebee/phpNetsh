<!DOCTYPE html>
<html>
<head>
<title>phpNetsh</title>
<script src="apiinterface.js"></script>
<style>
*{
  box-sizing: border-box;
  font-family: sans-serif;
  transition: all .15s linear;
}

body,html{
  padding: 0;
  margin: 0;
  background-color:#E3FFFF;
}

#container {
  width: 800px;
  margin:auto;
}

h1,h2{
  margin: 0;
  margin:bottom: 8px;
  padding: 0;
  font-family: sans-serif;
}

h2 {
  height: 28px;
  font-size: 24px;
}

ul{
  list-style:none;
  padding: 0;
  margin: 18px 0px;
  border: 1px solid black;
  
}



li{
  background-color: rgba(255,255,255,.3);
  padding: 8px;
  border-top: 1px solid black;
  max-height: 44px;
  overflow:hidden;
}

li:first-child{
    border-top: 0px solid black;
}

li:nth-child(odd){
  background-color: rgba(255,255,255,1);
}


form{
  display: block;
  padding: 12px;
  text-align:justify;
}

form * {
  display:inline-block;
  margin: 0px;
  width: 20%;
  padding: 4px;
}

form label {
  text-align: right;
}

form label::after{
  content: ":";
}

input[name="auth"]{
  border: 0px;

  cursor: default;
  text-align: left;
}

form input {
  background-color: transparent;
  border: 1px solid black;
}

</style>
</head>
<body>
<div id="container">
<h1>phpNetsh</h1>
<ul>

  <li>
    <h2>SSID</h2>
    <form>
      <label>Password</label><input type="text" name="password"><label>Authentification</label><input type="text" name="auth" readonly value="WPA2PSK"><button type="submit">Save</button>
    </form>
  </li>
</ul>
</div>

<script>
phpnetshinit();
</script>

</body>
</html>