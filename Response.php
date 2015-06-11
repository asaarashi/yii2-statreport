<?php

namespace thrieu\statreport;

use Yii;
use yii\base\InvalidParamException;
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
            foreach($this->data as $row) {
                $tableRow = [];
                $chartRow = [];
                foreach($this->dataSeries as $s) {
                    if (!is_null($s->value)) {
                        if (!is_object($s->value) || !$s->value instanceof \Closure) {
                            throw new InvalidParamException('Value is not a Closure.');
                        }
                        $row_value = $s->value($row);
                    } else {
                        $row_value = ArrayHelper::getValue($row, $s->name);
                    }

                    $tableRow[] = $row_value;
                    if($s->isInChart) {
                        $chartRow[] = $row_value;
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