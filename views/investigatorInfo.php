<?php
	//import config file
	$dbUser = "Collaboratum";
	$dbPass = "Collaboratum";
	$dbNameGeneral = "collaboratum";
	$dbNameNetwork = "parsingdata";
	
	$baseURL = "http://binf1.memphis.edu/Collaboratum";
	
	$lsiQueryHost = "localhost";
	$lsiQueryPort = "50005";
	
	$keywdQueryHost = "localhost";
	$keywdQueryPort = "50004";

	// get the ID for this grant
	$id = $_GET['id'];
	
	// Connect to database
	$con = mysql_connect($dbHost, $dbUser, $dbPass) or die(mysql_error());
	mysql_select_db($dbNameGeneral, $con) or die(mysql_error());
	
	// Get the investigator's name, phone number, email, office, city, and state.
	$query = "SELECT I.first_name, I.last_name, I.title, Ph.phone_number, E.email_address, 
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
	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
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
	
	
	// get grant suggestion data
	
	$query = "SELECT * FROM doc_pairwise_cosine_matrix WHERE col".intval($id)." = 1";
	$result = mysql_query ( $query );
	$grants = array(array());
	
	// grab all grants. 1177 is total number of records in investigators table. 289 is the column where grants begin.
	for($i = 0; $i < 1177; $i++)
	{
		$row = mysql_fetch_array($result);
		if($i >= 288)
		{
			// store sim score
			$grants[$i-288][0] = $row['col'.$id];
			// store the id
			$grants[$i-288][1] = $i+1;
		}
	}
	
	// resolve all of the grant titles from their ids
	mysql_select_db($dbNameGeneral, $con) or die(mysql_error());
	for($i = 0; $i < count($grants); $i++){
		$query = "SELECT first_name FROM investigator WHERE investigator_id = ".$grants[$i][1];
		$result = mysql_query( $query );
		$row = mysql_fetch_array($result);
		$grants[$i][2] = $row['first_name'];
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
		<script type='text/javascript' src='https://www.google.com/jsapi'></script>
		<script src="http://code.jquery.com/jquery.js"></script>
		<script src="../res/bootstrap/js/bootstrap.min.js"></script>   
		<script src="../res/js/jquery-1.8.2.js" type="text/javascript" charset="utf-8"></script>
		<script src="../res/js/flash_detect.js" type="text/javascript" charset="utf-8"></script>
		<script src="../res/js/jquery.infieldlabel.min.js" type="text/javascript"></script>
		<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
		<style type="text/css">
			#explorerTabContent {
				height: 100% !important;
			}
	
		.accordion-expand-holder {
    			margin:10px 0;
		}
		.accordion-expand-holder .open, .accordion-expand-holder .close {
    			margin:0 10px 0 0;
		}


		</style>
		<link rel="stylesheet" href="../res/css/index.css">
		<script>
			// Accordion - Expand All #01
