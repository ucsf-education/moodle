<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Mediasite plugin for Moodle.
 *
 * @package mod_mediasite
 * @copyright Sonic Foundry 2017  {@link http://sonicfoundry.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$oauthlastcomputedsignature = false;

class MediasiteOAuthException extends Exception {
}

class MediasiteOAuthConsumer {
    public $key;
    public $secret;

    public function __construct($key, $secret, $callbackurl = null) {
        $this->key = $key;
        $this->secret = $secret;
        $this->callbackurl = $callbackurl;
    }

    public function __toString() {
        return "MediasiteOAuthConsumer[key=$this->key,secret=$this->secret]";
    }
}

class MediasiteOAuthToken {
    public $key;
    public $secret;

    public function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }

    public function to_string() {
        return "oauth_token=" .
        MediasiteOAuthUtil::urlencode_rfc3986($this->key) .
        "&oauth_token_secret=" .
        MediasiteOAuthUtil::urlencode_rfc3986($this->secret);
    }

    public function __toString() {
        return $this->to_string();
    }
}

class MediasiteOAuthSignatureMethod {
    public function check_signature(&$request, $consumer, $token, $signature) {
        $built = $this->build_signature($request, $consumer, $token);
        return $built == $signature;
    }
}

class MediasiteOAuthSignatureMethod_HMAC_SHA1 extends MediasiteOAuthSignatureMethod {
    public function get_name() {
        return "HMAC-SHA1";
    }

    public function build_signature($request, $consumer, $token) {
        global $oauthlastcomputedsignature;
        $oauthlastcomputedsignature = false;

        $basestring = $request->get_signature_base_string();
        $request->basestring = $basestring;

        $keyparts = array(
            $consumer->secret,
             ($token) ? $token->secret : ""
        );

        $keyparts = MediasiteOAuthUtil::urlencode_rfc3986($keyparts);
        $key = implode('&', $keyparts);

        $computedsignature = base64_encode(hash_hmac('sha1', $basestring, $key, true));
        $oauthlastcomputedsignature = $computedsignature;
        return $computedsignature;
    }

}

class MediasiteOAuthSignatureMethod_PLAINTEXT extends MediasiteOAuthSignatureMethod {
    public function get_name() {
        return "PLAINTEXT";
    }

    public function build_signature($request, $consumer, $token) {
        $sig = array(
            MediasiteOAuthUtil::urlencode_rfc3986($consumer->secret)
        );

        if ($token) {
            array_push($sig, MediasiteOAuthUtil::urlencode_rfc3986($token->secret));
        } else {
            array_push($sig, '');
        }

        $raw = implode("&", $sig);
        $request->basestring = $raw;

        return MediasiteOAuthUtil::urlencode_rfc3986($raw);
    }
}

class MediasiteOAuthSignatureMethod_RSA_SHA1 extends MediasiteOAuthSignatureMethod {
    public function get_name() {
        return "RSA-SHA1";
    }

    protected function fetch_public_cert(&$request) {
        throw Exception("fetch_public_cert not implemented");
    }

    protected function fetch_private_cert(&$request) {
        throw Exception("fetch_private_cert not implemented");
    }

    public function build_signature(&$request, $consumer, $token) {
        $basestring = $request->get_signature_base_string();
        $request->basestring = $basestring;

        $cert = $this->fetch_private_cert($request);

        $privatekeyid = openssl_get_privatekey($cert);

        $ok = openssl_sign($basestring, $signature, $privatekeyid);

        openssl_free_key($privatekeyid);

        return base64_encode($signature);
    }

    public function check_signature(&$request, $consumer, $token, $signature) {
        $decodedsig = base64_decode($signature);

        $basestring = $request->get_signature_base_string();

        $cert = $this->fetch_public_cert($request);

        $publickeyid = openssl_get_publickey($cert);

        $ok = openssl_verify($basestring, $decodedsig, $publickeyid);

        openssl_free_key($publickeyid);

        return $ok == 1;
    }
}

class MediasiteOAuthRequest {
    private $parameters;
    private $httpmethod;
    private $httpurl;
    public $basestring;
    public static $version = '1.0';
    public static $postinput = 'php://input';

