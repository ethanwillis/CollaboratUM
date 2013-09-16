<?php
	//import config file
 $dbHost = "127.0.0.1";
$dbUser = "root";
$dbPass = "baseg;"
$dbNameGeneral = "collaboratum";
$dbNameNetwork = "parsingdata";
$baseURL = "http://projects.codemelody.com/Collaboratum";
$lsiQueryHost = "localhost";
$lsiQueryPort = "50005";
$keywdQueryHost = "localhost";
$keywdQueryPort = "50004";
	// get the ID for this grant
	$id = $_GET['id'];
	
	// Connect to database
	$con = mysql_connect($dbHost, $dbUser, $dbPass) or die(mysql_error());
	mysql_select_db($dbNameGeneral, $con) or die(mysql_error());
	
	// Get the agency, guidelink, purpose, section1, and title
	$resolvedID = mysql_query("SELECT first_name, title, last_name FROM investigator WHERE investigator_id = ".$id."")
	        or die(mysql_error());  
			
	// Then we take the results from the database and store them in a row
	$row = mysql_fetch_array( $resolvedID);
	
	$classNum = $row['first_name'];
	$classTitle = $row['title'];
	$classDesc = $row['last_name'];
	
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
  							
  								<blockquote class="pull-left">
  <h3 class="text-center"><?php echo $classNum; ?></h3>
  <small><?php echo $classTitle ?></small>
