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

$config = require 'config.php';

$host_index = _get('connection', 0);
$connections = $config['connections'];
$host_info = $connections[$host_index];
DbWrapper::config($host_info);
$dbname_index = _get('dbname', 0);
if (isset($host_info['dbnames']) && $host_info['dbnames'] !== true) {
    $dbnames = $host_info['dbnames'];
} else {
    $dbnames = DbWrapper::getDataBases();
}
$dbname = $dbnames[$dbname_index];
DbWrapper::config('dbname', $dbname);

if ($table = _get('t')) {
    $type = _get('type');
    $create = DbWrapper::getCreate($table);
    $fields = DbWrapper::getFields($table);

    $tpl = 'index-table';
} else {
    // 获取表的列表，支持关键字搜索
    $t = _get('table_like');
    if ($t) {
        $stmt = DbWrapper::exec("SHOW FULL TABLES LIKE ?", array("%$t%"));
    } else {
        $stmt = DbWrapper::exec("SHOW FULL TABLES");
    }

    $f = _get('field_like');

    // 获得表的注释，并将列表存在数组里
    $tables = array();
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $table = $row[0];
        $arr = explode(' ', $row[1]);
        $type = end($arr);
        if (preg_match('/#/', $table)) {
            continue;
        }
        $create = DbWrapper::getCreate($table);
        $rs = preg_match("/ENGINE=.+COMMENT='(.+)'$/", $create, $matches);
        $comment = $rs ? $matches[1] : '';
        $table_info = compact('table', 'type', 'comment', 'create');
        $found = true;
        if ($f) {
            $found = false;
            // 这里可以用 like 语句-
            $fields = DbWrapper::getFields($table);
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

    $tpl = 'index-list';
}

// render
include 'index.phtml';
