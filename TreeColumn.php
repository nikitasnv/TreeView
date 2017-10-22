<?php

namespace niksnv\treeView;

use Closure;
use yii\grid\DataColumn;
use yii\base\InvalidConfigException;
use Yii;
use yii\helpers\Html;

class TreeColumn extends DataColumn
{
    public $function;

    public function renderTreeCell($model, $key, $index, $level)
    {
        if ($this->contentOptions instanceof Closure) {
            $options = call_user_func($this->contentOptions, $model, $key, $index, $this);
        } else {
            $options = $this->contentOptions;
        }
        if($level !== false) {
            $options['style']['padding-left'] = $level . 'px';
        }
        return Html::tag('td', $this->renderDataCellContent($model, $key, $index), $options);
    }
}