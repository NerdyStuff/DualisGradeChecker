<?php
// Debug:
$debug_enabled = false;

// Login credentials:
$username = "sXXXXXX@student.dhbw-mannheim.de";
$password = "XXXXXXXXXXXX";
$receiverMail = "<mail>@domain.com";

// Select options:
$options = array();
$options[] = "000000015058000"; // SoSe 2020
$options[] = "000000015048000"; // WiSe 2019/20
$options[] = "000000015038000"; // SoSe 2019
$options[] = "000000015028000"; // WiSe 2018/2019

// Cookie file
$cookie_path = './cookies.txt';

// Temp store for grades
$current_grades_path = './current_grades.txt';

// Create headers array:
$headers = array();
$headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:72.0) Gecko/20100101 Firefox/72.0';
$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
$headers[] = 'Accept-Language: de,en-US;q=0.7,en;q=0.3';
$headers[] = 'Dnt: 1';
$headers[] = 'Connection: keep-alive';
$headers[] = 'Upgrade-Insecure-Requests: 1';
$headers[] = 'Pragma: no-cache';
$headers[] = 'Cache-Control: no-cache';
$headers[] = 'Content-Type: application/x-www-form-urlencoded';

//DEBUG
if($debug_enabled)
{
	print_r($headers);
}

// Init:
$ch = curl_init();

// Set options:
curl_setopt($ch, CURLOPT_URL, 'https://dualis.dhbw.de/scripts/mgrqispi.dll');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "APPNAME=CampusNet&PRGNAME=LOGINCHECK&ARGUMENTS=clino,usrname,pass,menuno,menu_type,browser,platform&clino=000000000000001&menuno=000324&menu_type=classic&browser=&platform=&usrname=".$username."&pass=".$password);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_path);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_path);
curl_setopt($ch, CURLOPT_VERBOSE, true );
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute login query:
$result = curl_exec($ch);

if (curl_errno($ch))
{
	echo 'Error:' . curl_error($ch);
}

//DEBUG
if($debug_enabled)
{
	echo $result;
}

// Get header:
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($result, 0, $header_size);
$arguments_start = strstr($header, "ARGUMENTS=");
$arguments = strstr($arguments_start, "Set-cookie:", true);
$arguments = substr($arguments, 10);

//DEBUG
if($debug_enabled)
{
	echo $arguments;
}

// Get header token:
$url_post_field = "APPNAME=CampusNet&PRGNAME=COURSERESULTS&ARGUMENTS=".$arguments;

// Set new options:
curl_setopt($ch, CURLOPT_URL, 'https://dualis.dhbw.de/scripts/mgrqispi.dll');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $url_post_field);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_path);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_path);
curl_setopt($ch, CURLOPT_VERBOSE, true );
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute query
$result = curl_exec($ch);

if (curl_errno($ch))
{
	echo 'Error:' . curl_error($ch);
}

//DEBUG
if($debug_enabled)
{
	echo $result;
}

$post_param_base = "APPNAME=CampusNet&PRGNAME=COURSERESULTS&ARGUMENTS=sessionno,menuno,semester";

// Split arguments
$argumentsArray = explode(",", $arguments);
$sessionNo = substr($argumentsArray[0], 2);
$menuId = substr($argumentsArray[1], 2);

//DEBUG
if($debug_enabled)
{
	echo $sessionNo;
	echo $menuId;
}

$resultsArray = array(count($options));

// Get all results from options
for($i = 0; $i < count($options); $i++)
{
	$post_param_option = $post_param_base."&sessionno=".$sessionNo."&menuno=".$menuId."&semester=".$options[$i];

	//DEBUG
	if($debug_enabled)
	{
		echo $post_param_option;
	}

	// Set new options:
	curl_setopt($ch, CURLOPT_URL, 'https://dualis.dhbw.de/scripts/mgrqispi.dll');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_param_option);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_path);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_path);
	curl_setopt($ch, CURLOPT_VERBOSE, false );
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	// Execute query
	$result = curl_exec($ch);

	if (curl_errno($ch))
	{
		echo 'Error:' . curl_error($ch);
	}

	//DEBUG
	if($debug_enabled)
	{
		echo $result."<br><br><br>";
	}

	$resultsArray[$i] = $result;
}

//DEBUG
if($debug_enabled)
{
	echo (count($resultsArray));
	print_r($resultsArray);
}

// String to store current grade state
$currentGradeString = "";

for($j = 0; $j < count($resultsArray); $j++)
{
	// New DOM parser object
	$dom = new DOMDocument();

	// Load html from array into parser
	if (!$dom->loadHTML($resultsArray[$j]))
	{
			die("Error");
	}

	$dom->preserveWhiteSpace = false;

	// Get table -> tbody -> tr (row)
	$tables = $dom->getElementsByTagName('table');
	$tbody = $tables->item(0)->getElementsByTagName('tbody');
	$rows = $tbody->item(0)->getElementsByTagName('tr');

	//DEBUG
	if($debug_enabled)
	{	
		echo "Tables: ";
		var_dump($tables);
		echo "TBody: ";
		var_dump($tbody);
		echo "Rows: ";
		var_dump($rows);
	}

	// Loop over rows
	foreach($rows as $row)
	{
		$cells = $row -> getElementsByTagName('td');

		$counter = 0; // Counter to get just needed infos 

  	foreach ($cells as $cell)
		{
			// Counter is used, to store only collumns 2 -> 5
			if($counter > 0 && $counter < 5)
			{
				$currentGradeString .= str_replace("\r", '', str_replace("\n", '', str_replace("\t", '', str_replace(' ', '', $cell->nodeValue)))); // print cells' content as 124578
			}

			$counter++;
  	}
	}
}

// Close connection:
curl_close ($ch);

// If no file exists, create one
if(!file_exists($current_grades_path))
{
	// Write
	$fp = fopen($current_grades_path, 'w');
	fwrite($fp, $currentGradeString);
	fclose($fp);
}

else
{
	// Read file with old grades
	$fileString = file_get_contents($current_grades_path);

	// If strings don't match send mail, else do nothing
	if($fileString != $currentGradeString)
	{
		//DEBUG
		if($debug_enabled)
		{	
			echo "Not the same!";
		}

		// Send mail
		sendMailToMe();

		// Send Telegram message 
		//sendTelegramMessage();

		// Write current grades to file
		$fp = fopen($current_grades_path, 'w');
		fwrite($fp, $currentGradeString);
		fclose($fp);
	}
}

// Function to send an email
function sendMailToMe($mail)
{
	$receiver = $mail;

	// Set Email header
	$header  = 'MIME-Version: 1.0' . "\r\n";
	$header .= 'Content-type: text/html; charset=ISO-8859-1' . "\r\n";
	$header .= "From: MAIL SERVER"."\r\n";

	// Mail title:
	$title = "Neue Noten im Dashboard!";

	// Mail body:
	$message = '<html><head><title>Neuigkeiten</title></head><body><p>Es gibt neue Noten in dualis.</p></body></html>';
		
	if(!mail($receiver, $title, $message, $header))
	{die("Error");}
}

//Function to send telegram message via bot api
/*
function sendTelegramMessage()
{

}*/
?>
