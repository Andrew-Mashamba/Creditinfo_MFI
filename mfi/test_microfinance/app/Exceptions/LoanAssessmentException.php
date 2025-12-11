<?php

namespace App\Exceptions;

use Exception;

class LoanAssessmentException extends Exception
{
    protected $context;

    public function __construct($message, $context = [])
    {
        parent::__construct($message);
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Loan Assessment Error',
                'message' => $this->getMessage(),
                'context' => $this->context
            ], 422);
        }

        return back()->withErrors([
            'assessment' => $this->getMessage()
        ])->withInput();
    }
} 