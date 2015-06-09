<<<<<<< HEAD
<?php
#
# Main page for showing disease report and options to export
# Called via POST from reports.php
#
include("redirect.php");
include("includes/db_lib.php");
include("includes/stats_lib.php");
include("includes/script_elems.php");
LangUtil::setPageId("reports");

include("../users/accesslist.php");
 if(!(isLoggedIn(get_user_by_id($_SESSION['user_id']))))
	header( 'Location: home.php' );

$script_elems = new ScriptElems();
$script_elems->enableJQuery();

$uiinfo = "from=".$date_from."%to=".$date_to;
?>
<script type='text/javascript'>
function export_as_word()
{
	var html_data = $('#report_content').html();
	$('#word_data').attr("value", html_data);
	//$('#export_word_form').submit();
	$('#word_format_form').submit();
}

function print_content(div_id)
{
	var DocumentContainer = document.getElementById(div_id);
	var WindowObject = window.open("", "PrintWindow", "toolbars=no,scrollbars=yes,status=no,resizable=yes");
	WindowObject.document.writeln(DocumentContainer.innerHTML);
	WindowObject.document.close();
	WindowObject.focus();
	WindowObject.print();
	WindowObject.close();
	//javascript:window.print();
}
</script>
<form name='word_format_form' id='word_format_form' action='export_word.php' method='post' target='_blank'>
	<input type='hidden' name='data' value='' id='word_data' />
	<input type='hidden' name='lab_id' value='<?php echo $lab_config_id; ?>' id='lab_id'>
	<input type='button' onclick="javascript:print_content('report_content');" value='<?php echo LangUtil::$generalTerms['CMD_PRINT']; ?>'></input>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type='button' onclick="javascript:export_as_word();" value='<?php echo LangUtil::$generalTerms['CMD_EXPORTWORD']; ?>'></input>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type='button' onclick="javascript:window.close();" value='<?php echo LangUtil::$generalTerms['CMD_CLOSEPAGE']; ?>'></input>
</form>
<hr>

<div id='report_content'>
<link rel='stylesheet' type='text/css' href='css/table_print.css' />
<b><?php echo "Specimen Count Report"; ?></b>
<br><br>
<?php
$lab_config_id = $_REQUEST['location'];
$lab_config = LabConfig::getById($lab_config_id);
if($lab_config == null)
{
	echo LangUtil::$generalTerms['MSG_NOTFOUND'];
	return;
}
$date_from = $_REQUEST['yyyy_from']."-".$_REQUEST['mm_from']."-".$_REQUEST['dd_from'];
$date_to = $_REQUEST['yyyy_to']."-".$_REQUEST['mm_to']."-".$_REQUEST['dd_to'];
$uiinfo = "from=".$date_from."&to=".$date_to;
putUILog('reports_specimen_count_grouped', $uiinfo, basename($_SERVER['REQUEST_URI'], ".php"), 'X', 'X', 'X');


$configArray = getSpecimenCountGroupedConfig($lab_config->id);
//echo "--".$configArray['group_by_age'].$configArray['group_by_gender'].$configArray['age_groups'].$configArray['measure_groups'].$configArray['measure_id']."<br>";
# Fetch report configuration
$byAge = $configArray['group_by_age'];
$age_group_list = decodeAgeGroups($configArray['age_groups']);
$byGender = $configArray['group_by_gender'];
$bySection = $configArray['measure_id'];
$combo = $configArray['test_type_id']; // 1 - registered, 2 - completed, 3 - completed / pending 
$combo = 1;
//$age_group_list = $site_settings->getAgeGroupAsList();
?>
<table>
	<tbody>
		<tr>
			<td><?php echo LangUtil::$generalTerms['FACILITY']; ?>:</td>
			<td><?php echo $lab_config->getSiteName(); ?></td>
		</tr>
		<tr>
			<td><?php echo LangUtil::$pageTerms['REPORT_PERIOD']; ?>:</td>
			<td>
			<?php
			if($date_from == $date_to)
			{
				echo DateLib::mysqlToString($date_from);
			}
			else
			{	
				echo DateLib::mysqlToString($date_from)." to ".DateLib::mysqlToString($date_to);
			}
			?>
			</td>
		</tr>
		
		
	</tbody>
