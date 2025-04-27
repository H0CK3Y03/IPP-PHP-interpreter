<?php

namespace IPP\Student;

use IPP\Student\Scope;

class Message
{
    /** @var Message|Literal|Block|Variable|Method|Assignment */
    public $receiver;
    public string $msg_name;
    /** @var array<Message|Literal|Block|Variable|Method|Assignment> */
    public array $send;

    /**
     * @param Message|Literal|Block|Variable|Method|Assignment $receiver
     * @param string $message
     * @param array<Message|Literal|Block|Variable|Method|Assignment> $send
     */
    public function __construct(
        Message|Literal|Block|Variable|Method|Assignment $receiver,
        string $msg,
        array $send
    ) {
        $this->receiver = $receiver;
        $this->msg_name = $msg;
        $this->send = $send;
    }

    public function evaluate(Scope $scope): mixed
    {
        $receiverObj = $this->receiver->evaluate($scope);
        $senderObj = $this->send;

        return $receiverObj->sendMessage($receiverObj, $this->msg_name, $scope, $senderObj);
    }
}
