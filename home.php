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
if (isset($_GET['categoryid'])){
    $category = $_GET['categoryid'];
    $sql = "SELECT * FROM tblEntries, tblCategories, tblCategoriesEntries ";
    $sql .= "WHERE pkEntryID = fkEntryID ";
    $sql .= "AND pkCategoryID = fkCategoryID ";
    $sql .= "AND fkCategoryID =" . $category . ";";
    $stmt = $db->prepare($sql);
    $stmt ->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $result){
            echo '<div class="entry">';
            echo '<h2>' . $result["fldEntryTitle"] . '</h2>';
            echo '<span><i>' . $result["fldEntryDate"] . '</i></span>';
            $blurb = substr($result["fldEntryText"], 0, 300);
            echo '<p>' . $blurb . '...<a href=entry.php?id="' . $result["pkEntryID"] .'">Continue</a></p>';
            echo '</div>';
    }
}else{
    $sql = "SELECT * FROM tblEntries ORDER BY pkEntryID DESC;";
    $stmt = $db->prepare($sql);
    $stmt ->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $result){
            echo '<div class="entry">';
            echo '<h2>' . $result["fldEntryTitle"] . '</h2>';
            echo '<span><i>' . $result["fldEntryDate"] . '</i></span>';
            $blurb = substr($result["fldEntryText"], 0, 300);
            echo '<p>' . $blurb . '...<a href=entry.php?id="' . $result["pkEntryID"] .'">Continue</a></p>';
            echo '</div>';
    }
}



?>

</div>
</body>
</html>
