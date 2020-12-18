<?php

/**
 *
 * Payhesap 3D Pos Basic PHP Class
 *
 * PHP versions 5 and 7
 *
 * @author  Mirarus <aliguclutr@gmail.com>
 * @version 1.8
 * @link https://github.com/mirarus/Payhesap
 *
 */

class Payhesap
{

	private 
	$config = [],
	$card = [],
	$buyer = [],
	$order_id,
	$price,
	$installment = 1,
	$currency_codes = ['TRY', 'USD', 'EUR'],
	$currency_code = 'TRY',
	$error;

	public function set_config($data=[])
	{
		if ($data['hash'] == null || $data['callback_url'] == null) {
			$this->error = "Missing api, url information.";
		} else {
			$this->config = [
				'hash'         => $data['hash'],
				'callback_url' => $data['callback_url']
			];
		}
	}

	public function set_card($data=[])
	{
		if ($data['fullname'] == null || $data['number'] == null || $data['exp_month'] == null || $data['exp_year'] == null || $data['cvv'] == null) {
			$this->error = "Missing Card information.";
		} else {
			if (strlen($data['exp_month']) == 2 && strlen($data['exp_year']) == 2 && (strlen($data['cvv']) == 3 || strlen($data['cvv']) == 4)) {
				$this->card = [
					'fullname'  => $data['fullname'],
					'number'    => $data['number'],
					'exp_month' => $data['exp_month'],
					'exp_year'  => $data['exp_year'],
					'cvv'       => $data['cvv']
				];
			} else {
				$this->error = "Missing Card information.";
			}
		}
	}

	public function set_buyer($data=[])
	{
		if ($data['name'] == null || $data['email'] == null || $data['phone'] == null || $data['city'] == null || $data['state'] == null || $data['address'] == null) {
			$this->error = "Missing Buyer information.";
		} else {

			$full_name = explode(' ', $data['name']);
			$last_name = array_pop($full_name);
			$first_name = implode(' ', $full_name);

			$this->buyer = [
				'firstname' => $first_name,
				'lastname'  => $last_name,
				'email'     => $data['email'],
				'phone'     => $data['phone'],
				'city'      => $data['city'],
				'state'     => $data['state'],
				'address'   => $data['address']
			];
		}
	}

	public function set_order_id($order_id)
	{
		if ($order_id != null) {
			$this->order_id = (time() . 'PH' . $order_id);
		}
	}

	public function set_price($price)
	{
		if ($price != null) {
			$this->price = number_format($price, 2, '.', '');
		}
	}

	public function set_installment($installment)
	{
		if ($installment != null) {
			if ($installment <= 12) {
				$this->installment = $installment;
			} else {
				$this->error = "Max ınstallment Count 12";
			}
		}
	}

	public function set_currency($currency)
	{
		if ($currency != null) {
			if (in_array($currency, $this->currency_codes)) {
				$this->currency_code = $currency;
			} else {
				$this->error = "Invalid Currency Code";
			}
		}
	}

	public function get_error()
	{
		if ($this->error != null) {
			return $this->error;
		}
	}

	public function init()
	{
		if ($this->config == null || $this->card == null || $this->buyer == null || $this->order_id == null || $this->price == null) {
			$this->error = "Insufficient Data";
		} else {

			$post = [
				'hash'             => $this->config['hash'],
				'callback_url'     => $this->config['callback_url'],

				'order_id'         => $this->order_id,
				'amount'           => $this->price,
				'installment'      => $this->installment,
				'currency'         => $this->currency_code,

				'cardcustomername' => $this->card['fullname'],
				'cardnumber'       => $this->card['number'],
				'cardexpmonth'     => $this->card['exp_month'],
				'cardexpyear'      => $this->card['exp_year'],
				'cardcvv'          => $this->card['cvv'],

				'customer'         => [
					'firstname' => $this->buyer['firstname'],
					'lastname'  => $this->buyer['lastname'],
					'email'     => $this->buyer['email'],
					'phone'     => $this->buyer['phone'],
					'city'      => $this->buyer['city'],
					'state'     => $this->buyer['state'],
					'address'   => $this->buyer['address'],
					'ip'        => $this->GetIP()
				]
			];


			$encode = json_encode($post, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			
			$ch = curl_init();
			curl_setopt_array($ch, [
				CURLOPT_URL => "https://www.payhesap.com/api/pay",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_FRESH_CONNECT => true,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => $encode,
				CURLOPT_HTTPHEADER => [
					'Content-Type: application/json',
					'Content-Length: ' . strlen($encode)
				]
			]);
			$response = @curl_exec($ch);

			if (curl_errno($ch)) {
				$this->error = curl_error($ch);
			} else {

				$result = json_decode($response, true);
				print_r($post);
				if ($result['status'] == true) {
					return $result['payUrl'];
				} else {
					$this->error = $result['errors'];
				}
			}
			curl_close($ch);
		}
	}

	public function callback()
	{
		echo '<pre>';
		print_r($_REQUEST);
		echo '</pre>';
		/*$status         = $this->post('status');
		$result_message = $this->post('resultMessage');
		$other_code     = $this->post('otherCode');
		$verify_hash    = $this->post('VerifyHash');
		$amount         = $this->post('amount');

		if ($status == true) {

			$hash = hash("sha256", $merchant_id . "|" . $merchant_mail . "|" . $merchant_secret . "|" . $other_code . "|true");
			if ($hash == $verify_hash) {
				return [
					'order_id' => explode('Payhesap', $other_code)[1],
					'amount'   => $amount,
					'hash'     => $verify_hash
				];
			} else {
				$this->error = "Invalid Verification Code";
			}
		} else {
			//$this->error = $result_message;
		}*/
	}

	private function GetIP()
	{
		if (getenv("HTTP_CLIENT_IP")) {
			$ip = getenv("HTTP_CLIENT_IP");
		} elseif (getenv("HTTP_X_FORWARDED_FOR")) {
			$ip = getenv("HTTP_X_FORWARDED_FOR");
			if (strstr($ip, ',')) {
				$tmp = explode (',', $ip);
				$ip = trim($tmp[0]);
			}
		} else{
			$ip = getenv("REMOTE_ADDR");
		}
		return $ip;
	}

	private function post($par, $empty=true) {
		if ($empty == true) {
			return (isset($_POST[$par]) && !empty($_POST[$par])) ? $_POST[$par] : null;
		} else {
			return (isset($_POST[$par])) ? $_POST[$par] : null;
		}
	}
}