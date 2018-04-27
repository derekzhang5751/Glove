<?php
/**
 * User: Derek
 * Date: 2018-04-27
 */

class Command {
    private $sourceCmd = "";
    private $cmd_array_charge = array('CG', '查', '充', '冲', '上');
    private $cmd_array_balance = array('QY', '查');
    private $cmd_array_withdraw = array('WD', '回');
    private $cmd_array_order_line = array('99', '和', '合');
    private $cmd_array_order_type = array(
        '大' => 'Z',
        '小' => 'A',
        '单' => 'D',
        '双' => "S"
    );
    private $cmd_array_type_collection = array('Z', 'A', 'D', 'S');
    
    private $cmdType = COMMAND_INVALID;
    private $cmdFormatted = "";
    
    private $orderLineNum = 0;  // 99 和， 0 - 9 道次
    private $orderValue = '';
    private $orderAmount = 0.0;
    
    public function __construct($cmd) {
        $this->sourceCmd = trim($cmd, " /\t\n\r\0\x0B");
        $this->cmdFormatted = $this->sourceCmd;
    }
    
    public function getCmdFormatted() {
        return $this->cmdFormatted;
    }
    
    public function getCmdType() {
        return $this->cmdType;
    }
    
    public function getLineNum() {
        return $this->orderLineNum;
    }
    
    public function getOrderValue() {
        return $this->orderValue;
    }
    
    public function getAmount() {
        return $this->orderAmount;
    }
    
    private function parseAmount($value) {
        $amount = floatval( trim($value, " /\t\n\r\0\x0B") );
        return round($amount, 2);
    }
    
    private function formatCommand() {
        $count = 0;
        $this->sourceCmd = str_replace(" ", "/", $this->sourceCmd, $i);
        if ($i > $count) {
            $count = $i;
        }
        $this->sourceCmd = str_replace(".", "/", $this->sourceCmd, $i);
        if ($i > $count) {
            $count = $i;
        }
        $this->sourceCmd = str_replace(",", "/", $this->sourceCmd, $i);
        if ($i > $count) {
            $count = $i;
        }
        $this->sourceCmd = str_replace("，", "/", $this->sourceCmd, $i);
        if ($i > $count) {
            $count = $i;
        }
        $this->sourceCmd = str_replace("。", "/", $this->sourceCmd, $i);
        if ($i > $count) {
            $count = $i;
        }
        // For balance command
        foreach ($this->cmd_array_balance as $value) {
            $pos = strpos($this->sourceCmd, $value);
            if ($pos !== false) {
                $strLeft = str_replace($value, '', $this->sourceCmd);
                $amount = $this->parseAmount($strLeft);
                if ($amount <= 0.0) {
                    $this->cmdType = COMMAND_BALANCE;
                    $this->cmdFormatted = $this->cmd_array_balance[0];
                    break;
                }
            }
        }
        if (COMMAND_BALANCE == $this->cmdType) {
            return COMMAND_BALANCE;
        }
        // For charge command
        foreach ($this->cmd_array_charge as $value) {
            $pos = strpos($this->sourceCmd, $value);
            if ($pos !== false) {
                $strLeft = str_replace($value, '', $this->sourceCmd);
                $amount = $this->parseAmount($strLeft);
                if ($amount > 0.0) {
                    $this->cmdType = COMMAND_CHARGE;
                    $this->cmdFormatted = $this->cmd_array_charge[0].'/'.$amount;
                    break;
                }
            }
        }
        if (COMMAND_CHARGE == $this->cmdType) {
            return COMMAND_CHARGE;
        }
        // For withdraw command
        foreach ($this->cmd_array_withdraw as $value) {
            $pos = strpos($this->sourceCmd, $value);
            if ($pos !== false) {
                $strLeft = str_replace($value, '', $this->sourceCmd);
                $amount = $this->parseAmount($strLeft);
                if ($amount > 0.0) {
                    $this->cmdType = COMMAND_WITHDRAW;
                    $this->cmdFormatted = $this->cmd_array_withdraw[0].'/'.$amount;
                    break;
                }
            }
        }
        if (COMMAND_WITHDRAW == $this->cmdType) {
            return COMMAND_WITHDRAW;
        }
        // For order command
        $order = array();
        $arr = explode("/", $this->sourceCmd);
        $size = count($arr);
        if ($size == 2) {
            array_push($order, "1");
            array_push($order, $arr[0]);
            array_push($order, $arr[1]);
        } else if ($size == 3) {
            array_push($order, $arr[0]);
            array_push($order, $arr[1]);
            array_push($order, $arr[2]);
        } else {
            return COMMAND_INVALID;
        }
        
        $formatted = $this->formatOrder($order);
        if ($formatted) {
            return COMMAND_ORDER;
        }
        
        return COMMAND_INVALID;
    }
    
    private function formatOrder($arrOrder) {
        // check first part
        $first = trim($arrOrder[0]);
        foreach ($this->cmd_array_order_line as $value) {
            $pos = strpos($first, $value);
            if ($pos !== false) {
                $this->orderLineNum = $this->cmd_array_order_line[0];
                break;
            }
        }
        if ($this->orderLineNum != $this->cmd_array_order_line[0]) {
            $lineNum = $this->formatNum($first);
            if ($lineNum === false) {
                return false;
            } else {
                $this->orderLineNum = $lineNum;
            }
        }
        
        // check second part
        $second = trim($arrOrder[1]);
        $num = $this->formatNum($second);
        if ($num === false) {
            foreach ($this->cmd_array_order_type as $key => $value) {
                $second = str_replace($key, $value, $second);
            }
            if (in_array($second, $this->cmd_array_type_collection)) {
                $this->orderValue = $second;
            } else {
                return false;
            }
        } else {
            $this->orderValue = $num + 1;
        }
        
        // check third part
        $third = trim($arrOrder[2]);
        $this->orderAmount = $this->parseAmount($third);
        
        // format it
        $this->cmdFormatted = $this->orderLineNum.'/'.$this->orderValue.'/'.$this->orderAmount;
        return $this->cmdFormatted;
    }
    
    private function formatNum($line) {
        $len = strlen($line);
        for ($i=0; $i<$len; $i++) {
            if (ord($line[$i]) > 57 || ord($line[$i]) < 48) {
                return false;
            }
        }
        $num = intval($line);
        if ($num < 0 || $num > 9) {
            return false;
        } else {
            return $num;
        }
    }
}
