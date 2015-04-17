<?php
namespace thrieu\statreport;

use Yii;
use yii\base\Widget;
use yii\bootstrap\ButtonGroup;
use yii\helpers\Html;
use yii\data\ArrayDataProvider;
use yii\base\InvalidConfigException;
use miloschuman\highcharts\Highcharts;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\helpers\ArrayHelper;

class StatReport extends Widget {
    public $htmlOptions = [];
    public $series = [];
    public $url;
    public $tableOptions = [];
    public $chartOptions = [];
    public $params = [];
    public $toggleBtnTableLabel = '<i class="fa fa-table"></i>';
    public $toggleBtnChartLabel = '<i class="fa fa-line-chart"></i>';

    const VIEW_CHART = 'chart';
    const VIEW_TABLE = 'table';

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

        $highcharts = Highcharts::begin([
            'htmlOptions' => [
                'data-view-role' => static::VIEW_CHART,
                'class' => 'stat-report-view',
            ],
            'options' => $this->chartOptions,
        ]);
        $highcharts->scripts = ['highcharts', 'modules/data'];
        $highcharts->callback = 'createHighcharts'.$this->getId();
        $highcharts->end();

        echo DataTablesGridView::widget([
            'filterModel' => null,
            'emptyText' => null,
            'columns' => $columns,
            'dataProvider' => new ArrayDataProvider([
                'pagination' => false,
            ]),
            'options' => [
                'data-view-role' => static::VIEW_TABLE,
                'class' => 'grid-view stat-report-view'
            ],
        ]);

        echo ButtonGroup::widget([
            'options' => [
                'class' => 'toggle-view-buttons',
            ],
            'buttons' => [
                ['label' => $this->toggleBtnChartLabel, 'options' => ['value' => static::VIEW_CHART]],
                ['label' => $this->toggleBtnTableLabel, 'options' => ['value' => static::VIEW_TABLE]],
            ],
            'encodeLabels' => false,
        ]);

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