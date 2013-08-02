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
            'dbnames' => array(
                'fy_me_cai',
                'fy_net_cai',
                'fy_new_me',
            ),
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