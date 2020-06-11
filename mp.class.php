<?php
	require_once __DIR__ .  '/vendor/autoload.php';

	define('TIENDA_PRODUCCION', false);

	if (TIENDA_PRODUCCION){
		define('MP_ACCESS_TOKEN', 'credenciales_a_obtener');
		define('MP_PUBLIC_KEY', 'credenciales_a_obtener');
		define('MP_INTEGRATOR_ID', 'credenciales_a_obtener');
	}
	else{
		define('MP_ACCESS_TOKEN', 'APP_USR-6317427424180639-042414-47e969706991d3a442922b0702a0da44-469485398');
		define('MP_PUBLIC_KEY', 'APP_USR-7eb0138a-189f-4bec-87d1-c0504ead5626');
		define('MP_INTEGRATOR_ID', 'dev_24c65fb163bf11ea96500242ac130004');
	}

	//define('MP_RETURN_URL', 'https://jabmaster-mp-commerce-php.herokuapp.com/checkout.php?retorno=');
	$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https:' : 'http:';
	define('MP_RETURN_URL', $protocol . "//" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/checkout.php?retorno=");
	define('MP_SUCCESS_URL', MP_RETURN_URL . 'aprobado');
	define('MP_FAILURE_URL', MP_RETURN_URL . 'error');
	define('MP_PENDING_URL', MP_RETURN_URL . 'pendiente');
	define('MP_NOTIFICATION_URL', MP_RETURN_URL . 'ipn');

	class MPCheckout{

		function __construct(){
			MercadoPago\SDK::setAccessToken(MP_ACCESS_TOKEN);
			MercadoPago\SDK::setIntegratorId(MP_INTEGRATOR_ID);
		}

		public function redirect($order, $excluded_payment_methods, $excluded_payment_types){

			$redirect_url = null;
			$payer = new MercadoPago\Payer();
			$items = array();

			$payer->name = $order->buyer->name;
			$payer->surname = $order->buyer->surname;
			$payer->email = $order->buyer->email;
			$payer->phone = array(
				"area_code" => $order->buyer->phone_area,
				"number" => $order->buyer->phone_number
			);

			$payer->address = array(
				"street_name" => $order->buyer->address_street_name,
				"street_number" => $order->buyer->address_number,
				"zip_code" => $order->buyer->address_zipcode
			);

			foreach ($order->items as $item){
				$item_mp = new MercadoPago\Item();
				$item_mp->id = $item->product->id;
				$item_mp->title = $item->product->name;
				$item_mp->description = $item->product->description;
				$item_mp->quantity = $item->quantity;
				$item_mp->picture_url = $item->product->image_uri;
				$item_mp->unit_price = $item->product->price;

				$items[] = $item_mp;
			}

			$preference = new MercadoPago\Preference();
			$preference->items = $items;
			$preference->payer = $payer;

			$preference->back_urls = array(
			    "success" => MP_SUCCESS_URL,
			    "failure" => MP_FAILURE_URL,
			    "pending" => MP_PENDING_URL
			);

			$preference->auto_return = "approved";

			$preference->payment_methods = array(
			  "excluded_payment_methods" => array($excluded_payment_methods),
			  "excluded_payment_types" => array($excluded_payment_types),
			  "installments" => $order->installments
			);

			$preference->notification_url = MP_NOTIFICATION_URL;
			$preference->external_reference = $order->id;

			$preference->save();

			if (!is_null($preference->id) && !empty($preference->id) && strlen($preference->id) > 1)
				$redirect_url = $preference->init_point;

			return $redirect_url;
		}
	}
?>
