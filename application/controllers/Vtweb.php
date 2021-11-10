<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vtweb extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */


	public function __construct()
    {
        parent::__construct();
        $params = array('server_key' => 'your_server_key', 'production' => false);
		$this->load->library('veritrans');
		$this->veritrans->config($params);
		$this->load->helper('url');
		
    }

	public function index()
	{
		$this->load->view('checkout_vtweb');
	}

	public function vtweb_checkout()
	{
		$transaction_details = array(
			'order_id' 			=> uniqid(),
			'gross_amount' 	=> 200000
		);

		// Populate items
		$items = [
			array(
				'id' 				=> 'item1',
				'price' 		=> 100000,
				'quantity' 	=> 1,
				'name' 			=> 'Adidas f50'
			),
			array(
				'id'				=> 'item2',
				'price' 		=> 50000,
				'quantity' 	=> 2,
				'name' 			=> 'Nike N90'
			)
		];

		// Populate customer's billing address
		$billing_address = array(
			'first_name' 		=> "Andri",
			'last_name' 		=> "Setiawan",
			'address' 			=> "Karet Belakang 15A, Setiabudi.",
			'city' 					=> "Jakarta",
			'postal_code' 	=> "51161",
			'phone' 				=> "081322311801",
			'country_code'	=> 'IDN'
			);

		// Populate customer's shipping address
		$shipping_address = array(
			'first_name' 	=> "John",
			'last_name' 	=> "Watson",
			'address' 		=> "Bakerstreet 221B.",
			'city' 				=> "Jakarta",
			'postal_code' => "51162",
			'phone' 			=> "081322311801",
			'country_code'=> 'IDN'
			);

		// Populate customer's Info
		$customer_details = array(
			'first_name' 			=> "Andri",
			'last_name' 			=> "Setiawan",
			'email' 					=> "andrisetiawan@me.com",
			'phone' 					=> "081322311801",
			'billing_address' => $billing_address,
			'shipping_address'=> $shipping_address
			);

		// Data yang akan dikirim untuk request redirect_url.
		// Uncomment 'credit_card_3d_secure' => true jika transaksi ingin diproses dengan 3DSecure.
		$transaction_data = array(
			'payment_type' 			=> 'vtweb', 
			'vtweb' 						=> array(
				//'enabled_payments' 	=> ['credit_card'],
				'credit_card_3d_secure' => true
			),
			'transaction_details'=> $transaction_details,
			'item_details' 			 => $items,
			'customer_details' 	 => $customer_details
		);
	
		try
		{
			$vtweb_url = $this->veritrans->vtweb_charge($transaction_data);
			header('Location: ' . $vtweb_url);
		} 
		catch (Exception $e) 
		{
    		echo $e->getMessage();	
		}
		
	}

	public function notification()
	{
		echo 'test notification handler';
		$json_result = file_get_contents('php://input');
		$result = json_decode($json_result);

		if($result){
		$notif = $this->veritrans->status($result->order_id);
		}

		error_log(print_r($result,TRUE));

		//notification handler sample

		$transaction = $notif->transaction_status;
		$type = $notif->payment_type;
		$order_id = $notif->order_id;
		$fraud = $notif->fraud_status;

		if ($transaction == 'capture') {
		  // For credit card transaction, we need to check whether transaction is challenge by FDS or not
		  if ($type == 'credit_card'){
		    if($fraud == 'challenge'){
		      // TODO set payment status in merchant's database to 'Challenge by FDS'
		      // TODO merchant should decide whether this transaction is authorized or not in MAP
		      echo "Transaction order_id: " . $order_id ." is challenged by FDS";
		      } 
		      else {
		      // TODO set payment status in merchant's database to 'Success'
		      echo "Transaction order_id: " . $order_id ." successfully captured using " . $type;
		      }
		    }
		  }
		else if ($transaction == 'settlement'){
		  // TODO set payment status in merchant's database to 'Settlement'
		  echo "Transaction order_id: " . $order_id ." successfully transfered using " . $type;
		  } 
		  else if($transaction == 'pending'){
		  // TODO set payment status in merchant's database to 'Pending'
		  echo "Waiting customer to finish transaction order_id: " . $order_id . " using " . $type;
		  } 
		  else if ($transaction == 'deny') {
		  // TODO set payment status in merchant's database to 'Denied'
		  echo "Payment using " . $type . " for transaction order_id: " . $order_id . " is denied.";
		}

	}
}
