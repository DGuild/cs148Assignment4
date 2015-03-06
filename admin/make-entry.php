<?php

$debug=false;
include('../connect.php');
include('../get-all-categories.php');
include('../top.php');
include('../header.php');

if($debug){
$entryTitle="Entry Title";
}else{
$entryTitle="";
}

if(isset($_POST["btnSubmit"])){

//checks to see if form is submitted from the correct web address for security

	$ref = getenv("http_referer");
	$url = "https://www.uvm.edu/~dguild/cs148/assignment4.1/admin/make-entry.php";
	if($debug){
		echo $ref . " should match " . $url;
	}
	if($ref !== $url){
		die("Submission from unknown location. Security breach detected");
	}

//checks to see that fields have been filled out properly

	$errors = false;

	if(empty($_POST["entryTitle"])){
		echo "<br> Oops, you forgot a title!";
		$errors = true;
	}

	if(empty($_POST["entryText"])){
		echo "<br> Oops, you forgot to write anything!";
		$errors = true;
	}
	
	if($errors == false){
		if($debug) echo "<br>Form is valid";

/////////Submits data to database

	$entryTitle = $_POST["entryTitle"];
	$entryText = $_POST["entryText"];
	$entryDate = date('F j, Y');

	try { 
            $db->beginTransaction(); 
            
            $sql = 'INSERT INTO tblEntries SET fldEntryTitle="' . $entryTitle . '", fldEntryDate="' . $entryDate . '", fldEntryText="' . $entryText . '";';
	     
            $stmt = $db->prepare($sql); 
            if ($debug) print "<p>sql ". $sql; 
        
            $stmt->execute(); 
             
            $entryID = $db->lastInsertId();
            if (isset($_POST["chkEvents"])){
	     $sqlEvents = 'INSERT INTO tblCategoriesEntries SET fkCategoryID=' . $_POST["chkEvents"] . ', fkEntryID=' . $entryID . ';';
	     $stmtEvents = $db->prepare($sqlEvents);
	     $stmtEvents->execute();
            }
            if (isset($_POST["chkFood"])){
	     $sqlFood = 'INSERT INTO tblCategoriesEntries SET fkCategoryID=' . $_POST["chkFood"] . ', fkEntryID=' . $entryID . ';';
	     $stmtFood = $db->prepare($sqlFood);
	     $stmtFood->execute();
            }
            if (isset($_POST["chkMusic"])){
	     $sqlMusic = 'INSERT INTO tblCategoriesEntries SET fkCategoryID=' . $_POST["chkMusic"] . ', fkEntryID=' . $entryID . ';';
	     $stmtMusic = $db->prepare($sqlMusic);
	     $stmtMusic->execute();
            }
            if (isset($_POST["chkOutdoors"])){
	     $sqlOutdoors = 'INSERT INTO tblCategoriesEntries SET fkCategoryID=' . $_POST["chkOutdoors"] . ', fkEntryID=' . $entryID . ';';
	     $stmtOutdoors = $db->prepare($sqlOutdoors);
	     $stmtOutdoors->execute();
            }
            if ($debug) print "<p>pk= " . $primaryKey; 

            // all sql statements are done so lets commit to our changes 
            $dataEntered = $db->commit(); 
            if ($debug) print "<p>transaction complete "; 
        } catch (PDOExecption $e) { 
            $db->rollback(); 
            if ($debug) print "Error!: " . $e->getMessage() . "</br>"; 
            $errorMsg[] = "There was a problem with accepting the entry"; 
        } 
	}//no errors



}
?>

<form action="<? print $_SERVER['PHP_SELF']; ?>"
	method="post"
	id="frmEntry">
	<fieldset id="fsEntry">
		<label for="entryTitle">Title: </label>
		<input type="text" id="entryTitle" name="entryTitle" value=<?php echo "$entryTitle"; ?> />
		<br>
		<label for="category">Categories: </label>
		<?php
			foreach ($categories as $category) {
				echo '<input type="checkbox" id=';
                                echo '"chk' . $category["fldCategoryName"] . '" ';
                                echo 'name="chk' . $category["fldCategoryName"] . '"';
                                echo 'value="' . $category["pkCategoryID"] . '">' . $category["fldCategoryName"];
			}
		?>
		<br>
		<label for="entryText">Entry: </label><br>
		<textarea id="entryText" name="entryText" rows="10" cols="40" placeholder="What's good in Burlington?"></textarea>
	</fieldset>
	<fieldset id="fsButtons">
		<input type="submit" id="btnSubmit" name="btnSubmit" />
	</fieldset>
</form>