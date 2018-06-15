<?php
/**
 * User: Derek
 * Date: 2018-04-27
 */

class Command {
    private $sourceCmd = "";
    private $cmd_array_charge = array('CG', '查', '充', '冲', '上');
    private $cmd_array_balance = array('QY', '查', '看');
    private $cmd_array_withdraw = array('WD', '回');
    private $cmd_array_order_line = array('99', '和', '合');
    private $cmd_array_order_type = array(
        '大' => 'Z',
        '小' => 'A',
        '单' => 'D',
        '双' => "S"
    );
    private $cmd_array_type_collection = array('Z', 'A', 'D', 'S');
    private $cmd_array_cancel = array('CANCEL', '取消', '错误');
    
    private $cmdType = COMMAND_INVALID;
    private $cmdFormatted = "";
    
    private $orderLineNum = 0;  // 99 和， 0 - 9 道次
    private $orderValue = '';
    private $orderAmount = 0.0;
    private $orderList = array();
    
    public function __construct($cmd) {
        $this->sourceCmd = trim($cmd, " /\t\n\r\0\x0B");
        $this->cmdFormatted = $this->sourceCmd;
        $this->cmdType = $this->formatCommand();
    }
    
    public function getCmdFormatted() {
        return $this->cmdFormatted;
    }
    
    public function getCmdType() {
        return $this->cmdType;
    }
    
    public function getOrderList() {
        return $this->orderList;
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
    
    private function preprocess() {
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
    }
    
    private function formatCommand() {
        $this->preprocess();
        
        // For cancel command
        foreach ($this->cmd_array_cancel as $value) {
            $pos = strpos($this->sourceCmd, $value);
            if ($pos !== false) {
                $this->cmdType = COMMAND_CANCEL;
                $this->cmdFormatted = $this->cmd_array_cancel[0];
                break;
            }
        }
        if (COMMAND_CANCEL == $this->cmdType) {
            return COMMAND_CANCEL;
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
                    $this->orderAmount = $amount;
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
                    $this->orderAmount = $amount;
                    $this->cmdFormatted = $this->cmd_array_withdraw[0].'/'.$amount;
                    break;
                }
            }
        }
        if (COMMAND_WITHDRAW == $this->cmdType) {
            return COMMAND_WITHDRAW;
        }
        // For order command
        $orders = $this->parseOrderList();
        if ($orders == false) {
            $this->cmdType = COMMAND_INVALID;
            return COMMAND_INVALID;
        } else {
            $this->cmdType = COMMAND_ORDER;
            $this->orderList = $orders;
            return COMMAND_ORDER;
        }
        