$(function () {
    $("#accordion").accordion({
        collapsible:true,
        active:false,
	heightStyle: "content"
    });
    var icons = $( "#accordion" ).accordion( "option", "icons" );
    $('.open').click(function () {
        $('.ui-accordion-header').removeClass('ui-corner-all').addClass('ui-accordion-header-active ui-state-active ui-corner-top').attr({
            'aria-selected': 'true',
            'tabindex': '0'
        });
        $('.ui-accordion-header-icon').removeClass(icons.header).addClass(icons.headerSelected);
        $('.ui-accordion-content').addClass('ui-accordion-content-active').attr({
            'aria-expanded': 'true',
            'aria-hidden': 'false'
        }).show();
        $(this).attr("disabled","disabled");
        $('.close').removeAttr("disabled");
    });
    $('.close').click(function () {
        $('.ui-accordion-header').removeClass('ui-accordion-header-active ui-state-active ui-corner-top').addClass('ui-corner-all').attr({
            'aria-selected': 'false',
            'tabindex': '-1'
        });
        $('.ui-accordion-header-icon').removeClass(icons.headerSelected).addClass(icons.header);
        $('.ui-accordion-content').removeClass('ui-accordion-content-active').attr({
            'aria-expanded': 'false',
            'aria-hidden': 'true'
        }).hide();
        $(this).attr("disabled","disabled");
        $('.open').removeAttr("disabled");
    });
    $('.ui-accordion-header').click(function () {
        $('.open').removeAttr("disabled");
        $('.close').removeAttr("disabled");
        
    });
});

		  $(function() {
		    $( "#publication_list" ).accordion({
		    	heightStyle: "content",
		    	collapsible: true
		    });
		  });


		 </script>
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
									<!-- href="#ResearchNetwork" data-toggle="tab">Research Network</a -->
								</li>
								<li>
									<a href="#TopCoAuthors" data-toggle="tab">Top Co-Authors</a>
								</li>
								<li>
									<a href="#TopJournals" data-toggle="tab">Top Journals</a>
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
							            	<img class="media-object" alt="<?php echo $first_name." ".$last_name; ?>" style="width: 64px; height: 64px;" src="<?php echo $baseURL.$picture_url; ?>">
							            </a>
						            	<div class="media-body">
								            <h4 class="media-heading"><a href=""><?php echo "<p class=\"text-center\">".$first_name." ".$last_name."</p>"; ?></a><?php if( !(empty($title)) ){ echo "<small> - ".$title."</small>"; } ?></h4>
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
								<div class="tab-pane fade" id="TopCoAuthors">
									<?php
								$names = explode(" ", $first_name);
                                                                             if(isset($names[0])){
                                                                                     $first_name = $names[0];
                                                                             }
                                                                             if(isset($names[1])){
                                                                                     $last_name = $names[1];
                                                                            }

										$coauthors = array();
									$num = array();
									$result = mysql_query("SELECT publication_id FROM publications where investigator_id = ".$id) or die(mysql_error());
									for($i = 0 ; $i < mysql_num_rows($result); $i++)
									{
											$row = mysql_fetch_array($result);
											$result2 = mysql_query("SELECT field_value FROM publication_data where medline_field = 'AU' AND publication_id = ".$row['publication_id']);
											for($j = 0; $j < mysql_num_rows($result2); $j++) {
												$row2 = mysql_fetch_array($result2);
												if( !in_array($row2['field_value'], $coauthors)) {
													$inString = strpos($row2['field_value'], $first_name);
													$inString2 = strpos($row2['field_value'], $last_name);
													if($inString === FALSE && $inString2 === FALSE){
														$coauthors[count($coauthors)] = $row2['field_value'];
														$num[count($coauthors)] = 1;
													}
												}
												else {
													$index = array_search($row2['field_value'], $coauthors);
													$num[$index] = $num[$index] + 1;
												}
										
										       }
										
									}
									$sortArray = array(array());
									$x = 0;
									$names = explode(" ", $first_name);
									if(isset($names[0])){
										$first_name = $names[0];
									}
									if(isset($names[1])){
										$last_name = $names[1];
									}
									foreach($coauthors as $author)
									{
										$inString = strpos($author, $first_name);
										$inString2 = strpos($author, $last_name);
										if($inString === FALSE && $inString2 === FALSE) {
											$sortArray[$x][0] = $author;
											$sortArray[$x][1] = $num[$x];
											$x = $x + 1;
										}
										else {
										}
									}
									/*for( $x = 0; $x < count($coauthors); $x++)
									{

										$sortArray[$x][0] = $coauthors[$x];
										$sortArray[$x][1] = $num[$x];
									}*/
									
 foreach ($sortArray as $key => $row) {
     		$volume[$key]  = $row[1];
         $edition[$key] = $row[0];
        }
?>

    <script type="text/javascript">

      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'CoAuthor');
        data.addColumn('number', '# Publications');
        data.addRows([ 
		<?php for($z = 0; $z < count($sortArray)-1; $z++) {
			$author = $sortArray[$z];
			$author[0] = str_replace(",", "", $author[0]);
			if(!isset($author[1])) { $author[1] = 1; }
			echo "['".$author[0]."', ".$author[1]."],";
			}
			$author = $sortArray[count($sortArray)-1];
			echo "['".$author[0]."', ".$author[1]."]";
		?>
]);

        // Set chart options
        var options = {'title':'Top CoAuthors',
                       'width':700,
                       'height':450,
		       'sliceVisibilityThreshold': 1/1000};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById('coauthor_chart'));
        chart.draw(data, options);
      }
    </script>
