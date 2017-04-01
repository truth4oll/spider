<?php

namespace spiders\walmart\page;


use GuzzleHttp\Client;
use spiders\walmart\model\Product;
use spiders\walmart\model\ProductVariant;

class ProductPage
{

    /** @var */
    public $url;

    /** @var string Исходник страницы */
    public $source;

    /** @var  \stdClass json данные __WML_REDUX_INITIAL_STATE__ со страницы */
    public $json;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Получить исходник страницы
     * @return string
     */
    private function getSource()
    {
        if (!$this->source) {
            //get content
            $client = new Client([
                'verify' => false,
            ]);

            $response = $client->get($this->url, [
                'headers' => ['User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.110 Safari/537.36']
            ]);

            $code = $response->getStatusCode();
            if ($code == 200) {
                $this->source = $response->getBody()->getContents();
            }
        }
        return $this->source;
    }

    /**
     * получить идентификатор продукта
     * @return string
     */
    public function getId()
    {
        return $this->getJson()->product->primaryProduct;
    }


    /**
     * Получить подготовленный объект продукта
     * @return Product
     */
    public function getProduct()
    {
        //тащим html (если его нет)
        $this->getSource();

        //подготавливаем цвета вариантов продукта id=>цвет
        $options['colors'] = $this->getColors();

        //подготавливаем размеры вариантов продукта id=>размер
        $options['sizes'] = $this->getSizes();

        $data['id'] = $this->getId();

        //подготавливаем варианты товара с учетом цветов и размеров
        $data['variants'] = $this->getProductVariants($options);

        //заполняем объект продукта данными
        $model = Product::loadFromProductPage($data);

        return $model;
    }


    /**
     * Получить подготовленные варианты продукта
     * С цветами и размерами
     * @param $options
     * @return ProductVariant[]
     */
    public function getProductVariants($options = [])
    {
        $variants = [];
        $products = $this->getJson()->product->products;
        foreach ($products as $id => $variant) {

            //prepare data
            $data = [];

            $data['id'] = $id;
            $data['offer'] = reset($variant->offers);
            $data['color'] = (isset($options['colors'])) ? $options['colors'][$id] : [];
            $data['size'] = (isset($options['sizes'])) ? $options['sizes'][$id] : [];
            $data['price'] = $this->getPriceByOfferId($data['offer']);
            $data['status'] = $this->getAvailabilityByOfferId($data['offer']);

            //fill data
            $variants[$id] = ProductVariant::loadFromProductPage($data);
        }
        return $variants;
    }


    /**
     * Получить цвета вариантов продукта id=>цвет
     * @return array
     */
    public function getColors()
    {
        $models = [];
        $variants = $this->getJson()->product->variantCategoriesMap->{$this->getId()}->actual_color->variants;
        foreach ($variants as $color) {
            $variant_products = $color->products;
            foreach ($variant_products as $id) {
                $models[$id] = $color->name;
            }
        }
        return $models;
    }


    /**
     * Получить размеры вариантов продукта id=>размер
     * @return array
     */
    public function getSizes()
    {
        $models = [];
        $variants = $this->getJson()->product->variantCategoriesMap->{$this->getId()}->size->variants;

        foreach ($variants as $size) {
            $variant_products = $size->products;
            foreach ($variant_products as $id) {
                $models[$id] = $size->name;
            }
        }
        return $models;
    }


    /**
     *  Получаем Json, в котором хранятся все данные по продукту
     * @return mixed
     */
    public function getJson()
    {
        if (!$this->json) {
            preg_match_all('/__WML_REDUX_INITIAL_STATE__ = (.+?);</is', $this->source, $matched);
            $sJson = $matched[1][0];
            $this->json = json_decode($sJson);
        }
        return $this->json;
    }

    /**
     * Получить цену выбранного варианта товара
     * @param $id
     * @return string
     */
    private function getPriceByOfferId($id)
    {
        return $this->getJson()->product->offers->{$id}->pricesInfo->priceMap->CURRENT->price;
    }

    /**
     *  Определить доступность выбраннного варианта товара
     * @param $id
     * @return string
     */
    private function getAvailabilityByOfferId($id)
    {
        return $this->getJson()->product->offers->{$id}->productAvailability->availabilityStatus;
    }
}
