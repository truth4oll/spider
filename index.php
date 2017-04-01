<?php

require 'vendor/autoload.php';


$client = new \GuzzleHttp\Client();
$walmaprtScraper = new \spiders\walmart\Walmart();

$url = 'https://www.walmart.com/ip/Danskin-Now-Women-s-Knit-Slip-on-Shoe/51630300';

$product = $walmaprtScraper->getProductByUrl($url);

//выводим продукт и все варианты цветов и размеров
print_r($product);


//берем случайноую модификацию товара
$variant = $product->variants[array_rand($product->variants)];
//получаем доставку
if ($delivery_data = $walmaprtScraper->getDeliveryByVariant($variant, 10001)) {
    print_r($delivery_data);
}