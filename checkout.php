<?php
	
	require_once __DIR__ .  '/mp.class.php';
	require_once __DIR__ .  '/order.class.php';
	require_once __DIR__ .  '/buyer.class.php';
	require_once __DIR__ .  '/product.class.php';

	if (isset($_POST['action']) && !empty($_POST['action'])){
		$action = $_POST['action'];

		switch ($action) {
			case "checkout":
				$buyer_id = 0;
				$product_name = null;
				$product_quantity = 0;
				$product_price = 0;
				$product_img = null;
				$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https:' : 'http:';

				if (isset($_POST['buyer_id']) && is_numeric($_POST['buyer_id']))
					$buyer_id = $_POST['buyer_id'];

				if (isset($_POST['product_name']) && !empty($_POST['product_name']))
					$product_name = $_POST['product_name'];

				if (isset($_POST['product_quantity']) && is_numeric($_POST['product_quantity']))
					$product_quantity = $_POST['product_quantity'];

				if (isset($_POST['product_price']) && is_numeric($_POST['product_price']))
					$product_price = $_POST['product_price'];

				if (isset($_POST['product_img']) && !empty($_POST['product_img']))
					$product_img = $_POST['product_img'];

				if ($buyer_id < 0 || is_null($product_name) || $product_quantity < 1 || $product_price < 0 || is_null($product_img))
					die("Datos inválidos");

				$buyer = new Buyer($buyer_id);
				$items = array();

				$product = new Product();
				$product->id = 1234;
				$product->name = $product_name;
				$product->description = "Dispositivo móvil de Tienda e-commerce";
				$product->image_uri = $protocol . "//" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/" . $product_img;
				$product->price = $product_price;

				$items[] = new Item($product, $product_quantity);

				$order = new Order($buyer, $items, 6);
				OrderController::saveOrder($order);

				$mp_checkout = new MPCheckout();
				$url_mp = $mp_checkout->redirect($order, array("id" => "amex"), array("id" => "atm"));

				if (!is_null($url_mp)){
					header("Location: " . $url_mp);
					exit();					
				}
				else{
					echo "Ocurrió un error con MercadoPago. Intenta más tarde.";
				}

				break;
			
			default:
				die("Acción desconocida");
				break;
		}
	}

	if (isset($_GET['retorno']) && !empty($_GET['retorno'])){

		$return_type = $_GET['retorno'];
		$payment_status = null;
		$payment_result = null;
		$show_html = true;

		switch ($return_type) {
			case "gracias":
			case "aprobado":
				$payment_status = "Pago aprobado";
				$payment_result = "¡Recibimos tu pago! Tu producto será despachado a la brevedad.<br><br>";

				if (isset($_GET['collection_id'])){
					$payment_result .= '
						<b>Referencia del pago</b>: ' . $_GET['collection_id'] . '<br>
					';
				}

				if (isset($_GET['merchant_order_id'])){
					$payment_result .= '
						<b>Referencia orden MP</b>: ' . $_GET['merchant_order_id'] . '<br>
					';
				}				

				if (isset($_GET['external_reference'])){
					$payment_result .= '
						<b>Orden interna</b>: ' . $_GET['external_reference'] . '<br>
					';
				}
			
				break;

			case "pendiente":
				$payment_status = "Pago pendiente de acreditación";
				$payment_result = "Te avisaremos por e-mail cuando tu pago se acredite.<br><br>";

				if (isset($_GET['collection_id'])){
					$payment_result .= '
						<b>Referencia del pago</b>: ' . $_GET['collection_id'] . '<br>
					';
				}

				if (isset($_GET['external_reference'])){
					$payment_result .= '
						<b>Ordern interna</b>: ' . $_GET['external_reference'] . '<br>
					';
				}

				break;

			case "error":
				$payment_status = "Pago cancelado";
				$payment_result = 'Tu pago no se completó';

				if (isset($_GET['preference_id']))
					$payment_result .= '
					<br><br>
					<a href="https://www.mercadopago.com.ar/checkout/v1/redirect?pref_id=' . $_GET['preference_id'] . '">Haz click aquí para intentar nuevamente</a>
				';
				break;

			case "ipn":
				$hoy = new DateTimeImmutable();
				file_put_contents(__DIR__ . '/ipn-mp.log', '--------------------------------' . PHP_EOL, FILE_APPEND);
				file_put_contents(__DIR__ . '/ipn-mp.log', $hoy->format('d/m/Y H:i:s') . PHP_EOL, FILE_APPEND);
				file_put_contents(__DIR__ . '/ipn-mp.log', '--------------------------------' . PHP_EOL, FILE_APPEND);
				file_put_contents(__DIR__ . '/ipn-mp.log', print_r(file_get_contents('php://input'), true) . PHP_EOL, FILE_APPEND);
				file_put_contents(__DIR__ . '/ipn-mp.log', '--------------------------------' . PHP_EOL, FILE_APPEND);
				file_put_contents(__DIR__ . '/ipn-mp.log', '----------------------------------------------------------------' . PHP_EOL . PHP_EOL, FILE_APPEND);

				echo "OK";
				$show_html = false;
				break;
			
			default:
				die("Invalid return");
				break;
		}

		if ($show_html){
			include "retorno.php";
		}
	}
?>