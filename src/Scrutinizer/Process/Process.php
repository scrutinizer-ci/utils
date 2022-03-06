<?php

namespace Scrutinizer\Process;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as BaseProcess;

class Process extends BaseProcess
{
    public function run(callable $outputCallback = null, array $env = []): int
    {
        if ($outputCallback instanceof LoggerInterface) {
            $outputCallback = function($type, $data) use ($outputCallback) {
                $outputCallback->info($data, array(
                    'type' => $type,
                    'is_console_output' => true,
                ));
            };
        }

        return parent::run($outputCallback, $env);
    }

    public function runOrException($outputCallback = null)
    {
        if (0 !== $rs = $this->run($outputCallback)) {
            throw new ProcessFailedException($this);
        }

        return $rs;
    }

    public function execute($outputCallback = null)
    {
        $this->run($outputCallback);

        return $this;
    }
}
