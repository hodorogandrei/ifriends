<?php


// Input scores/nodes (ID => Value)

$scores2 = json_decode(file_get_contents("php://input"), true);
// echo '<pre>';
// print_r($scores2);
// echo '</pre>';
// die();

foreach ($scores2 as $id => $val) {
	$subjImage = $val[1];
	$subjID = $id;
	break;
}
unset($scores2[$subjID]);

$nodes = sizeof($scores2); // Counts number of nodes to draw
$degree_change = 360.0 / $nodes; // Calculates the anglular positioning of nodes


// echo '<pre>';
// print_r($scores2);
// echo '</pre>';
// die();


// foreach ($scores2 as $id => $val) {
// 	echo $id;
// 	echo '<pre>';
// 	print_r($val);
// 	echo '</pre>';
// }

include_once('./diagram_data.php');

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>iFriends</title>
	<link rel="stylesheet" href="diagram_style.php">
</head>
<body>


<br/>
<h2>Evaluation complete!</h2>
<p>The closer your friends are to your picture, the stronger your relationship.</p>

<div id="diagram"> <!-- // Encapsulates diagram in a simple div -->
	<div class="circle firstCircle"></div> <!-- // Creates outer grey ring -->
	<div class="circle secondCircle"></div>
	<div class="circle thirdCircle"></div> <!-- // Creates inner grey ring -->

	<div class="user_obj subj_user">
		<?php echo '<img src="'.$subjImage.'">';?>
	</div> <!-- // Draws subject's node to the middle of the diagram -->

<?php
// Draws nodes to screen
$degrees = 0;
foreach($scores2 as $id => $val) {
	$val[0] = $val[0]; // Gets the inverse, inverse of friend's value - this means the close the node to the middle of the figure the stronger the friendship
	$x = cos(deg2rad($degrees)) * (($max_radius-$min_radius) * $val[0] + $min_radius) + $centre_x - 2; // Finds x positioning (-2 border thickness)
	$y = sin(deg2rad($degrees)) * (($max_radius-$min_radius) * $val[0] + $min_radius) + $centre_y - 2; // Finds y positioning (-2 border thickness)
	echo '<div id="id-'.$id.'" class="user_obj" style="margin-left:'.($x-$icon_diameter/2).'px;
										   margin-top:'.($y-$icon_diameter/2).'px;"">
										   <a href="https://www.facebook.com/'.$id.'" target="_blank">
										   <img src="'.$val[1].'" width="'.$icon_diameter.'"px" height="'.$icon_diameter.'px" style="border-radius:50%;">
										   </a>
		  <span class="toolTip"><b>Name:</b> '. $val[2].' <br/><b>Value:</b> '. $val[0] .'<br/>';
		  if($val[0] < 0.5) {
		  	echo 'Inner circle';
		  } else echo 'Outer circle';
		  echo '</span></div>
		  ';
	$degrees = $degrees + $degree_change; // Changes angle node is drawn at
}
?>

</div> <!-- // Closes diagram div block -->

<!-- // Creates mouseover event listenters -->

<script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
<script type="text/javascript">

var friends = <?= json_encode(array_keys($scores2)); ?>;

for(var id in friends){
	console.log(id);

	var obj = $(document.getElementById('id-' + friends[id]));

	obj.mouseenter(function() {
		$(this).find("span.toolTip").css("display", "inline");
	})

	obj.mouseleave(function() {
		$(this).find("span.toolTip").css("display", "none");
	});

}

</script> <!-- // Writes subject's event listeners to html file -->

</body>
</html>