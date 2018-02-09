<?php
/**
 * Copyright 2018 Redkeet ISC License
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Created by Maxime Allanic <maxime.allanic@redkeet.com> at 09/02/2018
 */

 require_once('./src/Post.php');

class Thread {
    private $service;
    private $dom;

    function __construct($service, $dom) {
        $this->service = $service;
        $this->dom = $dom;
    }

    public function getTitle() {
        $name = $this->dom->find('#top_subject')[0]->innertext();
        if (preg_match("/^\s*Topic: (.*)$/", $name, $matches))
            return trim($matches[1]);
        return null;
    }

    public function getAllPosts() {
        $prevnext = $this->dom->find('td.middletext')[0];
        $a = $prevnext->find('a');
        if (count($a) > 0) {
            $toLastPage = $a[count($a) - 1]->getAttribute('href');
            $html = $this->service->request($toLastPage);
        }
        else
            $html = $this->dom;

        $posts = [];
        foreach ($html->find('form > table > tbody > tr') as $post) {
            $post = new Post($this->service, $post);
            if ($post)
                array_push($posts, $post);
        }
        return $posts;
    }

    public function getLastPostFromUser($username) {
        $posts = $this->getAllPosts();
        $posts = array_reverse($posts);
        foreach ($posts as $post)
            if ($post->getUsername() == $username)
                return $post;
        return null;
    }

    public function postFrom($post, $message) {
        $quoted = $post->getQuoted();
        $message = "$quoted\n$message";
        return $this->post($message);
    }

    public function post($message) {
        $replyButton = $this->dom->find('#bodyarea table td.maintab_back a')[0];
        $html = $this->service->request($replyButton->getAttribute('href'));

        $postInput = $html->find("#postmodify input");
        $postSelect = $html->find("#postmodify select");
        $form = $html->find("#postmodify")[0];
        $formSubmitURL = $form->getAttribute("action");

        $postData=array(
            "topic" => $form->find('input[name="topic"]')[0]->getAttribute("value"),
            "subject" => $form->find('input[name="subject"]')[0]->getAttribute("value"),
            "icon" => "xx",
            "message" => $message,
            "notify" => "1",
            "do_watch" => "0",
            "do_watch" => "1",
            "goback" => "1",
            "post" => "Post",
            "num_replies" => $form->find('input[name="num_replies"]')[0]->getAttribute("value"),
            "additional_options" => $form->find('input[name="additional_options"]')[0]->getAttribute("value"),
            "sc" => $form->find('input[name="sc"]')[0]->getAttribute("value"),
            "seqnum" => $form->find('input[name="seqnum"]')[0]->getAttribute("value")
        );
        $html = $this->service->request($formSubmitURL, 'POST', $postData);
        $errormessage = $html->find('#bodyarea table tr')[1]->find('td')[0]->innertext();
        if ($errormessage
            && preg_match("/The last posting from your IP was less than 360 seconds ago. Please try again later. The thing you were trying to post was saved as a/", $errormessage)) {
                throw new Exception('Wait 360second to post');
        }
    }
}

?>