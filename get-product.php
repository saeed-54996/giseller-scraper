<?php
require_once "simple_html_dom.php";
// Initialize a cURL session
$ch = curl_init();

// Set the URL you want to fetch
$url = $_GET['url'];

// Set the cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Set the cookie with the ASP.NET_SessionId
$cookie = "TarganTkn=zup4byclcrx3watoz5mqyrfx";
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Cookie: $cookie"
)
);

// Execute the cURL request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    // Output the response
    //echo $response;
}
// Close the cURL session
curl_close($ch);

$html = str_get_html($response);
if (!$html) {
    echo json_encode(['ok' => false, 'description' => 'Failed to parse HTML']);
    exit;
}

$data["shop_name"] = trim($html->find('.ShopName',0)->plaintext);
$data["product_name"] = trim($html->find('h1.PDTitle', 0)->plaintext);
$data["image"] = trim($html->find('.large-img', 0)->find("img",0)->src);
$data["price"] = $html->find('.PDPrice', 0)->find('div', 1)->plaintext;
//if ($html->find('.PDPrice2', 0)->plaintext == "متاسفانه این کالا در حال حاضر موجود نیست.") {
if (preg_match("/متاسفانه/",$html->find('.PDPrice2', 0)->plaintext)) {
    //$data["available"] = False;
    $data["available"] = False;
} else if($data["shop_name"]!=""){
    $data["available"] = True;
}
else{
    $data["available"] = null;
}


echo json_encode($data);