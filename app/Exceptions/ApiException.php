<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

// Error bisnis dengan kode, otomatis dirender sebagai JSON {message, code}
class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public int $status = 400,
        public string $errorCode = 'ERROR',
        public array $extra = [],
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json(
            array_merge(['message' => $this->getMessage(), 'code' => $this->errorCode], $this->extra),
            $this->status
        );
    }
}
