<?php					     $dbHost = "127.0.0.1";
	$dbUser = "Collaboratum";
	$dbPass = "Collaboratum";
	$dbNameGeneral = "collaboratum";
	$dbNameNetwork = "parsingdata";
	
	$baseURL = "http://binf1.memphis.edu/Collaboratum";
	
	$lsiQueryHost = "localhost";
	$lsiQueryPort = "50005";
	
	$keywdQueryHost = "localhost";
	$keywdQueryPort = "50004";
				$startId = $_GET['startId'];
				$endId = $_GET['endId'];
				if($startId == -1 && $endId == -1 && isset($_GET['listId']) ) {
					$idList = explode(",", $_GET['listId']);
				}
				else {
					$idList = range($startId, $endId, 1);
				}
			        $title = $_GET['title'];
                                
                                $simMatrix = array( array() );
                                $names = array();
                                
                                $numElements = count($idList);
                        
                                        
                                // generate the sql statement to fetch a 2d array of sim values.
                                $sql = "SELECT ";
                                $sqlNames = "SELECT collaboratum.investigator.first_name, collaboratum.investigator.last_name FROM collaboratum.investigator WHERE";
                                foreach( $idList as $id ) {
                                        if($id == end($idList)) {
                                                $sql .= "parsingdata.doc_pairwise_cosine_matrix.col".$id;
                                                $sqlNames .= " collaboratum.investigator.investigator_id=".$id;
                                                
                                        }
                                        else {
                                                $sql .= "parsingdata.doc_pairwise_cosine_matrix.col".$id.", ";
                                                $sqlNames .= " collaboratum.investigator.investigator_id=".$id." OR ";
                                        }
                                }
                                $sql .= " FROM parsingdata.doc_pairwise_cosine_matrix LIMIT ".$startId.", ".$endId;
                                
                                $con = mysql_connect($dbHost, $dbUser, $dbPass) or die(mysql_error());
                                
                                $sqlResult = mysql_query($sql);
                                
                                $threshold = 0;
                                // fetch similarity matrix
                                // for each row
                                for( $i = 0; $i < $numElements; $i++ ) {
                                        // fetch the row
                                        $row = mysql_fetch_array($sqlResult);
                                        // for each column
                                        for( $j = 0; $j < $numElements; $j++ ) {
                                        		
                                                // Assign this to the appropriate 2d array slot
                                                $simMatrix[$i][$j] = $row[$j];
												if( $j > $i ) {
                                        			$threshold += $simMatrix[$i][$j];
                                        		}
                                        }
                                }
                                // calculate threshold
                                $threshold = 1.68 * ( $threshold / ( (($numElements*$numElements) - $numElements) / 2));
								
								
                                // fetch names of investigators
                                $sqlResult = mysql_query($sqlNames);
                                for( $i = 0; $i < $numElements; $i++ ) {
                                        // fetch row and name of investigator
                                        $row = mysql_fetch_array($sqlResult);
                                        $names[$i] = $row[0]." ".$row[1];
                                }
                                
                          
                                
                                // build graph data list.
                                $nodeList = "{ \"nodes\": [ ";
                                foreach( $names as $name )
                                {
                                        if($name == end($names)) {
                                                $nodeList .= " {\"id\": \"".trim($name)."\", \"label\": \"".trim($name)."\"}";
                                        }
                                        else {
                                                $nodeList .= " {\"id\": \"".trim($name)."\", \"label\": \"".trim($name)."\"},";
                                        }
                                }
                                $nodeList .= " ], ";
                                $edgeList = "\"edges\": [ ";
                                // for each row in sim matrix
                                for($i = 0; $i < $numElements; $i++) {
                                        // for each relevant column in sim matrix
                                        for($j = $i + 1; $j < $numElements; $j++) {
                                        		if($simMatrix[$i][$j] >= $threshold){
                                                	$edgeList .= "{\"id\": \"".$i."\", \"target\": \"".trim($names[$j])."\", \"source\": \"".trim($names[$i])."\"}, ";
                                        
																					 }
																				}
																}
                                
																$edgeList = substr($edgeList, 0, strlen($edgeList)-2);
                                $edgeList .= "]}";
                                if( $edgeList === "\"edges\": ]}" )
																{
																	$network_json = substr($nodeList, 0, strlen($nodeList)-2)."}";
																}
																else {
                                $network_json = $nodeList.$edgeList;
                          		 }

$network_json = "{\"dataSchema\":{\"nodes\":[{\"name\": \"id\", \"type\": \"string\"}, {\"name\": \"label\", \"type\": \"string\"}],\"edges\":[{\"name\": \"id\", \"type\": \"number\"}, {\"name\": \"target\", \"type\": \"string\"}, {\"name\": \"source\", \"type\": \"string\"}]}, \"data\":". $network_json. "}";
                               

 echo $network_json;
