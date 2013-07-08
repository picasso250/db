<?php

/**
 * 自动获取表结构，生成 fields 声明
 * 用法：放在www目录下，用浏览器访问即可
 * 作者：王霄池
 * 有 bug，请去打击王霄池
 */

// 获得数据库，话说其实这就是单例模式，不过不是面向对象的
function db_get()
{
    static $db;
    $dbname = _get('dbname') ?: 'fy_me_cai';
    if (is_null($db)) {
        $db = new Pdo('mysql:host=master.mysql.fy;dbname='.$dbname, 'master', 'cnk6', array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ));
    }
    return $db;
}

// 执行数据库的语句，支持参数变量
function db_exec($sql, $values = array())
{
    $db = db_get();
    $stmt = $db->prepare($sql);
    $i = 0;
    foreach ($values as $key => $value) {
        $i++;
        $stmt->bindValue($i, $value);
    }
    $stmt->execute();
    $has_error = $stmt->errorCode() + 0;
    if ($has_error) {
        var_dump($stmt->errorInfo());
        throw new Exception("error", 1);
    }
    return $stmt;
}

function get_fields($table_name)
{
    // 将创建表中的field都提取出来
    $fields = array();
    $stmt = db_exec('SHOW FULL COLUMNS FROM '.$table_name);
    while (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
        $fields[$row['Field']] = $row;
    }
    return $fields;
}

function _get($key = null)
{
    if ($key === null) {
        return $_GET;
    }
    return isset($_GET[$key]) ? trim($_GET[$key]) : null;
}
function _post($key = null)
{
    if ($key === null) {
        return $_POST;
    }
    return isset($_POST[$key]) ? trim($_POST[$key]) : null;
}
function _url($url = null, $search = array(), $preserve = false)
{
    if ($preserve) {
        $search = array_merge(_get(), $search);
    }
    if ($search) {
        $query = '?'.http_build_query($search);
    } else {
        $query = '';
    }
    return $url.$query;
}

// 得到表的创建语句
function get_create($table)
{
    $stmt = db_exec('SHOW CREATE TABLE '.$table);
    $c =  end($stmt->fetch(PDO::FETCH_NUM));
    return $c;
}

// 给代码加高亮
function code_format($code)
{
    $code = preg_replace('/ /', '&nbsp;', $code);
    $code = preg_replace('/\b([A-Z_]+|array|true)\b/u', '<span class="kw">$1</span>', $code);
    $code = preg_replace('/(`[a-z_]+`)/', '<span class="field">$1</span>', $code);
    $code = preg_replace("/('.*?')/u", '<span class="str">$1</span>', $code);
    $code = preg_replace('/(\$[a-z>-]+)/', '<span class="var">$1</span>', $code);
    $code = nl2br($code);
    return $code;
}

// 给搜索关键字加高亮
function high_light($str, $kw)
{
    return preg_replace('/'.$kw.'/', '<span class="high-light">'.$kw.'</span>', $str);
}

