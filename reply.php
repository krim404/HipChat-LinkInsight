<?php
$allowed = array("","php","html","htm");
$disallowed = array("youtube","twitter","imgur","github");

function checkValues($value)
{
	$value = trim($value);
	if (get_magic_quotes_gpc()) 
	{
		$value = stripslashes($value);
	}
	$value = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES)));
	$value = strip_tags($value);
	$value = htmlspecialchars($value);
	return $value;
}	

function sanitize($text)
{
	return htmlentities(strip_tags($text));
}

function fetch_record($path)
{
	$file = fopen($path, "r"); 
	if (!$file)
	{
		exit();
	} 
	$data = '';
	while (!feof($file))
	{
		$data .= fgets($file, 1024);
	}
	return $data;
}


$json = file_get_contents('php://input');
$json = json_decode($json);

if(is_object($json) && isset($json->item->message->message))
{
	$text = $json->item->message->message;
	preg_match("/(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.-]*)*\/?/", $text,$treffer);
	if(is_array($treffer) && strlen($treffer[0]) > 5)
	{
		$alr = true;
		foreach($disallowed as $d)
		{
			if(strpos($treffer[2], $d) !== false)
				$alr = false;
		}
		$url = $treffer[0];
		$url = checkValues($url);
		$ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
		if(in_array($ext,$allowed) && $alr)
		{
			$string = fetch_record($url);
			/// fetch title
			$title_regex = "/<title>(.+)<\/title>/i";
			preg_match_all($title_regex, $string, $title, PREG_PATTERN_ORDER);
			$url_title = $title[1];
			
			/// fetch decription
			$tags = get_meta_tags($url);
			
			// fetch images
			$image_regex = '/<img[^>]*'.'src=[\"|\'](.*)[\"|\']/Ui';
			preg_match_all($image_regex, $string, $img, PREG_PATTERN_ORDER);
			$images_array = $img[1];
			
			$big = 0;
			$img = "";
			
			for ($i=0;$i<=sizeof($images_array);$i++)
			{
				if(@$images_array[$i])
				{
					if(@getimagesize(@$images_array[$i]))
					{
						list($width, $height, $type, $attr) = getimagesize(@$images_array[$i]);
						if($width >= 50 && $height >= 50 )
						{
							if($width+$height > $big)
							{
								$img = $images_array[$i];
								$big = $width+$height;
							}
						}
					}
				}
			}
			$titel = strip_tags($url_title[0]);
			$desc = sanitize($tags['description']);
			$sub = sanitize(parse_url($url,PHP_URL_HOST));
			if(!$sub) $sub = $url;
			
			$msg = '<table><tr><td>';
			if($img)
				$msg .= '<img src="'.$img.'" width="100" />';
			$msg .= '
			</td><td><b>
				'.$titel.'
			</b><br>
			<i>
				<a href="'.$url.'">'.$sub.'</a>
			</i></td></tr></table>
				'.$desc.'
			';
		
			$array = array('color'=>'purple','message'=>$msg,'notify'=>false,'message_format'=>'html');
			header('Content-Type: application/json');
			echo json_encode($array);
		}
	}
}