<div id="coauthor_chart"></div>

 <script type='text/javascript'>
       google.load('visualization', '1', {packages:['table']});
       google.setOnLoadCallback(drawTable);
       function drawTable() {
         var data = new google.visualization.DataTable();
         data.addColumn('string', 'Co-Author');
         data.addColumn('number', '# Publications');
         data.addRows([
          <?php
	                         for($z = 0; $z < count($sortArray)-1; $z++) {
                                 $author = $sortArray[$z];
if(!isset($author[1])) { $author[1] = 1; }
                                echo "[\"".$author[0]."\", ".$author[1]."],";
                         }
                         $journal = $sortArray[count($sortArray)-1];
                         echo "[\"".$author[0]."\", ".$author[1]."]";
           ?>
         ]);

        var table = new google.visualization.Table(document.getElementById('coauthors_table'));
         table.draw(data, {showRowNumber: true, sortColumn: 1, sortAscending: false, width: '700'});
       }
     </script>
         <div id='coauthors_table'></div>

								</div>
								<div class="tab-pane fade" id="TopJournals">
										<?php
									$journals = array();
									$num = array();
									$result = mysql_query("SELECT publication_id FROM publications where investigator_id = ".$id) or die(mysql_error());
									for($i = 0 ; $i < mysql_num_rows($result); $i++)
									{
											$row = mysql_fetch_array($result);
											$result2 = mysql_query("SELECT field_value FROM publication_data where medline_field = 'SO' AND publication_id = ".$row['publication_id']);
											for($j = 0; $j < mysql_num_rows($result2); $j++) {
												$row2 = mysql_fetch_array($result2);
												if( !in_array($row2['field_value'], $journals)) {
													$journals[count($journals)] = $row2['field_value'];
													$num[count($journals)] = 1;
												}
												else {
													$index = array_search($row2['field_value'], $journals);
													$num[$index] = $num[$index] + 1;
												}
										
									}}
									$sortArray = array(array());
									for( $x = 0; $x < count($journals); $x++)
									{
											$sortArray[$x][0] = $journals[$x];
											$sortArray[$x][1] = $num[$x];
									}
								$sortArray[0][1] = 1;	
 foreach ($sortArray as $key => $row) {
     		$volume[$key]  = $row[0];
         $edition[$key] = $row[1];
        }
?>


<script type="text/javascript">

       // Load the Visualization API and the piechart package.
       google.load('visualization', '1.0', {'packages':['corechart']});

       // Set a callback to run when the Google Visualization API is loaded.
       google.setOnLoadCallback(drawChart);

       // Callback that creates and populates a data table,
       // instantiates the pie chart, passes in the data and
       // draws it.
        function drawChart() {

         // Create the data table.
         var data = new google.visualization.DataTable();
         data.addColumn('string', 'Journal');
         data.addColumn('number', '# Publications');
         data.addRows([
                 <?php for($z = 0; $z < count($sortArray)-1; $z++) {
                         $journal = $sortArray[$z];
                          echo "[\"".$journal[0]."\", ".$journal[1]."],";
                         }
                         $journal = $sortArray[count($sortArray)-1];
                         echo "[\"".$journal[0]."\", ".$journal[1]."]";
                 ?>
 ]);

         // Set chart options
         var options = {'title':'Top Journals',
                        'width':700,
                        'height':450,
                        'sliceVisibilityThreshold': 1/90};

         // Instantiate and draw our chart, passing in some options.
         var chart = new google.visualization.PieChart(document.getElementById('journal_chart'));
         chart.draw(data, options);
       }
     </script>
 <div id="journal_chart"></div>


<script type='text/javascript'>
      google.load('visualization', '1', {packages:['table']});
      google.setOnLoadCallback(drawTable);
      function drawTable() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Journal Name');
        data.addColumn('number', '# Publications');
        data.addRows([
          <?php
			for($z = 0; $z < count($sortArray)-1; $z++) {
                         	$journal = $sortArray[$z];
                         	echo "[\"".$journal[0]."\", ".$journal[1]."],";
                        }
                        $journal = $sortArray[count($sortArray)-1];
                        echo "[\"".$journal[0]."\", ".$journal[1]."]";
          ?>
        ]);

        var table = new google.visualization.Table(document.getElementById('journals_table'));
        table.draw(data, {showRowNumber: true, sortColumn: 1, sortAscending: false});
      }
    </script>
	<div id='journals_table'></div>

								</div>
								<div class="tab-pane fade" id="Publications">
									<!-- TODO insert js for accordion in head -->

<script type="text/javascript">
$( document ).ready(function() {

$("#expandAllPublications").trigger("click");
});
</script>									

<div class="accordion-expand-holder">
    <button id="expandAllPublications" type="button" class="open">Expand all</button>
    <button type="button" class="close">Collapse all</button>
