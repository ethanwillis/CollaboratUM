
<?php
        //import config file
        //include_once("../config.php");
        $dbUser = "root";
$dbPass = "baseg";
$dbNameGeneral = "collaboratum";
$dbNameNetwork = "parsingdata";
 $baseURL = "http://projects.codemelody.com/Collaboratum";

$lsiQueryHost = "localhost";
$lsiQueryPort = "50005";

$keywdQueryHost = "localhost";
$keywdQueryPort = "50004";

?>
 <?php
               
        			// Get the indices of the ids to use
                   $startId = $_GET['startId'];
				   $endId = $_GET['endId'];
				   $title = $_GET['title'];
                            
        ?>
       
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Collaboratum Home</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="../res/bootstrap/css/bootstrap.css" rel="stylesheet" media="screen">
		<link href="../res/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<link href='http://fonts.googleapis.com/css?family=Oxygen' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="../res/css/cytoscape_web.css"/>
        <link rel="stylesheet" href="../res/css/index.css">
        <script src="/Collaboratum/res/js/jquery-1.8.2.js" type="text/javascript" charset="utf-8"></script>
        <script type="text/javascript" src="/Collaboratum/res/cytoscape/js/min/json2.min.js"></script>
        <script type="text/javascript" src="/Collaboratum/res/cytoscape/js/min/AC_OETags.min.js"></script>
        <script type="text/javascript" src="/Collaboratum/res/cytoscape/js/min/cytoscapeweb.min.js"></script>

		
		<script type="text/javascript"> <!-- start of Cytoscape Graph Data -->
            window.onload = function() {
                // id of Cytoscape Web container div
                var div_id = "cytoscapeweb";

                var network_json;
				 $.ajax({
					type: 'GET',
					url: "http://projects.codemelody.com/Collaboratum/res/scripts/getSubnetGraph.php",
					data: {<?php echo "startId: \"".$startId."\", endId: \"".$endId."\", title: \"".$title."\""; ?>}, 
					dataType: "json",
					async: false,
					success: function(data) {
						console.log(data);
						network_json = data;
					},
					error: function() {
						alert("Sorry, There was an error loading the graph");
					}
				});

                // NOTE the "compound" prefix in some visual properties
                var visual_style = {
                    nodes: {
                        shape: "ELLIPSE",
                        label: { passthroughMapper: { attrName: "id" } } ,
                        compoundLabel: { passthroughMapper: { attrName: "id" } } ,
                        borderWidth: 2,
                        compoundBorderWidth: 1,
                        borderColor: "#83959d",
                        compoundBorderColor: "#999999",
                        size: 40,
                        color: "#ffffff", // #ff6666
                        compoundColor: "#eaeaea"
                    }
                };

                // initialization options
                var options = {
                    swfPath: "http://projects.codemelody.com/Collaboratum/res/cytoscape/swf/CytoscapeWeb",
                    flashInstallerPath: "http://projects.codemelody.com/Collaboratum/res/cytoscape/swf/playerProductInstall"
                };

                var vis = new org.cytoscapeweb.Visualization(div_id, options);

                vis.ready(function() {
                    // set the style programmatically
                    document.getElementById("layout").onclick = function(){
                        vis.layout("CompoundSpringEmbedder");
                    };
                });

                var draw_options = {
                    // your data goes here
                    network: network_json,
                    // this is the best layout to use when the network has compound nodes 
                    layout: "CompoundSpringEmbedder",
                    // set the style at initialisation
                    visualStyle: visual_style,
                    // hide pan zoom
                    panZoomControlVisible: true 
                };

                vis.draw(draw_options);
            };
         </script> <!-- End of Cytoscape graph data -->
	</head>
	<body>
		
		<!-- Begin Body Scaffolding -->
		<div class="row-fluid">
			<div class="span12">
				<!-- Begin Nav -->
				<div class="navbar navbar-static-top">
					<div class="navbar-inner">
						<a class="brand" href="#">Collaboratum</a>
						<ul class="nav">
							<li class="">
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
							    		<a href="<?php echo $baseURL; ?>/views/subnet.php?startId=1&endId=28&title=Biology">
							    			Biology 
							    		</a>
							    	</li>
							    	<li>
							    		<a href="<?php echo $baseURL; ?>/views/subnet.php?startId=38&endId=57&title=Chemistry">
							    			Chemistry
							    		</a>
							    	</li>
							    	<li>
							    		<a href="<?php echo $baseURL; ?>/views/subnet.php?startId=29&endId=38&title=Biomedical%20Engineering">
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
  			<div class="span12 alpha">
  				<div class="span10 well center vspace-normal">
						<center>
								<?php
									if(isset($title))
									{
										echo "<h1>".$title." Network View </h1>";
									}
								?>
							<div id="cytoscapeweb">
								Cytoscape Web will replace the contents of this div with your graph.
							</div>
						</center>
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
			    	</a>
				</li>
			</ul>
			<div class="modal-body">
                <p class="lead">
					Collaboratum provides principal investigators with the tools they need to find suitable collaborators and funding relevant to their
					research. 
				</p>
				<p>
					<em>
						We accomplish this through clever application of new and traditional information retrieval methods: A basic keyword search 
						and Conceptual search via LSI. 
						Typical keyword searches are provided to give you "directly associated" results relative to your queries. 
						However, through conceptual search we provide you with the ability to find previously invisible implied associations.
					</em>
				</p>
				
				<h3>Who's Behind This?</h3>
					<div class="media">
						<a class="pull-left" href="#">
							<img class="media-object" data-src="res/images/um.png">
						</a>
						<div class="media-body">
							<h4 class="media-heading">University of Memphis</h4>
						</div>
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
		<script src="http://code.jquery.com/jquery.js"></script>
		<script src="../res/bootstrap/js/bootstrap.min.js"></script>   
		<script src="../res/js/jquery-1.8.2.js" type="text/javascript" charset="utf-8"></script>
		<script src="../res/js/flash_detect.js" type="text/javascript" charset="utf-8"></script>
		<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
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
        
	</body>
</html>
