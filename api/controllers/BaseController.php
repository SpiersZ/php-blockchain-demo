<?php

namespace api\controllers;

use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\Response;

class BaseController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats'] = [
            'application/json' => Response::FORMAT_JSON,
        ];
//        $behaviors['authenticator'] = [
//            'class' => AccessTokenAuth::className(),
//            'optional' => [
//                '*',
//            ],
//        ];
        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                '*'  => ['POST','GET','OPTIONS'],
            ]
        ];
        return $behaviors;
    }

    public function init(){
        parent::init();
        \Yii::$app->user->enableSession = false;
    }
}
