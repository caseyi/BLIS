<!-- (c) C4G, Santosh Vempala, Ruban Monu and Amol Shintre -->

<script type="text/javascript">
	function changeTheme(theme) {
		alert(theme);
		$.ajax({
		type : 'POST',
		url : 'users/changetheme.php?theme='+theme,
		success : function(data) {
			alert(data);
		}
	});
	}
        
        
        
</script>

<?php
#
# This is the footer file to display at the end of the file.
# Closes any open database connections, and-
# displays footer so the users know the page is done loading.
#
include("db_close.php");
LangUtil::setPageId("footer");
?>
<<<<<<< HEAD


</div><!-- end of center_pane-->

<div id='bottom_pane'>
	<br>
	<hr>
	<div class='footer_message'>
		<small>
		
=======
</div>
			<!-- END PAGE CONTAINER-->		
		</div>
		<!-- END PAGE -->
	</div>
	<!-- END CONTAINER -->
	<!-- BEGIN FOOTER -->
	<div class="footer">
			<center>
				<small>
		<a href='userguide/BLIS_User_Guide.pdf' target='_blank' ><?php echo LangUtil::$generalTerms['USER_GUIDE']; ?> |</a>
>>>>>>> 8b8203b... Translation to French
		<?php
		if($_SESSION['locale'] == "en")
		{
			echo "<a href='userguide/BLIS_User_Guide.pdf' target='_blank' >User Guide |</a>";
		}
		else if($_SESSION['locale'] == "fr")
		{
			echo "<a href='userguide/BLIS_User_Guide.pdf' target='_blank' >Guide de l'utilisateur |</a>";
		}
		else
		{
			echo "<a href='userguide/BLIS_User_Guide.pdf' target='_blank'>User Guide |</a>";
		}
		?>
		
<<<<<<< HEAD
		<a rel='facebox' href='feedback/comments.php?src=<?php echo $_SERVER['PHP_SELF']; ?>'><?php echo "Comments" ?>?</a> |
		C4G BLIS v<?php echo $VERSION; ?> - <?php echo LangUtil::getPageTerm("FOOTER_MSG"); ?>
		<?php
=======
		<a rel='facebox' href='feedback/comments.php?src=<?php echo $_SERVER['PHP_SELF']; ?>'><?php echo LangUtil::$generalTerms['COMMENTS'] ?>?</a> |
		C4G BLIS v<?php echo $VERSION; ?> - <?php echo LangUtil::$allTerms["FOOTER_MSG"]; ?>
		<?php
		//TA: this part was commented out, I removed this comments
>>>>>>> 8b8203b... Translation to French
		if($_SESSION['locale'] !== "en")
		{
			?>
			 | <a href='lang_switch?to=en'><?php echo "English"; ?></a>
			<?php
		}
		else
		{
			echo " | English";
		}
		if($_SESSION['locale'] !== "fr")
		{
			?>
			 | <a href='lang_switch?to=fr'><?php echo "Francais"; ?></a>
			<?php
		}
		else
		{
			echo " | Francais";
			$str = utf8_encode("Facilit�");
			//$str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
			echo " |  $str";
		}
		?>
			 
			<?php

		/*Change Theme: <a href=javascript:changeTheme('Blue');>Blue</a> | <a href=javascript:changeTheme('Grey');>Grey*/
		if($TRACK_LOADTIME)
		{
			$endtime = microtime();
			$endarray = explode(" ", $endtime);
			$endtime = $endarray[1] + $endarray[0];
			$totaltime = $endtime - $starttime; 
			$totaltime = round($totaltime,5);
			$page_name = $_SERVER['PHP_SELF'];
			$page_name_parts = explode("/", $page_name);
			$file_name = $page_name_parts[count($page_name_parts)-1].".dat";
			$file_handle = fopen("../feedback/loadtimes/".$file_name, "a");
			fwrite($file_handle, $totaltime."\n");
			fclose($file_handle);
			echo "<br>$file_name This page loaded in $totaltime seconds.";
		}
		if($TRACK_LOADTIMEJS)
		{
			echo "<script type='text/javascript'>alert(new Date().getTime() - t.getTime());</script>";
		}
		?>
		</small>
		<br><br>
	</div>
</div><!--end of bottom_pane-->
</body>
</html>