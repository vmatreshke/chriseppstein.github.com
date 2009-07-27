<?php
// require the twitter library
require "twitter.lib.php";

$saved_tweet = "./tweet.txt";
if (file_exists($saved_tweet)){
  // if the last read tweet is over 10 minutes old, check for a new tweet.
  if(time() - filemtime($saved_tweet) > 600){
    $tweet = getLatestTweet();
  }
}

if($tweet){
  //if there is a new tweet, save it to tweet.txt
  $handle = fopen($saved_tweet, 'w') or die("can't open file");
  fwrite($handle, $tweet);
  fclose($handle);
}else{
  //if there isn't a new tweet read from tweet.txt
  $handle = fopen($saved_tweet, 'r') or die("can't open file");
  $tweet = fread($handle, filesize($saved_tweet));
  fclose($handle);
}
//echo the tweet for javascript to read in
echo $tweet;

//auto link urls, @usernames, and #hashtags
function autolink($text)
{
    $ret = ' ' . $text;
    $ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t<]*)#ise", "'\\1<a href=\"\\2\" >\\2</a>'", $ret);
    $ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#ise", "'\\1<a href=\"http://\\2\" >\\2</a>'", $ret);
    $ret = preg_replace("#(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
    $ret = preg_replace("/@([^()#@\s\,]+)/", "<a href='http://twitter.com/$1'>@$1</a>", $ret);
    $ret = preg_replace('/(^|\s)#(\w+)/','\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>', $ret);
    $ret = substr($ret, 1);
    return($ret);
}
function getLatestTweet(){
  //you'll need to create a credentials.php file in the same directory with the following:
  //$username = "your_user_name";
  //$password = "your_password";
  // 
  include "credentials.php";
  // initialize the twitter class
  $twitter = new Twitter($username, $password);
  // fetch your profile in xml format
  $xml = $twitter->getUserTimeline();
  $twitter_status = new SimpleXMLElement($xml);
  foreach($twitter_status->status as $status){
    if($status->in_reply_to_status_id == '') {
      return autolink($status->text);
    }
  }
}
?>