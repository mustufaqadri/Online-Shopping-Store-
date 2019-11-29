<?php
	include 'includes/session.php';

	if(isset($_SESSION['user'])){
		$conn = $pdo->open();

		$stmt = $conn->prepare("SELECT * FROM cart LEFT JOIN products on products.id=cart.product_id WHERE user_id=:user_id");
		$stmt->execute(['user_id'=>$user['id']]);

		$total = 0;
		foreach($stmt as $row){
			$subtotal = $row['price'] * $row['quantity'];
			$total += $subtotal;
		}
		$pdo->close();
		$myfile = fopen("Authorize/newfile.txt", "w") or die("Unable to open file!");
		$txt = $total;
		fwrite($myfile, $txt);
		fclose($myfile);
		echo json_encode($total);
	}
?>