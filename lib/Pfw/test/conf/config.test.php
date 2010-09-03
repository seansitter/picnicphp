<?php return array(

/**
 * This is the sample test config. Copy it to config.test.php and then modify.
 */

'database' => array(
  'default' => array(
    'params' => array(
      'host'     => 'localhost',
      'username' => 'root',
      'password' => '',
      'dbname'   => 'test',
      'adapter'  => 'Mysqli',
      'socket'   => ''
    )
  )
),

'arr_outer' => array(
  'arr_inner' => array(
    'name1' => 'value1',
    'name3' => 'value3'
  )
)

);?>