</table>
<?php

$table_css = "style='padding: .3em; border: 1px black solid; font-size:14px;'";
?>
<br>
<table style='border-collapse: collapse;'>
	<thead>
		<tr>
			<th><?php echo LangUtil::$generalTerms['SPECIMEN']; ?></th>
			<?php
			if($byGender == 1)
			{
				echo "<th >".LangUtil::$generalTerms['GENDER']."</th>";
			}
			if($byAge == 1)
			{
				echo "<th >".LangUtil::$pageTerms['RANGE_AGE']."</th>";
				for($i = 1; $i < count($age_group_list); $i++)
				{
					echo "<th >".LangUtil::$pageTerms['RANGE_AGE']."</th>";
				}
			}
                        else
                        {
                            echo "<th >"."Count"."</th>";
                        }
			if($byAge == 1 && $byGender == 1)
			{
				echo "<th >".LangUtil::$pageTerms['TOTAL_MF']."</th>";
			}
			?>
			
                        
                        <?php if($byAge == 1 || $byGender == 1)
                        {
                            echo "<th>".LangUtil::$pageTerms['TOTAL_TESTS']."</th>";
                        }
                        ?>
                        
		</tr>
		<tr>
			<th ></th>
			<?php
			if($byGender == 1)
			{
				echo "<th ></th>";
			}
			
			if($byAge == 1)
			{
				foreach($age_group_list as $age_slot)
				{
					echo "<th>$age_slot[0]";
					if(trim($age_slot[1]) == "+")
						echo "+";
					else
						echo " - $age_slot[1]";
					echo "</th>";
				}
			}
                        else
                        {
                            echo "<th ></th>";
                        }
			if($byAge == 1 && $byGender == 1)
			{
				echo "<th ></th>";
			}
                        
                        if($byAge == 1 || $byGender == 1)
                            echo "<th ></th>";
			?>
		<tr>
	</thead>
	<tbody>
        <?php
            # Fetching specimen IDs but keeping the variables similar to reports_testcount_grouped.php
            $test_type_list = get_lab_config_specimen_types($lab_config->id);
            //$test_type_list = get_lab_config_test_types($lab_config->id); // to get test type ids
            $saved_db = DbUtil::switchToLabConfig($lab_config->id);
            $tests_done_list = array();
            $tests_list=array();
            $summ = 0;
            foreach($test_type_list as $test_type_id)
		{
                    $test_name = get_specimen_name_by_id($test_type_id);
                    echo "<tr valign='top'>";
                        echo "<td>";
                            echo $test_name;
                        echo "</td>";
                        
                        if($byGender == 1)
                        {
                            echo "<td>";
                                echo "M";
                                echo "<br>";
                                echo "F";
                            echo "</td>";
                        }
                
                    # Group by age set to true: Fetch age slots from DB
                    if($byAge == 1)
                    {
                        $age_slot_list = decodeAgeGroups($configArray['age_groups']);
                        $count_male_t_total = 0;
                        $count_female_t_total = 0;
                        $count_male_c_total = 0;
                        $count_female_c_total = 0;
                        $count_male_p_total = 0;
                        $count_female_p_total = 0;
                        foreach($age_slot_list as $age_slot)
                        {
                            
                            $age_from = intval(trim($age_slot[0]));
                            if(trim($age_slot[1]) == "+")
                                $age_to = 100;
                            else
                                $age_to = intval(trim($age_slot[1]));
                            
                            if($byGender == 1)
                            {
                                
                                if($combo == 1)
                                {
                                    $gender = 'M';
                                    $count_male_t = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to);
                                    $gender = 'F';
                                    $count_female_t = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to);
                                    $count_male_t_total += $count_male_t;
                                    $count_female_t_total += $count_female_t;                                    
                                    echo "<td>";
                                    echo $count_male_t;
                                    echo "<br>";
                                    echo $count_female_t;
                                    echo "</td>";
                                    
                                }
                                else if ($combo == 2)
                                {
                                    $gender = 'M';
                                    $count_male_c = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to, 1);
                                    $gender = 'F';
                                    $count_female_c = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to, 1);
                                    $count_male_c_total += $count_male_c;
                                    $count_female_c_total += $count_female_c;
                                    echo "<td>";
                                    echo $count_male_c;
                                    echo "<br>";
                                    echo $count_female_c;
                                    echo "</td>";
                                }
                                else if ($combo == 3)
                                {
                                    $gender = 'M';
                                    $count_male_t = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to);
                                    $count_male_c = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to, 1);
                                    $gender = 'F';
                                    $count_female_t = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to);
                                    $count_female_c = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to, 1);
                                    $count_male_p = $count_male_t - $count_male_c;
                                    $count_female_p = $count_female_t - $count_female_c;
                                    
                                    $count_male_c_total += $count_male_c;
                                    $count_female_c_total += $count_female_c;
                                    $count_male_p_total += $count_male_p;
                                    $count_female_p_total += $count_female_p;
                                    
                                    echo "<td>";
                                    echo $count_male_c." / ".$count_male_p;
                                    echo "<br>";
                                    echo $count_female_c." / ".$count_female_p;
                                    echo "</td>";
                                }
                                    
                            }
                            else
                            {
                                if($combo == 1)
                                {
                                    $gender = 'M';
                                    $count_male_t = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to);
                                    $gender = 'F';
                                    $count_female_t = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to);
                                    $count_male_t_total += $count_male_t;
                                    $count_female_t_total += $count_female_t;
                                    echo "<td>";
                                    echo $count_male_t + $count_female_t;
                                    echo "</td>";
                                }
                                else if ($combo == 2)
                                {
                                    $gender = 'M';
                                    $count_male_c = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to, 1);
                                    $gender = 'F';
                                    $count_female_c = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to, 1);
                                    $count_male_c_total += $count_male_c;
                                    $count_female_c_total += $count_female_c;
                                    echo "<td>";
                                    echo $count_male_c + $count_female_c;
                                    echo "</td>";
                                }
                                else if ($combo == 3)
                                {
                                    $gender = 'M';
                                    $count_male_t = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to);
                                    $count_male_c = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to, 1);
                                    $gender = 'F';
                                    $count_female_t = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to);
                                    $count_female_c = get_specimen_count_grouped($test_type_id, $date_from, $date_to, $gender, $age_from, $age_to, 1);
                                    $count_male_p = $count_male_t - $count_male_c;
                                    $count_female_p = $count_female_t - $count_female_c;
                                    $count_male_c_total += $count_male_c;
                                    $count_female_c_total += $count_female_c;
                                    $count_male_p_total += $count_male_p;
                                    $count_female_p_total += $count_female_p;
                                    echo "<td>";
                                    echo ($count_male_c + $count_female_c)." / ".($count_male_p + $count_female_p);
                                    echo "</td>";
                                }
                            }
                        }
                        if($byGender == 1)
                        {
                            if($combo == 1)
                            {
                                    echo "<td>";
                                    echo $count_male_t_total;
                                    echo "<br>";
                                    echo $count_female_t_total;
                                    echo "</td>";
                                    echo "<td>";
                                    echo $count_male_t_total + $count_female_t_total;
                                    echo "</td>";
                            }
                            else if($combo == 2)
                            {
                                    echo "<td>";
                                    echo $count_male_c_total;
                                    echo "<br>";
                                    echo $count_female_c_total;
                                    echo "</td>";
                                    echo "<td>";
                                    echo $count_male_c_total + $count_female_c_total;
                                    echo "</td>";
                            }
                            else if($combo == 3)
                            {
                                    echo "<td>";
                                    echo $count_male_c_total." / ".$count_male_p_total;
                                    echo "<br>";
                                    echo $count_female_c_total." / ".$count_female_p_total;
                                    echo "</td>";
                                    echo "<td>";
                                    echo ($count_male_c_total + $count_female_c_total)." / ".($count_male_p_total + $count_female_p_total);
                                    echo "</td>";
                            }
                        }
                        else
                        {
                            if($combo == 1)
                            {
                                    echo "<td>";
                                    echo $count_male_t_total + $count_female_t_total;
                                    echo "</td>";
                            }
                            else if($combo == 2)
                            {
                                    echo "<td>";
                                    echo $count_male_c_total + $count_female_c_total;
                                    echo "</td>";
                            }
                            else if($combo == 3)
                            {
                                    echo "<td>";
                                    echo ($count_male_c_total + $count_female_c_total)." / ".($count_male_p_total + $count_female_p_total);
                                    echo "</td>";
                            }
                        }
                    }
                    else
                    {
                        if($byGender == 1)
                            {
                                if($combo == 1)
                                {
                                    $gender = 'M';
                                    $count_male_t = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender);
                                    $gender = 'F';
                                    $count_female_t = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender);
                                    echo "<td>";
                                    echo $count_male_t;
                                    echo "<br>";
                                    echo $count_female_t;
                                    echo "</td>";
                                    echo "<td>";
                                    echo $count_male_t + $count_female_t;
                                    echo "</td>";
                                }
                                else if ($combo == 2)
                                {
                                    $gender = 'M';
                                    $count_male_c = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender, 1);
                                    $gender = 'F';
                                    $count_female_c = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender, 1);
                                    
                                    echo "<td>";
                                    echo $count_male_c;
                                    echo "<br>";
                                    echo $count_female_c;
                                    echo "</td>";
                                    echo "<td>";
                                    echo $count_male_c + $count_female_c;
                                    echo "</td>";
                                }
                                else if ($combo == 3)
                                {
                                    $gender = 'M';
                                    $count_male_t = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender);
                                    $count_male_c = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender, 1);
                                    $gender = 'F';
                                    $count_female_t = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender);
                                    $count_female_c = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender, 1);
                                    $count_male_p = $count_male_t - $count_male_c;
                                    $count_female_p = $count_female_t - $count_female_c;
                                    echo "<td>";
                                    echo $count_male_c." / ".$count_male_p;
                                    echo "<br>";
                                    echo $count_female_c." / ".$count_female_p;
                                    echo "</td>";
                                    echo "<td>";
                                    echo ($count_male_c + $count_female_c)." / ".($count_male_p + $count_female_p);
                                    echo "</td>";
                                }
                                    
                            }
                            else
                            {
                                 if($combo == 1)
                                {
                                    $gender = 'M';
                                    $count_male_t = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender);
                                    $gender = 'F';
                                    $count_female_t = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender);
                                    echo "<td>";
                                    echo $count_male_t + $count_female_t;
                                    echo "</td>";
                                }
                                else if ($combo == 2)
                                {
                                    $gender = 'M';
                                    $count_male_c = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender, 1);
                                    $gender = 'F';
                                    $count_female_c = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender, 1);
                                    echo "<td>";
                                    echo $count_male_c + $count_female_c;
                                    echo "</td>";
                                }
                                else if ($combo == 3)
                                {
                                    $gender = 'M';
                                    $count_male_t = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender);
                                    $count_male_c = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender, 1);
                                    $gender = 'F';
                                    $count_female_t = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender);
                                    $count_female_c = get_specimen_count_grouped2($test_type_id, $date_from, $date_to, $gender, 1);
                                    $count_male_p = $count_male_t - $count_male_c;
                                    $count_female_p = $count_female_t - $count_female_c;
                                    echo "<td>";
                                    echo ($count_male_c + $count_female_c)." / ".($count_male_p + $count_female_p);
                                    echo "</td>";
                                }
                            }
                    }
                    echo "</tr>";
                } 
        ?>
 <!-- ********************************************************************** -->
	
	</tbody>