    public function __construct($httpmethod, $httpurl, $parameters = null) {
        @$parameters or $parameters = array();
        $this->parameters = $parameters;
        $this->httpmethod = $httpmethod;
        $this->httpurl = $httpurl;
    }

    public static function from_consumer_and_token($consumer, $token, $httpmethod, $httpurl, $parameters = null) {
        @$parameters or $parameters = array();
        $defaults = array(
            "oauth_version" => self::$version,
            "oauth_nonce" => self::generate_nonce(),
            "oauth_timestamp" => self::generate_timestamp(),
            "oauth_consumer_key" => $consumer->key
        );
        if ($token) {
            $defaults['oauth_token'] = $token->key;
        }

        $parameters = array_merge($defaults, $parameters);

        $parts = parse_url($httpurl);
        if (isset($parts['query'])) {
            $qparms = MediasiteOAuthUtil::parse_parameters($parts['query']);
            $parameters = array_merge($qparms, $parameters);
        }

        return new MediasiteOAuthRequest($httpmethod, $httpurl, $parameters);
    }

    public function set_parameter($name, $value, $allowduplicates = true) {
        if ($allowduplicates && isset($this->parameters[$name])) {
            if (is_scalar($this->parameters[$name])) {
                $this->parameters[$name] = array($this->parameters[$name]);
            }

            $this->parameters[$name][] = $value;
        } else {
            $this->parameters[$name] = $value;
        }
    }

    public function get_parameter($name) {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    public function get_parameters() {
        return $this->parameters;
    }

    public function unset_parameter($name) {
        unset($this->parameters[$name]);
    }

    public function get_signable_parameters() {
        $params = $this->parameters;

        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }

        return MediasiteOAuthUtil::build_http_query($params);
    }

    /**
     * Returns the base string of this request
     *
     * The base string defined as the method, the url
     * and the parameters (normalized), each urlencoded
     * and the concated with &.
     */
    public function get_signature_base_string() {
        $parts = array(
            $this->get_normalized_http_method(),
            $this->get_normalized_http_url(),
            $this->get_signable_parameters()
        );

        $parts = MediasiteOAuthUtil::urlencode_rfc3986($parts);

        return implode('&', $parts);
    }

    /**
     * just uppercases the http method
     */
    public function get_normalized_http_method() {
        return strtoupper($this->httpmethod);
    }

    /**
     * parses the url and rebuilds it to be
     * scheme://host/path
     */
    public function get_normalized_http_url() {
        $parts = parse_url($this->httpurl);

        $port = @$parts['port'];
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $path = @$parts['path'];

        $port or $port = ($scheme == 'https') ? '443' : '80';

        if (($scheme == 'https' && $port != '443') || ($scheme == 'http' && $port != '80')) {
            $host = "$host:$port";
        }
        return "$scheme://$host$path";
    }

    /**
     * builds a url usable for a GET request
     */
    public function to_url() {
        $postdata = $this->to_postdata();
        $out = $this->get_normalized_http_url();
        if ($postdata) {
            $out .= '?'.$postdata;
        }
        return $out;
    }

    /**
     * builds the data one would send in a POST request
     */
    public function to_postdata() {
        return MediasiteOAuthUtil::build_http_query($this->parameters);
    }

    /**
     * builds the Authorization: header
     */
    public function to_header() {
        $out = 'Authorization: OAuth realm=""';
        $total = array();
        foreach ($this->parameters as $k => $v) {
            if (substr($k, 0, 5) != "oauth") {
                continue;
            }
            if (is_array($v)) {
                throw new MediasiteOAuthException('Arrays not supported in headers');
            }
            $out .= ',' .
            MediasiteOAuthUtil::urlencode_rfc3986($k) .
            '="' .
            MediasiteOAuthUtil::urlencode_rfc3986($v) .
            '"';
        }
        return $out;
    }

    public function __toString() {
        return $this->to_url();
    }

    public function sign_request($signaturemethod, $consumer, $token) {
        $this->set_parameter("oauth_signature_method", $signaturemethod->get_name(), false);
        $signature = $this->build_signature($signaturemethod, $consumer, $token);
        $this->set_parameter("oauth_signature", $signature, false);
    }

