<?php

namespace thrieu\statreport;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;

class Response extends Object {
    public $data;
    public $dataSeries;
    public $caption;


    public function init() {
        foreach($this->dataSeries as $i => $s) {
            $this->dataSeries[$i] = Yii::createObject(array_merge([
                'class' => DataSeries::className(),
            ], $s));
        }
    }

    public function toArray() {
        $response = [];
        $response['table'] = [];
        $response['chart'] = [];
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

        return $response;
    }
}