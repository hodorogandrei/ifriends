<?php

include_once('./diagram_data.php');

header('Content-type: text/css');

echo "#diagram {
	position: relative;
	margin-left: auto;
	margin-right: auto;
	width:".($max_radius*2)."px;
	height:".($max_radius*2)."px;
}

#diagram .firstCircle {
	margin-left:".($centre_x - $max_radius)."px;
	margin-top:".($centre_y - $max_radius)."px;
	width:".($max_radius*2)."px;
	height:".($max_radius*2)."px;
	background:#ace7ff;
}

#diagram .secondCircle {
	margin-left:".($centre_x - ($max_radius+$min_radius)/2)."px;
	margin-top:".($centre_y - ($max_radius+$min_radius)/2)."px;
	width:".($max_radius+$min_radius)."px;
	height:".($max_radius+$min_radius)."px;
	background:#ffabab;
}

#diagram .thirdCircle {
	margin-left:".($centre_x - $min_radius)."px;
	margin-top:".($centre_y - $min_radius)."px;
	width:".($min_radius * 2)."px;
	height:".($min_radius * 2)."px;
	background:#fff;
}

#diagram .subj_user {
	margin-left:".($centre_x-$icon_diameter/2)."px;
	margin-top:".($centre_y-$icon_diameter/2)."px;
	".($icon_diameter/2)."px 0 0 -".($icon_diameter/2)."px;
}

#diagram .subj_user img {
	width: ".$icon_diameter."px;
	height: ".$icon_diameter."px;
	border-radius:50%;
}

#diagram .circle {
	position: absolute;
	border-radius: 50%;
}

#diagram .user_obj {
	position: absolute;
	display: inline-block;
	background: #fff;
	border: 2px solid;
	border-radius: 50%;
}

#diagram span.toolTip {
	display: none;
	position: absolute;
	z-index: 999999;
	width: auto;
	left: 103%;
	top: 0;
	background: #fff;
	padding: 0px 10px;

	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;

	-moz-opacity: 0.90;
	-khtml-opacity: 0.90;
	opacity: 0.90;
	filter: progid:DXImageTransform.Microsoft.Alpha(opacity=90);
	filter:alpha(opacity=90);

	white-space: nowrap;
}

";

?>