</table>
<br><br><br>
............................................
</div>
=======
<?php
#
# Main page for showing disease report and options to export
# Called via POST from reports.php
#
include("redirect.php");
include("includes/db_lib.php");
include("includes/stats_lib.php");
include("includes/script_elems.php");
LangUtil::setPageId("reports");

include("../users/accesslist.php");
 if(!(isLoggedIn(get_user_by_id($_SESSION['user_id']))))
	header( 'Location: home.php' );

$script_elems = new ScriptElems();
$script_elems->enableJQuery();
$script_elems->enableTableSorter();

$uiinfo = "from=".$date_from."%to=".$date_to;
?>
<html>
<head>
<script type="text/javascript" src="js/table2CSV.js"></script>
<script type='text/javascript'>
var curr_orientation = 0;
function export_as_word(div_id)
{
	var html_data = $('#'+div_id).html();
	$('#word_data').attr("value", html_data);
	//$('#export_word_form').submit();
	$('#word_format_form').submit();
}

function export_as_pdf(div_id)
{
	var content = $('#'+div_id).html();
	$('#pdf_data').attr("value", content);
	$('#pdf_format_form').submit();
}

function export_as_txt(div_id)
{
	var content = $('#'+div_id).html();
	$('#txt_data').attr("value", content);
	$('#txt_format_form').submit();
}

