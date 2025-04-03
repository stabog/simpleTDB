<?php

class StringHelper {
    public string $str;

    public function replaceForm (string $str) : string{
        $str = preg_replace("/(\r\n)+/i", " ", $str);
        $str = preg_replace("/[\s]{3,}/", " ", $str);
        $str = preg_replace('!\s++!u', ' ', $str);
        
        $str = trim($str);
        
        return $str; 
    } 
        
    public function replaceFormSimple (string $str) : string{
        $str = preg_replace("/(\r\n)/i", "<br />", $str);
        $str = preg_replace("/[\s]{3,}/", " ", $str);
        $str = preg_replace('!\s++!u', ' ', $str);
        
        $str = trim($str);
        
        return $str; 
    } 
        
    public function restoreFormSimpl (string $str) : string{
        $str = preg_replace('/\<br(\s*)?\/?\>/i', "\r\n", $str);
        return $str; 
        } 
        
        
    public function replaceString (string $str) : string{
        $str = preg_replace("/(\r\n)+/i", " ", $str);
        $str = preg_replace("/[\s]{3,}/", " ", $str);
        $str = preg_replace('!\s++!u', ' ', $str);
        
        $str = trim($str);
        $str = htmlspecialchars ($str);
        return $str; 
    } 
        
        /*--- Транслитерация ---*/
        
        
    public function translitIt(string $str) : string{
            
        $tr = array(
            "А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
            "Д"=>"d","Е"=>"e","Ж"=>"zh","З"=>"z","И"=>"i",
            "Й"=>"j","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
            "О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
            "У"=>"u","Ф"=>"f","Х"=>"kh","Ц"=>"ts","Ч"=>"ch",
            "Ш"=>"sh","Щ"=>"shh","Ъ"=>"","Ы"=>"y","Ь"=>"",
            "Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"zh",
            "з"=>"z","и"=>"i","й"=>"j","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"kh",
            "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"shh","ъ"=>"",
            "ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
            " "=>"-","?"=>"","!"=>"","*"=>"","'"=>"","\""=>"",
            ":"=>"","™"=>"","’"=>"","&"=>"","/"=>"-",
            "."=>"",","=>"","("=>"",")"=>"","«"=>"","»"=>"","№"=>""
            
        );
        
        $ss = array(
            "---"=>"-","--"=>"-",
        );
        
        $str = trim(ltrim($str));
        $str = preg_replace("/[\s]{2,}/", " ", $str);
        
        $str = strtolower($str);
        $str = strtr(trim($str),$tr);
        $str = strtr(trim($str),$ss);
        return $str;
    }

    public function guidv4($data = null) {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);
    
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function templateReplacement ($patterns, $string)
    {
        foreach ($patterns as $template => $value){
            $pattern = '^\['.$template.'\]^';
            $replacement = $value;
            $string = preg_replace($pattern, $replacement, $string);
        }
        return $string;
    }
        


}

