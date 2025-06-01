<?php

namespace PHPMaker2025\ucarsip;

use DebugBar\DataCollector\RequestDataCollector as BaseRequestDataCollector;

/**
 * Collects info about the current request
 */
class RequestDataCollector extends BaseRequestDataCollector
{
    /**
     * @return array
     */
    public function collect()
    {
        $vars = array('_GET', '_POST', '_SESSION', '_COOKIE', '_SERVER'); //***
        $data = array();
        foreach ($vars as $var) {
            if (isset($GLOBALS[$var])) {
                $key = "$" . $var;
                if ($this->isHtmlVarDumperUsed()) {
                    $data[$key] = $this->getVarDumper()->renderVar($GLOBALS[$var]);
                } else {
                    $data[$key] = $this->getDataFormatter()->formatVar($GLOBALS[$var]);
                }
            }
        }
        return $data;
    }
}
