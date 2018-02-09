<?php
/**
 * Copyright 2018 Redkeet ISC License
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Created by Maxime Allanic <maxime.allanic@redkeet.com> at 09/02/2018
 */

class Post {
    private $service;
    private $dom;

    function __construct(BitcoinTalk $service, $dom) {
        $this->service = $service;
        $this->dom = $dom;
    }

    public function getId() {

    }

    public function getUsername() {
        $userblock = $this->dom->find('td.poster_info b a');
        if (count($userblock) === 0)
            return null;
        return $userblock[0]->innertext();
    }

    public function getQuoted() {
        $content = $this->dom->find('td')[1];
        $td = $content->find('table td');
        $url = $td[count($td) - 1]->find('div a')[0]->getAttribute('href');
        $html = $this->service->request($url);
        return $html->find('textarea.editor')[0]->innertext();
    }
}

?>