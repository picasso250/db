<?php

/**
 * 自动获取表结构，生成 fields 声明
 * 用法：放在www目录下，用浏览器访问即可
 * 作者：王霄池
 * 有 bug，请去打击王霄池
 */

ini_set('display_errors', true);
error_reporting(E_ALL);

require 'lib.php';

if (_get('trans')) {
    $create = get_create(_get('trans'));
    $fields = get_fields(_get('trans'));

    // 转换成 field 结构的 php 代码
    $field_code = '$this->fields = array('."\n";
    foreach ($fields as $field) {
        $type = reset(explode('(', $field['Type']));
        $comment = reset(explode('{', $field['Comment']));
        $field_code .= "    '$field[Field]' => array('$type', '$comment', true),\n";
    }
    $field_code .= ');';

} else {

    // 获取表的列表，支持关键字搜索
    $t = _get('table_like');
    if ($t) {
        $stmt = db_exec("SHOW TABLES LIKE ?", array("%$t%"));
    } else {
        $stmt = db_exec("SHOW TABLES");
    }

    $f = _get('field_like');

    // 获得表的注释，并将列表存在数组里
    $tables = array();
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $table = reset($row);
        if (preg_match('/#/', $table)) {
            continue;
        }
        $create = get_create($table);
        $rs = preg_match("/ENGINE=.+COMMENT='(.+)'$/", $create, $matches);
        $comment = $rs ? $matches[1] : '';
        $table_info = compact('table', 'comment', 'create');
        $found = true;
        if ($f) {
            $found = false;
            // 这里可以用 like 语句-
            $fields = get_fields($table);
            foreach ($fields as $field) {
                if (preg_match('/'.$f.'/', $field['Field'])) {
                    $table_info['find_field'] = $found = true;
                    $table_info['field_matches'][] = $field;
                }
            }
        }
        if ($found) {
            $tables[] = $table_info;
        }
    }
}

// render
include 'index.phtml';
