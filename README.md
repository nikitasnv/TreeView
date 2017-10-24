# yii2-TreeView
This module extends Yii2 GridView and allows you to build simple TreeGrid with multiple levels of grouping.
# Example
Controller:
```php
 $dataProvider = new ArrayDataProvider([
            'allModels' => [
                [
                    'name' => 'name1',
                    'group1' => 'group1',
                    'group2' => 'group1',
                    'value1' => 353,
                    'value2' => 221,
                ],
                [
                    'name' => 'name2',
                    'group1' => 'group1',
                    'group2' => 'group2',
                    'value1' => 182,
                    'value2' => 974,
                ],
                [
                    'name' => 'name3',
                    'group1' => 'group2',
                    'group2' => 'group1',
                    'value1' => 186,
                    'value2' => 888,
                ],
                [
                    'name' => 'name4',
                    'group1' => 'group2',
                    'group2' => 'group2',
                    'value1' => 638,
                    'value2' => 274,
                ],
                [
                    'name' => 'name5',
                    'group1' => 'group1',
                    'group2' => 'group1',
                    'value1' => 672,
                    'value2' => 851,
                ],
                [
                    'name' => 'name6',
                    'group1' => 'group1',
                    'group2' => 'group2',
                    'value1' => 841,
                    'value2' => 457,
                ],
                [
                    'name' => 'name7',
                    'group1' => 'group2',
                    'group2' => 'group1',
                    'value1' => 836,
                    'value2' => 184,
                ],
                [
                    'name' => 'name8',
                    'group1' => 'group2',
                    'group2' => 'group2',
                    'value1' => 735,
                    'value2' => 526,
                ],
            ],
        ]);
```
View:
```php
use niksnv\treeView\TreeView;

echo TreeView::widget([
    'dataProvider' => $dataProvider,
    'group' => ['group1', 'group2'],
    'rootLabel' => 'Root row',
    'rowOptions' => function ($model) {
        switch ($model['level']) {
            case 0:
                return ['style' => 'background:red;color:white'];
            case 1:
                return ['style' => 'background:blue;color:white'];
            case 2:
                return ['style' => 'background:green;color:white'];
            default:
                return [];
        }
    },
    'columns' => [
        'name',
        [
            'attribute' => 'value1',
            'function' => 'F_SUM',
        ],
        [
            'attribute' => 'value2',
            'function' => 'F_AVG',
        ]
    ]
]);
```
