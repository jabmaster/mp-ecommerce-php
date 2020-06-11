<?php
	class Order{

		public $id;
		public $buyer;
		public $items;
		public $installments;

		function __construct ($buyer, $items, $installments){
			$this->buyer = $buyer;
			$this->items = $items;
			$this->installments = $installments;
		}	
	}

	class OrderController{
		public static function saveOrder(&$order){
			$order->id = "jabmaster@gmail.com";
		}
	}
?>