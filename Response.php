<?php

namespace thrieu\statreport;

use Yii;
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
            foreach($this->data as $value) {
                $tableRow = [];
                $chartRow = [];
                foreach($this->dataSeries as $s) {
                    $tableRow[] = ArrayHelper::getValue($value, $s->name);
                    if($s->isInChart) {
                        $chartRow[] = ArrayHelper::getValue($value, $s->name);
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