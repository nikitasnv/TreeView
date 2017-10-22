<?php

namespace niksnv\treeView;

use Closure;
use yii\base\InvalidConfigException;
use yii\grid\GridView;
use Yii;
use yii\helpers\Html;

class TreeView extends GridView
{

    public $group;
    public $treeObject;
    public $columns = [];
    public $bootstrap = false;
    private $_rows = [];
    public $labelTreeOffset = 15;
    public $rootLabel = null;
    public $tableOptions = ['class' => 'table table-bordered', 'style' => 'background:white'];

    public function renderTreeRows($object)
    {
        $cells = [];
        if (!$object->lastGroup) {
            foreach ($this->columns as $index => $column) {
                $array_func = ($index !== 0) ? $object->func : $object->group_label;
                $cells[] = $column->renderTreeCell($array_func, $object->key, $index, ($index === 0) ? ($object->level * $this->labelTreeOffset) : false);
            }
            $this->_rows[] = $this->renderTreeRow(['level' => $object->level, 'key' => (!$object->root) ? $object->group_key : 'root', 'value' => current(array_values($object->group_label)),
                'groupRow' => true, 'name' => current(array_keys($object->group_label))], $object->key, $object->level, $cells);
            foreach ($object->children as $child) {
                $this->renderTreeRows($child);
            }
        } else {
            foreach ($this->columns as $index => $column) {
                $array_func = ($index !== 0) ? $object->func : $object->group_label;
                $cells[] = $column->renderTreeCell($array_func, $object->key, $index, ($index === 0) ? ($object->level * $this->labelTreeOffset) : false);
            }
            $this->_rows[] = $this->renderTreeRow(['level' => $object->level, 'key' => (!$object->root) ? $object->group_key : 'root', 'value' => current(array_values($object->group_label)),
                'groupRow' => true, 'name' => current(array_keys($object->group_label))], $object->key, $object->level, $cells);
            foreach ($object->children as $index => $child) {
                $cells = [];
                $data = $child->data;
                foreach ($this->columns as $column_index => $column) {
                    $cells[] = $column->renderTreeCell($data, $object->key, $index, ($column_index === 0) ? ($child->level * $this->labelTreeOffset) : false);
                }
                if ($this->beforeRow !== null) {
                    $row = call_user_func($this->beforeRow, $data, $child->key, $index, $this);
                    if (!empty($row)) {
                        $rows[] = $row;
                    }
                }
                $this->_rows[] = $this->renderTreeRow(['level' => $child->level, 'data' => $child->data,
                    'groupRow' => false], $child->key, $child->level, $cells);
                if ($this->afterRow !== null) {
                    $row = call_user_func($this->afterRow, $data, $child->key, $index, $this);
                    if (!empty($row)) {
                        $rows[] = $row;
                    }
                }
            }
        }
    }

    public function renderTreeRow($model, $key, $index, $cells)
    {
        if ($this->rowOptions instanceof Closure) {
            $options = call_user_func($this->rowOptions, $model, $key, $index, $this);
        } else {
            $options = $this->rowOptions;
        }
        $options['data-key'] = is_array($key) ? json_encode($key) : (string)$key;
        $options['data-level'] = $index;

        return Html::tag('tr', implode('', $cells), $options);
    }

    public function renderTableBody()
    {
        $this->renderTreeRows($this->treeObject);
        if (empty($this->_rows) && $this->emptyText !== false) {
            $colspan = count($this->columns);
            return "<tbody>\n<tr><td colspan=\"$colspan\">" . $this->renderEmpty() . "</td></tr>\n</tbody>";
        } else {
            return "<tbody>\n" . implode("\n", $this->_rows) . "\n</tbody>";
        }
    }

    public function init()
    {
        parent::init();
        $this->formatter->nullDisplay = null; //todo
        $this->dataProvider->pagination = false; //todo
        $this->treeObject = Yii::createObject(TreeObject::className());
        if (!is_array($this->group) || empty($this->group)) {
            throw new InvalidConfigException('Group parameter must be an array');
        }
        $this->group = array_values($this->group);
        $this->makeTreeData();
        $this->applyFunctions($this->treeObject);
    }

    private function makeTreeData()
    {
        $result = Yii::createObject(['class' => TreeObject::className(), 'group_label' => [$this->columns[0]->attribute => $this->rootLabel], 'root' => true, 'level' => 0]);
        $group_count = count($this->group);
        $data = $this->dataProvider->getModels();
        $keys = $this->dataProvider->getKeys();
        foreach ($data as $index => $value) {
            $key = $keys[$index];
            $array = &$result->children;
            $level = 1;
            foreach ($this->group as $group) {
                if (!array_key_exists($group, (is_object($value) ? $value->attributes : $value))) {
                    throw new InvalidConfigException('Wrong property ' . $group);
                }
                if (!isset($array[$value[$group]])) {
                    $array[$value[$group]] = Yii::createObject(['class' => TreeObject::className(), 'group_key' => $group, 'group_label' => [$this->columns[0]->attribute => $value[$group]],
                        'level' => $level, 'lastGroup' => ($group_count === $level)]);
                }
                $array = &$array[$value[$group]]->children;
                $level++;
            }
            $array[] = Yii::createObject(['class' => TreeObject::className(), 'key' => $key, 'level' => $level, 'data' => $value]);
        }
        $this->treeObject = $result;
    }

    private function applyFunctions(&$object)
    {
        if (!$object->lastGroup) {
            $array = &$object->children;
            foreach ($array as $key => $value) {
                $this->applyFunctions($value);
            }
            $this->executeFunction($object);
        } else {
            $this->executeFunction($object);
        }
    }

    private function executeFunction(&$object)
    {
        $link_data = $object->lastGroup ? 'data' : 'func';
        foreach ($this->columns as $column) {
            $data = [];
            $name = $column->attribute;
            if (!empty($column->function)) {
                foreach ($object->children as $value) {
                    $data_array = $value->$link_data;
                    if (!array_key_exists($name, (is_object($data_array) ? $data_array->attributes : $data_array))) {
                        throw new InvalidConfigException('Wrong column ' . $name);
                    }
                    $data[] = $data_array[$name];
                }
                switch ($column->function) {
                    case 'F_SUM':
                        $object->func[$name] = array_sum($data);
                        break;
                    case 'F_MAX':
                        $object->func[$name] = max($data);
                        break;
                    case 'F_MIN':
                        $object->func[$name] = min($data);
                        break;
                    case 'F_AVG':
                        $object->func[$name] = array_sum($data) / count($data);
                        break;
                    case 'F_COUNT':
                        $object->func[$name] = $object->lastGroup ? count($data) : array_sum($data);
                        break;
                    default:
                        throw new InvalidConfigException('Wrong function ' . $column->function);
                }
            }
        }

    }

    protected function initColumns()
    {
        if (empty($this->columns)) {
            $this->guessColumns();
        }
        foreach ($this->columns as $i => $column) {
            if (is_string($column)) {
                $column = $this->createDataColumn($column);
            } else {
                $column = Yii::createObject(array_merge([
                    'class' => $this->dataColumnClass ?: TreeColumn::className(),
                    'grid' => $this,
                ], $column));
            }
            if (!$column->visible) {
                unset($this->columns[$i]);
                continue;
            }
            $this->columns[$i] = $column;
        }
    }

}