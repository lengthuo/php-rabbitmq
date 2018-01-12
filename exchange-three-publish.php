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


$msg = new AMQPMessage(rand(100,10000));
//使用默认的队列直接指定队列名称
//$channel->queue_declare('jry-hello', false, false, false, false);

//使用交换机 1 交换机的名字 2 交换机的类型fanout （扇形=广播）
// 必选在已有的类型中选择  rabbitmqctl list_exchanges
//声明一个交换价
$channel->exchange_declare('logs', 'fanout', false, false, false);

//list($queue_name, ,) = $channel->queue_declare(""); //声明一个临时队列，名字随机
//$channel->queue_bind($queue_name, 'logs'); //绑定队列到交换机

//$channel->basic_publish($msg, '', 'jry-hello');
$channel->basic_publish($msg, 'logs');  //发布消息
//sleep(10);
//如果没有队列绑定到交换机上，消息将会丢失，但对我们来说没关系; 如果没有消费者正在听，我们可以放心地丢弃消息。

$channel->close();//
$connection->close();