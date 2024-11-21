<?php
require_once "simple_html_dom.php";

class ProductScraper {
    private $url;
    private $cookie;
    private $html;

    public function __construct($url, $cookie) {
        $this->url = $url;
        $this->cookie = $cookie;
    }

    public function fetchPage() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: {$this->cookie}"));
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        $this->html = str_get_html($response);
        
        if (!$this->html) {
            echo json_encode(['ok' => false, 'description' => 'Failed to parse HTML']);
            return false;
        }

        return true;
    }

    public function extractData() {
        if (!$this->html) return null;

        $data = [];
        $data["shop_name"] = trim($this->html->find('.ShopName', 0)->plaintext);
        $data["product_name"] = trim($this->html->find('h1.PDTitle', 0)->plaintext);
        $data["image"] = trim($this->html->find('.large-img img', 0)->src);
        $data["price"] = $this->html->find('.PDPrice div', 1)->plaintext;
        
        if (preg_match("/متاسفانه/", $this->html->find('.PDPrice2', 0)->plaintext)) {
            $data["available"] = false;
        } else if ($data["shop_name"] != "") {
            $data["available"] = true;
        } else {
            $data["available"] = null;
        }

        return $data;
    }

    public function getData() {
        if ($this->fetchPage()) {
            return json_encode($this->extractData());
        }
        return json_encode(['ok' => false, 'description' => 'Failed to fetch page']);
    }
}

// Usage
$url = $_GET['url'] ?? null;
$cookie = "TarganTkn=33u4jrxnng4tqaocryufblv1";

if ($url) {
    $scraper = new ProductScraper($url, $cookie);
    echo $scraper->getData();
} else {
    echo json_encode(['ok' => false, 'description' => 'No URL provided']);
}
