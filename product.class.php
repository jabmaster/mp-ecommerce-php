<?php
	class Product{

		public $id;
		public $name;
		public $description;
		public $image_uri;
		public $price;
	}

	class Item{

		public $product;
		public $quantity;

		function __construct ($product, $quantity){
			$this->product = $product;
			$this->quantity = $quantity;
		}		
	}
?>