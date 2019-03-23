<?php
/*******************************************************************************
	Footer
	
	Page footer and javascript declarations
		
*******************************************************************************/

	displayPageAlerts();
?>

	</div id='a'><!-- End Page Wrapper -->

<!-- Footer content -->
	<div class="large-12 cell text-right " style='border-top: 1px solid black; margin-top: 20px;'>
		<div class='grid-x grid-margin-x align-right'>
			<div class='shrink cell'>
				<a href='index.php'>HEMA Scorecard</a><BR>
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
    <script src="includes/foundation/js/app.js<?=$vJ?>"></script>
    
    <script src="https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>


	<script type='text/javascript' src='includes/scripts/general_scripts.js<?=$vJ?>'></script>
	<script type='text/javascript' src='includes/scripts/delete_checking_scripts.js<?=$vJ?>'></script>
	
	


	<?php 
		if(isset($jsIncludes)){
			foreach((array)$jsIncludes as $includePath){
				echo "<script type='text/javascript' src='includes/scripts/{$includePath}{$vJ}'></script>";
			}
		}
	?>
	<?php if(isset($createSortableDataTable)): ?>
			
	    <script src='https://code.jquery.com/jquery-3.3.1.js'></script>
		<script src='https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'></script>
		<script src='https://cdn.datatables.net/1.10.19/js/dataTables.foundation.min.js'></script>

		<script>
		<?php foreach($createSortableDataTable as $tableName): ?>

			$(document).ready(function() { 
				$('#<?=$tableName?>').DataTable(); 
			} );
			
		<?php endforeach ?>
		</script>
		
	<?php endif ?>

<!-- End Scripts -->


	<script>
		if ( $( "#termsOfUseModal" ).length ) {
			$(document).ready(function(){$('#termsOfUseModal').foundation('open');});
		}
	</script>

</body>


</html>
