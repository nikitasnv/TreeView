<?php

namespace niksnv\treeView;

use yii\base\Object;


class TreeObject extends Object
{
    public $level = 0;
    public $key;
    public $data;
    public $root = false;
    public $group_key;
    public $lastGroup = false;
    public $group_label;
    public $func = [];
    public $children = [];

}