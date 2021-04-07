<?php

namespace app\models;

use yii\base\Model;

class ProductBatchCreateForm extends Model
{
    public $products;

    public function rules()
    {
        return [
            ['products', 'required'],
            ['products', function($attribute) {
                if (!is_array($this->$attribute)) {
                    $this->addError($attribute, 'Параметр должен быть массивом');
                    return;
                }

                if (count($this->$attribute) > 50) {
                    $this->addError($attribute, 'Максимум товаров для одновременного добавления - 50');
                    return;
                }

                foreach ($this->$attribute as $i => $item) {
                    $product = new Product();
                    $product->load($item, '');
                    $product->validate();
                    foreach ($product->errors as $attr => $errArray) {
                        foreach ($errArray as $error) {
                            $this->addError($attribute.'.'.$i.'.'.$attr, $error);
                        }
                    }
                }
            }]
        ];
    }

    public function submit()
    {
        if (!$this->validate()) {
            return false;
        }

        foreach ($this->products as $product) {
            $productModel = new Product();
            $productModel->load($product, '');
            $productModel->save();
        }

        return true;
    }
}