function export_as_csv(table_id, table_id2)
{
	var content = $('#'+table_id).table2CSV({delivery:'value'}) + '\n\n' + $('#'+table_id2).table2CSV({delivery:'value'});
	$("#csv_data").val(content);
	$('#csv_format_form').submit();
}

function print_content(div_id)
{
	var DocumentContainer = document.getElementById(div_id);
	var WindowObject = window.open("", "PrintWindow", "toolbars=no,scrollbars=yes,status=no,resizable=yes");
	WindowObject.document.writeln(DocumentContainer.innerHTML);
	WindowObject.document.close();
	WindowObject.focus();
	WindowObject.print();
	WindowObject.close();
	//javascript:window.print();
}

$(document).ready(function(){
	$("input[name='do_landscape']").click( function() {
		change_orientation();
	});
});

function change_orientation()
{
	var do_landscape = $("input[name='do_landscape']:checked").attr("value");
	if(do_landscape == "Y" && curr_orientation == 0)
	{
		$('#report_config_content').removeClass("portrait_content");
		$('#report_config_content').addClass("landscape_content");
		curr_orientation = 1;
	}
	if(do_landscape == "N" && curr_orientation == 1)
	{
		$('#report_config_content').removeClass("landscape_content");
		$('#report_config_content').addClass("portrait_content");
		curr_orientation = 0;
	}
}

