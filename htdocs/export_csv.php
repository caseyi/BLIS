<?php
#
# Exports the given HTML content as csv document
#
header('Content-type: application/octet-stream');
header('Content-Disposition: attachment; filename="blisreport_'.date('Ymdhi').'.csv"');
$data=stripcslashes($_REQUEST['csv_data']);
echo $data; 

/*include("../includes/db_lib.php");
putUILog('export_csv', 'X', basename($_SERVER['REQUEST_URI'], ".php"), 'X', 'X', 'X');
$date = date("Ymdhi");
$file_name = "blisreport_".$date.".csv";
require_once('class.html2text.inc'); 
$html_content = $_REQUEST['data'];
//print $html_content;
// The "source" HTML you want to convert. 
//$html = 'Sample string with HTML code in it'; 
$html = $html_content;
var_dump($html); die;

// Instantiate a new instance of the class. Passing the string 
// variable automatically loads the HTML for you. 
$h2t =& new html2text($html); 

// Simply call the get_text() method for the class to convert 
// the HTML to the plain text. Store it into the variable. 
$text = $h2t->get_text(); 

// Or, alternatively, you can print it out directly: 
//return;
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=$file_name");
$h2t->print_text();*/
?>