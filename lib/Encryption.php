<?php
class Encryption
{

    public function encode($string = null, $key = '52239d538a4893ef50570dc2683da6c8', $default_salt = '52239d538a4893ef50570dc2683da6c8')
    {
        if ($string == null || strlen($string) == 0 || trim($string) == '') {
            return false;
        }

        $string = $default_salt . trim($string);

        $key     = sha1($key);
        $str_len = strlen($string);
        $key_len = strlen($key);
        $j       = 0;
        $hash    = '';
        for ($i = 0; $i < $str_len; $i++) {
            $ord_str = ord(substr($string, $i, 1));
            if ($j == $key_len) {
                $j = 0;
            }
            $ord_key = ord(substr($key, $j, 1));
            $j++;
            $hash .= strrev(base_convert(dechex($ord_str + $ord_key), 16, 36));
        }
        return $hash;
    }

    public function decode($string = null, $key = '52239d538a4893ef50570dc2683da6c8', $default_salt = '52239d538a4893ef50570dc2683da6c8')
    {
        if ($string == null || strlen($string) == 0 || trim($string) == '') {
            return false;
        }

        $string = trim($string);
        $search = $default_salt;

        $key     = sha1($key);
        $str_len = strlen($string);
        $key_len = strlen($key);
        $j       = 0;
        $hash    = '';
        for ($i = 0; $i < $str_len; $i += 2) {
            $ord_str = hexdec(base_convert(strrev(substr($string, $i, 2)), 36, 16));
            if ($j == $key_len) {
                $j = 0;
            }
            $ord_key = ord(substr($key, $j, 1));
            $j++;
            $hash .= chr($ord_str - $ord_key);
        }
        return str_replace($search, '', $hash);
    }
    // Mobile
    public function getSessionKey($id)
    {
        $token    = array();
        $Jwttoken = '';
        if ((stristr(strtolower($_SERVER['HTTP_USER_AGENT']), 'android') || stristr(strtolower($_SERVER['HTTP_USER_AGENT']), 'ios')) && (strtolower($_SERVER['HTTP_PACKAGE_NAME']) == 'com.sagoon.connect.share.earn') && $_SERVER['HTTP_DEVICE_ID'] != '') {
            $duration          = '+1 minutes';
            $token['uid']      = $id;
            $token['deviceID'] = $_SERVER['HTTP_DEVICE_ID'];
            $token['exp']      = strtotime($duration, strtotime(date("Y-m-d H:i:s")));
            $Jwttoken          = Encryption::jwtencode($token, 'AZWEC854ZXM052');
        } else {
            $duration            = '+1 minutes';
            $token['uid']        = $id;
            $token['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $token['session_id'] = session_id();
            //$token['exp']=strtotime($duration, strtotime(date("Y-m-d H:i:s")));
            $Jwttoken = Encryption::jwtencode($token, 'AZWEC854ZXM052');
        }
        return ($Jwttoken);
    }

    // Mobile
    public function getUserIdFromToken($token)
    {       
        try {
            #$deviceID      = $_SERVER['HTTP_DEVICE_ID'];
            $decoded       = Encryption::jwtdecode($token, 'AZWEC854ZXM052');
            return $decoded->uid; 
            /*if ($TokendeviceID == $deviceID) {
                return $decoded->uid; // 'Token and deviceid are  live and both are valid.';
            } else {
                return (0); // 'Token & deviceid do not match , redirect to login page, invalid user.';
            }*/
        } catch (\Exception $e) {
            // echo 'Caught exception: ',  $e->getMessage(), "\n";
            return (0); //  token in invalid
        }

    }

    public function getDeviceIdFromToken($token)
    {
        $deviceID      = $_SERVER['HTTP_DEVICE_ID'];
        $decoded       = Encryption::jwtdecode($token, 'AZWEC854ZXM052');
        $TokendeviceID = $decoded->deviceID;
        if ($TokendeviceID == $deviceID) {
            return $decoded->deviceID; // 'Token and deviceid are  live and both are valid.';
        } else {
            return (0); // 'Token & deviceid do not match , redirect to login page, invalid user.';
        }
    }

    public function getDeviceTimeToken($token)
    {
        $deviceID      = $_SERVER['HTTP_DEVICE_ID'];
        $decoded       = Encryption::jwtdecode($token, 'AZWEC854ZXM052');
        $TokendeviceID = $decoded->deviceID;
        if ($TokendeviceID == $deviceID) {
            return $decoded->exp; //'Token and deviceid are  live and both are valid.';
        } else {
            return (0); //'Token & deviceid do not match , redirect to login page, invalid user.';
        }
    }

    public function reGenerateUserToken($token)
    {
        $deviceID      = $_SERVER['HTTP_DEVICE_ID'];
        $decoded       = Encryption::jwtdecode($token, 'AZWEC854ZXM052');
        $TokendeviceID = $decoded->deviceID;
        $uid           = $decoded->uid;
        if ($TokendeviceID == $deviceID) {
            $newToken = $this->generateToken($uid, $deviceID);
            return ($newToken);
        } else {
            return (0); // 'Token & deviceid do not match , redirect to login page, invalid user.';
        }
    }

    ////////////////////////////////////////////////////Start Function for JWT ///////////////////////////////////////////
    /**
     * Decodes a JWT string into a PHP object.
     *
     * @param string      $jwt    The JWT
     * @param string|null $key    The secret key
     * @param bool        $verify Don't skip verification process
     *
     * @return object      The JWT's payload as a PHP object
     * @throws UnexpectedValueException Provided JWT was invalid
     * @throws DomainException          Algorithm was not provided
     *
     * @uses jsonDecode
     * @uses urlsafeB64Decode
     */
    public static function jwtdecode($jwt, $key = null, $verify = true)
    {
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            throw new UnexpectedValueException('Wrong number of segments');
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        if (null === ($header = Encryption::jsonDecode(Encryption::urlsafeB64Decode($headb64)))) {
            throw new UnexpectedValueException('Invalid segment encoding');
        }
        if (null === $payload = Encryption::jsonDecode(Encryption::urlsafeB64Decode($bodyb64))) {
            throw new UnexpectedValueException('Invalid segment encoding');
        }
        $sig = Encryption::urlsafeB64Decode($cryptob64);
        if ($verify) {
            if (empty($header->alg)) {
                throw new DomainException('Empty algorithm');
            }
            if ($sig != Encryption::sign("$headb64.$bodyb64", $key, $header->alg)) {
                throw new UnexpectedValueException('Signature verification failed');
            }
        }
        return $payload;
    }
    /**
     * Converts and signs a PHP object or array into a JWT string.
     *
     * @param object|array $payload PHP object or array
     * @param string       $key     The secret key
     * @param string       $algo    The signing algorithm. Supported
     *                              algorithms are 'HS256', 'HS384' and 'HS512'
     *
     * @return string      A signed JWT
     * @uses jsonEncode
     * @uses urlsafeB64Encode
     */
    public static function jwtencode($payload, $key, $algo = 'HS256')
    {
        $header        = array('typ' => 'JWT', 'alg' => $algo);
        $segments      = array();
        $segments[]    = Encryption::urlsafeB64Encode(Encryption::jsonEncode($header));
        $segments[]    = Encryption::urlsafeB64Encode(Encryption::jsonEncode($payload));
        $signing_input = implode('.', $segments);
        $signature     = Encryption::sign($signing_input, $key, $algo);
        $segments[]    = Encryption::urlsafeB64Encode($signature);
        return implode('.', $segments);
    }
    /**
     * Sign a string with a given key and algorithm.
     *
     * @param string $msg    The message to sign
     * @param string $key    The secret key
     * @param string $method The signing algorithm. Supported
     *                       algorithms are 'HS256', 'HS384' and 'HS512'
     *
     * @return string          An encrypted message
     * @throws DomainException Unsupported algorithm was specified
     */
    public static function sign($msg, $key, $method = 'HS256')
    {
        $methods = array(
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        );
        if (empty($methods[$method])) {
            throw new DomainException('Algorithm not supported');
        }
        return hash_hmac($methods[$method], $msg, $key, true);
    }
    /**
     * Decode a JSON string into a PHP object.
     *
     * @param string $input JSON string
     *
     * @return object          Object representation of JSON string
     * @throws DomainException Provided string was invalid JSON
     */
    public static function jsonDecode($input)
    {
        $obj = json_decode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            Encryption::_handleJsonError($errno);
        } else if ($obj === null && $input !== 'null') {
            throw new DomainException('Null result with non-null input');
        }
        return $obj;
    }
    /**
     * Encode a PHP object into a JSON string.
     *
     * @param object|array $input A PHP object or array
     *
     * @return string          JSON representation of the PHP object or array
     * @throws DomainException Provided object could not be encoded to valid JSON
     */
    public static function jsonEncode($input)
    {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            Encryption::_handleJsonError($errno);
        } else if ($json === 'null' && $input !== null) {
            throw new DomainException('Null result with non-null input');
        }
        return $json;
    }
    /**
     * Decode a string with URL-safe Base64.
     *
     * @param string $input A Base64 encoded string
     *
     * @return string A decoded string
     */
    public static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
    /**
     * Encode a string with URL-safe Base64.
     *
     * @param string $input The string you want encoded
     *
     * @return string The base64 encode of what you passed in
     */
    public static function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
    /**
     * Helper method to create a JSON error.
     *
     * @param int $errno An error number from json_last_error()
     *
     * @return void
     */
    private static function _handleJsonError($errno)
    {
        $messages = array(
            JSON_ERROR_DEPTH     => 'Maximum stack depth exceeded',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX    => 'Syntax error, malformed JSON',
        );
        throw new DomainException(
            isset($messages[$errno])
            ? $messages[$errno]
            : 'Unknown JSON error: ' . $errno
        );
    }

    ////////////////////////////////////////////////////End Function for JWT ///////////////////////////////////////////

    //-------------------------------------------------------------------------------------//
    public function generateToken($uid, $deviceID)
    {
        $token             = array();
        $duration          = '+1 minutes';
        $token['uid']      = $uid;
        $token['deviceID'] = $deviceID;
        $token['exp']      = strtotime($duration, strtotime(date("Y-m-d H:i:s")));
        $token             = Encryption::jwtencode($token, 'AZWEC854ZXM052');
        return ($token);
    }

    /*
    function validTokenDeviceid($token, $deviceID){
    try{
    $decoded = Encryption::jwtdecode($token, 'AZWEC854ZXM052');
    $TokendeviceID=$decoded->deviceID;
    if($TokendeviceID==$deviceID){
    return(1); // 'Token and deviceid are  live and both are valid.';
    }
    else{
    return(2); // 'Token & deviceid do not match , redirect to login page, invalid user.';
    }

    }catch(\Exception $e){
    // echo 'Caught exception: ',  $e->getMessage(), "\n";
    return(3); //  token in invalid
    }
    }

    ///This function is for future use if client wants to regenerate token
    function reGenerateToken($token, $deviceID){
    $decoded = Encryption::jwtdecode($token, 'AZWEC854ZXM052');
    $TokendeviceID=$decoded->deviceID;
    $uid=$decoded->uid;
    if($TokendeviceID==$deviceID)
    {
    $newToken=$this->generateToken($uid, $deviceID);
    return($newToken);
    }
    else{
    return(2); // 'Token & deviceid do not match , redirect to login page, invalid user.';
    }
    }*/

    public function uencode($data)
    {
        return strtr(rtrim(base64_encode($data), '='), '+/', '-_');
    }

    public function udecode($base64)
    {
        return base64_decode(strtr($base64, '-_', '+/'));

    }

} //End of class cakephpCDN
