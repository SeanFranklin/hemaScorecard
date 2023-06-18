<style>
<?php
$boxWidth = 300;  //293 will match [5 levels = page width] but the lines don't line up as nicely

if($bracketType == BRACKET_PRIMARY){$maxMatches = pow(2,$bracketLevels-1);}
else {$maxMatches = getNumEntriesAtLevel_consolation($bracketLevels,'matches');}

$totalHeight = $maxMatches * 100;

$width = $bracketLevelsToDisplay * $boxWidth;

echo "#tournament_box .tier{
		width: {$boxWidth}px;
		position: relative;
		height: 100%;
		float: left;
}";

echo"
	#tournament_box{
		width: {$width}px;
		margin: 0;
		padding: 0;
		height: {$totalHeight}px;
	}";

for($i=1;$i<=$bracketLevels;$i++){

	if($bracketType == BRACKET_PRIMARY){
		$height = 100 / pow(2,$i-1);
	} else {
		$height = 100/getNumEntriesAtLevel_consolation($i,'matches');
	}

	$name = ".depth{$i}";
	echo "

	#tournament_box {$name}{
	width: 100%;
	height: {$height}%;
	display: table;
	}";

	$name = ".depth{$i}_rightBottom";
	echo "
	#tournament_box {$name}{
	border-right: 2px solid;
	height: 50%;
	}";

	$name = ".depth{$i}_rightTop";
	$top = $totalHeight*$height/200;

	echo "
	#tournament_box {$name}{
	border-right: 2px solid;
	height: 50%;
	position: relative;
	top: {$top}px;
	}";
}

?>

.bracket-select {
	width: 230px;
	height: 35px
	!important;
	margin: 0px;
}

.bracket-top-slot{
	border-bottom: 2px solid black;
}

#tournament_box {
	margin-bottom: 100px;
}

#tournament_box span {
	display:inline;
	position: relative;
	top: 50%;
	transform: translateY(-50%);
	width: 100px;
}

#tournament_box em {
	font-style: normal;
	color: #666;

}

#tournament_box .centerCrap{
	display: table-cell;
	vertical-align: middle;
}

table.bracket {
	border-collapse: collapse;
	border: none;
}

.bracket td {
	vertical-align: middle;
	width: 40em;
	margin: 0;
	padding: 10px;
}

.bracket td p {
	border-bottom: solid 1px black;
	margin: 0;
	padding: 5px 5px 5px 5px;
}

.bracket th{
	text-align:center;
}

</style>
