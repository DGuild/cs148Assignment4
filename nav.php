<aside>

	<p>Welcome to Burlington Adventures. This is a personal journal of things
 	seen, heard, tasted, and otherswise experienced while living in the Queen City: Burlington,VT</p>

	<span>Would you like to receive blog updates via email?
	<a href="http://www.uvm.edu/~dguild/cs148/assignment4.1/register.php">Register.</a>
	</span>
	
	<nav>
	<h4>Post Categories:</h4>
		<ol>
			<?php
			include('get-all-categories.php');
			
			foreach ($categories as $category){
				echo '<li><a href="?categoryid=' . $category['pkCategoryID'] . '">' . $category['fldCategoryName'] . '</a></li>';
			}
			?>
		</ol>
	</nav>

</aside>