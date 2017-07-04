<?php header('Content-Type: application/json'); 

$path = dirname($_SERVER["SCRIPT_URI"]);
$array = array();
$array['key'] = "krim-hipchat-insight";
$array['name'] = "Link Insight";
$array['description'] = "A simple add-on showing cool stuff";
$array['vendor'] = array("name"=>"Krim","url"=>"http://krim.me");
$array['links'] = array("self"=>"");
$array['capabilities']["hipchatApiConsumer"]["scopes"] = array("send_notification","view_room");


$hook = array("event"=>"room_message","url"=>$path."/reply.php","pattern"=>"(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?","name"=>"URL Parser","authentication"=>"jwt");
$array['capabilities']["webhook"][] = $hook;
echo json_encode($array);