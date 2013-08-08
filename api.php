<?php

/**
 * The BlueRover API class allows the user to make calls to the BlueRover API by generating authentication tokens and signing each request.
 *
 * @author: Andrew Hassan
 *
 * Usage: Create a BlueRoverApi object. The constructor needs your secret key, your authorization token, and a base URL. The base URL is essentially the
 * domain for the requests (e.g. http://developers.polairus.com).
 *
 */
class BlueRoverApi {
    private $key;
    private $token;
    private $base_url;

    function __construct($key, $token, $base_url) {
        if (is_null($base_url) or empty($base_url)) {
            throw new Exception("The base URL cannot be empty.");
        }
        $this->base_url = $base_url;
        $this->set_credentials($key, $token);
    }

    function set_credentials($key, $token) {
        if (is_null($key) 
            or is_null($token)
            or empty($key)
            or empty($token)) {
            throw new Exception("The key or token cannot be empty.");
        }
        
        $this->key = $key;
        $this->token = $token;
    }

    function clear_credentials() {
        $this->key = null;
        $this->token = null;
    }

    function set_base_url($base_url) {
        $this->base_url = $base_url;
    }

    function event($start_time, $end_time, $page_num) {
        return $this -> call_api('/event', array('start_time' => $start_time, 'end_time' => $end_time, 'page' => $page_num), false);
    }

    private function call_api($relative_url, $parameters = array(), $post_data = false) {
        // At the time of writing, the API does not support POST. So this value is set to false regardless of
        // the param passed in.
        $post_data = false;

        ksort($parameters);

        $url = $this->base_url . $relative_url;

        if ($post_data) {
            $http_method = "POST";
        }
        else {
            $http_method = "GET";
        }

        $signature = self::generate_signature($this->key, $http_method, $url, $parameters);
        if ($post_data == false) {
            $endpoint_url = $url;
            if (count($parameters) > 0) {
                $endpoint_url .=  "?";
                $joined_params = array();
                foreach ($parameters as $k => $v) {
                    $temp_str = $k . "=" . $v;
                    array_push($joined_params, $temp_str);
                }
                $joined_params = implode("&", $joined_params);
                $endpoint_url .= $joined_params;
            }

            $ch = curl_init($endpoint_url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: BR " . $this->token . ":" . $signature));
            $response = curl_exec($ch);
            curl_close($ch);

            return substr($response, 0, -1);
        }
        else {
            throw new Exception("POST not supported yet!");
        }
    }

    private static function generate_signature($key, $method, $url, $parameters = array()) {
        $decomposed_url = self::decompose_url($url);
        $scheme = $decomposed_url['scheme'];
        $netloc = $decomposed_url['netloc'];
        $path = $decomposed_url['path'];

        $normalized_url = strtolower($scheme) . "://" . strtolower($netloc) . $path;
        
        $base_elems = array();
        array_push($base_elems, strtoupper($method));
        array_push($base_elems, $normalized_url);

        ksort($parameters);
        $combined_params = array();
        foreach ($parameters as $k => $v) {
            array_push($combined_params, $k . "=" . $v);
        }
        $combined_params = implode("&", $combined_params);
        array_push($base_elems, $combined_params);

        $escaped_base = array();
        foreach ($base_elems as $val) {
            array_push($escaped_base, self::oauth_escape($val));
        }

        $base_string = implode("&", $escaped_base);
        
        return self::oauth_hmacsha1($key, $base_string);
    }
    
    private static function oauth_escape($val) {
        $val = utf8_encode($val);
        // TODO: Make sure this is the right function to use
        return urlencode($val);
    }

    private static function decompose_url($url) {
        $scheme_pos = strpos($url, "://");
        if ($scheme_pos == false) {
            throw new Exception("Incorrectly formatted URL.");
        }
        $scheme = substr($url, 0, $scheme_pos);
        $url = substr($url, $scheme_pos + 3);
        $netloc_pos = strpos($url, "/");
        if ($netloc_pos == false) {
            $netloc = $url;
            $url = "";
        }
        else {
            $netloc = substr($url, 0, $netloc_pos);
            $url = substr($url, $netloc_pos);
        }
        $path = $url;

        return array("scheme" => $scheme, "netloc" => $netloc, "path" => $path);
    }

    private static function oauth_hmacsha1($key, $data) {
        return base64_encode(self::hmacsha1($key, $data));
    }

    private static function hmacsha1($key,$data) {
        $blocksize=64;
        $hashfunc='sha1';
        if (strlen($key)>$blocksize)
            $key=pack('H*', $hashfunc($key));
        $key=str_pad($key,$blocksize,chr(0x00));
        $ipad=str_repeat(chr(0x36),$blocksize);
        $opad=str_repeat(chr(0x5c),$blocksize);
        $hmac = pack(
                    'H*',$hashfunc(
                        ($key^$opad).pack(
                            'H*',$hashfunc(
                                ($key^$ipad).$data
                            )
                        )
                    )
                );
        return $hmac;
    }
}
?>
