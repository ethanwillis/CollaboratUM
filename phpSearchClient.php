<?php
	//import config file
	include_once(__DIR__."/config.php");
	
	 // get query
    $query = "cancer";
    echo "Query String: ".$query;
	
    $queryResult = querySearchService($lsiQueryHost, $lsiQueryPort, $query, 0);
    $queryResult = explode("\r\n", $queryResult);

    echo "<div id='lsi' style='float: left; border-width: 1px;'>numResults: ".count($queryResult)."<br>";

    for($i = 0; $i < count($queryResult) - 1; $i++)
    {
    	$entry = explode(" ", $queryResult[$i]);

    	mysql_connect($dbHost, $dbUser, $dbPass) or die(mysql_error());
		mysql_select_db($dbNameGeneral) or die(mysql_error());

		// Retrieve all the data from the "example" table
		$result = mysql_query("SELECT `investigator`.name FROM investigator WHERE `investigator`.investigator_id = ".$entry[0]."")
		or die(mysql_error());  

		// store the record of the "example" table into $row
		$row = mysql_fetch_array( $result );
		// Print out the contents of the entry 
		$entry[0] = $row['name'];
		echo "Result ".$i.": ".$queryResult[$i]."<br>";
		$queryResult[$i] = implode(" ", $entry);
    }
    echo "</div>";

    $queryResult = querySearchService($keywdQueryHost, $keywdQueryPort, $query, 0);
    $queryResult = explode("\r\n", $queryResult);
    echo "<div id='keywd' style='float: left; border-width: 1px;'>numResults: ".count($queryResult)."<br>";
    for($i = 0; $i < count($queryResult); $i++)
    {
    	echo "Result ".$i.": ".$queryResult[$i]."<br>";
    }
    echo "</div>";

    function querySearchService($hostname, $port, $query, $type)
    {
        $sock = socket_create(AF_INET, SOCK_STREAM, 0);
        $message = $type." | ".$query;
        $buf = "";
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
                
        //Now receive reply from server
        if(socket_recv ( $sock , $buf , 2045 , MSG_WAITALL ) === FALSE)
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            die("Could not receive data: [$errorcode] $errormsg \n");
        }

        //return the search results
        return $buf;

        
    }
    
    function destroyConnection($connection)
    {
        fclose($connection);
    }
    
    function getConnection($hostname, $port)
    {
        $addr = gethostbyname($hostname);
        $client = stream_socket_client("tcp://".$addr.":".$port, $errno, $errorMessage);

        if ($client === false) 
        {
            throw new UnexpectedValueException("Failed to connect: $errorMessage");
        }
        else
        {
            return $client;
        }
        
    }
    
    function sendMessage($client, $message)
    {
        $result = fwrite($client, $message);
        
        // if the message was not successfully sent return false; true otherwise.
        if($result === false)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    
    function readResponse($client)
    {
        return stream_get_contents($client, 100000, 0);
    }
	
?>
