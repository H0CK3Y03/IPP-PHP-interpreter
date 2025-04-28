<?php

// Author: Adam Veselý
// Login: xvesela00
// File: Message.php

namespace IPP\Student;

use IPP\Student\Scope;

/**
 * Represents sending a message to a receiver object with optional arguments.
 */
class Message
{
    /**
     * Receiver of the message.
     *
     * @var Message|Literal|Block|Variable|Method|Assignment
     */
    public Message|Literal|Block|Variable|Method|Assignment $receiver;

    /**
     * Name of the message (method selector).
     *
     * @var string
     */
    public string $messageName;

    /**
     * Arguments to send along with the message.
     *
     * @var array<Message|Literal|Block|Variable|Method|Assignment>
     */
    public array $arguments;

    /**
     * Message constructor.
     *
     * @param Message|Literal|Block|Variable|Method|Assignment $receiver
     * @param string $messageName
     * @param array<Message|Literal|Block|Variable|Method|Assignment> $arguments
     */
    public function __construct(
        Message|Literal|Block|Variable|Method|Assignment $receiver,
        string $messageName,
        array $arguments
    ) {
        $this->receiver = $receiver;
        $this->messageName = $messageName;
        $this->arguments = $arguments;
    }

    /**
     * Evaluates the message by first evaluating the receiver and then sending the message.
     *
     * @param Scope $scope
     * @return mixed
     */
    public function evaluate(Scope $scope): mixed
    {
        $receiverObject = $this->receiver->evaluate($scope);

        // Pass the unevaluated arguments along; the receiver decides whether to evaluate them
        return $receiverObject->sendMessage($receiverObject, $this->messageName, $scope, $this->arguments);
    }
}