        return COMMAND_INVALID;
    }
    
    private function parseOrderList() {
        $result = $this->parseOrdersOfUnion();
        if ($result === false) {
            $result = $this->parseOrdersOfSingle();
        }
        return $result;
    }
    
    private function parseOrdersOfUnion() {
        $strLeft = $this->sourceCmd;
        foreach ($this->cmd_array_order_line as $value) {
            $pos = strpos($this->sourceCmd, $value);
            if ($pos !== false) {
                $strLeft = str_replace($value, '', $this->sourceCmd);
                $lines = array();
                array_push($lines, $this->cmd_array_order_line[0]);
                return $this->parseOrdersOfDetail($lines, $strLeft);
            }
        }
        return false;
    }
    
    private function parseOrdersOfSingle() {
        $strLeft = $this->sourceCmd;
        $lines = array();
        $arr = explode("/", $strLeft);
        $size = count($arr);
        if ($size == 3) {
            $lines = $this->parseLinesForSingleType($arr[0]);
            if (count($lines) > 0) {
                $strLeft = $arr[1] . '/' . $arr[2];
            } else {
                return false;
            }
        } else {
            array_push($lines, '1');
        }
        return $this->parseOrdersOfDetail($lines, $strLeft);
    }
    
    private function parseOrdersOfDetail($lines, $detail) {
        $msgLeft = trim($detail, " /\t\n\r\0\x0B");
        foreach ($this->cmd_array_order_type as $key => $value) {
            $msgLeft = str_replace($key, $value, $msgLeft);
        }
        
        $isUnion = false;
        if (count($lines) == 1 && $lines[0] == '99') {
            $isUnion = true;
        }
        
        $values = array();
        $value = '';
        $amount = 0;
        
        $pos = strpos($msgLeft, 'Z');
        if ($pos !== false) {
            $value = 'Z';
        }
        $pos = strpos($msgLeft, 'A');
        if ($pos !== false) {
            $value = 'A';
        }
        $pos = strpos($msgLeft, 'D');
        if ($pos !== false) {
            $value = 'D';
        }
        $pos = strpos($msgLeft, 'S');
        if ($pos !== false) {
            $value = 'S';
        }
        
        if ($value === '') {
            // lines + amount
            $arr = explode("/", $msgLeft);
            $size = count($arr);
            if ($size == 2) {
                if ($isUnion) {
                    $values = $this->parseValuesForUnionType($arr[0]);
                } else {
                    $values = $this->parseValuesForSingleType($arr[0]);
                }
                $amount = trim($arr[1]);
                $amount = intval($amount);
                if ($values === false) {
                    return false;
                }
            } else {
                // incorrect format
                return false;
            }
        } else {
            // 大小单双 + amount
            $amount = str_replace($value, '', $msgLeft);
            $amount = trim($amount, " /");
            $amount = intval($amount);
            if ($amount < 0 || $amount > 500000) {
                return false;
            } else {
                array_push($values, $value);
            }
        }
        
        return $this->generateOrders($lines, $values, $amount);
    }
    
    private function generateOrders($lines, $values, $amount) {
        $orders = array();
        foreach ($lines as $line) {
            foreach ($values as $value) {
                $this->orderAmount += intval($amount);
                // $order = $line . '/' . $value . '/' . strval($amount);
                $order = array(
                    'line' => $line,
                    'value' => $value,
                    'amount' => $amount
                );
                array_push($orders, $order);
            }
        }
        return $orders;
    }
    
    private function parseLinesForSingleType($valueStr) {
        // 1367
        $lines = array();
        $valueStr = strval($valueStr);
        $len = strlen($valueStr);
        for ($i=0; $i<$len; $i++) {
            $v = intval( $valueStr[$i] );
            if ($v >= 0 && $v <= 9) {
                if ($v == 0) {
                    $v = 10;
                }
                array_push($lines, strval($v));
            }
        }
        return $lines;
    }
    private function parseValuesForSingleType($valueStr) {
        // 1367
        $values = array();
        $valueStr = strval($valueStr);
        $len = strlen($valueStr);
        for ($i=0; $i<$len; $i++) {
            $v = intval( $valueStr[$i] );
            if ($v >= 0 && $v <= 9) {
                if ($v == 0) {
                    $v = 10;
                }
                array_push($values, strval($v));
            }
        }
        return $values;
    }
    private function parseValuesForUnionType($valueStr) {
        // 131415 or 5 or 136711
        $values = array();
        $valueStr = strval($valueStr);
        $len = strlen($valueStr);
        for ($i=0; $i<$len; $i++) {
            $sv = $valueStr[$i];
            if ($sv == '1' && $i < $len-1) {
                $i++;
                $sv = $sv . $valueStr[$i];
            }
            $v = intval($sv);
            if ($v >= 0 && $v <= 19) {
                if ($v == 0) {
                    $v = 10;
                }
                array_push($values, strval($v));
            }
        }
        return $values;
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
                if ($lineNum == 0) {
                    $this->orderLineNum = 10;
                } else {
                    $this->orderLineNum = $lineNum;
                }
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
            if ($num == 0) {
                $this->orderValue = 10;
            } else {
                $this->orderValue = $num;
            }
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
