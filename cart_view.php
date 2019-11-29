<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<?php
	$connection = mysqli_connect('localhost','root','','ecomm');
	if($connection)
	{
		if(isset($_POST['Insert']))
		{
			// $UserID=$_SESSION['user'];
			// $Date = date('Y-m-d');
			// $PayID="4111111111111111";
			// $insert = "INSERT INTO `sales` (`user_id`, `pay_id`, `sales_date`) VALUES ($UserID,'$PayID','$Date')";
			// $result = mysqli_query($connection,$insert);

			$payid = "123";
			$date = date('Y-m-d');
	
			$conn = $pdo->open();
	
			try{
	
				$stmt = $conn->prepare("INSERT INTO sales (user_id, pay_id, sales_date) VALUES (:user_id, :pay_id, :sales_date)");
				$stmt->execute(['user_id'=>$user['id'], 'pay_id'=>$payid, 'sales_date'=>$date]);
				$salesid = $conn->lastInsertId();
				
				try{
					$stmt = $conn->prepare("SELECT * FROM cart LEFT JOIN products ON products.id=cart.product_id WHERE user_id=:user_id");
					$stmt->execute(['user_id'=>$user['id']]);
	
					foreach($stmt as $row){
						$stmt = $conn->prepare("INSERT INTO details (sales_id, product_id, quantity) VALUES (:sales_id, :product_id, :quantity)");
						$stmt->execute(['sales_id'=>$salesid, 'product_id'=>$row['product_id'], 'quantity'=>$row['quantity']]);
					}
	
					$stmt = $conn->prepare("DELETE FROM cart WHERE user_id=:user_id");
					$stmt->execute(['user_id'=>$user['id']]);
	
					$_SESSION['success'] = 'Transaction successful. Thank you.';
	
				}
				catch(PDOException $e){
					$_SESSION['error'] = $e->getMessage();
				}
	
			}
			catch(PDOException $e){
				$_SESSION['error'] = $e->getMessage();
			}
	
			$pdo->close();
		}
	}
	else
	{
		echo "Connection Failed ";
	}


?>



<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

	<?php include 'includes/navbar.php'; ?>
	 
	  <div class="content-wrapper">
	    <div class="container">

	      <!-- Main content -->
	      <section class="content">
	        <div class="row">
	        	<div class="col-sm-9">
	        		<h1 class="page-header">YOUR CART</h1>
	        		<div class="box box-solid">
	        			<div class="box-body table-responsive">
		        		<table class="table table-bordered">
		        			<thead>
		        				<th></th>
		        				<th>Photo</th>
		        				<th>Name</th>
		        				<th>Price</th>
		        				<th width="20%">Quantity</th>
		        				<th>Subtotal</th>
		        			</thead>
		        			<tbody id="tbody">
		        			</tbody>
		        		</table>
	        			</div>
	        		</div>
					<form method="POST" action="">
						<!-- <button type="button" id="Reload" name ="Insert" onclick="doFunction();" class="btn btn-primary">Load Transaction Fields</button> -->
						<button type="submit" id="Reload" name ="Insert" class="btn btn-primary">Load Transaction Fields</button>

					</form>
					
	        		<?php
	        			// if(isset($_SESSION['user'])){
	        			// 	echo "
	        			// 		<div id='paypal-button'></div>
	        			// 	";
	        			// }
	        			// else{
	        			// 	echo "
	        			// 		<h4>You need to <a href='login.php'>Login</a> to checkout.</h4>
	        			// 	";
						// }
						include 'Authorize/index.php';
	        		?>
	        	</div>
	        	<div class="col-sm-3">
	        		<?php include 'includes/sidebar.php'; ?>
	        	</div>
	        </div>
	      </section>
	     
	    </div>
	  </div>
  	<?php $pdo->close(); ?>
  	<?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
document.getElementById("Reload").onclick = function () 
{ 
	location.reload();
};
var total = 0;
$(function(){
	$(document).on('click', '.cart_delete', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		$.ajax({
			type: 'POST',
			url: 'cart_delete.php',
			data: {id:id},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	$(document).on('click', '.minus', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var qty = $('#qty_'+id).val();
		if(qty>1){
			qty--;
		}
		$('#qty_'+id).val(qty);
		$.ajax({
			type: 'POST',
			url: 'cart_update.php',
			data: {
				id: id,
				qty: qty,
			},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	$(document).on('click', '.add', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var qty = $('#qty_'+id).val();
		qty++;
		$('#qty_'+id).val(qty);
		$.ajax({
			type: 'POST',
			url: 'cart_update.php',
			data: {
				id: id,
				qty: qty,
			},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	getDetails();
	getTotal();

});

function getDetails(){
	$.ajax({
		type: 'POST',
		url: 'cart_details.php',
		dataType: 'json',
		success: function(response){
			$('#tbody').html(response);
			getCart();
		}
	});
}

function getTotal(){
	$.ajax({
		type: 'POST',
		url: 'cart_total.php',
		dataType: 'json',
		success:function(response){
			total = response;
		}
	});
}
</script>
<!-- Paypal Express -->
<script>
paypal.Button.render({
    env: 'sandbox', // change for production if app is live,

	client: {
        sandbox:    'ASb1ZbVxG5ZFzCWLdYLi_d1-k5rmSjvBZhxP2etCxBKXaJHxPba13JJD_D3dTNriRbAv3Kp_72cgDvaZ',
        //production: 'AaBHKJFEej4V6yaArjzSx9cuf-UYesQYKqynQVCdBlKuZKawDDzFyuQdidPOBSGEhWaNQnnvfzuFB9SM'
    },

    commit: true, // Show a 'Pay Now' button

    style: {
    	color: 'gold',
    	size: 'small'
    },

    payment: function(data, actions) {
        return actions.payment.create({
            payment: {
                transactions: [
                    {
                    	//total purchase
                        amount: { 
                        	total: total, 
                        	currency: 'USD' 
                        }
                    }
                ]
            }
        });
    },

    onAuthorize: function(data, actions) {
        return actions.payment.execute().then(function(payment) {
			window.location = 'sales.php?pay='+payment.id;
        });
    },

}, '#paypal-button');
</script>
</body>
</html>