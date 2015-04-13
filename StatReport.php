<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/4/9
 * Time: 17:53
 */

namespace thrieu\statreport;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\data\ArrayDataProvider;
use yii\base\InvalidConfigException;
use miloschuman\highcharts\Highcharts;
use yii\helpers\Json;
use yii\web\JsExpression;

class StatReport extends Widget {
    public $htmlOptions = [];
    public $series = [];
    public $url;
    public $tableOptions = [];
    public $chartOptions = [];
    public $params = [];

    public function run() {
        if( ! $this->url) {
            throw new InvalidConfigException('The "url" property must be specified.');
        }

        // determine the ID of the container element
        if (isset($this->htmlOptions['id'])) {
            $this->id = $this->htmlOptions['id'];
        } else {
            $this->id = $this->htmlOptions['id'] = $this->getId();
        }

        foreach($this->series as $i => $s) {
            $this->series[$i] = Yii::createObject(array_merge([
                'class' => '\thrieu\statreport\Series',
            ], $s));
        }

        // render the container element
        echo Html::beginTag('div', $this->htmlOptions);

        $columns = [];
        foreach($this->series as $s) {
            $column = [];
            $column['header'] = $s->header;
            $column['footer'] = $s->footer;
            $column['visible'] = $s->visible;
            $column['headerOptions'] = $s->headerOptions;
            $column['footerOptions'] = $s->footerOptions;

            $columns[] = $column;
        }

        echo DataTablesGridView::widget([
            'filterModel' => null,
            'emptyText' => null,
            'columns' => $columns,
            'dataProvider' => new ArrayDataProvider([
                'pagination' => false,
            ]),
        ]);

        $highcharts = Highcharts::begin($this->chartOptions);
        $highcharts->scripts = ['highcharts', 'modules/data'];
        $highcharts->callback = 'createHighcharts'.$this->getId();
        $highcharts->end();

        echo Html::endTag('div');

        StatReportAsset::register($this->view);

        $chartSeries = [];
        foreach($this->series as $s) {
            if($s->isInChart) {
                $chartSeries[] = $s->header;
            }
        }

        //$js = "var dataTable{$this->id} = $('#{$this->id} .grid-view table').eq(0).dataTable({ ajax: '{$this->url}' });\n";
        $chartSeries = Json::encode($chartSeries);
        $highchartsOptions = Json::encode($highcharts->options, JSON_NUMERIC_CHECK);
        $tableOptions = Json::encode($this->tableOptions, JSON_NUMERIC_CHECK);
        $js = "var chartSeries{$this->id} = [{$chartSeries}];\n";
        $js .= "$('#{$this->id}').statReport({
            table: $('#{$this->id} > .grid-view > table').eq(0),
            chart: $('#{$highcharts->getId()}'),
            url: '{$this->url}',
            params: {$this->encodeParams()},
            tableOptions: {$tableOptions},
            chartSeries: chartSeries{$this->id},
            chartOptions: {$highchartsOptions}
        });\n";
        /*
        $js .= "dataTable{$this->id}.on('xhr.dt', function(e, settings, json) {
            var data = chartSeries{$this->id}.concat(json.data);
            console.log(data);
            var highchartsOptions = {$highchartsOptions};
            highchartsOptions.data = {
                rows: data
            };
            $('#{$highcharts->getId()}').highcharts(highchartsOptions);
        });";
        */
        $this->view->registerJs($js);

        parent::run();
    }

    public function encodeParams() {
        $params = array();
        foreach($this->params as $key => $param) {
            if(is_array($param) && count($param) == 2) {
                list($model, $attribute) = $param;
                $params[Html::getInputName($model, $attribute)] = new JsExpression('$("#'.Html::getInputId($model, $attribute).')');
            } else {
                $params[$key] = $param;
            }
        }
        return Json::encode($params);
    }
}