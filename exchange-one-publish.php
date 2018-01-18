<?php
/**
 * Date: 2018/1/3
 * User: lishuo
 */


/**
 * p(生产者)   ------queue--------  c消费者
 * The simplest thing that does something
 */
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
//channel是为了创建exchangesroutes 交换机对象
$channel = $connection->channel();

$channel->queue_declare('ack-hello', false, true, false, false);

$msg = new AMQPMessage('Hello World!');

$channel->basic_publish($msg, '', 'ack-hello');//基础的发布，默认是绑定到""交换机上
echo " [x] Sent 'Hello World!'\n";


$channel->close();
$connection->close();