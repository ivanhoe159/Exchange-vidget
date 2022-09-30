<?php     
	error_reporting(E_ERROR | E_PARSE);
	date_default_timezone_set('Asia/Novosibirsk');
	$conn = mysqli_connect("127.0.0.1", "root", "", "valutes");            //connect to MySQL database
	if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } 
	$sql = 'SELECT id, valute FROM charcodes';                             //load all displayed exchanges
    $result = mysqli_query($conn, $sql);
    $time = date('F j, Y H:i', time());                                  
	$xml = simplexml_load_file("http://www.cbr.ru/scripts/XML_daily.asp");  //load XML file
	$ct = mysqli_fetch_all($result, MYSQLI_ASSOC); 
	foreach($ct as $chars)
	{
		if(isset($chars['valute']))
		{
		    $charcods[] = $chars['valute'];                                //save to charcods[] array
		}
	}
	foreach($charcods as $item)                                            //update info
	{
		foreach($xml->Valute as $items)
		{
			if($item == $items->CharCode)
			{
				$piece = explode(",", $items->Value);                     //decode file info into a proper php and mysql format
				$value[] = $piece[0] . '.' . $piece[1];
			}
		}
	}
	$sql = 'SELECT id, time FROM timer';                                  //load saved timer to update
    $result = mysqli_query($conn, $sql);
	$ct = mysqli_fetch_all($result, MYSQLI_ASSOC);
	foreach($ct as $chars)
	{
		if(isset($chars['time']))
		{
		    $times = $chars['time'];
		}
	}
	$sql = 'SELECT id, valute FROM savecodes';                            //load all saved exchanges
	$result = mysqli_query($conn, $sql);
	$ct = mysqli_fetch_all($result, MYSQLI_ASSOC);
	foreach($ct as $chars)
	{
		if(isset($chars['valute']))
		{
		    $savecods[] = $chars['valute'];                               
		}
	}                                                                        //update info
	foreach($savecods as $item)
	{
		foreach($xml->Valute as $items)
		{
			if($item == $items->CharCode)
			{
				$piece = explode(",", $items->Value);
				$values[] = $piece[0] . '.' . $piece[1];
			}
		}
	}
	if($savecods)                                                          //update info to all saved exchanges and save them into the mysql database
	{
		for($i = 0; $i < count($savecods); $i++)
		{
			$sql = 'SELECT id, valute, value, gain FROM changecodes WHERE valute = "'.$savecods[$i].'";';
			$result = mysqli_query($conn, $sql);
			$ct = mysqli_fetch_all($result, MYSQLI_ASSOC);
			if($ct == NULL)
			{
				$sql = 'insert into changecodes (valute, value, gain) values ("'.$savecods[$i].'", '.$values[$i].', 0);';
				$result = mysqli_query($conn, $sql);		
			}
			else 
			{
				foreach($ct as $chars)
				{      
					$id = $chars['id'];
					$oldvalue = $chars['value'];		 
				}
				if($values[$i] - $oldvalue != 0)
				$gains = $values[$i] - $oldvalue;
				else $gains = $chars['gain'];
				$sql = 'UPDATE changecodes SET value = '.$values[$i].', gain = '.$gains.' WHERE id = '.$id.';';
				$result = mysqli_query($conn, $sql);
			}
		}
	}
	header("refresh: $times");                                                      //refresh of a current timer
?>
	
<html>
<title>Money vidget</title>
<head>
<script src="https://code.jquery.com/jquery-3.5.0.js"></script> 
<link rel="stylesheet" href="styles.css">
</head>
<body>
<br>
<div class="rateblock">
<h2 class="centers">Exchange course<br>on <?php echo $time ?></h2>                   <!-- Current date -->
<table> 
<?php
if($charcods)
{
    for($i = 0; $i < count($charcods); ++$i)
    {
		$sql = 'SELECT gain FROM changecodes WHERE valute = "'.$charcods[$i].'";';   //load gains from all displayed exchanges
		$result = mysqli_query($conn, $sql);
		$ct = mysqli_fetch_all($result, MYSQLI_ASSOC);
		foreach($ct as $chars)
	     {      
		    $gains = $chars['gain'];	                                             //gain > 0 - green, < 0 - red
            if($gains >= 0)
                $clr = "up";
            else $clr = "down";			
		}
	    echo "<tr><td><h2 class='mi'>".$charcods[$i]."</h2></td><td><h2>".$value[$i]."</h2></td><td><h2 class='".$clr."'>".$gains."</h2></tr>";  //table with all displayed exchanges
    }
}
else	  
{
	echo "<br><h3 class='centers'>Nothing to show. Select exchange courses in the 'Exchanges to show' panel.</h2>";   //no exchanges - no list
}
?>

</table>
</div>
</body>
</html>