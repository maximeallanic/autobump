<?php
/**
 * Copyright 2018 Redkeet ISC License
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Created by Maxime Allanic <maxime.allanic@redkeet.com> at 09/02/2018
 */


require_once('src/Bitcointalk.php');

try {
    $data = json_decode(file_get_contents('./data.json'));

    $users = [];

    foreach ($data->users as $user) {
        $users[$user->username] = new Bitcointalk($user);
    }

    $threads = [];
    foreach ($data->threads as $thread) {
        foreach ($thread->messages as $message) {
            echo "Sending message from $message->from: ";

            if (property_exists($message, 'sended') && $message->sended) {
                echo "Already sent\n";
            }
            else {
                $t = $users[$message->from]->getThread($thread->id);
                if (property_exists($message, 'quottedFrom')) {
                    $quottedPost = $t->getLastPostFromUser($message->quottedFrom);
                    if ($quottedPost)
                        $t->postFrom($quottedPost, $message->message);
                    else
                        throw new Error('No quotted post from $message->quottedFrom');
                }
                else
                    $t->post($message->message);

                $message->sended = true;

                file_put_contents('./data.json', json_encode($data, JSON_PRETTY_PRINT));

                echo "Sended\n";
                return ;
            }
        }
    }
} catch (Exception $e) {
    echo $e->getMessage()."\n";
}
?>