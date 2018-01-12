<?php
/**
 * Date: 2018/1/3
 * User: lishuo
 */


/**
 * 最简单的队列
 * p(生产者)   ------queue--------  c消费者
 * The simplest thing that does something
 */
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
//channel是为了创建exchangesroutes 交换机对象
$channel = $connection->channel();



//请注意，我们也在这里声明队列。因为我们可能会在发布者之前启动消费者，所以我们希望确保队列存在，然后再尝试使用消息。
$channel->queue_declare('jry-hello', false, false, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
$callback = function($msg) {
    echo " [x] Received ", $msg->body, "\n";
};

$channel->basic_consume('jry-hello', '', false, true, false, false, $callback);
while(count($channel->callbacks)) {
    $channel->wait();
}


$channel->close();
$connection->close();