</script>
</head>
<body>
<div id='report_content'>

<link rel='stylesheet' type='text/css' href='css/table_print.css' />

<style type='text/css'>

div.editable {

	/*padding: 2px 2px 2px 2px;*/

	margin-top: 2px;

	width:900px;

	height:20px;

}



div.editable input {

	width:700px;

}

div#printhead {

position: fixed; top: 0; left: 0; width: 100%; height: 100%;

padding-bottom: 5em;

margin-bottom: 100px;

display:none;

}



@media all

{

  .page-break { display:none; }

}

@media print

{

	#options_header { display:none; }

	/* div#printhead {	display: block;

  } */

  div#docbody {

  margin-top: 5em;

  }

}



.landscape_content {-moz-transform: rotate(90deg) translate(300px); }



.portrait_content {-moz-transform: translate(1px); rotate(-90deg) }

</style>

<!--form name='word_format_form' id='word_format_form' action='export_word.php' method='post' target='_blank'>
	<input type='hidden' name='data' value='' id='word_data' />
	<input type='hidden' name='lab_id' value='<?php echo $lab_config_id; ?>' id='lab_id'>
	<input type='button' onclick="javascript:print_content('report_content');" value='<?php echo LangUtil::$generalTerms['CMD_PRINT']; ?>'></input>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<!-- <input type='button' onclick="javascript:export_as_word();" value='<?php echo LangUtil::$generalTerms['CMD_EXPORTWORD']; ?>'></input> -->
	&nbsp;&nbsp;&nbsp;&nbsp;
	<!-- <input type='button' onclick="javascript:window.close();" value='<?php echo LangUtil::$generalTerms['CMD_CLOSEPAGE']; ?>'></input> -->
