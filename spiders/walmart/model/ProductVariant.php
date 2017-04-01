<?php

namespace spiders\walmart\model;

/**
 * Class Product
 * @package spiders\walmart\model
 */
class ProductVariant
{

    const STATUS_IN_STOCK = 'IN_STOCK';

    const STATUS_OUT_OF_STOCK = 'OUT_OF_STOCK';

    public $id;
    /**
     * @var string цвет
     */
    public $color;

    /**
     * @var array размер
     */
    public $size;

    /**
     * @var int статус
     */
    public $status;

    /**
     * @var
     */
    public $offer;

    /**
     * @var
     */
    public $price;


    public static function loadFromProductPage($data)
    {
        $model = new self();
        $model->id = $data['id'];
        $model->color = $data['color'];
        $model->size = $data['size'];
        $model->price = $data['price'];
        $model->status = $data['status'];
        $model->offer = $data['offer'];
        return $model;
    }

}


?>