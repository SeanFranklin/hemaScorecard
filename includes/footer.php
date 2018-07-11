<?php
/*******************************************************************************
	Footer
	
	Page footer and javascript declarations
		
*******************************************************************************/
?>

	</div id='a'><!-- End Page Wrapper -->

<!-- Footer content -->
	<div class="large-12 cell text-right " style='border-top: 1px solid black; margin-top: 20px;'>
		<div class='grid-x grid-margin-x align-right'>
			<div class='shrink cell'>
				Developed by Sean Franklin <BR>
				A <a href='https://www.hemaalliance.com/'>HEMA Alliance</a> Project
			</div>
			<div class='shrink cell'>
				<img src='includes/images/hemaa_logo_s.png'>
			</div>

		</div>
	</div>

<!-- Start Scripts -->
	<script src="includes/foundation/js/vendor/jquery.js"></script>
    <script src="includes/foundation/js/vendor/what-input.js"></script>
    <script src="includes/foundation/js/vendor/foundation.js"></script>
    <script src="includes/foundation/js/app.js"></script>
    <script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
	<script type='text/javascript' src='includes/scripts/general_scripts.js'></script>
	<script type='text/javascript' src='includes/scripts/delete_checking_scripts.js'></script>
	
	<?php
		foreach((array)$jsIncludes as $includePath){
			echo "<script type='text/javascript' src='includes/scripts/{$includePath}'></script>";
		}
		
	?>
<!-- End Scripts -->


	<script>
		if ( $( "#termsOfUseModal" ).length ) {
			$(document).ready(function(){$('#termsOfUseModal').foundation('open');});
		}
	</script>

</body>


</html>