</blockquote>
  							
  						</div>
  						<div class="span12 well text-left">
  							
							<h5>Descrpiption: <?php echo $classDesc;?></h5>
							<hr>
							
  						</div>
  					</div>
  				</div>
  			</div>
  			<div class="row-fluid">
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
				<ul class="thumbnails">
  				<li class="span4 center vspace-small">
			    	<a href="#" class="thumbnail">
			    	<img data-src="holder.js/360x270" alt="360x270" style="width: 360px; height: 270px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAWgAAAEOCAYAAACkSI2SAAANjklEQVR4Xu3cO29TSxuG4RUhTgU1iA7RQo3E36eiQXSIGtFGokAgcdhbjuRoMlonO4/j1+ai+yB5M+ua2XfWt+L44vLy8r/BHwIECBAoJ3Ah0OX2xIIIECBwJSDQDgIBAgSKCgh00Y2xLAIECAi0M0CAAIGiAgJddGMsiwABAgLtDBAgQKCogEAX3RjLIkCAgEA7AwQIECgqINBFN8ayCBAgINDOAAECBIoKCHTRjbEsAgQICLQzQIAAgaICAl10YyyLAAECAu0MECBAoKiAQBfdGMsiQICAQDsDBAgQKCog0EU3xrIIECAg0M4AAQIEigoIdNGNsSwCBAgItDNAgACBogICXXRjLIsAAQIC7QwQIECgqIBAF90YyyJAgIBAOwMECBAoKiDQRTfGsggQICDQzgABAgSKCgh00Y2xLAIECAi0M0CAAIGiAgJddGMsiwABAgLtDBAgQKCogEAX3RjLIkCAgEA7AwQIECgqINBFN8ayCBAgINDOAAECBIoKCHTRjbEsAgQICLQzQIAAgaICAl10YyyLAAECAu0MECBAoKiAQBfdGMsiQICAQDsDBAgQKCog0EU3xrIIECAg0M4AAQIEigoIdNGNsSwCBAgItDNAgACBogICXXRjLIsAAQIC7QwQIECgqIBAF90YyyJAgIBAOwMECBAoKiDQRTfGsggQICDQzgABAgSKCgh00Y2xLAIECAi0M0CAAIGiAgJddGMsiwABAgLtDBAgQKCogEAX3RjLIkCAgEA7AwQIECgqINBFN8ayCBAgINDOAAECBIoKCHTRjbEsAgQICLQzQIAAgaICAl10YyyLAAECAu0MECBAoKiAQBfdGMsiQICAQDsDBAgQKCog0EU3xrIIECAg0M4AAQIEigoIdNGNsSwCBAgItDNAgACBogICXXRjLIsAAQIC7QwQIECgqIBAF90YyyJAgIBAOwMECBAoKiDQRTfGsggQICDQzgABAgSKCgh00Y2xLAIECAi0M0CAAIGiAgJddGMsiwABAgLtDBAgQKCogEAX3RjLIkCAgEA7AwQIECgqINBFN8ayCBAgINDOAAECBIoKCHTRjbEsAgQICLQzQIAAgaICAl10YyyLAAECAu0MECBAoKiAQBfdGMsiQICAQDsDBAgQKCog0EU3xrIIECAg0M4AAQIEigoIdNGNsSwCBAgItDNAgACBogICXXRjLIsAAQIC7QwQIECgqIBAF90YyyJAgIBAOwMECBAoKiDQRTfGsggQICDQzgABAgSKCgh00Y2xLAIECAi0M0CAAIGiAgJddGMsiwABAgLtDBAgQKCogEAX3RjLIkCAgEA7AwQIECgqINBFN8ayCBAgINDOAAECBIoKCHTRjbEsAgQICLQzQIAAgaICAl10YyyLAAECAu0MECBAoKiAQBfdGMsiQICAQDsDBAgQKCog0EU3xrIIECAg0M4AAQIEigoIdNGNsSwCBAgItDNAgACBogICXXRjLIsAAQIC7QwQIECgqIBAF90YyyJAgIBAOwMECBAoKiDQRTfGsggQICDQzgABAgSKCgh00Y2xLAIECAi0M0CAAIGiAgJddGMsiwABAgLtDKwS+P379/Du3bvhz58/1x//5MmT4c2bN7Of/+nTp+Hr16/XH3NxcTG8fft2ePz48ejn/f37d3j//v3w/fv3639/9OjR1de5f//+qrUufdDl5eXw8ePHYfO12j+vXr0anj17dv1XY9c8NXvsuu7iWpau1b+ftoBAn/b+3cnqv3z5Mnz+/Hn0a00FdyxO7YAXL14ML1++vDFzKpybD1oK+1qIDx8+DJuvM/Wn/aZzm0DfxbWsvWYfd7oCAn26e3cnK18TqbE76aUQ9nfFS0HfXOxt76TnvtGMffNYc+3bz3v69Onw+vXrq/95F9dyJ5vvixxdQKCPvgW1F9BGrb2LbR9d9He3/d3j9m556u83An08t48b+s/pH0Os1euj2ca+D/GabwTtuvpvUIe+lrXX7ONOX0CgT38PD3oF7Z1w+1iij1obzjbefezaee1d59Tfby5u7t/WXny/3v4RSxvVpUC3sR979HLoa1l7zT7u9AUE+vT38ChX0N5BtpHq71TbCE8ttP+cPp5t8Ld3q/2ddft19rnr3iXQ7Xr669vnWo6ygb7oSQgI9ElsU61FzkV4LFDfvn278YO5pVdL9P8+Fc+xxywPHz688SqQNd8g+rv0uVentHfiY3fPc//Pon+Us3SnXmvXreYYAgJ9DPUT/pr9D//6AK79wVr7eftGbeybwYMHD65fcbI2gFPPjMe2ae7uefPx+17LCR8JSz+ggEAfEPfcRk+9OqG9410b6I3N9vNuE7U2rpu7581d7c+fP6/o1/xAsY/zbe6eBfrcTvzxr0egj78HJ7mCqaiOBXoqxNsY3ibQ/eOJLeaaRxt9nJfuuNuPnwr5ba/lJA+DRR9MQKAPRnv+g8eC1Qdq7iVo2yBu7nrb31Jc+wx6K7zPy+T633Bc+q3IpR/+Ta1l12s5/1PjCncREOhdtHzsDYGxH97du3dv9od0az5nzas42oWM/QLK2G8qbj+nj/Pcx46Fd+63GpdCPvaKFMeKwJSAQDsbkwJLrx2eer1z+4PENXfQm/fY2Pe1w1PPvKciussPBKe+CSw9Ctn3WhxFAr2AQDsTkwL93WAbvf61xm2I+whunwf38+Y+Z+1vEvYx/PXr1/VL+vpvDvu8PnrsrnvpcYjfJPQfVUpAoFOSZzpn7ftXtM9a17wXxYZr11d/9HeuY7+G/uPHjxvvVNc+vlh6f5DtFo7dIe/y24xrXsmydBd+psfJZe0oINA7gv2LH94/s+0Nxp7hLkXqtu9mN/est43p9q6/D/fcPi69kdOaZ9beze5f/C8lf80CnTc9y4ljwVlzF9jftS69beja91Cee7+Psccvz58/n3zL1H7DEoHezFx7LWd5YFxURECgI4yGECBAIC8g0HlTEwkQIBAREOgIoyEECBDICwh03tREAgQIRAQEOsJoCAECBPICAp03NZEAAQIRAYGOMBpCgACBvIBA501NJECAQERAoCOMhhAgQCAvINB5UxMJECAQERDoCKMhBAgQyAsIdN7URAIECEQEBDrCaAgBAgTyAgKdNzWRAAECEQGBjjAaQoAAgbyAQOdNTSRAgEBEQKAjjIYQIEAgLyDQeVMTCRAgEBEQ6AijIQQIEMgLCHTe1EQCBAhEBAQ6wmgIAQIE8gICnTc1kQABAhEBgY4wGkKAAIG8gEDnTU0kQIBARECgI4yGECBAIC8g0HlTEwkQIBAREOgIoyEECBDICwh03tREAgQIRAQEOsJoCAECBPICAp03NZEAAQIRAYGOMBpCgACBvIBA501NJECAQERAoCOMhhAgQCAvINB5UxMJECAQERDoCKMhBAgQyAsIdN7URAIECEQEBDrCaAgBAgTyAgKdNzWRAAECEQGBjjAaQoAAgbyAQOdNTSRAgEBEQKAjjIYQIEAgLyDQeVMTCRAgEBEQ6AijIQQIEMgLCHTe1EQCBAhEBAQ6wmgIAQIE8gICnTc1kQABAhEBgY4wGkKAAIG8gEDnTU0kQIBARECgI4yGECBAIC8g0HlTEwkQIBAREOgIoyEECBDICwh03tREAgQIRAQEOsJoCAECBPICAp03NZEAAQIRAYGOMBpCgACBvIBA501NJECAQERAoCOMhhAgQCAvINB5UxMJECAQERDoCKMhBAgQyAsIdN7URAIECEQEBDrCaAgBAgTyAgKdNzWRAAECEQGBjjAaQoAAgbyAQOdNTSRAgEBEQKAjjIYQIEAgLyDQeVMTCRAgEBEQ6AijIQQIEMgLCHTe1EQCBAhEBAQ6wmgIAQIE8gICnTc1kQABAhEBgY4wGkKAAIG8gEDnTU0kQIBARECgI4yGECBAIC8g0HlTEwkQIBAREOgIoyEECBDICwh03tREAgQIRAQEOsJoCAECBPICAp03NZEAAQIRAYGOMBpCgACBvIBA501NJECAQERAoCOMhhAgQCAvINB5UxMJECAQERDoCKMhBAgQyAsIdN7URAIECEQEBDrCaAgBAgTyAgKdNzWRAAECEQGBjjAaQoAAgbyAQOdNTSRAgEBEQKAjjIYQIEAgLyDQeVMTCRAgEBEQ6AijIQQIEMgLCHTe1EQCBAhEBAQ6wmgIAQIE8gICnTc1kQABAhEBgY4wGkKAAIG8gEDnTU0kQIBARECgI4yGECBAIC8g0HlTEwkQIBAREOgIoyEECBDICwh03tREAgQIRAQEOsJoCAECBPICAp03NZEAAQIRAYGOMBpCgACBvIBA501NJECAQERAoCOMhhAgQCAvINB5UxMJECAQERDoCKMhBAgQyAsIdN7URAIECEQEBDrCaAgBAgTyAgKdNzWRAAECEQGBjjAaQoAAgbyAQOdNTSRAgEBEQKAjjIYQIEAgLyDQeVMTCRAgEBEQ6AijIQQIEMgLCHTe1EQCBAhEBAQ6wmgIAQIE8gICnTc1kQABAhEBgY4wGkKAAIG8gEDnTU0kQIBARECgI4yGECBAIC8g0HlTEwkQIBAREOgIoyEECBDICwh03tREAgQIRAQEOsJoCAECBPICAp03NZEAAQIRAYGOMBpCgACBvMD/2YGRhfgtMpUAAAAASUVORK5CYII=">
			    	</a>
				</li>
			</ul>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
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
	</body>
</html>
