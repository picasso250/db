<?php

/**
 * 配置
 */

return array(
    'connections' => array(
        array(
            'host' => 'master.mysql.fy',
            'username' => 'master',
            'password' => 'cnk6',
            'dbnames' => true,
        ),
        array(
            'host' => 'localhost',
            'username' => 'root',
            'password' => '',
            'dbnames' => array(
                'fdd_customers',
                'fangyun',
                'www_fangdd_com',
            ),
        )
    ),
);