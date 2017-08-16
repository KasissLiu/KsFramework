<?php

/**
 * @author kasiss
 * @date 2017-08-11
 * @description
 *  简单的日志记录类
 */
class KsfLogger
{
    // 日志路径
    private $logPath;
    // 日志基础名称
    private $name;
    // 日志内容格式化类型
    private $format;
    // 日志分割类型
    private $split;
    // 单例
    private static $_instance;
    // 日志格式 json
    const FORMAT_TYPE_JSON = 1;
    // 日志格式 text
    const FORMAT_TYPE_TEXT = 2;
    // 日志按天分割
    const SPLIT_TYPE_DAY = 1;
    // 日志按小时分割
    const SPLIT_TYPE_HOUR = 2;

    public function __construct()
    {
        $this->logPath = defined('LOG_PATH') ? LOG_PATH : ROOT_PATH . 'storage/log';
        $this->name = 'log';
        $this->split = self::SPLIT_TYPE_DAY;
        $this->format = self::FORMAT_TYPE_TEXT;
    }

    /**
     * 配置设置
     * 
     * @param string $path            
     * @param string $name            
     * @param string $split            
     * @param string $format            
     */
    public function config($path = '', $name = '', $split = '', $format = '')
    {
        if ($path)
            $this->logPath = $path;
        if ($name)
            $this->name = $name;
        if (in_array($split, array(
            self::SPLIT_TYPE_DAY,
            self::SPLIT_TYPE_HOUR
        )))
            $this->split = $split;
        if (in_array($format, array(
            self::FORMAT_TYPE_JSON,
            self::FORMAT_TYPE_TEXT
        )))
            $this->format = $format;
    }

    /**
     * 普通信息记录
     * 
     * @param unknown $msg            
     * @param unknown $content            
     * @return boolean
     */
    public function info($msg, $content)
    {
        return $this->writerLog('info', $msg, $content);
    }

    /**
     * 调试信息记录
     * 
     * @param unknown $msg            
     * @param unknown $content            
     * @return boolean
     */
    public function debug($msg, $content)
    {
        return $this->writerLog('debug', $msg, $content);
    }

    /**
     * 错误信息记录
     * 
     * @param unknown $msg            
     * @param unknown $content            
     * @return boolean
     */
    public function error($msg, $content)
    {
        return $this->writerLog('error', $msg, $content);
    }

    /**
     * 获取日志文件名
     */
    private function getFileName()
    {
        switch ($this->split) {
            case self::SPLIT_TYPE_HOUR:
                return $this->name . '_' . date('Y_m_d') . '_' . date('H') . '.log';
            case self::SPLIT_TYPE_DAY:
                return $this->name . '_' . date('Y_m_d') . '.log';
            default:
                return $this->name;
        }
    }

    /**
     * 获取日志写入内容
     * 
     * @param unknown $level            
     * @param unknown $msg            
     * @param unknown $content            
     */
    private function getContent($level, $msg, $content)
    {
        switch ($this->format) {
            case self::FORMAT_TYPE_JSON:
                return $this->getJsonContent($level, $msg, $content);
            case self::FORMAT_TYPE_TEXT:
                return $this->getTextContent($level, $msg, $content);
            default:
                return null;
        }
    }

    /**
     * 获取text格式内容
     * 
     * @param unknown $level            
     * @param unknown $msg            
     * @param unknown $content            
     */
    private function getTextContent($level, $msg, $content)
    {
        $info = array();
        $info['@level'] = $level;
        $info['@msg'] = $msg;
        $info['@timestamp'] = date('Y-m-d H:i:s');
        $info['@data'] = json_encode($content);
        
        $content = '[' . date('Y-m-d H:i:s') . ']';
        foreach ($info as $key => $val) {
            $content .= $key . ':' . $val . ';';
        }
        return $content;
    }

    /**
     * 获取json格式内容
     * 
     * @param unknown $level            
     * @param unknown $msg            
     * @param unknown $content            
     */
    private function getJsonContent($level, $msg, $content)
    {
        $info = array();
        $info['@level'] = $level;
        $info['@msg'] = $msg;
        $info['@timestamp'] = date('Y-m-d H:i:s');
        $info['@data'] = $content;
        
        return json_encode($info);
    }

    /**
     * 写入日志
     * 
     * @param string $level            
     * @param string $msg            
     * @param string $content            
     */
    private function writerLog($level = 'info', $msg = '', $content = '')
    {
        $name = $this->getFileName();
        $filename = rtrim($this->logPath, '/') . '/' . $name;
        $message = $this->getContent($level, $msg, $content);
        
        $fp = fopen($filename, "a+");
        fputs($fp, $message . "\n");
        fclose($fp);
        return true;
    }

    /**
     * 获取单例
     */
    public static function getInstance()
    {
        if (! self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
}