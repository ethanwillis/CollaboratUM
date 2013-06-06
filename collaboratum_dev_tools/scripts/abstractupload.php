<nav>
	<p>
		<a href="./index.html">Home</a>
	</p>
</nav>

<?php
	//Upload Archive
	$pathName = "";
	$allowedExts = array("zip");
	$extension = end(explode(".", $_FILES["file"]["name"]));

		if ($_FILES["file"]["error"] > 0)
	    {
	    echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
	    }
	  	else
	    {
	    	echo "<b>Upload:</b> " . $_FILES["file"]["name"] . "<br>";
	    	echo "<b>Type:</b> " . $_FILES["file"]["type"] . "<br>";
	    	echo "<b>Size:</b> " . ($_FILES["file"]["size"] / 1024) . " kB<br>"; 
	
	    	if (file_exists("upload/" . $_FILES["file"]["name"]))
	      	{
	      		echo $_FILES["file"]["name"] . " already exists. ";
	      	}
	   		else
	      	{
	      		move_uploaded_file($_FILES["file"]["tmp_name"],
	      		"../abstracts/uploads/" . $_FILES["file"]["name"]);
	      		$pathname = "../abstracts/uploads/" . $_FILES["file"]["name"];
				echo "<b>Stored in:</b> " . $pathname . "<br>";
	      	}
	    }
	
	
	//Unzip Archive
	$savename = str_replace(' ', '', microtime());
	$savelocation = realpath( '../abstracts/uploads/' ) . '\\' .$savename;
	 
	$zip = new ZipArchive;
	$res = $zip->open($pathname);
	if ($res === TRUE) {
	  $zip->extractTo( $savelocation );
	  $zip->close();
	  echo '<i>Decompressing... done</i>'."<br>";
	} else {
	  echo 'There was an error decompressing the archive.'."<br>";
	}
	
	//Delete the uploaded archive
	unlink($pathname);
	
	//Get the path to the AbstractFilterer Jar
	$AbstractFiltererPath = dirname(__FILE__). '\AbstractFilterer.jar';
	echo "Filtering using: " . $AbstractFiltererPath . "<br>";
	
	//Filter the uploaded abstract files.
	exec("\"" . $AbstractFiltererPath . "\" \"" . $savelocation . "\" \"" . $savename . "\" " ." TI AB");
	
	echo $AbstractFiltererPath . " " . $savelocation . " " . $savename . " " ." TI AB";
	//Compress the filtered abstracts
	$path = realpath( '../abstracts/downloads/'). '\\' . $savename;
	$zip = new ZipArchive;
	fclose(fopen($path.'.zip', 'x+'));
	$zip->open($path.'.zip', ZipArchive::CREATE);
	if (false !== ($dir = opendir($path)))
	{
	    while (false !== ($file = readdir($dir)))
	    {
	        if ($file != '.' && $file != '..')
	        {
	        	$zip->addFile($path.DIRECTORY_SEPARATOR.$file, $savename.DIRECTORY_SEPARATOR.$file); 
	        }
	    }
	}
	else
	{
    	die('Can\'t read dir');
	}
	$zip->close();
	
	// delete the uncompressed directory
	foreach (glob($path."/*") as $file) {
    	//unlink($file);
	}
	rmdir($path);
?>

<br>
<br>
<a href="<?php echo '../abstracts/downloads/'.$savename.'.zip'; ?>"> Download Filtered Abstracts </a>
