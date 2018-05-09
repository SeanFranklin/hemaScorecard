<?php
/*******************************************************************************
	Footer
	
	Page footer and javascript declarations
		
*******************************************************************************/
?>

	</div>
	<div class="large-12 cell text-right " style='border-top: 1px solid black; margin-top: 20px;'>
		Developed by Sean Franklin &nbsp;
	</div>

	<script src="includes/foundation/js/vendor/jquery.js"></script>
    <script src="includes/foundation/js/vendor/what-input.js"></script>
    <script src="includes/foundation/js/vendor/foundation.js"></script>
    <script src="includes/foundation/js/app.js"></script>
	<script type='text/javascript' src='includes/scripts/general_scripts.js'></script>
	<script type='text/javascript' src='includes/scripts/delete_checking_scripts.js'></script>
	
	<?php
		foreach((array)$jsIncludes as $includePath){
			echo "<script type='text/javascript' src='includes/scripts/{$includePath}'></script>";
		}
	?>

</body>


</html>
