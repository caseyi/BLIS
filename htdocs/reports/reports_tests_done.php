<<<<<<< HEAD
<?php
#
# Shows tests performed report for a site/location and date interval
#
include("redirect.php");
include("includes/header.php");
include("includes/stats_lib.php");
LangUtil::setPageId("reports");

$script_elems->enableFlotBasic();
$script_elems->enableFlipV();
$script_elems->enableTableSorter();
$script_elems->enableLatencyRecord();



?>
<script type='text/javascript'>
$(window).load(function(){
	$('#stat_graph').hide();
});
function toggle_stat_table()
{
	$('#stat_graph').toggle();
	var linktext = $('#showtablelink').text();
	if(linktext.indexOf("<?php echo LangUtil::$pageTerms['MSG_SHOWGRAPH']; ?>") != -1)
		$('#showtablelink').text("<?php echo LangUtil::$pageTerms['MSG_HIDEGRAPH']; ?>");
	else
		$('#showtablelink').text("<?php echo LangUtil::$pageTerms['MSG_SHOWGRAPH']; ?>");
}
</script>
<style type='text/css'>
.flipv_up {
	font-size: 12px;
	font-family: Tahoma;
}
.flipv {
	font-size: 12px;
	font-family: Tahoma;
}
</style>
<br>
<b><?php echo LangUtil::$pageTerms['COUNT_TEST']; ?></b> 
<?php /*| <a href="javascript:toggle_stat_table();" id='showtablelink'> # echo LangUtil::$pageTerms['MSG_SHOWGRAPH']; </a> */ ?>
 | <a href='reports.php?show_c'>&laquo; <?php echo LangUtil::$pageTerms['MSG_BACKTOREPORTS']; ?></a>
<br><br>
<?php
$lab_config_id = $_REQUEST['location'];
$date_from = $_REQUEST['yyyy_from']."-".$_REQUEST['mm_from']."-".$_REQUEST['dd_from'];
$date_to = $_REQUEST['yyyy_to']."-".$_REQUEST['mm_to']."-".$_REQUEST['dd_to'];
$uiinfo = "from=".$date_from."&to=".$date_to;
putUILog('reports_test_count_ungrouped', $uiinfo, basename($_SERVER['REQUEST_URI'], ".php"), 'X', 'X', 'X');
DbUtil::switchToLabConfig($lab_config_id);
$lab_config = get_lab_config_by_id($lab_config_id);
if($lab_config == null)
{
	?>
	<div class='sidetip_nopos'>
		<?php echo LangUtil::$generalTerms['MSG_NOTFOUND']; ?> <a href='javascript:history.go(-1);'>&laquo; <?php echo LangUtil::$generalTerms['CMD_BACK']; ?></a>
	</div>
	<?php
	return;
}
 $site_list = get_site_list($_SESSION['user_id']);
			if(count($site_list) != 1)
			{ echo LangUtil::$generalTerms['FACILITY'] ?>: <?php echo $lab_config->getSiteName(); ?> | 
<?php
}

if($date_from == $date_to)
{
	echo LangUtil::$generalTerms['DATE'].": ".DateLib::mysqlToString($date_from);
}
else
{	
	echo LangUtil::$generalTerms['FROM_DATE'].": ".DateLib::mysqlToString($date_from);
	echo " | ";
	echo LangUtil::$generalTerms['TO_DATE'].": ".DateLib::mysqlToString($date_to);
}
?>
<br><br>

<?php $stat_list = StatsLib::getTestsDoneStats($lab_config, $date_from, $date_to); 
//print_r($stat_list);

