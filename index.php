<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Collaboratum Home</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="res/bootstrap/css/bootstrap.css" rel="stylesheet" media="screen">
		<link href="res/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<link rel="stylesheet" href="res/css/index.css">
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
							<li class="active">
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
  			<div class="span12 alpha">
  				<div class="span8 center vspace-normal">
  					<div class="span10 center text-center">
  						<img src="/Collaboratum/res/images/collaboratum_logo_dark.png" alt="CollaboratUM">
  					</div>
  					<div class="span10 center text-center well vspace-small alpha">
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
		<script src="res/bootstrap/js/bootstrap.min.js"></script>   
		<script src="res/js/jquery-1.8.2.js" type="text/javascript" charset="utf-8"></script>
		<script src="res/js/flash_detect.js" type="text/javascript" charset="utf-8"></script>
		<script src="res/js/jquery.infieldlabel.min.js" type="text/javascript"></script>
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
				$("filterButton").val("Everything");			
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
