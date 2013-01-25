<?php

namespace Scrutinizer\Process;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as BaseProcess;

class Process extends BaseProcess
{
    public function run($outputCallback = null)
    {
        if ($outputCallback instanceof LoggerInterface) {
            $outputCallback = function($type, $data) use ($outputCallback) {
                $outputCallback->info($data, array(
                    'type' => $type,
                    'is_console_output' => true,
                ));
            };
        }

        return parent::run($outputCallback);
    }

    public function runOrException($outputCallback = null)
    {
        if (0 !== $rs = $this->run($outputCallback)) {
            throw new ProcessFailedException($this);
        }

        return $rs;
    }
}