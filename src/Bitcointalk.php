<?php
/**
 * Copyright 2018 Redkeet ISC License
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Created by Maxime Allanic <maxime.allanic@redkeet.com> at 09/02/2018
 */

require_once('./src/Thread.php');
require_once('./simple_html_dom.php');

class Bitcointalk {
    private $webClient;

    function __construct($options) {
        $this->webClient = curl_init();

        curl_setopt($this->webClient, CURLOPT_HEADER, true);
        curl_setopt($this->webClient, CURLOPT_NOBODY, false);
        curl_setopt($this->webClient, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->webClient, CURLOPT_COOKIEJAR, "$options->username.cookie");
        $cookie = json_decode(json_encode($options->cookie));

        array_walk($cookie, function (&$value, $key) {
            $value = "$key=$value";
        });

        $cookie = implode('; ', get_object_vars($cookie));

        curl_setopt($this->webClient, CURLOPT_COOKIE, "cookiename=0; $cookie");
        curl_setopt($this->webClient, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
        curl_setopt($this->webClient, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->webClient, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->webClient, CURLOPT_FOLLOWLOCATION, true);
    }

    function request($url, $method = "GET", $data = []) {
        if (!preg_match("/^https:\/\/bitcointalk\.org\//", $url))
            $url = "https://bitcointalk.org/$url";
        curl_setopt($this->webClient, CURLOPT_URL, $url);
        curl_setopt($this->webClient, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->webClient, CURLOPT_FOLLOWLOCATION, true);

        if ($method === 'POST') {
            curl_setopt($this->webClient, CURLOPT_POST, 1);
            curl_setopt($this->webClient, CURLOPT_POSTFIELDS, $data);
        }

        $html = curl_exec($this->webClient);

        return str_get_html($html);
    }

    function getThread($id) {
        $dom = $this->request("index.php?topic=$id");
        return new Thread($this, $dom);
    }
}

?>