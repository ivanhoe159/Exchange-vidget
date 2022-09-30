<?php     
	date_default_timezone_set('Asia/Novosibirsk');
	error_reporting(E_ERROR | E_PARSE);
	$xml = simplexml_load_file("http://www.cbr.ru/scripts/XML_daily.asp");
	foreach($xml->Valute as $items)
	{
		$charcode[] = $items->CharCode;
	}
	
?>

<html>
<title>Main page</title>
<head>
<link rel="stylesheet" href="styles.css">
<br>
<h1>Exchange control panel</h1>
</head>
 <script src="https://code.jquery.com/jquery-3.5.0.js"></script>
<script>
   function saveAndSend()                                                      //save all "exchanges to save"
   {
       var elm = document.getElementById("savexc[]");
       var values = [];
       for (var i = 0; i < elm.options.length; i ++) 
	   {
           if (elm.options[i].selected) 
		   {
               values.push(elm.options[i].value);
		   }
       }   
	   var stri = JSON.stringify(values);                                      //send them by the GET() method
	   window.location.href = 'http://localhost/forext/main.php?stri=' + stri;
	   alert("Successfully saved");
   }
</script>
<script>
  function saveAndShow()                                                        //save all "exchanges to show"
   {
       var elm = document.getElementById("showxc[]");
       var values = [];   
       for (var i = 0; i < elm.options.length; i ++) 
	   {
           if (elm.options[i].selected) 
		   {	   
               values.push(elm.options[i].value);
		   }
       }   
	   var strix = JSON.stringify(values);
	   window.location.href = 'http://localhost/forext/main.php?strix=' + strix;
	   alert("Successfully selected");
   }
</script>
<script>
  function saveAndTime()                                                          //save current timer value
	{
		var elm = document.getElementById("timexc[]");
		for (var i = 0; i < elm.options.length; i ++) 
		{
			if (elm.options[i].selected) 
			{	   
               	var strit = JSON.stringify(elm.options[i].value);
			}
		}   
		window.location.href = 'http://localhost/forext/main.php?strit=' + strit;
		alert("Successfully timed");
	}
</script>
<body>
<iframe src="index.php" width="470" height="600"  frameborder="0" scrolling="no" align="left">     <!-- Frame with exchange courses -->
</iframe> 
<?php 
if($_GET['stri'])                                                                   //load new exchanges to save
{
  $sk = $_GET['stri'];
  $arr = json_decode($sk);
  $conn = mysqli_connect("127.0.0.1", "root", "", "valutes");
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  } 
  $sql = 'select * from changecodes';
  $result = mysqli_query($conn, $sql);
  $ct = mysqli_fetch_all($result, MYSQLI_ASSOC);
  if($ct)
  {
		foreach($ct as $chars)
		{
			if(isset($chars['valute']))
			{
			$savedval[] = $chars['valute'];
		}
	}
	for($i = 0; $i < count($savedval); ++$i)                                         //save new exchanges and delete old ones; if exchange & gain already exists - do nothing
	{
		$f = 0;
		for($j = 0; $j < count($arr); ++$j)
		{
			if($savedval[$i] == $arr[$j])
				$f = 1;
		}
		if($f == 0)
		{
			$sql = 'delete from changecodes where valute = "'.$savedval[$i].'";';
			$result = mysqli_query($conn, $sql);  
		}
	}
  } 
  $sql = 'delete from charcodes';                                                       //replace outdated info
  $result = mysqli_query($conn, $sql);
  $sql = 'delete from savecodes';
  $result = mysqli_query($conn, $sql);
  for($i = 0; $i < count($arr); ++$i)
  {
	$sql = 'insert into savecodes (valute) values ("' .$arr[$i]. '");';
    $result = mysqli_query($conn, $sql);
  }
}
if($_GET['strix'])                                                                       //load new exchanges to show
{
  $sk = $_GET['strix'];
  $arr = json_decode($sk);
  $conn = mysqli_connect("127.0.0.1", "root", "", "valutes");
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  } 
  $sql = 'delete from charcodes';
  $result = mysqli_query($conn, $sql);
  for($i = 0; $i < count($arr); ++$i)
  {
	$sql = 'insert into charcodes (valute) values ("' .$arr[$i]. '");';
    $result = mysqli_query($conn, $sql);
  }
}
if($_GET['strit'])                                                                       //load timer
{
  $sk = $_GET['strit'];
  $arr = json_decode($sk);
  $conn = mysqli_connect("127.0.0.1", "root", "", "valutes");
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  } 
  $sql = 'delete from timer';
  $result = mysqli_query($conn, $sql);
  $sql = 'insert into timer values (0, '.$arr.');';
  $result = mysqli_query($conn, $sql);
}
?>
<div class="mainblock">
<form action="main.php" method="POST">
<table> 
<tr><td><h2>Exchanges to save:</h2></td><td><h2><select id="savexc[]" size="3" multiple>      <!-- All exchanges from a file -->
<?php
   $is = 0;
   foreach($charcode as $chars)                             
   {
	   $is++;
	   echo "<option id='".$is."' name='".$chars."'>".$chars."</option>";		
   }
?>
</select></h2></td></tr></table> 
<button id="test" class="enter f2" onclick="saveAndSend()">Save exchanges</button>                 <!-- Button to save exchanges -->
<br><br>
<table><tr><td><h2>Exchanges to show:</h2></td><td><h2><select id="showxc[]" size="3" multiple>   <!-- All saved exchanges that can be shown -->
<?php
    $conn = mysqli_connect("127.0.0.1", "root", "", "valutes");
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } 
	$sql = 'SELECT id, valute FROM savecodes';
    $result = mysqli_query($conn, $sql);
	$ct = mysqli_fetch_all($result, MYSQLI_ASSOC);
	foreach($ct as $chars)
	{
		if(isset($chars['valute']))
		{
		    $savecode[] = $chars['valute'];
		}
	}
    foreach($savecode as $chars)
    {
	   $is++;
	   echo "<option id='".$is."' name='".$chars."'>".$chars."</option>";			
    }
?>
</select></h2></td></tr></table>
<button id="test" class="enter f2" onclick="saveAndShow()">Show exchanges</button><br><br>           <!-- Button to show exchanges -->
<table><tr><td><h2>Update every:</h2></td>
<td><h2>
<?php
    $conn = mysqli_connect("127.0.0.1", "root", "", "valutes");                                    //timer choose
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } 
	$sql = 'SELECT * FROM timer';
	$result = mysqli_query($conn, $sql);
	$ct = mysqli_fetch_all($result, MYSQLI_ASSOC);
	foreach($ct as $chars)
	{
		if(isset($chars['time']))
		{
		    $timeval = $chars['time'];
		}
	}	
?>
<select id = "timexc[]">
    <option value="1">1 second</option>
    <option value="5">5 seconds</option>
	<option value="10">10 seconds</option>
	<option value="30">30 seconds</option>
	<option value="60">1 minute</option>
    <option value="1800">30 minutes</option>
    <option value="3600">1 hour</option>
    <option value="7200">2 hours</option>
    <option value="10800">3 hours</option>
    <option value="86400">1 day</option>
	<option value="172800">2 days</option>
</select></h2></td></tr></table><br>
<script>
var timez = '<?php echo $timeval;?>';
var elm = document.getElementById("timexc[]");
for (var i = 0; i < elm.options.length; i ++) 
{
	if(elm.options[i].value == timez)
	{
		elm.options[i].selected = true;
	}
}
</script>
<button id="test" class="enter f2" onclick="saveAndTime()">Save time settings</button><br><br>
</form>
</div>
</body>
</html>