?>
<?php
/*
<div id='stat_graph'>
<?php
# To avoid clutter on the graph, divide stat_list to chunks
$chunk_size = 999;
$stat_chunks = array_chunk($stat_list, $chunk_size, true);
$i = 1;
foreach($stat_chunks as $stat_chunk)
{
	$div_id = "placeholder_".$i;
	$ylabel_id = "ylabel_".$i;
	$legend_id = "legend_".$i;
	$width_px = count($stat_chunk)*150;
	?>
	<table>
	<tbody>
	<tr valign='top'>
	<td>
		<span id="<?php echo $ylabel_id; ?>" class='flipv_up' style="width:30px;height:30px;"><?php echo LangUtil::$pageTerms['COUNT_TEST']; ?></span>
	</td>
	<td>
		<div style='width:900px;height:340px;overflow:auto'>
			<div id="<?php echo $div_id; ?>" style="width:<?php echo $width_px; ?>px;height:300px;"></div>
		</div>
	</td>
	<td>
		<div id="<?php echo $legend_id; ?>" style="width:200px;height:300px;"></div>
	</td>
	</tr>
	</tbody>
	</table>
	<script id="source" language="javascript" type="text/javascript"> 
	$(function () {
		<?php
		$x_val = 0;
		$count = 1;
		foreach($stat_chunk as $key=>$value)
		{
			$test_type_id = $key;
			$tests_done_count = $value;
			echo "var d$count = [];";
			echo "d$count.push([$x_val, $tests_done_count]);";
			$count++;
			$x_val += 2;
		}
		?>
		$.plot($("#<?php echo $div_id; ?>"), [
			<?php
			$count = 1;
			$index_count = 0;
			$tick_array = "[";
			foreach($stat_chunk as $key=>$value)
			{
				$test_name = get_test_name_by_id($key);
				$tick_array .= "[$index_count+0.4, '$test_name']";
				?>
				{
					data: d<?php echo $count; ?>,
					bars: { show: true }//,
					//label: "<?php #echo get_test_name_by_id($key); ?>"
				}
				<?php
				$count++;
				$index_count += 2;
				if($count < count($stat_chunk) + 1)
				{
					echo ",";
					$tick_array .= ",";
				}
			}
			$tick_array .= "]";
			?>
		], { xaxis: {ticks: <?php echo $tick_array; ?>}, legend: {container: "#<?php echo $legend_id; ?>"}  });
		$('#<?php echo $ylabel_id; ?>').flipv_up();
	});
	</script>	
	<?php
	# End of loop
	$i++;
}
?>
</div>
*/
?>
<div id='stat_table'>
	<?php $page_elems->getTestsDoneStatsTable($stat_list); ?>
</div>
<?php include("includes/footer.php"); ?>
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
<b><?php echo LangUtil::$pageTerms['TEST_COUNT_REPORT']; ?></b>
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
putUILog('reports_test_count_ungrouped', $uiinfo, basename($_SERVER['REQUEST_URI'], ".php"), 'X', 'X', 'X');
# Fetch site-wide settings
//$site_settings = DiseaseReport::getByKeys($lab_config->id, 0, 0);

/*
$byAge = 1;
$bySection = 1;
$byGender = 0;
*/
 //$age_group_list = $site_settings->getAgeGroupAsList();
 
$query = 'SELECT a.name AS `Test Type`, SUM(IF(result<>\'\', 1, 0)) AS `Completed Tests`,
	SUM(IF(result=\'\', 1, 0)) AS `Pending Tests`
	FROM (test_type a INNER JOIN test b ON a.test_type_id=b.test_type_id)
	INNER JOIN specimen c ON b.specimen_id=c.specimen_id
	WHERE date_collected BETWEEN \''.$date_from.'\' AND \''.$date_to.'\'
	GROUP BY a.test_type_id ORDER BY a.name';
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
// 		for ($counter=0; $counter<mysql_num_fields($result); $counter++){
// 			echo '<th>'.mysql_field_name($result, $counter).'</th>';
// 		}
		echo '<th>'.LangUtil::$generalTerms['TEST_TYPE'].'</th>';
		echo '<th>'.LangUtil::$generalTerms['COMPLETED_TESTS'].'</th>';
		echo '<th>'.LangUtil::$pageTerms['MENU_PENDINGTESTS'].'</th>';
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
</div>
</div>
</body>
</html>
>>>>>>> 8b8203b... Translation to French
