<?php
define("BOT_KEY", "[BOT_KEY]");
define("SECRET_KEY", "[SECRET_KEY]");
if(!isset($_GET["t"]) || $_GET["t"] != SECRET_KEY)
{
    //avoid invalid access
    echo "Error!";
    die;
}
function TelegramAPI($post_fields, $method)
{
       $postdata = http_build_query($post_fields);
	$opts = array("http" =>
	    array(
		"method"  => "POST",
		"header"  => "Content-type: application/x-www-form-urlencoded",
		"content" => $postdata
	    )
	);
	$context  = stream_context_create($opts);
	$result = file_get_contents("https://api.telegram.org/bot".BOT_KEY."/$method", false, $context);
}
function Forward_Telegram_Message($chatId, $message_id)
{
	$data = array(
		"chat_id" => $chatId,
		"from_chat_id" => $chatId,
		"message_id" => $message_id
	    );
	TelegramAPI($data, "forwardMessage");
}
function Send_Telegram_Photo($chatId, $photo_id, $caption)
{
       $data = array(
		"chat_id" => $chatId,
		"photo" => $photo_id
	    );
        if($caption != null)
            $data ["caption"] = $caption;
	TelegramAPI($data, "sendPhoto");
}
function Send_Telegram_Video($chatId, $video_id, $caption)
{
       $data = array(
		"chat_id" => $chatId,
		"video" => $video_id
	    );
        if($caption != null)
            $data ["caption"] = $caption;
	TelegramAPI($data, "sendVideo");
}
function Send_Telegram_Audio($chatId, $audio_id, $caption)
{
       $data = array(
		"chat_id" => $chatId,
		"audio" => $audio_id
	    );
        if($caption != null)
            $data ["caption"] = $caption;
	TelegramAPI($data, "sendAudio");
}
function Send_Telegram_File($chatId, $file_id, $caption)
{
	$data = array(
		"chat_id" => $chatId,
		"document" => $file_id
	    );
        if($caption != null)
            $data ["caption"] = $caption;
	TelegramAPI($data, "sendDocument");
}
function Send_Telegram_Message($chatId, $text)
{
	$data = array(
		"text" => $text,
		"chat_id" => $chatId
	    );
	TelegramAPI($data, "sendMessage");
}
function Send_Telegram_Message_CURL($chatId, $text)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"https://api.telegram.org/bot".BOT_KEY."/sendMessage");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,"text=$text&chat_id=$chatId");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$server_output = curl_exec ($ch);
	curl_close ($ch);
	return $server_output;
}
//Extract posted data
$data = json_decode(file_get_contents("php://input"));
//Find text of message
$messageText = $data->message->text;
$messageId   = $data->message->message_id;
//Find Chat id
$chatId      = $data->message->chat->id;
//Reply to Telegram: It's OK!
echo json_encode(array("ok"=> true));
if(isset($data->message->reply_to_message))
{
   //If the message is reply to a previous message -> just change the caption
   $caption = $data->message->text;
   $data->message = $data->message->reply_to_message;
   $data->message->caption = $caption;
}
if(isset($data->message->document))
{
    $file_id = $data->message->document->file_id;
    if(isset($data->message->caption))
       $caption = $data->message->caption;
    else
       $caption = null;
    Send_Telegram_File($chatId,$file_id,$caption);
}
else if(isset($data->message->photo))
{
    $file_id = $data->message->photo[0]->file_id;
    if(isset($data->message->caption))
       $caption = $data->message->caption;
    else
       $caption = null;
    Send_Telegram_Photo($chatId,$file_id, $caption);
}
else if(isset($data->message->video))
{
    $file_id = $data->message->video->file_id;
    if(isset($data->message->caption))
       $caption = $data->message->caption;
    else
       $caption = null;
    Send_Telegram_Video($chatId,$file_id, $caption);
}
else if(isset($data->message->audio))
{
    $file_id = $data->message->audio->file_id;
    if(isset($data->message->caption))
       $caption = $data->message->caption;
    else
       $caption = null;
    Send_Telegram_Audio($chatId,$file_id, $caption);
}
else
{
    Send_Telegram_Message($chatId,$messageText);
}
?>
