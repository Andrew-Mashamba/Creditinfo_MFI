<?php

namespace App\Plugins;

use PSI_Plugin;
use CommonFunctions;

class CustomPingTest extends PSI_Plugin
{
    private $_filecontent = array();
    private $_result = array();

    public function __construct($enc)
    {
        parent::__construct(__CLASS__, $enc);
        if (defined('PSI_PLUGIN_PINGTEST_ADDRESSES') && is_string(PSI_PLUGIN_PINGTEST_ADDRESSES)) {
            if (preg_match(ARRAY_EXP, PSI_PLUGIN_PINGTEST_ADDRESSES)) {
                $addresses = eval(PSI_PLUGIN_PINGTEST_ADDRESSES);
            } else {
                $addresses = array(PSI_PLUGIN_PINGTEST_ADDRESSES);
            }

            switch (strtolower(PSI_PLUGIN_PINGTEST_ACCESS)) {
                case 'command':
                    if (PHP_OS == 'WINNT') {
                        $params = "-n 1";
                        if (defined('PSI_PLUGIN_PINGTEST_TIMEOUT')) {
                            $timeout = PSI_PLUGIN_PINGTEST_TIMEOUT;
                            if (is_numeric($timeout)) {
                                $tout = max((int)$timeout, 0);
                                if ($tout > 0) {
                                    $params .= " -w ".(1000*$tout);
                                }
                            }
                        } else {
                            $params .= " -w 2000";
                        }
                    } else {
                        $params = "-c 1";
                        if (defined('PSI_PLUGIN_PINGTEST_TIMEOUT')) {
                            $timeout = PSI_PLUGIN_PINGTEST_TIMEOUT;
                            if (is_numeric($timeout)) {
                                $tout = max((int)$timeout, 0);
                                if ($tout > 0) {
                                    $params .= " -W ".$tout;
                                }
                            }
                        } else {
                            $params .= " -W 2";
                        }
                    }
                    foreach ($addresses as $address) {
                        CommonFunctions::executeProgram("ping".((strpos($address, ':') === false)?'':((PHP_OS != 'WINNT')?'6':'')), $params." ".$address, $buffer, PSI_DEBUG);
                        if ((strlen($buffer) > 0) && preg_match("/[=<]([\d\.]+)\s*ms/", $buffer, $tmpout)) {
                            $this->_filecontent[] = array($address, $tmpout[1]);
                        }
                    }
                    break;
                case 'data':
                    CommonFunctions::rftsdata("pingtest.tmp", $buffer);
                    $addresses = preg_split("/\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($addresses as $address) {
                        $pt = preg_split("/[\s]?\|[\s]?/", $address, -1, PREG_SPLIT_NO_EMPTY);
                        if (count($pt) == 2) {
                            $this->_filecontent[] = array(trim($pt[0]), trim($pt[1]));
                        }
                    }
                    break;
                default:
                    $this->global_error->addConfigError("__construct()", "[pingtest] ACCESS");
            }
        }
    }

    public function execute()
    {
        if (defined('PSI_PLUGIN_PINGTEST_ADDRESSES') && is_string(PSI_PLUGIN_PINGTEST_ADDRESSES)) {
            if (preg_match(ARRAY_EXP, PSI_PLUGIN_PINGTEST_ADDRESSES)) {
                $addresses = eval(PSI_PLUGIN_PINGTEST_ADDRESSES);
            } else {
                $addresses = array(PSI_PLUGIN_PINGTEST_ADDRESSES);
            }
            foreach ($addresses as $address) {
                $this->_result[] = array($address, $this->address_inarray($address, $this->_filecontent));
            }
        }
    }

    public function xml()
    {
        foreach ($this->_result as $pt) {
            $xmlps = $this->xml->addChild("Ping");
            $xmlps->addAttribute("Address", $pt[0]);
            $xmlps->addAttribute("PingTime", $pt[1]);
        }

        return $this->xml->getSimpleXmlElement();
    }

    private function address_inarray($needle, $haystack)
    {
        foreach ($haystack as $stalk) {
            if ($needle === $stalk[0]) {
                return $stalk[1];
            }
        }

        return "lost";
    }
} 