    public function build_signature($signaturemethod, $consumer, $token) {
        $signature = $signaturemethod->build_signature($this, $consumer, $token);
        return $signature;
    }

    /**
     * util function: current timestamp
     */
    private static function generate_timestamp() {
        return time();
    }

    /**
     * util function: current nonce
     */
    private static function generate_nonce() {
        $mt = microtime();
        $rand = mt_rand();

        return md5($mt.$rand);
    }
}

class MediasiteOAServer {
    protected $timestampthreshold = 300;
    protected $version = 1.0;
    protected $signaturemethods = array();
    protected $datastore;

    public function __construct($datastore) {
        $this->datastore = $datastore;
    }

    public function add_signature_method($signaturemethod) {
        $this->signaturemethods[$signaturemethod->get_name()] = $signaturemethod;
    }

    /**
     * process a request_token request
     * returns the request token on success
     */
    public function fetch_request_token(&$request) {
        $this->get_version($request);

        $consumer = $this->get_consumer($request);

        $token = null;

        $this->check_signature($request, $consumer, $token);

        $newtoken = $this->datastore->new_request_token($consumer);

        return $newtoken;
    }

    /**
     * process an access_token request
     * returns the access token on success
     */
    public function fetch_access_token(&$request) {
        $this->get_version($request);

        $consumer = $this->get_consumer($request);

        $token = $this->get_token($request, $consumer, "request");

        $this->check_signature($request, $consumer, $token);

        $newtoken = $this->datastore->new_access_token($token, $consumer);

        return $newtoken;
    }

    /**
     * verify an api call, checks all the parameters
     */
    public function verify_request(&$request) {
        global $oauthlastcomputedsignature;
        $oauthlastcomputedsignature = false;
        $this->get_version($request);
        $consumer = $this->get_consumer($request);
        $token = $this->get_token($request, $consumer, "access");
        $this->check_signature($request, $consumer, $token);
        return array(
            $consumer,
            $token
        );
    }

    /**
     * version 1
     */
    private function get_version(&$request) {
        $version = $request->get_parameter("oauth_version");
        if (!$version) {
            $version = 1.0;
        }
        if ($version && $version != $this->version) {
            throw new MediasiteOAuthException("OAuth version '$version' not supported");
        }
        return $version;
    }

    /**
     * figure out the signature with some defaults
     */
    private function get_signature_method(&$request) {
        $signaturemethod = @ $request->get_parameter("oauth_signature_method");
        if (!$signaturemethod) {
            $signaturemethod = "PLAINTEXT";
        }
        if (!in_array($signaturemethod, array_keys($this->signaturemethods))) {
            throw new MediasiteOAuthException("Signature method '$signaturemethod' not supported " .
            "try one of the following: " .
            implode(", ", array_keys($this->signaturemethods)));
        }
        return $this->signaturemethods[$signaturemethod];
    }

    /**
     * try to find the consumer for the provided request's consumer key
     */
    private function get_consumer(&$request) {
        $consumerkey = @ $request->get_parameter("oauth_consumer_key");
        if (!$consumerkey) {
            throw new MediasiteOAuthException("Invalid consumer key");
        }

        $consumer = $this->datastore->lookup_consumer($consumerkey);
        if (!$consumer) {
            throw new MediasiteOAuthException("Invalid consumer");
        }

        return $consumer;
    }

    /**
     * try to find the token for the provided request's token key
     */
    private function get_token(&$request, $consumer, $tokentype = "access") {
        $tokenfield = @ $request->get_parameter('oauth_token');
        if (!$tokenfield) {
            return false;
        }
        $token = $this->datastore->lookup_token($consumer, $tokentype, $tokenfield);
        if (!$token) {
            throw new MediasiteOAuthException("Invalid $tokentype token: $tokenfield");
        }
        return $token;
    }

