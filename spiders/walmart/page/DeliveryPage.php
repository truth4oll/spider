<?php

namespace spiders\walmart\page;


use GuzzleHttp\Client;
use spiders\walmart\model\ProductVariant;

class DeliveryPage
{
    /** @var */
    public $url;

    /** @var */
    public $source;

    /** @var  */
    public $json;

    /** @var  ProductVariant */
    public $productVariant;

    /** @var  string почтовый индекс */
    public $postalcode;


    /**
     * DeliveryPage constructor.
     * @param $productVariant
     * @param $postalcode
     */
    public function __construct($productVariant, $postalcode)
    {
        $this->productVariant = $productVariant;
        $this->postalcode = $postalcode;

        $this->url = 'https://www.walmart.com/terra-firma/item/' . $productVariant->id . '/location/' . $postalcode . '?selected=true&wl13=';
    }


    /**
     * @return string
     */
    public function getSource()
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


    public function getDelivery()
    {
        $this->getSource();

        $json = json_decode($this->source);

        $fulfillment = $json->payload->offers->{$this->productVariant->offer}->fulfillment;

        $shiping = $this->getShippingByFulfillment($fulfillment);
        $pickup = $this->getPickupByFulfillment($fulfillment);

        return [
            'shiping' => $shiping,
            'pickup' => $pickup
        ];
    }


    /**
     * Получить параметры доставки
     * @param $fulfillment
     * @return array
     * @internal param $delivery
     */
    private function getShippingByFulfillment($fulfillment)
    {
        $result = [];
        if (isset($fulfillment->shippingOptions)) {
            $shippingOptions = $fulfillment->shippingOptions;
            foreach ($shippingOptions as $shippingOption) {
                $result[] = [
                    'method' => $shippingOption->shipMethod,
                    'price' => $shippingOption->fulfillmentPrice->price,
                    'price_type' => $shippingOption->fulfillmentPriceType,
                    'delivery_date' => $shippingOption->fulfillmentDateRange->exactDeliveryDate,
                    'delivery_date_formatted' => date('l, M d', $shippingOption->fulfillmentDateRange->exactDeliveryDate / 1000)
                ];
            }
        }
        return $result;
    }


    /**
     * Получить параметры самовывоза
     * @param $fulfillment
     * @return array
     */
    private function getPickupByFulfillment($fulfillment)
    {
        $result = [];
        if (isset($fulfillment->pickupOptions)) {
            $pickupOptions = $fulfillment->pickupOptions;
            foreach ($pickupOptions as $pickupOption) {
                $result[] = [
                    'method' => $pickupOption->pickupMethod,
                    'city' => $pickupOption->storeCity,
                    'address' => $pickupOption->storeAddress,
                    'price' => $pickupOption->fulfillmentPrice->price,
                    'delivery_date' => $pickupOption->fulfillmentDateRange->exactDeliveryDate,
                    'delivery_date_formatted' => date('l, M d', $pickupOption->fulfillmentDateRange->exactDeliveryDate / 1000)
                ];
            }
        }
        return $result;
    }

}
