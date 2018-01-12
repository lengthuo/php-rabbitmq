<?php
/**
 * Date: 2018/1/3
 * User: lishuo
 */


require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class FibonacciRpcClient
{
    private $connection;
    private $channel;
    private $callback_queue;
    private $response;
    private $corr_id;


    public function __construct()
    {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();


        list($this->callback_queue, ,) = $this->channel->queue_declare("", false, false, true, false);
        //绑定消费
        $this->channel->basic_consume($this->callback_queue, '', false, false, false, false, [$this, 'on_response']);
    }

    public function on_response($rep)
    {
        if ($rep->get('correlation_id') == $this->corr_id) {
            $this->response = $rep->body;
        }
    }

    public function call($n)
    {
        $this->response = null;
        $this->corr_id = uniqid();
        var_dump($this->corr_id);
        var_dump($this->callback_queue);
        $msg = new AMQPMessage((string)$n, ['correlation_id' => $this->corr_id, 'reply_to' => $this->callback_queue]);
        //发布消息
        $this->channel->basic_publish($msg, '', 'rpc_queue');
        //一直等待返回结果
        while (!$this->response) {
            $this->channel->wait();
        }
        return intval($this->response);
    }
};


$fibonacci_rpc = new FibonacciRpcClient();
$response = $fibonacci_rpc->call(30);
echo " [.] Got ", $response, "\n";