</form-->

<form name='word_format_form' id='word_format_form' action='export_word.php' method='post' target='_blank'>
	<input type='hidden' name='data' value='' id='word_data' />
</form>
<form name='pdf_format_form' id='pdf_format_form' action='export_pdf.php' method='post' target='_blank'>
	<input type='hidden' name='data' value='' id='pdf_data' />
</form>
<form name='txt_format_form' id='txt_format_form' action='export_txt.php' method='post' target='_blank'>
	<input type='hidden' name='data' value='' id='txt_data' />
</form>
<form name='csv_format_form' id='csv_format_form' action='export_csv.php' method='post' target='_blank'> 
	<input type='hidden' name='csv_data' id='csv_data'>
</form>
<input type='radio' name='do_landscape' value='N' <?php
	//if($report_config->landscape == false) echo " checked ";
	echo " checked ";
?>>Portrait</input>
&nbsp;&nbsp;
<input type='radio' name='do_landscape' value='Y' <?php
	//if($report_config->landscape == true) echo " checked ";
?>><?php echo LangUtil::$generalTerms['LANDSCAPE_TYPE']; ?></input>&nbsp;&nbsp;

<input type='button' onclick="javascript:print_content('export_content');" value='<?php echo LangUtil::$generalTerms['CMD_PRINT']; ?>'></input>
&nbsp;&nbsp;
<!-- <input type='button' onclick="javascript:export_as_word('export_content');" value='<?php echo LangUtil::$generalTerms['CMD_EXPORTWORD']; ?>'></input> -->
&nbsp;&nbsp;
<input type='button' onclick="javascript:export_as_pdf('export_content');" value='<?php echo LangUtil::$generalTerms['CMD_EXPORTPDF']; ?>'></input>
&nbsp;&nbsp;
<!--input type='button' onclick="javascript:export_as_txt('export_content');" value='<?php echo LangUtil::$generalTerms['CMD_EXPORTTXT']; ?>'></input>
&nbsp;&nbsp;-->
<input type='button' onclick="javascript:export_as_csv('report_content_header', 'report_content_table1');" value='<?php echo LangUtil::$generalTerms['CMD_EXPORTCSV']; ?>'></input>
&nbsp;&nbsp;
<!-- <input type='button' onclick="javascript:window.close();" value='<?php echo LangUtil::$generalTerms['CMD_CLOSEPAGE']; ?>'></input> -->
&nbsp;&nbsp;

<hr>

<div id='export_content'>
<link rel='stylesheet' type='text/css' href='css/table_print.css' />
<div id='report_config_content'>
<b><?php echo LangUtil::$pageTerms['SPECIMEN_COUNT_REPORT'] ?></b>
<br><br>
<?php
$lab_config_id = $_REQUEST['location'];
$lab_config = LabConfig::getById($lab_config_id);
if($lab_config == null)
{
	echo LangUtil::$generalTerms['MSG_NOTFOUND'];
	return;
}
$date_from = $_REQUEST['from-report-date'];
$date_to = $_REQUEST['to-report-date'];
$uiinfo = "from=".$date_from."&to=".$date_to;
putUILog('reports_specimen_count_grouped', $uiinfo, basename($_SERVER['REQUEST_URI'], ".php"), 'X', 'X', 'X');


