<?php

$debug=false;

include("/usr/local/uvm-inc/dguild.inc");
include ('top.php');
include ('connect.php');
include ('header.php');
include ('nav.php');

?>
<div id="content">

<?php

$entry = $_GET["id"];


$sql = "SELECT * FROM tblEntries WHERE pkEntryID = " . $entry . ";";
$stmt = $db->prepare($sql);
$stmt ->execute();

$results = $stmt->fetch(PDO::FETCH_ASSOC);

	echo '<div class="entry">';
	echo '<h2>' . $results["fldEntryTitle"] . '</h2>';
	echo '<span><i>' . $results["fldEntryDate"] . '</i></span>';
	echo '<p>' . $results["fldEntryText"] . '</p>';
	echo '</div>';


?>
<hr class='clear'>
</div>
</body>
</html>