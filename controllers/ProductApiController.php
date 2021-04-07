<?php

namespace app\controllers;

use app\models\ProductBatchCreateForm;
use Yii;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\ServerErrorHttpException;

class ProductApiController extends ActiveController
{
    public $modelClass = 'app\models\Product';

    /*
    public function actions()
    {
        return array_merge(parent::actions(), [
            'batch-create' => [$this, 'batchCreate']
        ]);
    }*/

    protected function verbs()
    {
        return array_merge(parent::verbs(), [
            'batch-create' => ['POST']
        ]);
    }

    public function actionBatchCreate()
    {
        $model = new ProductBatchCreateForm();

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->submit()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create objects for unknown reason.');
        }

        return $model;
    }
}