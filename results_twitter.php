<?php 
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
        	$queryResult = querySearchService("localhost", "50005", $query, $searchType);
        	$queryResult = explode("\n", $queryResult);  
			$largestSimilarity = 1;
			$unresolved = $queryResult;
			$queryResult = resolveIDs( $queryResult );
        }
		// Otherwise we want to use Keyword search
        else
        {
        	// connect to the Keyword query service on port 50004 and query it.
            $queryResult = querySearchService("localhost", "50004", $query, $searchType);
            $queryResult = explode("\n", $queryResult);
			$largestSimilarity = findLargestSimilarity( $queryResult );
			$unresolved = $queryResult;
			$queryResult = resolveIDs( $queryResult );
        }
		
		
    }
	// There was no search query to use.
    else
    {
        echo "There was an error performing the search";
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
		     $score = $entry[count($entry) - 1 ];
			 
			
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
	        mysql_connect("localhost", "root", "baseg") or die(mysql_error());
	        mysql_select_db("collaboratum") or die(mysql_error());
			
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
        
        if(!socket_connect($sock , 'localhost' , $port))
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
						    	<div class="explorer-heading navbar-inner"  data-toggle="collapse" data-parent="#explorer1" href="#collapseOne">
						        	<a class="explorer-toggle offset6">
						        		<i class="icon-chevron-up"></i>
						        	</a>
						    	</div>
						    	<div id="collapseOne" class="explorer-body collapse">
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
										<div id="explorerTabContent" class="tab-content" style="height: 100% !important;">
											<div class="tab-pane fade" id="search">
												<div class="span12 well">
													<form class="form-inline" action="/Collaboratum/views/results_twitter.php">
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
												<h1>Histogram and other statistics widgets go here.</h1>
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
		
		<div id="helpModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 id="helpModalLabel">Help</h3>
			</div>
			<div class="modal-body">
				<ul class="thumbnails">
  				<li class="span4 center vspace-small">
			    	<a href="#" class="thumbnail">
			    	<img data-src="holder.js/360x270" alt="360x270" style="width: 360px; height: 270px;" src="data:image/png;base64,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">
			    	</a>
				</li>
			</ul>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
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
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
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
        			$('#customFilterModal').modal({
        				keyboard: true,
        			});
        			$('#customFilterModal').modal('show');
        		}
        	}
        </script>
	</body>
</html>
