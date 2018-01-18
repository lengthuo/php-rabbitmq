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
//第三个参数，持久化（即使服务死掉）
//虽然这个命令本身是正确的，但是在我们现在的设置中不起作用。这是因为我们已经定义了一个名为jry-hello的队列 ，这个队列并不耐用。RabbitMQ不允许您使用不同的参数重新定义现有的队列，并会将错误返回给任何尝试这样做的程序。但有一个快速的解决方法 - 让我们声明一个不同名称的队列，例如task_queue：
$channel->queue_declare('ack-hello', false, true, false, false);

echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";
$callback = function($msg) {
    echo " [x] Received1 ", $msg->body, "\n";
//    sleep(100);//这里我强行退出。
//    echo " [x] Received11 ", $msg->body, "\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    //如果是确认机制的，这个地方如果不确认的话，一直都在，不管消费多少次
};

//第四个参数 为false的时候 是由确认机制的
$channel->basic_consume('ack-hello', '', false, false, false, false, $callback);
while(count($channel->callbacks)) {
    $channel->wait();
}


$channel->close();
$connection->close();