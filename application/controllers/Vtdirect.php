<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vtdirect extends CI_Controller {

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
		$this->load->view('checkout_with_3ds');
	}

	public function vtdirect_cc_charge()
	{
		$token_id = $this->input->post('token_id');
		$transaction_details = array(
			'order_id' 			=> uniqid(),
			'gross_amount' 	=> 10000
		);

		// Populate items
		$items = [
			array(
				'id' 				=> 'item1',
				'price' 		=> 5000,
				'quantity' 	=> 1,
				'name' 			=> 'Adidas f50'
			),
			array(
				'id'				=> 'item2',
				'price' 		=> 2500,
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

		$transaction_data = array(
			'payment_type'      => 'credit_card', 
			'credit_card'       => array(
			   'token_id'  => $token_id,
			   'bank'    => 'bni'
			   ),
			'transaction_details'   => $transaction_details,
		);

		$params = array('server_key' => 'VT-server-UJ4uPuXhwiNXwhpQx5-S76U1', 'production' => false);
		$this->load->library('veritrans',$params);
		$response = null;
		try
		{
			$response= $this->veritrans->vtdirect_charge($transaction_data);
		} 
		catch (Exception $e) 
		{
    		echo $e->getMessage();	
		}

		//var_dump($response);
		if($response)
		{
			if($response->transaction_status == "capture")
			{
				//success
				echo "Transaksi berhasil. <br />";
				echo "Status transaksi untuk order id ".$response->order_id.": ".$response->transaction_status;

				echo "<h3>Detail transaksi:</h3>";
				echo "<pre>";
  				var_dump($response);
  				echo "</pre>";
			}
			else if($response->transaction_status == "deny")
			{
				//deny
				echo "Transaksi ditolak. <br />";
				echo "Status transaksi untuk order id ".$response->order_id.": ".$response->transaction_status;

				echo "<h3>Detail transaksi:</h3>";
				echo "<pre>";
  				var_dump($response);
  				echo "</pre>";
			}
			else if($response->transaction_status == "challenge")
			{
				//challenge
				echo "Transaksi challenge. <br />";
				echo "Status transaksi untuk order id ".$response->order_id.": ".$response->transaction_status;

				echo "<h3>Detail transaksi:</h3>";
				echo "<pre>";
  				var_dump($response);
  				echo "</pre>";
			}
			else
			{
				//error
				echo "Terjadi kesalahan pada data transaksi yang dikirim.<br />";
				echo "Status message: [".$response->status_code."] ".$response->status_message;

				echo "<h3>Response:</h3>";
				echo "<pre>";
  				var_dump($response);
  				echo "</pre>";
			}	
		}
	
	}

}
