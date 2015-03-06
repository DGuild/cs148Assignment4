<?php			
$sql = "SELECT * FROM tblCategories ORDER BY fldCategoryName ASC;";
			$stmt = $db->prepare($sql);
			$stmt ->execute();

			$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>