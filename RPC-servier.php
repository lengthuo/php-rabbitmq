<?php
/**
 * Date: 2018/1/3
 * User: lishuo
 */

//https://www.rabbitmq.com/tutorials/tutorial-six-php.html  具体原理
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();


//队列声明
$channel->queue_declare('rpc_queue', false, false, false, false);


function fib($n)
{
    if ($n == 0) return 0;
    if ($n == 1) return 1;
    return fib($n - 1) + fib($n - 2);
}

echo " [x] Awaiting RPC requests\n";

$callback = function ($req) {
    $n = intval($req->body);
    echo " [.] fib(", $n, ")\n";
    $msg = new AMQPMessage((string)fib($n), ['correlation_id' => $req->get('correlation_id')]);
    $req->delivery_info['channel']->basic_publish($msg, '', $req->get('reply_to')); //发送到队列消息
    $req->delivery_info['channel']->basic_ack($req->delivery_info['delivery_tag']); //确认机制
};

$channel->basic_qos(null, 1, null);

$channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);
while (count($channel->callbacks)) {
    $channel->wait();
}
$channel->close();
$connection->close();


