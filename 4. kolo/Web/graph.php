<!DOCTYPE HTML>
<html>
<script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>
<head>
<script type="text/javascript">
window.onload = function () {
	var chart = new CanvasJS.Chart("chartContainer",
	{
		animationEnabled: true,
		title:{
			text: "Graf teploty"
		},
		data: [
		{
			type: "line", //change type to bar, line, area, pie, etc
			showInLegend: false,        
			dataPoints: [
			<?php
			$start = 0;

			$db_file = 'databaze_malinovky.db';
			$db = new SQLite3($db_file);
			$results = $db->query('SELECT * FROM graph_modules WHERE hash="'. $_GET['hash'] .'" LIMIT 30 ');

			while ($row = $results->fetchArray()) {
				$start += 10;
				echo '{ x: '. $start .', y: '. $row['temp'] .'},';
			}	

			?>

			]
			}
		],
		legend: {
			cursor: "pointer",
			itemclick: function (e) {
				if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
					e.dataSeries.visible = false;
				} else {
					e.dataSeries.visible = true;
			}
			chart.render();
			}
		}
	});

	chart.render();
}
</script>
</head>
<body>
<div id="chartContainer" style="height: 400px; width: 80%;"></div>
</body>

</html>