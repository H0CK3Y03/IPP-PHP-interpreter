<?php

namespace IPP\Student;

use IPP\Student\Scopes;

class AstMessage
{
    /** @var AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|AstAssignment */
    public $receiver;
    public string $message_name;
    /** @var array<AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|AstAssignment> */
    public array $send;

    /**
     * @param AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|AstAssignment $receiver
     * @param string $message
     * @param array<AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|AstAssignment> $send
     */
    public function __construct(
        AstMessage|AstLiteral|AstBlock|AstVariable|AstMethod|AstAssignment $receiver,
        string $message,
        array $send
    ) {
        $this->receiver = $receiver;
        $this->message_name = $message;
        $this->send = $send;
    }

    public function evaluate(Scopes $scope): mixed
    {
        $receiverObject = $this->receiver->evaluate($scope);
        $senderObjects = $this->send;

        return $receiverObject->sendMessage($receiverObject, $this->message_name, $scope, $senderObjects);
    }
}
