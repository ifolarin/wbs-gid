<?php
class XmlRpc_Client {
	private $url;
	function __construct($url, $username, $password, $autoload=true) {
		$this->url = $url;
		$this->username = $username;
		$this->password = $password;
		
// 		$this->methods = array();
// 		if ($autoload) {
// 			$resp = $this->call('system.listMethods', null);
// 			$this->methods = $resp;			
// 		}
	}
	public function call($method, $params = null) {
		$params = array('login.username'=>$this->username, 'login.password' => $this->password, $params);
		$request = xmlrpc_encode_request($method, $params);
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		echo($request);
		return xmlrpc_decode($result);
// 		return xmlrpc_decode($this->connection->post($this->url, $post));
	}
}