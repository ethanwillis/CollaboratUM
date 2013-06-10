<?php
	// load Configuration options
	require_once("../config.php");
	
	// get the ID for this grant
	$id = $_GET['id'];
	
	// Connect to database
	mysql_connect($dbHost, $dbUser, $dbPass) or die(mysql_error());
	mysql_select_db($dbNameGeneral) or die(mysql_error());
	
	// Get the investigator's name, phone number, email, office, city, and state.
	$query = "SELECT I.name, I.title, Ph.phone_number, E.email_address, 
		L.city, L.address, L.state, L.country, L.zipcode, L.office, L.institution, F.fax_number, D.department, 
		Pi.picture_url FROM investigator as I 
		LEFT JOIN phone_number AS Ph ON Ph.investigator_id = ".$id." 
		LEFT JOIN email_address AS E ON E.investigator_id = ".$id." 
		LEFT JOIN location AS L ON L.investigator_id = ".$id." 
		LEFT JOIN fax_number AS F ON F.investigator_id = ".$id." 
		LEFT JOIN department AS D ON D.investigator_id = ".$id." 
		LEFT JOIN pictures AS Pi ON Pi.investigator_id = ".$id." 
		WHERE I.investigator_id = ".$id." AND Pi.picture_type = 'profile'";
		
	$queryResult = mysql_query($query);
			
	// Then we take the results from the database and store them in a row
	$row = mysql_fetch_array($queryResult);
	
	// Store the profile data from the mysql query.
	$name = $row['name'];
	$title = $row['title'];
	$phone = $row['phone_number'];
	$email = $row['email_address'];
	$city = $row['city'];
	$address = $row['address'];
	$state = $row['state'];
	$country = $row['country'];
	$zipcode = $row['zipcode'];
	$office = $row['office'];
	$institution = $row['institution'];
	$fax = $row['fax_number'];
	$department = $row['department'];
	$picture_url = $row['picture_url'];
	
	// Get similarity data for histogram
	mysql_select_db($dbNameNetwork) or die(mysql_error());
	$query="SELECT count(*) FROM doc_pairwise_cosine_matrix WHERE (col".intval($id)." BETWEEN 0.0 AND 0.1) AND NOT col".intval($id)." = 0.1
			UNION
			SELECT count(*) FROM doc_pairwise_cosine_matrix WHERE (col".intval($id)." BETWEEN 0.1 AND 0.2) AND NOT col".intval($id)." = 0.2
			UNION
			SELECT count(*) FROM doc_pairwise_cosine_matrix WHERE (col".intval($id)." BETWEEN 0.2 AND 0.3) AND NOT col".intval($id)." = 0.3
			UNION
			SELECT count(*) FROM doc_pairwise_cosine_matrix WHERE (col".intval($id)." BETWEEN 0.3 AND 0.4) AND NOT col".intval($id)." = 0.4
			UNION
			SELECT count(*) FROM doc_pairwise_cosine_matrix WHERE (col".intval($id)." BETWEEN 0.4 AND 0.5) AND NOT col".intval($id)." = 0.5
			UNION
			SELECT count(*) FROM doc_pairwise_cosine_matrix WHERE (col".intval($id)." BETWEEN 0.5 AND 0.6) AND NOT col".intval($id)." = 0.6
			UNION
			SELECT count(*) FROM doc_pairwise_cosine_matrix WHERE (col".intval($id)." BETWEEN 0.6 AND 0.7) AND NOT col".intval($id)." = 0.7
			UNION
			SELECT count(*) FROM doc_pairwise_cosine_matrix WHERE (col".intval($id)." BETWEEN 0.7 AND 0.8) AND NOT col".intval($id)." = 0.8
			UNION
			SELECT count(*) FROM doc_pairwise_cosine_matrix WHERE (col".intval($id)." BETWEEN 0.8 AND 0.9) AND NOT col".intval($id)." = 0.9
			UNION
			SELECT count(*) FROM doc_pairwise_cosine_matrix WHERE (col".intval($id)." BETWEEN 0.9 AND 1.0)";
	$rows = mysql_query( $query );
	
	// Store all of the data needed for the histogram.
	$i = 0;
	$totalSimilarEntities = 0;
	while( $row = mysql_fetch_array($rows) )
	{
		$histogram[$i] = $row[0];
		$totalSimilarEntities += $histogram[$i];
		++$i;
	}
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Collaboratum Home</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="../res/bootstrap/css/bootstrap.css" rel="stylesheet" media="screen">
		<link href="../res/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<link rel="stylesheet" href="../res/css/jquery-ui.css">
		<style type="text/css">
			#explorerTabContent {
				height: 100% !important;
			}
		</style>
		<link rel="stylesheet" href="../res/css/index.css">
	</head>
	<body>
		
		<!-- Begin Body Scaffolding -->
		<div class="row-fluid">
			<div class="span12">
				<!-- Begin Nav -->
				<div class="navbar navbar-static-top">
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
							<li>
								<a href="javascript:history.back()">
									<i class="icon-arrow-left"></i>
									Go Back
								</A>
							</li>
						</ul>
					</div>
				</div>
				<!-- End Nav -->
			</div>
  			<div class="span12 alpha">
  				<div class="span8 center vspace-small">
  					<div class="span10 center">
  						<div class="span12">
  							
  						</div>
  						<div class="span12 well">
  							<ul class="nav nav-tabs">
								<li class="active">
									<a href="#Profile" data-toggle="tab">Profile</a>
								</li>
								<li>
									<a href="#Publications" data-toggle="tab">Publications</a>
								</li>
								<li>
									<a href="#ResearchNetwork" data-toggle="tab">Research Network</a>
								</li>
								<li>
									<a href="#GrantNetwork" data-toggle="tab">Grant Suggestions</a>
								</li>
								<li>
									<a href="#Histogram" data-toggle="tab">Histogram</a>
								</li>
							</ul>
							<div id="explorerTabContent" class="tab-content">
								
								<div class="tab-pane fade active in" id="Profile">
									<div class="media">
							            <a class="pull-left" href="#">
							            	<img class="media-object" alt="<?php echo $name; ?>" style="width: 64px; height: 64px;" src="<?php echo $baseURL.$picture_url; ?>">
							            </a>
						            	<div class="media-body">
								            <h4 class="media-heading"><a href=""><?php echo "<p class=\"text-center\">".$name."</p>"; ?></a><?php if( !(empty($title)) ){ echo "<small> - ".$title."</small>"; } ?></h4>
								            <address>
							            		<?php
							            			// show the institution and department only if that information is available.
							            			if( !(empty($institution)) )
							            			{
							            				echo "<strong>".$institution;
							            				if( !(empty($department)) )
							            				{
							            					echo "<small> - ".$department."</small>";
							            				}
							            				echo "</strong><br>";
							            			}
													if( !(empty($office)) )
													{
														echo $office."<br>";
													}
							            			if( !(empty($address)) )
							            			{
							            				echo $address."<br>";
							            			}
								            		
								            		echo $city.", ".$state." ".$zipcode." ".$country; ?>
								            	
								            </address>
								            
								            <address>
								            	
								            	<strong> Contact Information </strong> <br>
								            	<?php
								            		// check to make sure we have any contact information on file.
								            		if( !(empty($email)) || !(empty($phone)) || !(empty($fax)) )
													{
														//TODO Modify the SQL and this section to handle multiple email addresses, phone numbers, and fax numbers.
														if( !(empty($email)) )
														{
															 echo "Email: <a href=\"mailto:<?php echo $email; ?>\">"; echo $email."</a> <br>";
														}
								            			if( !(empty($phone)) ) 
								            			{
								            				echo "Phone: ".$phone." <br>";
								            			}
														if( !(empty($fax)) )
														{
															
								            				echo "Fax: ".$fax;
														} 
													}
													else {
														echo "<p class=\"text-error\"> Sorry, We don't have any contact information on file. </p>";
													}
								            	?>
								            	
								            </address>
							            </div>
						            </div>
								</div>
								<div class="tab-pane fade" id="Publications">
									<h1> Publications will be listed here </h1>
								</div>
								<div class="tab-pane fade" id="ResearchNetwork">
									<h1> Research Network will be displayed here </h1>
								</div>
								<div class="tab-pane fade" id="GrantNetwork">
									<h1> Grant Suggestions will be here</h1>
								</div>
								<div class="tab-pane fade" id="Histogram">
									<script type="text/javascript" src="https://www.google.com/jsapi"></script>
									<script type="text/javascript">
								      google.load("visualization", "1", {packages:["corechart"]});
								      google.setOnLoadCallback(drawChart);
								      function drawChart() {
								        var data = new google.visualization.DataTable();
								        data.addColumn('string', 'Similarity Range'); // Implicit domain label col.
										data.addColumn('number', '# Entities');
										data.addColumn({type: 'string', role: 'tooltip'});
										
										//['Similarity Range', '# Entities'],
								        data.addRows([ 
								          <?php
								          	//echo out all of the similarity data in the correct format.
								          	echo "['<0.1',  ".$histogram[0].", '<0.1 Similarity \u000D\u000A Percent: %".($histogram[0]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[0]."'],
										          ['<0.2',  ".$histogram[1].", '<0.2 Similarity \u000D\u000A Percent: %".($histogram[1]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[1]."'],
										          ['<0.3',  ".$histogram[2].", '<0.3 Similarity \u000D\u000A Percent: %".($histogram[2]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[2]."'],
										          ['<0.4',  ".$histogram[3].", '<0.4 Similarity \u000D\u000A Percent: %".($histogram[3]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[3]."'],
										          ['<0.5',  ".$histogram[4].", '<0.5 Similarity \u000D\u000A Percent: %".($histogram[4]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[4]."'],
										          ['<0.6',  ".$histogram[5].", '<0.6 Similarity \u000D\u000A Percent: %".($histogram[5]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[5]."'],
										          ['<0.7',  ".$histogram[6].", '<0.7 Similarity \u000D\u000A Percent: %".($histogram[6]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[6]."'],
										          ['<0.8',  ".$histogram[7].", '<0.8 Similarity \u000D\u000A Percent: %".($histogram[7]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[7]."'],
										          ['<0.9',  ".$histogram[8].", '<0.9 Similarity \u000D\u000A Percent: %".($histogram[8]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[8]."'],
										          ['<=1.0', ".$histogram[9].", '<=1.0 Similarity \u000D\u000A Percent: %".($histogram[9]/$totalSimilarEntities)."\u000D\u000A # Entities: ".$histogram[9]."'],";
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
									
  							<table class="table table-striped">
  								
  							</table>
  						</div>
  						
  					</div>
  				</div>
  			</div>
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
											<li class="active">
												<a href="#search" data-toggle="tab">Search</a>
											</li>
											<li>
												<a href="#help" data-toggle="tab">Help</a>
											</li>
										</ul>
										<div id="explorerTabContent" class="tab-content">
											<div class="tab-pane fade active in" id="search">
												<div class="span12 well">
													<!-- Begin search widget -->
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
													<!-- End Search Widget -->
												</div>
											</div>
											<div class="tab-pane fade" id="help">
												<h1>help widget goes here</h1>
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
			    	<img data-src="holder.js/360x270" alt="360x270" style="width: 360px; height: 270px;" src="">
			    	</a>
				</li>
			</ul>
			<div class="modal-body">
				<div class="media">
                  <a class="pull-left" href="#">
                  	<img class="media-object" data-src="holder.js/64x64" alt="64x64" style="width: 64px; height: 64px;" src="">
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
