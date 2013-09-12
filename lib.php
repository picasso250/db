<?php

/**
 * 自动获取表结构，生成 fields 声明
 * 用法：放在www目录下，用浏览器访问即可
 * 作者：王霄池
 * 有 bug，请去打击王霄池
 */

// 获得数据库，话说其实这就是单例模式，不过不是面向对象的
class ORM
{
    protected static $db;
    protected static $config;

    public static function config()
    {
        $num_args = func_num_args();
        if ($num_args == 1) {
            $arr = func_get_arg(0);
            if (is_array($arr)) {
                foreach ($arr as $k => $v) {
                    self::$config[$k] = $v;
                }
            }
        } elseif ($num_args == 2) {
            $key = func_get_arg(0);
            $value = func_get_arg(1);
            self::$config[$key] = $value;
        }
    }

    public static function db()
    {
        if (self::$db === null) {
            $config = self::$config;
            self::$db = new Pdo(
                "mysql:host=$config[host];dbname=$config[dbname]", 
                $config['username'], 
                $config['password'], 
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                )
            );
        }
        return self::$db;
    }
    // 执行数据库的语句，支持参数变量
    public static function exec($sql, $values = array())
    {
        $db = self::db();
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

    public static function get_fields($table_name)
    {
        // 将创建表中的field都提取出来
        $fields = array();
        $stmt = self::exec('SHOW FULL COLUMNS FROM '.$table_name);
        while (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
            $fields[$row['Field']] = $row;
        }
        return $fields;
    }


    // 得到表的创建语句
    public static function get_create($table, $type = 'TABLE')
    {
        $stmt = self::exec("SHOW CREATE $type $table");
        $row = $stmt->fetch(PDO::FETCH_NUM);
        return $row[1];
    }
}

function _get($key = null, $or = null)
{
    if ($key === null) {
        return $_GET;
    }
    return isset($_GET[$key]) ? trim($_GET[$key]) : $or;
}
function _post($key = null, $or = null)
{
    if ($key === null) {
        return $_POST;
    }
    return isset($_POST[$key]) ? trim($_POST[$key]) : $or;
}
function _url($url = null, $search = array(), $preserve = false)
{
    if ($preserve) {
        $search = array_merge(_get(), $search);
    }
    if ($search) {
        $query = '?'.htmlspecialchars(http_build_query($search));
    } else {
        $query = '';
    }
    return $url.$query;
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

