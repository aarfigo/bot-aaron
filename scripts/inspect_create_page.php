<?php
$base = 'http://127.0.0.1:8000';
$cookie = __DIR__.'/tmp_cookie_inspect';
if(!is_dir(__DIR__.'/')) mkdir(__DIR__,0777,true);
function http_get($path,$cookie){global $base; $ch=curl_init($base.$path);curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie);curl_setopt($ch, CURLOPT_COOKIEJAR,$cookie);$res=curl_exec($ch);curl_close($ch);return $res;}
function http_post($path,$data,$cookie){global $base; $ch=curl_init($base.$path);curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie);curl_setopt($ch, CURLOPT_COOKIEJAR,$cookie);curl_setopt($ch, CURLOPT_POST,true);curl_setopt($ch, CURLOPT_POSTFIELDS,$data);$res=curl_exec($ch);curl_close($ch);return $res;}

// login
$html = http_get('/login',$cookie);
if(preg_match('/name="_token" value="([^"]+)"/',$html,$m)) $token=$m[1]; else {echo "No CSRF token on login\n"; exit(1);} 
http_post('/login', ['_token'=>$token,'email'=>'mesero@example.com','password'=>'password'],$cookie);

$create = http_get('/staff/orders/create',$cookie);
file_put_contents(__DIR__.'/create_page.html',$create);
// count occurrences of product-row
$count = preg_match_all('/class="[^"]*product-row[^"]*"/',$create,$matches);
// also check if items loop produced any product names
preg_match_all('/product-row.*?>(.*?)</s',$create,$m2);

echo "Saved to scripts/create_page.html\n";
echo "product-row count: ".$count."\n";
// quick dump first 10 product names if present
if(preg_match_all('/card-title mb-1">([^<]+)</',$create,$names)){
    echo "First product names:\n";
    foreach(array_slice($names[1],0,10) as $n) echo " - ".trim($n)."\n";
} else {
    echo "No product titles found in HTML.\n";
}

// Check category options
if(preg_match_all('/<select[^>]*id="category-filter"[^>]*>(.*?)<\/select>/s',$create,$sel)){
    echo "Category select present.\n";
    if(preg_match_all('/<option[^>]*value="([^"]*)"[^>]*>([^<]+)</',$sel[1][0],$opts)){
        echo "Categories: \n";
        foreach($opts[2] as $o) echo " - ".trim($o)."\n";
    }
}
