<?php
/**
 * Date: 2018/1/3
 * User: lishuo
 */


require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
//channel是为了创建exchangesroutes 交换机对象
$channel = $connection->channel();

//声明一个交换机 并初始化路由方式：direct
$channel->exchange_declare('direct_logs', 'direct', false, false, false);

//=======================begin生成临时队列并绑定到交换机上direct_logs==================================
list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);
//只可以开启一个队列来监听这个信息
$severities = array_slice($argv, 1);
if(empty($severities )) {
    file_put_contents('php://stderr', "Usage: $argv[0] [info] [warning] [error]\n");
    exit(1);
}
foreach($severities as $severity) {
    $channel->queue_bind($queue_name, 'direct_logs', $severity);
}
echo ' [*] Waiting for logs. To exit press CTRL+C', "\n";
$callback = function($msg){
    echo ' [x] ',$msg->delivery_info['routing_key'], ':', $msg->body, "\n";
};

//=======================end生成临时队列并绑定到交换机上direct_logs==================================

$channel->basic_consume($queue_name, '', false, true, false, false, $callback);
while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();