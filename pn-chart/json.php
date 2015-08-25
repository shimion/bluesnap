<?php
require_once 'php-ofc-library/open-flash-chart.php';

// generate some random data
srand((double)microtime()*1000000);

$max = 20;
$tmp = array();
for( $i=0; $i<13; $i++ )
{
  $tmp[] = rand(0,$max);
}

$title = new title( date("D M d Y"));

$bar = new bar();
$bar->set_values( array(3,2,3,4,5,6,7,8,9,3,12,4) );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $bar );

$path = "data.json.inc";
$file = fopen($path, "w");
fwrite($file, $chart->toString());
fclose($file);

?>