$configArray = getSpecimenCountGroupedConfig($lab_config->id);
//echo "--".$configArray['group_by_age'].$configArray['group_by_gender'].$configArray['age_groups'].$configArray['measure_groups'].$configArray['measure_id']."<br>";
# Fetch report configuration
$byAge = $configArray['group_by_age'];
$age_group_list = decodeAgeGroups($configArray['age_groups']);
$byGender = $configArray['group_by_gender'];
$bySection = $configArray['measure_id'];
$combo = $configArray['test_type_id']; // 1 - registered, 2 - completed, 3 - completed / pending 
$combo = 1;
//$age_group_list = $site_settings->getAgeGroupAsList();
 
$query = 'SELECT a.name AS Specimen, Sex, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 0 AND 4, 1, 0)) AS `0-4`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 5 AND 9, 1, 0)) AS `5-9`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 10 AND 14, 1, 0)) AS `10-14`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 15 AND 19, 1, 0)) AS `15-19`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 20 AND 24, 1, 0)) AS `20-24`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 25 AND 29, 1, 0)) AS `25-29`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 30 AND 34, 1, 0)) AS `30-34`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 35 AND 39, 1, 0)) AS `35-39`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 40 AND 44, 1, 0)) AS `40-44`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 45 AND 49, 1, 0)) AS `45-49`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 50 AND 54, 1, 0)) AS `50-54`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 55 AND 59, 1, 0)) AS `55-59`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) BETWEEN 60 AND 64, 1, 0)) AS `60-64`, 
	SUM(IF((YEAR(CURDATE())-YEAR(dob))-(RIGHT(CURDATE(),5)<RIGHT(dob, 5)) >64, 1, 0)) AS `>65+`, 
	COUNT(*) AS `Total Specimens`
	FROM (specimen_type a INNER JOIN specimen b ON a.specimen_type_id=b.specimen_type_id)
	INNER JOIN patient c ON b.patient_id=c.patient_id
	WHERE date_collected BETWEEN \''.$date_from.'\' AND \''.$date_to.'\'
	GROUP BY a.specimen_type_id, sex ORDER BY a.name, sex';
global $con;
$result = mysql_query($query, $con);

?>
<table id='report_content_header' class="print_entry_border draggable">
	<tbody>
		<tr>
			<td><?php echo LangUtil::$generalTerms['FACILITY']; ?>:</td>
			<td><?php echo $lab_config->getSiteName(); ?></td>
		</tr>
		<tr>
			<td><?php echo LangUtil::$pageTerms['REPORT_PERIOD']; ?>:</td>
			<td>
			<?php
			if($date_from == $date_to)
			{
				echo DateLib::mysqlToString($date_from);
			}
			else
			{	
				echo DateLib::mysqlToString($date_from)." to ".DateLib::mysqlToString($date_to);
			}
			?>
			</td>
		</tr>
		
		
	</tbody>
</table>
<?php

$table_css = "style='padding: .3em; border: 1px black solid; font-size:14px;'";
?>
<br>
<table id='report_content_table1' class="print_entry_border draggable">
<?php
	if ($result){
		echo '<thead><tr>';
		echo '<th>'.LangUtil::$generalTerms['SPECIMEN'].'</th>';
		echo '<th>'.LangUtil::$generalTerms['GENDER'].'</th>';
		for ($counter=2; $counter<mysql_num_fields($result)-1; $counter++){
			echo '<th>'.mysql_field_name($result, $counter).'</th>';
		}
		echo '<th>'.LangUtil::$pageTerms['TOTAL_SPECIMENS'].'</th>';
		echo '</tr></thead><tbody>';
		while($row = mysql_fetch_assoc($result)){
			echo '<tr><td>'.implode($row, '</td><td>').'</tr>';
		}
		echo '</tbody>';
	}
?>
</table>
<br><br><br>
............................................
</div>

<script type="text/javascript">
$(document).ready(function(){
	$('#report_content_table1').tablesorter();
});
</script>
</div>
</body>
</html>
>>>>>>> 8b8203b... Translation to French
