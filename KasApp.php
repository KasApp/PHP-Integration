<?

/**
 *
 */
class KasApp {

	public 	$APP_ID;
	public	$APP_SECRET;
	public	$URL = "https://kasapp.ru/v1/";
	public	$DEBUG_MODE = false;

	puplic function __construct($APP_ID = null, $APP_SECRET = null, $DEBUG_MODE = false)
	{
		$this->APP_ID 		= (int) (defined('KASAPP_APP_ID') ? KASAPP_APP_ID : $APP_ID);
		$this->APP_SECRET 	= (defined('KASAPP_APP_SECRET') ? KASAPP_APP_SECRET : $APP_SECRET);
		$this->DEBUG_MODE 	= $DEBUG_MODE;
	}


	public function request($method, $data)
	{
		return [];
	}


	private function post($url, $params)
	{
            $curl = curl_init();
            $headers = array();
            $headers[] = 'Cache-Control: no-cache';
            $headers[] = "X-Requested-With: XMLHttpRequest";
            $headers[] = "Accept: application/json";
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_RETURNTRANSFER => true
            ));
            $response = curl_exec($curl);
            $response = json_decode($response, true);
            curl_close($curl);

            if($this->DEBUG_MODE && !$response['status']){
            	throw new KasAppException($response['error']['message'], $response['error']['code']);
            }

            return $response;
	}

}

class KasAppException extends Exception {}