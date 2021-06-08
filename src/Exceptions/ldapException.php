<?php


class ldapException extends Exception
{
    private $OptDiagnosticMessage='None';

    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    /**
     * @return string
     */
    public function getOptDiagnosticMessage(): string
    {
        return $this->OptDiagnosticMessage;
    }

    /**
     * @param string $OptDiagnosticMessage
     */
    public function setOptDiagnosticMessage(string $OptDiagnosticMessage): void
    {
        $this->OptDiagnosticMessage = $OptDiagnosticMessage;
    }
    
}

