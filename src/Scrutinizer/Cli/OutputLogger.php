<?php

namespace Scrutinizer\Cli;

use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Output\OutputInterface;

class OutputLogger extends AbstractLogger
{
    private $output;
    private $verbose;
    private $errors = array();

    public function __construct(OutputInterface $output, $verbose = false)
    {
        $this->output = $output;
        $this->verbose = $verbose;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function isVerbose()
    {
        return $this->verbose;
    }

    public function flushErrors()
    {
        if (empty($this->errors)) {
            return;
        }

        $this->output->writeln("Errors:");
        foreach ($this->errors as $error) {
            $this->output->writeln(' - '.$error);
        }
        $this->output->write("\n");

        $this->errors = array();
    }

    public function log($level, $message, array $context = array())
    {
        if ( ! $this->verbose && $level === 'debug') {
            return;
        }

        if ($level === 'error') {
            $this->errors[] = trim($this->formatMessage($message, $context));

            return;
        }

        $this->output->write($this->formatMessage($message, $context));
    }

    private function formatMessage($message, array $context)
    {
        $map = array();
        foreach ($context as $k => $v) {
            if ( ! is_scalar($v) && $v !== null
                && ( ! is_object($v) || ! method_exists($v, '__toString'))) {
                continue;
            }

            $map['{'.$k.'}'] = (string) $v;
        }

        return strtr($message, $map);
    }
}