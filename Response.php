<?php

namespace thrieu\statreport;

use Yii;
use yii\helpers\Html;
use yii\base\Object;
use yii\helpers\ArrayHelper;

class Response extends Object {
    public $data;
    public $dataSeries = [];
    public $caption;
    public $status = self::STATUS_SUCCESS;
    public $message;

    const STATUS_SUCCESS = 0;
    const STATUS_FAILURE = -1;

    public function init() {
        foreach($this->dataSeries as $i => $s) {
            $this->dataSeries[$i] = Yii::createObject(array_merge([
                'class' => DataSeries::className(),
            ], $s));
        }
    }

    public function toArray() {
        $response = [];
        $response['status'] = $this->status;
        $response['table'] = [];
        $response['chart'] = [];
        if($response['status'] == static::STATUS_SUCCESS) {
            if($this->caption) {
                $response['caption'] = $this->caption;
            }
            foreach($this->data as $key => $row) {
                $tableRow = [];
                $chartRow = [];
                foreach($this->dataSeries as $s) {
                    if ($s->value !== null) {
                        $value = call_user_func($s->value, $row, $key);
                    } else {
                        $value = ArrayHelper::getValue($row, $s->name);
                    }

                    $value = $s->encode ? Html::encode($value) : $value;
                    $tableRow[] = $value;
                    if($s->isInChart) {
                        $chartRow[] = $value;
                    }
                }
                $response['table'][] = $tableRow;
                $response['chart'][] = $chartRow;
            }
        } else {
            $response['message'] = $this->message;
        }

        return $response;
    }
}