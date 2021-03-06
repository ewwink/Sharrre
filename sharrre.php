<?php
header('content-type: application/json');
//Sharrre by Julien Hany
$json = array('url' => '', 'count' => 0);
$myDomain = "www.arkantv.com";
if(!isset($_GET['url']) or !isset($_GET['type']))
  exit(0);
$url = preg_replace('/#.*/', "", $_GET['url']);
$json['url'] = $url;
$url = urlencode($url);
$type = urlencode($_GET['type']);
$check_url = parse_url($_GET['url']);
if(!preg_match('#^$myDomain$#', $check_url['host']))
  exit(0);
if(filter_var($_GET['url'], FILTER_VALIDATE_URL))
{
  if($type == 'googlePlus')
  { //source http://www.helmutgranda.com/2011/11/01/get-a-url-google-count-via-php/
    if(!file_exists("arkan-cache"))
    {
      mkdir("arkan-cache");
    }
    $find = array(":", "/", "?", "#",);
    $cache_file = "arkan-cache/z-sharrre-".str_replace($find, "", $_GET['url']);
    if(strlen($cache_file) > 250)
    {
      $json['count'] = 0;
    }
    // cache for 5 minutes
    if(file_exists($cache_file) && (filemtime($cache_file) > (time()-60*5)))
    {
      $file = file_get_contents($cache_file);
      $json['count'] = $file;
    }
    else
    {
      $contents = parse('https://plusone.google.com/u/0/_/+1/fastbutton?url='.$url.'&count=true');
      preg_match('/window\.__SSR = {c: ([\d]+)/', $contents, $matches);
      if(isset($matches[0]))
      {
        $json['count'] = (int) str_replace('window.__SSR = {c: ', '', $matches[0]);
        file_put_contents($cache_file, $json['count'], LOCK_EX);
      }
      else
      {
        file_put_contents($cache_file, "0", LOCK_EX);
      }
    }
  }
  else
    if($type == 'stumbleupon')
    {
      $content = parse("http://www.stumbleupon.com/services/1.01/badge.getinfo?url=$url");
      $result = json_decode($content);
      if(isset($result->result->views))
      {
        $json['count'] = $result->result->views;
      }
    }
}
echo str_replace('\\/', '/', json_encode($json));

function parse($encUrl)
{
  $options = array(CURLOPT_RETURNTRANSFER => true, // return web page
  CURLOPT_HEADER => false, // don't return headers
  CURLOPT_FOLLOWLOCATION => true, // follow redirects
  CURLOPT_ENCODING => "", // handle all encodings
  CURLOPT_USERAGENT => 'sharrre', // who am i
  CURLOPT_AUTOREFERER => true, // set referer on redirect
  CURLOPT_CONNECTTIMEOUT => 5, // timeout on connect
  CURLOPT_TIMEOUT => 10, // timeout on response
  CURLOPT_MAXREDIRS => 3, // stop after 10 redirects
  CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => false,);
  $ch = curl_init();
  $options[CURLOPT_URL] = $encUrl;
  curl_setopt_array($ch, $options);
  $content = curl_exec($ch);
  $err = curl_errno($ch);
  $errmsg = curl_error($ch);
  curl_close($ch);
  if($errmsg != '' || $err != '')
  {
/*print_r($errmsg);
print_r($errmsg);*/
  }
  return $content;
}

?>