    /**
     * all-in-one function to check the signature on a request
     * should guess the signature method appropriately
     */
    private function check_signature(&$request, $consumer, $token) {
        global $oauthlastcomputedsignature;
        $oauthlastcomputedsignature = false;

        $timestamp = @ $request->get_parameter('oauth_timestamp');
        $nonce = @ $request->get_parameter('oauth_nonce');

        $this->check_timestamp($timestamp);
        $this->check_nonce($consumer, $token, $nonce, $timestamp);

        $signaturemethod = $this->get_signature_method($request);

        $signature = $request->get_parameter('oauth_signature');
        $validsig = $signaturemethod->check_signature($request, $consumer, $token, $signature);

        if (!$validsig) {
            $extext = "Invalid signature";
            if ($oauthlastcomputedsignature) {
                $extext = $extext . " ours= $oauthlastcomputedsignature yours=$signature";
            }
            throw new MediasiteOAuthException($extext);
        }
    }

    /**
     * check that the timestamp is new enough
     */
    private function check_timestamp($timestamp) {
        $now = time();
        if ($now - $timestamp > $this->timestampthreshold) {
            throw new MediasiteOAuthException("Expired timestamp, yours $timestamp, ours $now");
        }
    }

    /**
     * check that the nonce is not repeated
     */
    private function check_nonce($consumer, $token, $nonce, $timestamp) {
        $found = $this->datastore->lookup_nonce($consumer, $token, $nonce, $timestamp);
        if ($found) {
            throw new MediasiteOAuthException("Nonce already used: $nonce");
        }
    }

}

class MediasiteOADataStore {
    protected function lookup_consumer($consumerkey) {
    }

    protected function lookup_token($consumer, $tokentype, $token) {
    }

    protected function lookup_nonce($consumer, $token, $nonce, $timestamp) {
    }

    protected function new_request_token($consumer) {
    }

    protected function new_access_token($token, $consumer) {
    }

}

class MediasiteOAuthUtil {
    public static function urlencode_rfc3986($input) {
        if (is_array($input)) {
            return array_map(array(
                'MediasiteOAuthUtil',
                'urlencode_rfc3986'
            ), $input);
        } else {
            if (is_scalar($input)) {
                return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode($input)));
            } else {
                return '';
            }
        }
    }

    public static function urldecode_rfc3986($string) {
        return urldecode($string);
    }

    public static function split_header($header, $onlyallowoauthparameters = true) {
        $pattern = '/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/';
        $offset = 0;
        $params = array();
        while (preg_match($pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
            $match = $matches[0];
            $headername = $matches[2][0];
            $headercontent = (isset($matches[5])) ? $matches[5][0] : $matches[4][0];
            if (preg_match('/^oauth_/', $headername) || !$onlyallowoauthparameters) {
                $params[$headername] = self::urldecode_rfc3986($headercontent);
            }
            $offset = $match[1] + strlen($match[0]);
        }

        if (isset($params['realm'])) {
            unset($params['realm']);
        }

        return $params;
    }

    public static function get_headers() {
        if (function_exists('apache_request_headers')) {
            return apache_request_headers();
        }
        $out = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                $out[$key] = $value;
            }
        }
        return $out;
    }

    public static function parse_parameters($input) {
        if (!isset($input) || !$input) {
            return array();
        }

        $pairs = explode('&', $input);

        $parsedparameters = array();
        foreach ($pairs as $pair) {
            $split = explode('=', $pair, 2);
            $parameter = self::urldecode_rfc3986($split[0]);
            $value = isset($split[1]) ? self::urldecode_rfc3986($split[1]) : '';

            if (isset($parsedparameters[$parameter])) {

                if (is_scalar($parsedparameters[$parameter])) {
                    $parsedparameters[$parameter] = array(
                        $parsedparameters[$parameter]
                    );
                }

                $parsedparameters[$parameter][] = $value;
            } else {
                $parsedparameters[$parameter] = $value;
            }
        }
        return $parsedparameters;
    }

    public static function build_http_query($params) {
        if (!$params) {
            return '';
        }

        $keys = self::urlencode_rfc3986(array_keys($params));
        $values = self::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);

        uksort($params, 'strcmp');

        $pairs = array();
        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                natsort($value);
                foreach ($value as $duplicatevalue) {
                    $pairs[] = $parameter . '=' . $duplicatevalue;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }
        return implode('&', $pairs);
    }
}