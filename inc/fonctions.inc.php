<?php

// conversion date fr en date mysql
function convertDateDb($date, $time) 
{
	$datetime = $date . ' ' . $time . ':00';
	//echo $datetime . '<br>';
	$day    = substr($datetime,0,2);
	//echo $day . '<br>';
	$month  = substr($datetime,3,2);
	//echo $month . '<br>';
	$year   = substr($datetime,6,4);
	//echo $year . '<br>';
	$hour   = substr($datetime,11,2);
	//echo $hour . '<br>';
	$minute = substr($datetime,14,2);
	//echo $minute . '<br>';
	$second = substr($datetime,17,2);
	//echo $second . '<br>';
	$timestamp= mktime($hour,$minute,$second,$month,$day,$year);
	return date('Y-m-d H:i:s',$timestamp);  
}

function membreConnecte()
{
	if(isset($_SESSION['membre']) && !empty($_SESSION['membre']))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function membreConnecteAdmin()
{
	if(isset($_SESSION['membre']['statut']) && $_SESSION['membre']['statut'] == 1)
	{
		return true;
	}
	else
	{
		return false;
	}
}

//$resultat = convertDateDb('27/01/2017', '19:00');
//echo $resultat . '<br>';

// autre conversion date fr en date mysql
/*function convertDateDb($date, $time)
{
	$date_explode = explode("/", $date);
	$date_implode = implode("-", $date_explode);
	$date_sql = $date_implode . ' ' . $time;
}*/

?>