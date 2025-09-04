<?php
class Encryption {
    private $algorithm;
    
    public function __construct($algorithm = 'enphp') {
        $this->algorithm = $algorithm;
    }
    
    public function encrypt($code) {
        switch ($this->algorithm) {
            case 'eval':
                return $this->evalEncrypt($code);
            case 'goto':
                return $this->gotoEncrypt($code);
            case 'enphp':
                return $this->enphpEncrypt($code);
            case 'enphpv2':
                return $this->enphpV2Encrypt($code);
            case 'jiamiZym':
                return $this->jiamiZymEncrypt($code);
            case 'magicTwo':
                return $this->magicTwoEncrypt($code);
            case 'noname1':
                return $this->noname1Encrypt($code);
            case 'phpjm':
                return $this->phpjmEncrypt($code);
            case 'phpjm2':
                return $this->phpjm2Encrypt($code);
            default:
                throw new Exception('未知的加密算法: ' . $this->algorithm);
        }
    }
    
    public function decrypt($code) {
        // 解密功能实现
        // 注意：某些算法可能是单向的或需要密钥，这里仅作示例
        return base64_decode($code);
    }
    
    private function evalEncrypt($code) {
        // 清理代码，移除PHP标签
        $code = $this->cleanCode($code);
        
        // 确保代码以分号结束
        $code = rtrim($code, ';') . ';';
        
        return 'eval(base64_decode("' . base64_encode($code) . '"));';
    }
    
    private function gotoEncrypt($code) {
        // 清理代码，移除PHP标签
        $code = $this->cleanCode($code);
        
        // 确保代码以分号结束
        $code = rtrim($code, ';') . ';';
        
        // 简化的goto加密示例
        $encoded = base64_encode($code);
        return 'goto a; die; a: eval(base64_decode("' . $encoded . '"));';
    }
    
    private function enphpEncrypt($code) {
        // 清理代码，移除PHP标签
        $code = $this->cleanCode($code);
        
        // 确保代码以分号结束
        $code = rtrim($code, ';') . ';';
        
        // 简化的enphp加密示例
        $encoded = str_rot13(base64_encode($code));
        return 'eval(str_rot13(base64_decode("' . $encoded . '")));';
    }
    
    private function enphpV2Encrypt($code) {
        // 清理代码，移除PHP标签
        $code = $this->cleanCode($code);
        
        // 确保代码以分号结束
        $code = rtrim($code, ';') . ';';
        
        // 简化的enphpv2加密示例
        $encoded = gzdeflate(base64_encode($code));
        $encoded = base64_encode($encoded);
        return 'eval(base64_decode(gzinflate(base64_decode("' . $encoded . '"))));';
    }
    
    private function jiamiZymEncrypt($code) {
        // 清理代码，移除PHP标签
        $code = $this->cleanCode($code);
        
        // 确保代码以分号结束
        $code = rtrim($code, ';') . ';';
        
        // 简化的jiamiZym加密示例
        $encoded = str_rot13(gzdeflate($code));
        $encoded = base64_encode($encoded);
        return 'eval(gzinflate(str_rot13(base64_decode("' . $encoded . '"))));';
    }
    
    private function magicTwoEncrypt($code) {
        // 清理代码，移除PHP标签
        $code = $this->cleanCode($code);
        
        // 确保代码以分号结束
        $code = rtrim($code, ';') . ';';
        
        // 简化的magicTwo加密示例
        $encoded = gzencode(base64_encode($code));
        $encoded = str_rot13($encoded);
        return 'eval(base64_decode(gzdecode(str_rot13("' . $encoded . '"))));';
    }
    
    private function noname1Encrypt($code) {
        // 清理代码，移除PHP标签
        $code = $this->cleanCode($code);
        
        // 确保代码以分号结束
        $code = rtrim($code, ';') . ';';
        
        // 简化的noname1加密示例
        $encoded = strrev(base64_encode($code));
        return 'eval(base64_decode(strrev("' . $encoded . '")));';
    }
    
    private function phpjmEncrypt($code) {
        // 清理代码，移除PHP标签
        $code = $this->cleanCode($code);
        
        // 确保代码以分号结束
        $code = rtrim($code, ';') . ';';
        
        // 简化的phpjm加密示例
        $encoded = gzcompress(base64_encode($code));
        $encoded = str_rot13($encoded);
        return 'eval(base64_decode(gzuncompress(str_rot13("' . $encoded . '"))));';
    }
    
    private function phpjm2Encrypt($code) {
        // 清理代码，移除PHP标签
        $code = $this->cleanCode($code);
        
        // 确保代码以分号结束
        $code = rtrim($code, ';') . ';';
        
        // 简化的phpjm2加密示例
        $encoded = gzdeflate(str_rot13(base64_encode($code)));
        $encoded = base64_encode($encoded);
        return 'eval(str_rot13(base64_decode(gzinflate(base64_decode("' . $encoded . '")))));';
    }
    
    // 清理代码的辅助函数
    private function cleanCode($code) {
        // 移除PHP开始和结束标签
        $code = trim($code);
        
        // 移除BOM标记
        $code = preg_replace('/^\xEF\xBB\xBF/', '', $code);
        
        // 移除PHP开始标签
        if (substr($code, 0, 5) === '<?php') {
            $code = substr($code, 5);
        } else if (substr($code, 0, 2) === '<?') {
            $code = substr($code, 2);
        }
        
        // 移除PHP结束标签
        if (substr($code, -2) === '?>') {
            $code = substr($code, 0, -2);
        }
        
        // 移除开头和结尾的空白字符
        $code = trim($code);
        
        // 移除HTML标签（如果存在）
        if (substr($code, 0, 1) === '<' && strpos($code, '>') !== false) {
            $firstTagEnd = strpos($code, '>') + 1;
            $code = substr($code, $firstTagEnd);
        }
        
        return trim($code);
    }
}
?>