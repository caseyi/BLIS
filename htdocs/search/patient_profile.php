<?php
#
# Main page for showing patient profile, test history,
# and options like updating profile, registering new specimen
#
include("redirect.php");
include("includes/header.php");
LangUtil::setPageId("patient_profile");

putUILog('patient_profile', 'X', basename($_SERVER['REQUEST_URI'], ".php"), 'X', 'X', 'X');

$pid = $_REQUEST['pid'];
$script_elems->enableJQueryForm();
$script_elems->enableDatePicker();
$script_elems->enableTableSorter();
$script_elems->enableLatencyRecord();
?>
<script type='text/javascript'>
function toggle_profile_divs()
{
	$('#profile_div').toggle();
	$('#profile_update_div').toggle();
	$('#profile_update_form').resetForm();
}

function update_profile()
{
	$('#pd_ym').attr("value", "0");
	$('#pd_y').attr("value", "0");
	var yyyy = $('#yyyy').attr("value");
	var mm = $('#mm').attr("value");
	var dd = $('#dd').attr("value");
	var age = $('#age').attr("value");
	var error_message = "";
	var error_flag = 0;
	//Age not given
	if(age.trim() == "")
	{
		//Check partial DoB
		if(yyyy.trim() != "" && mm.trim() != "" && dd.trim() == "")
		{
			dd = "01";
			if(checkDate(yyyy, mm, dd) == false)
			{
				alert("<?php echo LangUtil::$generalTerms['ERROR'].": ".LangUtil::$generalTerms['DOB']." ".LangUtil::$generalTerms['INVALID']; ?>");
				return;
			}
			$('#pd_ym').attr("value", "1");
			
		}
		else if(yyyy.trim() != "" && mm.trim() == "" && dd.trim() == "")
		{
			mm = "01";
			dd = "01";
			if(checkDate(yyyy, mm, dd) == false)
			{
				alert("<?php echo LangUtil::$generalTerms['ERROR'].": ".LangUtil::$generalTerms['DOB']." ".LangUtil::$generalTerms['INVALID']; ?>");
				return;
			}
			$('#pd_y').attr("value", "1");
		}
		else if(yyyy.trim() == "" && mm.trim() == "" && dd.trim() == "")
		{
			error_message += "Please enter either Age or Date of Birth\n";//<br>";
			error_flag = 1;
			alert("<?php echo LangUtil::$generalTerms['ERROR'].": ".LangUtil::$pageTerms['TIPS_AGEORDOB']; ?>");
			return;
		}
		else
		{
			//Full DoB - check
			if(checkDate(yyyy, mm, dd) == false)
			{
				alert("<?php echo LangUtil::$generalTerms['ERROR'].": ".LangUtil::$generalTerms['DOB']." ".LangUtil::$generalTerms['INVALID']; ?>");
				return;
			}
		}
	}
	else if (isNaN(age))
	{
		alert("<?php echo LangUtil::$generalTerms['ERROR'].": ".LangUtil::$generalTerms['AGE']." ".LangUtil::$generalTerms['INVALID']; ?>");
		return;
	}	
	
	$('#update_profile_progress').show();
	var params = $('#profile_update_form').formSerialize();
	$.ajax({
		type: "POST",
		url: "ajax/patient_update.php",
		data: params,
		success: function(msg) {
			$('#update_profile_progress').hide();
			window.location.reload();
		}
	});	
}
</script>
<br>
<b><?php echo LangUtil::getTitle(); ?></b>
 | <a href='javascript:history.go(-1);'>&laquo; <?php echo LangUtil::$generalTerms['CMD_BACK']; ?></a>
<br><br>
<table>
	<tr valign='top'>
		<td>
			<div id='profile_div'>
				<?php $page_elems->getPatientInfo($pid); ?>
			</div>
			<div id='profile_update_div' style='display:none;' >
				<?php $page_elems->getPatientUpdateForm($pid); ?>
			</div>
		</td>
		<td>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</td>
		<td>
			<?php $page_elems->getPatientTaskList($pid); ?>
		</td>
	</tr>
</table>
<br>
<b><?php echo LangUtil::$generalTerms['CMD_THISTORY']; ?></b><br>
<?php $page_elems->getPatientHistory($pid); ?>
<?php include("includes/footer.php"); ?>