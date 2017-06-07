<?php
/**
 * Verifico la respuesta de un reCAPTCHA.
 *
 * @link   http://www.google.com/recaptcha
 */

/**
 * Envía las peticiones cURL al servicio reCAPTCHA.
 */
class ReCaptcha
{
	const SITE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';  // URL al cual se envían las peticiones vía cURL.
	const SECRET = '6LfK_BUTAAAAAJXc56hN2W_U91NrylVYrJYhLpHn';  // Llave secreta brindada por Google.
	private $response;
	private $remoteIp;

	public function __construct($response, $remoteIp = NULL)
	{
		$this->response = $response;
		$this->remoteIp = $remoteIp;
	}

	/**
	 * @return array respuesta del reCAPTCHA
	 */
	public function verificar()
	{
		if($this->response=='')
		{
			return array('success' => false, 'error-codes' => array('missing-input-response'));  // No diligenciaron el captcha.
		}

		$params = array('secret' => self::SECRET, 'response' => $this->response, 'remoteip' => $this->remoteIp);
		$params = http_build_query($params, '', '&');
		$handle = curl_init(self::SITE_VERIFY_URL);

		$options = array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $params,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/x-www-form-urlencoded'
			),
			CURLINFO_HEADER_OUT => false,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => true
		);
		curl_setopt_array($handle, $options);

		$response = curl_exec($handle);
		curl_close($handle);

		return json_decode($response, true);
	}
}
?>