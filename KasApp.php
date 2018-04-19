<?

/**
 * KasApp - класс для работы с KasApp Public API
 *
 * @link https://docs.kasapp.ru/
 */
class KasApp {

    public  $APP_ID        = null;
    private $APP_SECRET    = null;
    public  $URL           = "https://kasapp.ru/v1/";
    public  $DEBUG_MODE    = false;
    public  $REDIRECT_URL  = null;
    private $CODE          = null;
    private $ACCSES_TOKEN  = null;


    /**
     * KasApp constructor.
     * @param null|int $APP_ID
     * @param null|string $APP_SECRET
     * @param null|string $REDIRECT_URL
     * @param bool $DEBUG_MODE
     * @return null
     */
    public function __construct($APP_ID = null, $APP_SECRET = null, $REDIRECT_URL = null, $DEBUG_MODE = false)
    {
        $this->APP_ID       = (int) (defined('KASAPP_APP_ID') ? KASAPP_APP_ID : $APP_ID);
        $this->APP_SECRET   = (defined('KASAPP_APP_SECRET') ? KASAPP_APP_SECRET : $APP_SECRET);
        $this->REDIRECT_URL = (defined('KASAPP_REDIRECT_URL') ? KASAPP_REDIRECT_URL : $REDIRECT_URL);
        $this->DEBUG_MODE   = (bool) $DEBUG_MODE;
        return true;
    }

    /**
     * Получение ссылки для авторизации пользователя и получения code
     * @return string
     */
    public function getAuthorizeUrl()
    {
        return 'https://kasapp.ru/authorize?app_id='.$this->APP_ID.'&redirect_uri='.$this->REDIRECT_URL;
    }

    /**
     * Получение access_token используя code
     *
     * @param null|string $code
     * @return mixed
     * @throws KasAppException
     */
    public function getAccessToken($code = null)
    {
        $code = (!empty($code) ? $code : $this->CODE);
        $url = 'https://kasapp.ru/access_token'.
            '?app_id='.$this->APP_ID.
            '&redirect_uri='.$this->REDIRECT_URL.
            '&code='.$code.
            '&app_secret='.$this->APP_SECRET;
        $result = file_get_contents($url);
        $data = json_decode($result, true);

        if(!empty($data)){
            if($data['status']){
                return $this->ACCSES_TOKEN = $data['response']['access_token'];
            } elseif($this->DEBUG_MODE) {
                throw new KasAppException($data['error']['message'], $data['error']['code']);
            }
        }
        return null;
    }

    /**
     * Установка code
     * @param null $code - Код для обновления access_token
     * @return bool
     */
    public function setCode($code = null)
    {
        if(is_null($code)){
            $code = $_GET['code'];
        }
        $this->CODE = $code;
        return true;
    }

    /**
     * Запрос к API
     * @param string $method - метод API
     * @param mixed $data - парамтеры запроса
     * @return array - ответ api
     */
    public function request($method, $data)
    {
        $url = $this->URL . $method;
        $params = $this->prepare_data($data);

        return $this->post($url, $params);
    }

    /**
     * Подготовка данных, перед отправкой
     * @param $data - парамтеры запроса
     * @return array
     * @throws KasAppException
     */
    private function prepare_data($data){
        if(empty($this->ACCSES_TOKEN) and $this->DEBUG_MODE){
            throw new KasAppException('EMPTY ACCESS TOKEN', 1);
        }
        $data['app_id'] = $this->APP_ID;
        $data['access_token'] = $this->ACCSES_TOKEN;

        return $data;
    }


    private function post($url, $params = null)
    {
        $curl = curl_init();
        $headers = array();
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = "X-Requested-With: XMLHttpRequest";
        $headers[] = "Accept: application/json";
        $setopt = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true
        );
        if(!empty($params)){
            $setopt[CURLOPT_POSTFIELDS] = $params;
        }

        curl_setopt_array($curl, $setopt);
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