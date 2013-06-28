

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
       
        
        <script type="text/javascript"> <!-- start of Cytoscape Graph Data -->
            window.onload = function() {
                // id of Cytoscape Web container div
                var div_id = "cytoscapeweb";

                var network_json = {
                        // NOTE the parent attribute
                        data: {
                            nodes: [ {id: "Bayer"},
{id: "Beck"},
{id: "Biggers"},
{id: "Chung"},
{id: "Cole"},
{id: "Coons"},
{id: "Ferkin"},
{id: "Freeman"},
{id: "Gartner"},
{id: "Goodwin"},
{id: "Homayouni"},
{id: "Kennedy"},
{id: "Lessman"},
{id: "Liu"},
{id: "Lopez-Estrano"},
{id: "McKenna"},
{id: "Nakazato"},
{id: "Ourth"},
{id: "Parris"},
{id: "Pezeshki"},
{id: "Schoech"},
{id: "Schwartzback"},
{id: "Simco"},
{id: "Skalli"},
{id: "Stevens"},
{id: "Sutter"},
{id: "Taller"},
{id: "Wong"},
                            ],
                            edges: [ 
{ id: "1", target: "Kennedy", source: "Bayer" },
{ id: "2", target: "McKenna", source: "Bayer" },
{ id: "3", target: "Nakazato", source: "Bayer" },
{ id: "4", target: "Kennedy", source: "Beck" },
{ id: "5", target: "Skalli", source: "Chung" },
{ id: "6", target: "Sutter", source: "Chung" },
{ id: "7", target: "Coons", source: "Cole" },
{ id: "8", target: "Gartner", source: "Cole" },
{ id: "9", target: "Schwartzback", source: "Cole" },
{ id: "10", target: "Skalli", source: "Cole" },
{ id: "11", target: "Gartner", source: "Coons" },
{ id: "12", target: "Freeman", source: "Ferkin" },
{ id: "13", target: "Kennedy", source: "Ferkin" },
{ id: "14", target: "Parris", source: "Ferkin" },
{ id: "15", target: "Schoech", source: "Ferkin" },
{ id: "16", target: "Schoech", source: "Freeman" },
{ id: "17", target: "Schwartzback", source: "Gartner" },
{ id: "18", target: "Homayouni", source: "Goodwin" },
{ id: "19", target: "Skalli", source: "Goodwin" },
{ id: "20", target: "Schwartzback", source: "Homayouni" },
{ id: "21", target: "Skalli", source: "Homayouni" },
{ id: "22", target: "Schoech", source: "Kennedy" },
{ id: "23", target: "Nakazato", source: "McKenna" },
{ id: "24", target: "Wong", source: "Ourth" },
{ id: "25", target: "Schoech", source: "Parris" },
{ id: "26", target: "Wong", source: "Schoech" },
{ id: "27", target: "Skalli", source: "Schwartzback" }
                            ]
                        }
                };

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
                    swfPath: "/Collaboratum/res/cytoscape/swf/CytoscapeWeb",
                    flashInstallerPath: "/Collaboratum/res/cytoscape/swf/playerProductInstall"
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
        <script src="/Collaboratum/res/js/flash_detect.js" type="text/javascript" charset="utf-8"></script>
    </head>
    <body>
        <div style="" id="page-container">
            
            <div id="container1">
				<center>
					<h1> 
						Biology Department Network View
					</h1>
				
					<div id="cytoscapeweb">
						Cytoscape Web will replace the contents of this div with your graph.
					</div>
				</center>
			</div>
        </div>
    </body>
</html>
