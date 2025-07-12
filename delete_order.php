			<?php 
			$total = 0;
			include 'db_connect.php';
			echo $_GET['id'];
			$qry = $conn->query("Delete FROM orders where id =".$_GET['id']);
			echo $qry;
			if($qry==TRUE)
			{
				header("Location: index.php?page=orders");
 				exit;
			}
			