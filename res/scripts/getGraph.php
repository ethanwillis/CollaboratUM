<?php

/*
* This script takes search results and transforms them into a JSON data structure to be used by cytoscape.
*/

// get a php imploded array seperated by ~
$queryTerm = $_POST['queryTerm'];
$queryResult = explode("~", $_POST['queryResult']);
$threshHold = $_POST['threshHold'];
?>
{"dataSchema":{"nodes":[{"name": "label", "type": "string"}, {"name": "type", "type": "string"}, {"name": "similarity", "type": "string"}], "edges":[{"name": "label", "type": "string"}, {"name": "similarity", "type":"string"}]},  "data":{"nodes":[{"id":"<?php echo $queryTerm; ?>", "label":"<?php echo $queryTerm; ?>", "type":"queryTerm", "similarity":"-2"},
		<?php 
			$result = "";
			// Each element in the queryResult array is another array that contains the name, similarity score, and database id
			// for each node
			for($i = 0; $i < count($queryResult); $i++)
			{
				$queryRow = explode("`", $queryResult[$i]);
				$label = $queryRow[0];
				$similarity = $queryRow[1];
				$id = $queryRow[2];
				$type = $queryRow[3];
				// build a new node that contains those 3 attributes.
				if($similarity > $threshHold )
				{
					$result .= "{\"id\":\"".$id."\", \"label\":\"".$label."\", \"type\":\"".$type."\", \"similarity\":\"".$similarity."\"}, ";
				}
			}
			echo substr($result, 0, -2);
			$result = "";
		?>
],"edges":[<?php 
			// for each edge between nodes
			for($i = 0; $i < count($queryResult); $i++)
			{
				$queryRow = explode("`", $queryResult[$i]);
				$label = $queryRow[0];
				$similarity = $queryRow[1];
				$id = $queryRow[2];
				$type = $queryRow[3];
				if($similarity > $threshHold )
				{
					$result .= "{\"target\":\"".$id."\",\"source\":\"".$queryTerm."\", \"label\":\"".$label."\", \"similarity\":\"".$similarity."\"},";
				}
			   
			}
			echo substr($result, 0, -1);
			
			?>]}}
