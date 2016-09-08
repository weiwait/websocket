<?php

use Workerman\Worker;

require './Autoloader.php';

$socket = new Worker('websocket://0.0.0.0:8888');
$socket->count = 1;

$socket->weiwait_connections = [];

$socket->onMessage = function ($connection, $data) use ($socket) {
    if (!isset($connection->uid)) {
        $connection->uid = $data;
        $socket->weiwait_connections[$data] = $connection;
        $connection->send('your uid is:  ' . $data);
        return;
    }
    list($cur_id, $message) = explode(':', $data);
    if ($cur_id == 'all') {
        broadcast($message, $socket);
    } else {
        sendById($cur_id, $message, $socket);
    }
};

$socket->onClose = function ($connection) use ($socket) {
    if (isset($connection->uid)) {
        unset($socket->weiwait_connections[$connection->uid]);
    }
};

function broadcast($message, $socket) {
    foreach ($socket->users as $connection) {
        $connection->send($message);
    }
}

function sendById($cur_id, $message, $socket) {
    foreach ($socket->users as $uid => $connection) {
        if ($uid == $cur_id) {
            $connection->send($message);
        }
    }
}

Worker::runAll();
