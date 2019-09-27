<?php

use Phalcon\Di;

class UtilService
{
    /**
     * 错误返回
     * @param int $statusCode
     * @param string $status
     * @param string $message
     */
    public static function errorResponse(int $statusCode, string $status, string $message): void
    {
        Di::getDefault()->getResponse()->setStatusCode($statusCode)->setJsonContent([
            'status' => $status,
            'message' => $message
        ])->send();
        exit;
    }

    /**
     * 获取非截取异常信息
     * @param Throwable $e
     * @return string
     */
    public static function getStringTrace(\Throwable $e): string
    {
        $traces = $e->getTrace();
        $traceStrings = [];
        foreach ($traces as $k => $trace) {
            $traceString = "#{$k} ";
            if (isset($trace['file'])) {
                $traceString .= $trace['file'] . ':' . $trace['line'] . ' ';
            }

            $trace['args'] = array_map(function ($item) {
                if (is_object($item)) {
                    return  get_class($item);
                }
                return preg_replace(["/\n/", '/ +/'], ' ', var_export($item, true));
            }, $trace['args']);

            $traceString .= $trace['class'] . $trace['type'] . $trace['function'] . '(' . implode(', ', $trace['args']) . ')';
            $traceStrings[] = $traceString;
        }

        return $e->getMessage() . " \n" . implode(" \n", $traceStrings);
    }

}
