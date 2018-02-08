<?php

require_once "functions.php";

__INITIATE_CRON__:

$webClient=curl_init();
prepareWebClient($webClient);
loadAllThreads();
foreach($data->threads as $thread){

    if(!isset($thread->lastActivityAt) || $thread->lastActivityAt==""){
        $lastPost=getRecentPost($webClient,$thread->url);
        $thread->lastActivityAt=$lastPost->at;
    }

    if(isOlderThan24Hour($thread->lastActivityAt)){
        $lastPost=getRecentPost($webClient,$thread->url);

        if(isOlderThan24Hour($lastPost->at)){
            login($webClient);

            if(isset($data->settings->lastPostAt)){
                $secondsFromLastPost=intval((time() - $data->settings->lastPostAt) / 1000);
                if(minPostInterval(getUserActivity($webClient)) > $secondsFromLastPost){
                    # User is not eligible to post reply to his thread.
                    # Spo, it's better to break the whole operation for now.
                    # We can take care of BUMP'ing later on next run.
                    break;
                }
            }
            $userActivity=getUserActivity($webClient);
            $threadPage=getThreadPage($webClient,$thread->url);
            if(isset($threadPage->options["Reply"])){

                if(isset($thread->lastBumpURL) && $thread->lastBumpURL!=""){
                    deleteBump($webClient,$thread->lastBumpURL);
                }

                $bump=bumpIt($webClient,$threadPage->options["Reply"]);
                if($bump!==false){
                    $thread->lastBumpURL=$bump->buttons["permalink"];
                    $thread->lastActivityAt=time();
                    $data->settings->lastPostAt=time();
                }
                logout($webClient);
            }
            else
                echo "No reply button";
        }else{
            $thread->lastActivityAt=$lastPost->at;
        }
    }
    else
        echo "Your not older than 24hour";

}
saveData();

__END_CRON__:

?>
