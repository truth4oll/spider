<?php

namespace spiders\walmart\model;


/**
 * Class Product
 * @package spiders\walmart\model
 */
class Product
{
    /** @var  string */
    public $id;

    /** @var ProductVariant[] Модификации товар */
    public $variants;


    public static function loadFromProductPage($data)
    {
        $model = new self();
        $model->id = $data['id'];
        $model->variants = $data['variants'];
        return $model;
    }
}