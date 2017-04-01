<?php

namespace spiders\walmart;

use spiders\walmart\model\Product;
use spiders\walmart\model\ProductVariant;
use spiders\walmart\page\DeliveryPage;
use spiders\walmart\page\ProductPage;

class Walmart
{

    public static $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.110 Safari/537.36';

    /**
     * Получить продукт по ссылке
     * @param $url
     * @return Product
     */
    public function getProductByUrl($url)
    {
        //объект страницы продукта
        $page = new ProductPage($url);
        //выцепляем подготовленный объект продукта со страницы
        $product = $page->getProduct();
        return $product;
    }


    /**
     * @param ProductVariant $productVariant
     * @param $postalcode
     * @return array|bool
     */
    public function getDeliveryByVariant(ProductVariant $productVariant, $postalcode)
    {
        //проверяем в наличии ли товар
        if ($productVariant->status == ProductVariant::STATUS_IN_STOCK) {
            $deliveryPage = new DeliveryPage($productVariant, $postalcode);
            return $deliveryPage->getDelivery();
        }
        return false;
    }
}

