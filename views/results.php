<?php 
	//import config file
	include_once(__DIR__."../config.php");
	
    /*
	 * Get the search query and the type of query.
	 * 
	 * $query: The search string to use against the database 
	 * $searchType: The filter to use for search results. 
	 * 		searchType possible values 
	 * 		0 - Do not filter, return both investigators and grants.
	 * 		1 - Return only related grant information.
	 * 		2 - Return only related investigators. 
	 * $exactSearch: Whether or not we want to use Keyword search or LSI search.
	 * 		exactSearch possible values
	 * 		'true'  - Use Keyword search
	 * 		'false' - Use LSI search
	 */ 
    $query = $_GET['searchBox'];
    $searchType = $_GET['searchType'];
	$largestSimilarity = 0;
	$unresolved;
	$histogram;
	
	if( isset($_GET['exactSearch']) )
	{
		$exactSearch = $_GET['exactSearch'];
	}
	else
	{
		$exactSearch = "false";
	}
	
	// if we have a search query to use
    if(isset($query))
    {
    	// If we want to use LSI search
        if($exactSearch === "false")
        {
        	// connect to the LSI query service on port 50005 and query it.
        	$queryResult = querySearchService($lsiQueryHost, $lsiQueryPort, $query, $searchType);
        	$queryResult = explode("\n", $queryResult);  
			$largestSimilarity = 1;
			$unresolved = $queryResult;
			$queryResult = resolveIDs( $queryResult );
        }
		// TODO finish implementing histogram widget
		// Otherwise we want to use Keyword search
        else
        {
        	// connect to the Keyword query service on port 50004 and query it.
            $queryResult = querySearchService($lsiQueryHost, $lsiQueryPort, $query, $searchType);
            $queryResult = explode("\n", $queryResult);
			$largestSimilarity = findLargestSimilarity( $queryResult );
			if($exactSearch === "true" )
			{
				// pass the largest similarity from the keyword search
				$histogram = generateHistogramData($queryResult, 0, $largestSimilarity);
			}
			else {
				// the range of similarity scores for LSI is -1 to 1.
				$histogram = generateHistogramData($queryResult, -1, 1);
			}
			$unresolved = $queryResult;
			$queryResult = resolveIDs( $queryResult );
        }
		
		
    }
	// There was no search query to use.
    else
    {
        echo "There was an error performing the search";
    }
	
	/*
	 * $data is the array that contains scores.
	 * $min is the minimum score that can be encountered.
	 * $max is the maximum score that can be encountered.
	 */ 
	function generateHistogramData( $data, $min, $max )
	{
		// for each similarity score.
		for($i = 0; $i < count($data) - 1; $i++)
	    {
	    	// get the similarity
			$entry = explode(" ", $data[$i]);
		    $name = "";
			$entry = explode(" ", $data[count($entry)]);
			$similarity = $entry[count($entry) - 1 ];
			
			// update the count depending on the range that $similarity falls into.
	    }
		return $largest + 0.5;
	}
    
	function findLargestSimilarity( $data )
	{
		$largest = 0;
		for($i = 0; $i < count($data) - 1; $i++)
	    {
	    	/*
			 *  convert the line into an array, where each index is a word separated by a space.
			 *  For example say, $unresolvedIDs[0] = "1531 1.312"
			 *  The string "1531 1.312" is composed of two parts. The first part is an ID that can 
			 *  be resolved against the Collaboratum database. The second part is a similarity or 
			 * 	ranking score used to determine how relevant this result is to the search query.
			 * 
			 *  When we explode(" ", "1531 1.312") we get an array $entry where
			 *  $entry[0] = "1531"
			 *  $entry[1] = "1.312"
			 */ 
			 $entry = explode(" ", $data[$i]);
		     $name = "";
			 $entry = explode(" ", $data[count($entry)]);
			 
			
			$similarity = $entry[count($entry) - 1 ];
			
			if ($similarity > $largest)
			{
				$largest = $similarity;
			}
	    }
		return $largest + 0.5;
	}
	
	/**
	 * Description: This function takes IDs returned by query services from their database and resolves them
	 * to an investigator or grant name.  
	 * 
	 * Parameters:
	 * $unresolvedIDs: This is an array that contains all of the IDs from the query service
	 * that need to be resolved into names using the Collaboratum database.
	*/
	function resolveIDs( $unresolvedIDs )
	{
		// Now we connect to the Collaboratum database
	        mysql_connect($dbHost, $dbUser, $dbPass) or die(mysql_error());
	        mysql_select_db($dbNameGeneral) or die(mysql_error());
			
		$resolvedIDs = array();
		// for each line in $unresolvedIDs
	    for($i = 0; $i < count($unresolvedIDs) - 1; $i++)
	    {
	    	/*
			 *  convert the line into an array, where each index is a word separated by a space.
			 *  For example say, $unresolvedIDs[0] = "1531 1.312"
			 *  The string "1531 1.312" is composed of two parts. The first part is an ID that can 
			 *  be resolved against the Collaboratum database. The second part is a similarity or 
			 * 	ranking score used to determine how relevant this result is to the search query.
			 * 
			 *  When we explode(" ", "1531 1.312") we get an array $entry where
			 *  $entry[0] = "1531"
			 *  $entry[1] = "1.312"
			 */ 
	        $entry = explode(" ", $unresolvedIDs[$i]);
		// When we explode this tring there will be more array entries than we need. This is because the sever inserts multiple spaces
		// to visually align the id and similiarity scores in output.

		// TODO modify the lsi and keyword python servers to only have one space in between the id and sim score.	
	        // Then we query the Collaboratum database with the ID($entry[0]) from our exploded array, $entry.
		$entry[1] = $entry[count($entry)-1];
		$size = count($entry);
		for( $j = 4; $j < $size; $j++)
		{
			unset($entry[$j]);
		}
		$entry = array_values($entry);

	        $resolvedID = mysql_query("SELECT `investigator`.name, `investigator`.type FROM investigator WHERE `investigator`.investigator_id = ".$entry[0]."")
	        or die(mysql_error());  
	
			// Then we take the results from the database and store them in
	        $row = mysql_fetch_array( $resolvedID);
	        // Then we overwrite the id with the textual name from the database.
		$entry[2] = $entry[0];  
	        $entry[0] = $row['name'];
		// also store the type
		$entry[3] = $row['type'];
	        $resolvedIDs[$i] = implode("`", $entry);
	    }
		return $resolvedIDs;
	}
	
	/**
	 * Description: This function connects to the given query service, queries it, and returns the results from 
	 * the query service.
	 * 
	 * Parameters:
	 * $hostname: The hostname is the machine on which the query service is running. Ex. 'localhost', '127.0.0.1', '75.66.31.45', etc.
	 * $port: The port on which the query service is running on the given host.
	 * $query: The search string to query the service with.
	 * $type: The type of results that are desired from the query service.
	 * 		type possible values
	 * 		0 - return results containing both grants and investigators
	 * 		1 - return results containing only grants
	 * 		2 - return results containing only investigators.
	 */
    function querySearchService($hostname, $port, $query, $type)
    {
        $sock = socket_create(AF_INET, SOCK_STREAM, 0);
        $message = $type." | ".$query;
        
	if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0)))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            die("Couldn't create socket: [$errorcode] $errormsg \n");
        }
        
        if(!socket_connect($sock , $hostname, $port))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            die("Could not connect: [$errorcode] $errormsg \n");
        }
                
        //Send the message to the server
        if( ! socket_send ( $sock , $message , strlen($message) , 0))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            die("Could not send data: [$errorcode] $errormsg \n");
        }   
         
        //Now receive reply from server into 
	$buffer = "";
	$in = "";

	while( ( $res = socket_recv($sock, $buffer, 1, MSG_PEEK) ) != FALSE && $buffer != "\0" )
	{
		$in .= $buffer;
		socket_recv($sock, $buffer, 1, MSG_WAITALL);
	}
	$message = "ack";
        if( !socket_send( $sock, $message, strlen($message), 0 ) )
        {
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);
                die("Could not send acknowledgement: [$errorcode] $errrormsg \n");
        }

	
        //return the search results
        return $in;

        
    }
    
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php
			echo feof($sock);
		?>
		<title>Collaboratum Home</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="../res/bootstrap/css/bootstrap.css" rel="stylesheet" media="screen">
		<link href="../res/css/jquery-ui.css" rel="stylesheet">
		<style type="text/css">
			body {
				padding: 40px;
			}
			
			#explorerTabContent {
				height: 100% !important;
			}
		</style>
		<link href="../res/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<link rel="stylesheet" href="../res/css/index.css">
		
	</head>
	<body>
		<!-- Begin Body Scaffolding -->
		<div class="row-fluid">
			<div class="span12">
				<!-- Begin Nav -->
				<div class="navbar navbar-fixed-top">
					<div class="navbar-inner">
						<a class="brand" href="/Collaboratum/index.php">Collaboratum</a>
						<ul class="nav">
							<li>
								<a href="/Collaboratum/index.php">Home</a>
							</li>
							<li class="divider-vertical"></li>
							<li>
								<a href="#aboutModal" data-toggle="modal">About</a>
							</li>
							<li class="divider-vertical"></li>
							<li>
								<a href="#helpModal" data-toggle="modal">Help</a>
							</li>
							<li class="divider-vertical"></li>
							<li class="dropdown">
							    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
							    	Browse Faculty Networks	
							    	<i class="icon-share-alt"></i>
							    	
							    </a>
							    <ul class="dropdown-menu">
							    	<li>
							    		<a href="#">
							    			Biology 
							    		</a>
							    	</li>
							    	<li>
							    		<a href="#">
							    			Chemistry
							    		</a>
							    	</li>
							    	<li>
							    		<a href="#">
							    			Biomedical Engineering
							    		</a>
							    	</li>
							    </ul>
						    </li>
						    <li class="divider-vertical"></li>
						</ul>
					</div>
				</div>
				<!-- End Nav -->
			</div>
			<!-- Main Content -->
			<div class="span12">
				<div class="span10 offset1 text-center">
                        <div id="cytoscapeweb" style="height: 475px;">
                            Cytoscape Web will replace the contents of this div with your graph.
                        </div>
						<p>
							<label for="amount"> </label>
							<input type="text" id="amount" style="border: 0; color: #f6931f; font-weight: bold;" />
						</p>
						<div id="slider-vertical" style="height: 20px;"></div>
            	</div>
			</div>
			<!-- End Main Content -->
			<div class="row-fluid">
				<div class="span12">
					<!-- Begin Explorer UI -->
					<div class="nav navbar-fixed-bottom">
						<div class="explorerUI" id="explorer1">
							<div class="explorer-group">
						    	<div id="collapsibleDiv" class="explorer-heading navbar-inner"  data-toggle="collapse" data-parent="#explorer1" href="#collapseOne">
						        	<a class="explorer-toggle offset6">
						        		<i class="icon-chevron-up"></i>
						        	</a>
						    	</div>
						    	<div id="collapseOne" class="explorer-body collapse in">
						      		<div class="accordion-inner" style="background-color: #fff;">
						        		<ul class="nav nav-tabs">
											<li>
												<a href="#search" data-toggle="tab">Search</a>
											</li>
											<li class="active">
												<a href="#results" data-toggle="tab">Results</a>
											</li>
											<li>
												<a href="#statistics" data-toggle="tab">Statistics</a>
											</li>
										</ul>
										<div id="explorerTabContent" class="tab-content">
											
											<div class="tab-pane fade" id="search">
												<div class="span12 well">
													<!-- Begin search widget -->
													<form class="form-inline" action="/Collaboratum/views/results.php">
														<div class="span11">
															<div class="input-prepend input-append text-left">
																<div class="btn-group">
															    	<button id="searchTypeButton" type="button" class="btn dropdown-toggle" data-toggle="dropdown">
															      		Keyword
															      		<span class="caret"></span>
															    	</button>
															    	<ul class="dropdown-menu">
															   
															      		<li><a tabindex="-1" href="#" onclick="selectSearch(0);" data-toggle="tooltip" data-placement="right" title="LSI is a more abstract search that provides results which are conceptually similar">LSI Search</a></li>
															      		<li><a tabindex="-1" href="#" onclick="selectSearch(1);" data-toggle="tooltip" data-placement="right" title="Keyword search provides more 'concrete' results than LSI">Keyword Search(Default)</a></li>
															      		
															    	</ul>
															    </div>
															    
																<input name="searchBox" type="text" class="input-xlarge" placeholder="Enter your Query..">
																
																<div class="btn-group">
															    	<button id="filterButton" type="button" class="btn dropdown-toggle" data-toggle="dropdown">
															      		Filter
															      		<span class="caret"></span>
															    	</button>
															    	<ul class="dropdown-menu">
															    		<li><a tabindex="-1" href="#" onclick="selectFilter(0);">Everything(Default)</a></li>
															      		<li><a tabindex="-1" href="#" onclick="selectFilter(1);">Grants Only</a></li>
															      		<li><a tabindex="-1" href="#" onclick="selectFilter(2);">Collaborators Only</a></li>
															      		<li><a tabindex="-1" href="#" onclick="selectFilter(3);">Classes Only</a></li>
															      		<li class="divider"></li>
															      		<li><a tabindex="-1" href="#" onclick="selectFilter(4);" data-toggle="modal" data-target="#customFilterModal">Build Custom Filter</a></li>
															    	</ul>
															    </div>
																<input id="searchType" type="hidden" name="exactSearch" value="true"> Keyword Search
								                    			<input id="filterType" type="hidden" name="searchType" value="0"> 
								                    			<input id="isFlashEnabled" name="isFlashEnabled" type="hidden" value="">
															</div>
														    <button type="submit" class="btn btn-primary">Search!</button> 
														</div>
													</form>
													<!-- End Search Widget -->
												</div>
											</div>
											
											<div class="tab-pane fade active in" id="results">
												<div id="searchResults">
							                        <table class="table table-striped table-hover table-condensed">
							                            <thead>
							                                <tr>
							                                    <td>#</td>
							                                    <td>Name</td>
											    				<td>Similiarity</td>
							                                </tr>
							                            </thead>
							                                <tbody>
							                                    <?php
													               // the number of results
								                                   	$numResults = 0;
																	$id = "";
							                                        for($i = 0; $i < count($queryResult); $i++)
							                                        {
							                                           $id = "";
							                                           $entry = explode("`", $queryResult[$i]);
																	  
																	   
																	   $name = $entry[0];
																	   $score = $entry[1]; 
																	   $id = $entry[2];
																	   $type = $entry[3];
																	   $tempName = trim($name);
																	   if($tempName == "")
																	   {
																	   	$name = "No Title Found";
																	   }
								                    				   if($score > 0)
												     				   {
																			$numResults++;
																			if($id <= 57)
																			{
																				echo '<tr>
																				<td>'.($numResults).'</td>
																				<td>
																				<a href="/Collaboratum/views/investigatorInfo.php?id='.$id.'">
																					'.$name.'
																				</a>
																				</td>
																				<td>
																					'.( $score ).'
																				</td>
																				</tr>';
																			}
																			else
																			{
																				echo '<tr>
																				<td>'.($numResults).'</td>
																				<td>
																				<a href="/Collaboratum/views/grantInfo.php?id='.$id.'">
																					'.$name.'
																				</a>
																				</td>
																				<td>
																					'.( $score ).'
																				</td>
																				</tr>';
																			}
												 					   }
							                                        }
							                                    ?>
							                                </tbody>
							                                <tfoot>
							                                    <tr>
							                                        <td>
							                                            <!-- Empty Column -->
							                                        </td>
							                                        <td>
							                                                # results found:
							                                        </td>
							                                        <td>
							                                                <?php echo $numResults; ?>
							                                        </td>
							                                    </tr>
							                                </tfoot>
							                            </table>
						                		</div>
						
						           			</div><!-- End results -->
											<div class="tab-pane fade" id="statistics">
												<script type="text/javascript" src="https://www.google.com/jsapi"></script>
												<script type="text/javascript">
											      google.load("visualization", "1", {packages:["corechart"]});
											      google.setOnLoadCallback(drawChart);
											      function drawChart() {
											        var data = new google.visualization.DataTable();
											        data.addColumn('string', 'Similarity Range'); // Implicit domain label col.
													data.addColumn('number', '# Entities');
													data.addColumn({type: 'string', role: 'tooltip'});
													
													
											        data.addRows([ 
											          <?php
											          	// if we are using LSI. Use 20 columns from -1 to 1
											          	if($exactSearch === "false")
														{
												          	echo "['>=-1',  ".$histogram[0].", '<0.1 Similarity \u000D\u000A Percent: %".($histogram[0]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[0]."'],
														          ['>0.9',  ".$histogram[1].", '<0.2 Similarity \u000D\u000A Percent: %".($histogram[1]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[1]."'],
														          ['>0.8',  ".$histogram[2].", '<0.3 Similarity \u000D\u000A Percent: %".($histogram[2]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[2]."'],
														          ['>0.7',  ".$histogram[3].", '<0.4 Similarity \u000D\u000A Percent: %".($histogram[3]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[3]."'],
														          ['>0.6',  ".$histogram[4].", '<0.5 Similarity \u000D\u000A Percent: %".($histogram[4]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[4]."'],
														          ['>0.5',  ".$histogram[5].", '<0.6 Similarity \u000D\u000A Percent: %".($histogram[5]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[5]."'],
														          ['>0.4',  ".$histogram[6].", '<0.7 Similarity \u000D\u000A Percent: %".($histogram[6]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[6]."'],
														          ['>0.3',  ".$histogram[7].", '<0.8 Similarity \u000D\u000A Percent: %".($histogram[7]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[7]."'],
														          ['>0.2',  ".$histogram[8].", '<0.9 Similarity \u000D\u000A Percent: %".($histogram[8]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[8]."'],
														          ['>0.1',  ".$histogram[9].", '<0.1 Similarity \u000D\u000A Percent: %".($histogram[9]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[9]."'],
														          ['<0.1',  ".$histogram[10].", '<0.2 Similarity \u000D\u000A Percent: %".($histogram[10]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[10]."'],
														          ['<0.2',  ".$histogram[11].", '<0.3 Similarity \u000D\u000A Percent: %".($histogram[11]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[11]."'],
														          ['<0.3',  ".$histogram[12].", '<0.4 Similarity \u000D\u000A Percent: %".($histogram[12]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[12]."'],
														          ['<0.4',  ".$histogram[13].", '<0.5 Similarity \u000D\u000A Percent: %".($histogram[13]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[13]."'],
														          ['<0.5',  ".$histogram[14].", '<0.6 Similarity \u000D\u000A Percent: %".($histogram[14]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[14]."'],
														          ['<0.6',  ".$histogram[15].", '<0.7 Similarity \u000D\u000A Percent: %".($histogram[15]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[15]."'],
														          ['<0.7',  ".$histogram[16].", '<0.8 Similarity \u000D\u000A Percent: %".($histogram[16]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[16]."'],
														          ['<0.8',  ".$histogram[17].", '<0.9 Similarity \u000D\u000A Percent: %".($histogram[17]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[17]."'],
														          ['<0.9',  ".$histogram[18].", '<0.9 Similarity \u000D\u000A Percent: %".($histogram[18]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[18]."'],
														          ['<=1', ".$histogram[19].", '<=1.0 Similarity \u000D\u000A Percent: %".($histogram[19]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[19]."'],";
														}
														// Otherwise using Keyword, use 20 columns from 0 to max.
														else {
															
														}
											          ?>
											        ]);
											
											        var options = {
											          title: 'Distribution of Related Entities',
											          bar:  {groupWidth: "100%"},
											          width: 600,
											          height: 450,
											          backgroundColor: {strokeWidth: 2, stroke: "#000"},
											          hAxis: {title: 'Similarity',  titleTextStyle: {color: 'red'}}
											        };
											
											        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
											        chart.draw(data, options);
											      }
											    </script>
												<div id="chart_div" class="span11" style="width: 600px; height: 450px;"></div>
											</div>
										</div>
						      		</div>
						    	</div>
						  	</div>
						</div>
					</div>
					<!-- End Nav -->
				</div>
			</div>		
		</div>
		<!-- End Body Scaffolding -->
		
		<!-- Begin Modals -->
		
		<!-- Modal that provides information about Collaboratum --> 
		<div id="aboutModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="aboutModalLabel" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 id="aboutModalLabel">About Collaboratum</h3>
			</div>
			<ul class="thumbnails">
  				<li class="span4 center vspace-small">
			    	<a href="#" class="thumbnail">
			    	<img data-src="holder.js/360x270" alt="360x270" style="width: 360px; height: 270px;" src="data:image/png;base64,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">
			    	</a>
				</li>
			</ul>
			<div class="modal-body">
				<div class="media">
                  <a class="pull-left" href="#">
                  	<img class="media-object" data-src="holder.js/64x64" alt="64x64" style="width: 64px; height: 64px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAACDUlEQVR4Xu2Yz6/BQBDHpxoEcfTjVBVx4yjEv+/EQdwa14pTE04OBO+92WSavqoXOuFp+u1JY3d29rvfmQ9r7Xa7L8rxY0EAOAAlgB6Q4x5IaIKgACgACoACoECOFQAGgUFgEBgEBnMMAfwZAgaBQWAQGAQGgcEcK6DG4Pl8ptlsRpfLxcjYarVoOBz+knSz2dB6vU78Lkn7V8S8d8YqAa7XK83ncyoUCjQej2m5XNIPVmkwGFC73TZrypjD4fCQAK+I+ZfBVQLwZlerFXU6Her1eonreJ5HQRAQn2qj0TDukHm1Ws0Ix2O2260RrlQqpYqZtopVAoi1y+UyHY9Hk0O32w3FkI06jkO+74cC8Dh2y36/p8lkQovFgqrVqhFDEzONCCoB5OSk7qMl0Gw2w/Lo9/vmVMUBnGi0zi3Loul0SpVKJXRDmphvF0BOS049+n46nW5sHRVAXMAuiTZObcxnRVA5IN4DJHnXdU3dc+OLP/V63Vhd5haLRVM+0jg1MZ/dPI9XCZDUsbmuxc6SkGxKHCDzGJ2j0cj0A/7Mwti2fUOWR2Km2bxagHgt83sUgfcEkN4RLx0phfjvgEdi/psAaRf+lHmqEviUTWjygAC4EcKNEG6EcCOk6aJZnwsKgAKgACgACmS9k2vyBwVAAVAAFAAFNF0063NBAVAAFAAFQIGsd3JN/qBA3inwDTUHcp+19ttaAAAAAElFTkSuQmCC">
                  </a>
                  <div class="media-body">
                    <h4 class="media-heading">Person's Name</h4>
                    Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin commodo. Cras purus odio, vestibulum in vulputate at, tempus viverra turpis. Fusce condimentum nunc ac nisi vulputate fringilla. Donec lacinia congue felis in faucibus.
                  </div>
                </div>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
		
		<!-- Modal that provides help information for the current page -->
		<div id="helpModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 id="helpModalLabel">Help</h3>
			</div>
			<div class="modal-body">
				<b>Help Index</b>
				<ul>
					<li><a href="#help1">Search Interface - Overview</a></li>
					<li><a href="#help2">Search Interface - Search Methods</a></li>
					<li><a href="#help3">Search Interface - Filters</a></li>
				</ul>
				<br>
				<br>
				
				<h3><a name="help1">Search Interface(Overview):</a></h3>
				<img src="<?php echo $baseURL; ?>/res/images/tutorials/searchinterface.png"><br>
				
				<p style="text-indent: 3em;">The search interface is the first way in which you'll interact with CollaboratUM. It has several components that allow you to specify what datasets you'd like to search and how you'd like to search them. The interface is composed of 2 drop down lists, a text input for entering queries, and a submit button. The first drop down list, labeled as "Keyword" by default, allows you to select your search method. The second drop down list, labeled as "Filter" by default, allows you to specify the datasets you'd like to search within. 
				<br>
				<br>
				<h3><a name="help2">Search Interface (Search Methods):</a></h3>
				<img src="<?php echo $baseURL; ?>/res/images/tutorials/searchinterface2.png"><br>
				<p>The search methods available at this time are a keyword search algorithm, and an LSI search algorithm.  What’s the difference between the two?</p>
				<ul>
					<li>Keyword Search - A keyword search is what most users will be familiar with. It looks for direct associations between a datum and the given keywords. This is done by measuring how frequently a keyword is seen within that datum. </li>
					<li>LSI Search - A LSI(Latent Semantic Indexing) search differs from keyword search in that it looks for implied associations in a dataset using the given keyword(s). </li>
				</ul>





				<h3><a name="help3">Search Interface (Filters):</a></h3>
				
				<img src="<?php echo $baseURL; ?>/res/images/tutorials/searchinterface3.png"><br>
				<p style="text-indent: 3em;">As well as several searching methods there are several "filters" that can be applied. Filters basically allow you to specify what datasets you would like to limit your search to. Our current datasets include a list of Grants automatically pulled from the NIH, Biology classes at the University of Memphis, and a set of 57 Investigators and Faculty at University of Memphis. </p>
				<p style="text-indent: 3em;">When clicking the filter button, you can elect to search all datasets by selecting "Everything." You can also select to search just for Grants, or Collaborators, or Classes that are relevant to your query. </p>
				<p style="text-indent: 3em;">While selecting a single dataset or all datasets would be preferable in most cases, sometimes it will be desired to search a "mix-and-match" of different datasets. </p>
				<p style="text-indent: 3em;">By clicking "Build Custom Filter" you can select any and all datasets you wish to search within. </p>
				<br>
				<img src="<?php echo $baseURL; ?>/res/images/tutorials/searchinterface4.png">
				<br>
				<br>
				<p style="text-indent: 3em;">Simply check the checkboxes next to the datasets you wish to include in your results and click "Close." The filter will then be applied and you can begin searching with it. </p>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
		
		<!-- Modal that provides an interface for building a custom filter -->
		<div id="customFilterModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="customFilterModalLabel" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="verifyFilter();">&times;</button>
				<h3 id="customFilterModalLabel">Build your custom filter!</h3>
			</div>
			<div class="modal-body">
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<td>
								Filters:
							</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<input type="checkbox" id="customFilterGrant" value="grants" onchange="customFilter(this, 1);"> Grants
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" id="customFilterCollaborator" value="collaborators" onchange="customFilter(this, 2);"> Collaborators
							</td>
						</tr>
						<tr>
							<td>
								<input type="checkbox" id="customFilterClasses" value="classes" onchange="customFilter(this, 3);"> Classes
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true" onclick="verifyFilter();">Close</button>
			</div>
		</div>
		<!-- End Modals -->
		
		<!-- Begin load JS -->
		<script type="text/javascript" src="/Collaboratum/res/cytoscape/js/min/json2.min.js"></script>
        <script type="text/javascript" src="/Collaboratum/res/cytoscape/js/min/AC_OETags.min.js"></script>
        <script type="text/javascript" src="/Collaboratum/res/cytoscape/js/min/cytoscapeweb.min.js"></script>
		<script src="http://code.jquery.com/jquery.js"></script>
		<script src="../res/bootstrap/js/bootstrap.min.js"></script>   
		<script src="../res/js/jquery-1.8.2.js" type="text/javascript" charset="utf-8"></script>
		<script src="../res/js/flash_detect.js" type="text/javascript" charset="utf-8"></script>
		<script src="../res/js/jquery.infieldlabel.min.js" type="text/javascript"></script>
		<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
		<script type="text/javascript" charset="utf-8">
			$(function(){ $("label").inFieldLabels(); });
			$(function(){
				var availableTags = [
					"cancer",
					"breast",
					"reelin"
				];
				$( "#searchBox" ).autocomplete({
					source: availableTags
				});
			});
		</script>
		<script type="text/javascript">
	        /*
	        * This script determines if flash is installed
	        * if not it passes a hidden parameter instructing
	        * the search page to generate a png image of the search's graph
	        * and display that instead of the cytoscape web swf graph
	        */
	
	        if(FlashDetect.installed)
	        {
	                // If flash is installed
	                $("#isFlashEnabled").val("true");
	        }
	        else
	        {
	                // If flash isn't installed
	                $("#isFlashEnabled").val("false");
	        }
        </script>
        <!-- start of Cytoscape Graph Data -->
        <script type="text/javascript"> 
            window.onload = function() {
         
                // id of Cytoscape Web container div
                var div_id = "cytoscapeweb";
				
                var network_json;
				$.ajax({
					type: 'POST',
					url: "../res/scripts/getGraph.php",
					data: <?php echo "{threshHold: \"0.1\", queryTerm: \"".$query."\", queryResult: \"".preg_replace('/\s+/', ' ', trim(implode("~", $queryResult)))."\"}"; ?>, 
					dataType: "json",
					async: false,
					success: function(data) {
						console.log(data);
						network_json = data;
					},
					error: function() {
						alert("Sorry, There was an error loading the graph"); // + "<?php echo preg_replace('/\s+/', ' ', trim(implode("~", $queryResult))); ?>");
					}
				});

  var nodeColorMapper = {
                          "attrName": "type",
                          "entries": [ { "attrValue": "investigator", "value": "#0000ff" }, { "attrValue": "queryTerm", "value": "#ff0000"}, {"attrValue": "grant", "value":"#00ff00"} ]
                  };

                  // NOTE the "compound" prefix in some visual properties
                  var visual_style = {
                      "nodes": {
                          "shape": "RECTANGLE",
                          "label": "", // { "passthroughMapper": { "attrName": "label" } },
                          "borderColor": "#83959d",
                          "color": { "discreteMapper": nodeColorMapper  },
                      }
                  };

                // initialization options
                var options = {
                    swfPath: "/Collaboratum/res/cytoscape/swf/CytoscapeWeb",
                    flashInstallerPath: "/Collaboratum/res/cytoscape/swf/playerProductInstall"
                };

                var vis = new org.cytoscapeweb.Visualization(div_id, options);
                
		vis.ready(function() {
                    // set the style programmatically
			
                });

                var draw_options = {
                    // your data goes here
                    network: network_json,
                    // set the style at initialisation
                    visualStyle: visual_style,
                    // hide pan zoom
                    panZoomControlVisible: true 
                };

                vis.draw(draw_options);
            };
        </script> <!-- End of Cytoscape graph data -->
        <script type="text/javascript"> <!-- start of scale bar script -->
		$(function() {
			$( "#slider-vertical" ).slider({
				orienatation: "vertical",
				range: "min",
				min: 0,
				max: <?php echo $largestSimilarity; ?>, 
				value: 0.1,
				step: 0.05,
				slide: function( event, ui ) {
					$( "#amount" ).val( ui.value );
				},
				change: function( event, ui ) {
							$.ajax({
								type: 'POST',
								url: "../res/scripts/getGraph.php",
								data: <?php echo "{ threshHold: ui.value, queryTerm: \"".$query."\", queryResult: \"".preg_replace('/\s+/', ' ', trim(implode("~", $queryResult)))."\"}"; ?>,
								dataType: "json",
								async: false,
								success: function(data) {
									network_json = data;
									try{ 
										var div_id = "cytoscapeweb";
										var options = {
											swfPath: "/Collaboratum/res/cytoscape/swf/CytoscapeWeb",
											flashInstallerPath: "/Collaboratum/res/cytoscape/swf/playerProductInstall"
										};



 var nodeColorMapper = {
                         "attrName": "type",
                         "entries": [ { "attrValue": "investigator", "value": "#0000ff" }, { "attrValue": "queryTerm", "value": "#ff0000"}, {"attrValue": "grant", "value":"#00ff00"} ]
                 };

                 // NOTE the "compound" prefix in some visual properties
                 var visual_style = {
                     "nodes": {
                         "shape": "RECTANGLE",
                         "label": { "passthroughMapper": { "attrName": "label" } },
                         "borderColor": "#83959d",
                         "color": { "discreteMapper": nodeColorMapper  },
                     }
                 };


										var vis = new org.cytoscapeweb.Visualization(div_id, options);


										var draw_options = {
											// your data goes here
											network: network_json,
											// set the style at initialisation
											visualStyle: visual_style,
											// hide pan zoom
											panZoomControlVisible: true 
										};

										vis.draw(draw_options);
										
									}
									catch(err)
									{
										alert(err);
									}
								},
								error: function() {
									alert("Sorry, There was an error loading the graph"); // + "<?php echo preg_replace('/\s+/', ' ', trim(implode("~", $queryResult))); ?>");
								}
							});
				}
			});
			$( "#amount" ).val( $( "#slider-vertical" ).slider( "value" ) );
		});
	</script> <!-- End of scale bar script -->		
	<script type="text/javascript">
        	/*
        	 * This script updates the search type to be used
        	 */
        	function selectSearch( searchType )
        	{
        		// Do LSI Search if search type is 0
        		if(searchType == 0)
        		{
        			$("#searchType").val('false');
        			$("#searchTypeButton").text("LSI");
        		}
        		//If search type is 1 do Keyword search
        		else if(searchType == 1)
        		{
        			$("#searchType").val('true');
        			$("#searchTypeButton").text("Keyword");
        		}
        	}
        
        	/*
        	 * This script updates the filter to be applied to the search.
        	 * It does this by modifying a hidden input on the search <form>
        	 * 
        	 * filterType = 0 : Everything will be returned in search results
        	 * filterType = 1 : Only grants will be returned in search results
        	 * filterType = 2 : Only collaborators will be returned in search results
        	 * filterType = 3 : Only classes will be returned in search results
        	 * filterType = 4 : A custom search filter has been applied to search results
        	 */
        	function selectFilter( filterType )
        	{
        		if(filterType == 0)
        		{
        			$("#filterButton").text("Everything");
        			$("#filterType").val('0');
        		}
        		if(filterType == 1)
        		{
        			$("#filterButton").text("Grants");
        			$("#filterType").val('1');
        		}
        		if(filterType == 2)
        		{
        			$("#filterButton").text("Collaborators");
        			$("#filterType").val('2');
        		}
        		if(filterType == 3)
        		{
        			$("#filterButton").text("Classes");
        			$("#filterType").val('3');
        		}
        		if(filterType == 4)
        		{
        			$("#filterButton").text("Custom");
        			$('#customFilterModal').modal('show');
        		}
        	}
        </script>
        <script type="text/javascript">
        	/*
        	 * This script updates the search type to be used
        	 */
        	function selectSearch( searchType )
        	{
        		// Do LSI Search if search type is 0
        		if(searchType == 0)
        		{
        			$("#searchType").val('false');
        			$("#searchTypeButton").text("LSI");
        		}
        		//If search type is 1 do Keyword search
        		else if(searchType == 1)
        		{
        			$("#searchType").val('true');
        			$("#searchTypeButton").text("Keyword");
        		}
        	}
        
        	/*
        	 * This script updates the filter to be applied to the search.
        	 * It does this by modifying a hidden input on the search <form>
        	 * 
        	 * filterType = 0 : Everything will be returned in search results
        	 * filterType = 1 : Only grants will be returned in search results
        	 * filterType = 2 : Only collaborators will be returned in search results
        	 * filterType = 3 : Only classes will be returned in search results
        	 * filterType = 4 : A custom search filter has been applied to search results
        	 */
        	function selectFilter( filterType )
        	{
        		if(filterType == 0)
        		{
        			
        			$("#filterButton").text("Everything");
        			$("#filterType").val('0');
        		}
        		if(filterType == 1)
        		{
        			
        			$("#filterButton").text("Grants");
        			$("#filterType").val('1');
        		}
        		if(filterType == 2)
        		{
        			
        			$("#filterButton").text("Collaborators");
        			$("#filterType").val('2');
        		}
        		if(filterType == 3)
        		{
        			
        			$("#filterButton").text("Classes");
        			$("#filterType").val('3');
        		}
        		if(filterType == 4)
        		{
        			
        			$("#filterButton").text("Custom");
					// Wipe the current filter and set all checkboxes to unchecked.
					$("#filterType").val("");
					// clear checboxes
					clearCheckboxes();
					// Show the modal to allow the client to build a new custom filter.				 
        			$("#customFilterModal").modal({
					show: true,
					keyboard: true
				});
        		}
			verifyFilter();
        	}

		function clearCheckboxes()
		{
			$("#customFilterGrant").prop("checked", false);
			$("#customFilterCollaborator").prop("checked", false);
			$("#customFilterClasses").prop("checked", false);
		}

		// create a customFilter
		function customFilter( obj, additionalFilter )
		{

			// Initialize the current filter.
			var curFilter = "";
			// append each checkbox if checked
			if( $("#customFilterGrant").is(":checked") )
			{
				curFilter = curFilter + '1';
			}
			if( $("#customFilterCollaborator").is(":checked") )
			{
				if(curFilter === "")
				{
					curFilter = curFilter + '2';
				}
				else
				{
					curFilter = curFilter + ',' + '2';
				}
			}
			if( $("#customFilterClasses").is(":checked") )
			{
				if(curFilter === "")
				{
					curFilter = curFilter + '3';
				}
				else
				{
					curFilter = curFilter + ',' + '3';
				}
			}
			$("#filterType").val(curFilter);
		}

		// verify that the current filter is valid, and if not attempt to fix it.
		function verifyFilter()
		{
			// TODO do validation checking using a finite state machine.
			var curFilter = $("#filterType").val();
			
			// the filter is not allowed to be empty, default to 0, update UI, and issue warning.
			if( curFilter === "" )
			{
				$("#filterType").val("0");	
				$("#filterButton").val("Everything");			
				alert("An invalid search filter has been detected. This search has been reset to search Everything.");	
			}
			// do a basic check to see if there are any invalid characters present in the filter string
			for( var i = 0; i < curFilter.length(); i++)
			{
				// if an error is detected default to everything and break out of loop.
				if( curFilter.charat(i) != '0' || curFilter.charAt(i) != '1' || curFilter.charAt(i) != '2' || curFilter.charAt(i) != '3' || curFilter.charAt!= ',') 
				{
					$("#filterType").val("0");
					$("#filterButton").val("Everything");
					alert("An invalid search filter has been detected. This search has been reset to search Everything.");
				}
			}	
		}
        </script>
	</body>
</html>
