<?php
	class Buyer{

		public $id;
		public $name;
		public $surname;
		public $email;
		public $phone_area;
		public $phone_number;
		public $address_street_name;
		public $address_number;
		public $address_zipcode;

		function __construct ($buyerID){
			$this->id = $buyerID;
			BuyerController::getBuyerByID($this);
		}
	}

	class BuyerController{
		public static function getBuyerByID(&$buyer){
			switch ($buyer->id){		
				default:
				    $buyer->name = "Lalo";
				    $buyer->surname = "Landa";
				    $buyer->email = "test_user_63274575@testuser.com";
				    $buyer->phone_area = "11";
				    $buyer->phone_number = "22223333";
				    $buyer->address_street_name = "False";
				    $buyer->address_number = 123;
				    $buyer->address_zipcode = "1111";					
					break;
			}
		}
	}
?>