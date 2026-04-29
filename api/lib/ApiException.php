<?php
namespace HemaScorecard\Api\Lib;

class ApiException extends \Exception {

    private string $errorCode;
    private int $httpStatus;

    public function __construct(string $errorCode, int $httpStatus, string $message) {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->httpStatus = $httpStatus;
    }

    public function getErrorCode(): string {
        return $this->errorCode;
    }

    public function getHttpStatus(): int {
        return $this->httpStatus;
    }
}