</div>
									<div id="accordion">
									<?php
										$result = mysql_query( "SELECT collaboratum.publication_information.publication_id FROM collaboratum.publication_information WHERE collaboratum.publication_information.investigator_id = ".$id." GROUP BY collaboratum.publication_information.publication_id") or die(mysql_error());

										for($i = 0; $i < mysql_num_rows($result); $i++)
										{
											$row = mysql_fetch_array($result);
											
											$publicationInfo = getPubInfo($row['publication_id']);
											
											$authorText = "";
											foreach( $publicationInfo['authors'] as $author )
											{
												$authorText .= $author."<br>";
											}
											
											$publication = "<h3>". $publicationInfo['TI'] ."</h3><div>
													<table>
													<td>
													
													
													<tr>
														<b><em>Journal: </em></b>
															".$publicationInfo['JT']."
													</tr>
													<br>";
											if(isset($publicationInfp['DI'])) {
											$publication = $publication."
													<tr>
														<b><em>DOI Link: </em></b>
															<a href=http://dx.doi.org/".$publicationInfo['DI'].">".$publicationInfo['DI']."<i class=\"icon-share-alt\"></i></a>
													</tr>
													<br>";
}
											$publication = $publication."
													<tr>
														<b><em>PMID: </em></b>
															".$publicationInfo['PMID-']."
													</tr>
													<br>
													<tr>
													<b><em>Authors: </em></b>
															".$authorText."
											
													</tr>
													<tr>
														<b><em>Publication Date: </em></b>
															".$publicationInfo['PD']."
													</tr>
													<br>
													<tr>
														<b><em>Publication Year: </em></b>
															".$publicationInfo['PY']."
													</tr>
													<br>
													<tr>
														<b><em>Copyright Information: </em></b>
															".$publicationInfo['CI']."
													</tr>
													
													<br>
													<tr>
													<b><em>Abstract: </em></b>
															".$publicationInfo['AB']."
													</tr>
													<br>
													
													</td>
													</table>
											</div>";
											echo $publication;
										}
										
										function getPubInfo($pubId)
										{
											$info = array();
											$sqlGetSingular = "SELECT collaboratum.publication_information.field_value, collaboratum.publication_information.medline_field FROM collaboratum.publication_information WHERE (collaboratum.publication_information.medline_field = 'AB' OR collaboratum.publication_information.medline_field = 'TI' OR collaboratum.publication_information.medline_field = 'CI' OR collaboratum.publication_information.medline_field = 'PY' OR collaboratum.publication_information.medline_field = 'PD' OR collaboratum.publication_information.medline_field = 'JT' OR collaboratum.publication_information.medline_field = 'PMID-' OR collaboratum.publication_information.medline_field = 'DI') AND collaboratum.publication_information.publication_id = ".$pubId;
											//echo $sqlGetSingular;
											$result = mysql_query( $sqlGetSingular ) or die(mysql_error());
											
											for( $i = 0; $i < mysql_num_rows($result); $i++ )
											{
			
												$row = mysql_fetch_array($result);
												if($row['medline_field'] === 'AB'){
													$info['AB'] = $row['field_value'];
												}
												else if($row['medline_field'] === 'TI'){
													$info['TI'] = $row['field_value'];
												}
												else if($row['medline_field'] === 'CI'){
													$info['CI'] = $row['field_value'];
												}
												else if($row['medline_field'] === 'PY'){
													$info['PY'] = $row['field_value'];
												}
												else if($row['medline_field'] === 'PD'){
													$info['PD'] = $row['field_value'];
												}
												else if($row['medline_field'] === 'JT'){
													$info['JT'] = $row['field_value'];
												}
												else if($row['medline_field'] === 'PMID-'){
													$info['PMID-'] = $row['field_value'];
												}
												else if($row['medline_field'] === 'DI'){
													$info['DI'] = $row['field_value'];
												}
											}
											
											$sqlAuthors = "SELECT collaboratum.publication_information.field_value FROM collaboratum.publication_information WHERE collaboratum.publication_information.medline_field = 'AU' AND collaboratum.publication_information.publication_id = ".$pubId;
											$result = mysql_query ( $sqlAuthors );
											$authors = array();
											for( $i = 0; $i < mysql_num_rows($result); $i++ )
											{
												$row = mysql_fetch_array($result);
												$authors[$i] = $row['field_value'];
											}
											$info['authors'] = $authors;

											return $info;
										}
										
											
										
									?>
									</div>
									
								</div>
								<div class="tab-pane fade" id="ResearchNetwork">
									<h1> Research Network will be displayed here </h1>
								</div>
								<div class="tab-pane fade" id="GrantNetwork">
									<table>
										<thead>
											<tr>
												<th>
													#
												</th>
												<th>
													Grant Name
												</th>
												<th>
													Similarity
												</th>
											</tr>
										</thead>
										<tbody>
											<?php 
											for($j = 0; $j < count($grants); $j++)
											{
												echo "<tr>
													<td>
														".($j+1)."
													</td>
													<td>
														<a href='".$baseURL."/views/grantInfo.php?id=".$grants[$j][1]."'>".$grants[$j][2]."
													</td>
													<td>
														".$grants[$j][0]."
													</td>
												</tr>";
											}
											?>
										</tbody>
									</table>
								</div>
								<div class="tab-pane fade" id="Histogram">
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
															      		<li><a tabindex="-1" href="#" onclick="selectFilter(2);">Researchers Only</a></li>
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
				<p style="text-indent: 3em;">When clicking the filter button, you can elect to search all datasets by selecting "Everything." You can also select to search just for Grants, Researchers, or Classes that are relevant to your query. </p>
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
								<input type="checkbox" id="customFilterCollaborator" value="collaborators" onchange="customFilter(this, 2);"> Researchers
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
        			
        			$("#filterButton").text("Researchers");
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
