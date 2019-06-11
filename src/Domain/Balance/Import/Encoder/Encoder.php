<?php
namespace App\Domain\Balance\Import\Encoder;

/**
 * Description of Encoder
 *
 * @author Sebastian
 */
class Encoder {

    const ENCODING_UTF_8 = 'UTF-8';
    const ENCODING_WINDOWS_1250 = 'Windows-1250';
    
    /**
     * file encoding
     * 
     * @var string
     */
    protected $encoding;
    
    /**
     * path to imported file
     * 
     * @var string
     */
    protected $filePath;
    
    /**
     * @param string $filePath
     */
    public function __construct($filePath) {
        $this->filePath = $filePath;
        $this->checkFileEncoding();
    }
    
    
    /**
     * check file encoding, correct is UTF-8, Windows-1250
     * 
     * @param string $fileUri - path to imported file
     * 
     * @return string|null
     * 
     * @throws \Exception
     */
    public function checkFileEncoding(){
        $file = file_get_contents($this->filePath);

        // check utf-8
        if(mb_detect_encoding($file, 'UTF-8', true)) {
            return $this->encoding = self::ENCODING_UTF_8;
        }
        
        // check windows-1250
        $sample = iconv('windows-1251', 'windows-1251', $file);
        if (md5($sample) == md5($file)) { 
            return $this->encoding = self::ENCODING_WINDOWS_1250;
        }
         
        throw new \Exception('Nieznane kodowanie pliku!');
    }
    
    /**
     * convert line by recognized encodig
     * 
     * @param string $value
     * 
     * @return string
     */
    public function convertToUtf8($value) {
        switch ($this->encoding) {
            case self::ENCODING_WINDOWS_1250:
                return $this->w1250ToUtf8($value);
        }
        return $value;
    }
    
    
    /**
     * convert string in Windows-1250 to UTF-8
     * 
     * @param string $text - input text
     * 
     * @return string
     */
    public function w1250ToUtf8($text) {
        // map based on:
        // http://konfiguracja.c0.pl/iso02vscp1250en.html
        // http://konfiguracja.c0.pl/webpl/index_en.html#examp
        // http://www.htmlentities.com/html/entities/
        $map = array(
            chr(0x8A) => chr(0xA9),
            chr(0x8C) => chr(0xA6),
            chr(0x8D) => chr(0xAB),
            chr(0x8E) => chr(0xAE),
            chr(0x8F) => chr(0xAC),
            chr(0x9C) => chr(0xB6),
            chr(0x9D) => chr(0xBB),
            chr(0xA1) => chr(0xB7),
            chr(0xA5) => chr(0xA1),
            chr(0xBC) => chr(0xA5),
            chr(0x9F) => chr(0xBC),
            chr(0xB9) => chr(0xB1),
            chr(0x9A) => chr(0xB9),
            chr(0xBE) => chr(0xB5),
            chr(0x9E) => chr(0xBE),
            chr(0x80) => '&euro;',
            chr(0x82) => '&sbquo;',
            chr(0x84) => '&bdquo;',
            chr(0x85) => '&hellip;',
            chr(0x86) => '&dagger;',
            chr(0x87) => '&Dagger;',
            chr(0x89) => '&permil;',
            chr(0x8B) => '&lsaquo;',
            chr(0x91) => '&lsquo;',
            chr(0x92) => '&rsquo;',
            chr(0x93) => '&ldquo;',
            chr(0x94) => '&rdquo;',
            chr(0x95) => '&bull;',
            chr(0x96) => '&ndash;',
            chr(0x97) => '&mdash;',
            chr(0x99) => '&trade;',
            chr(0x9B) => '&rsquo;',
            chr(0xA6) => '&brvbar;',
            chr(0xA9) => '&copy;',
            chr(0xAB) => '&laquo;',
            chr(0xAE) => '&reg;',
            chr(0xB1) => '&plusmn;',
            chr(0xB5) => '&micro;',
            chr(0xB6) => '&para;',
            chr(0xB7) => '&middot;',
            chr(0xBB) => '&raquo;',
        );
        return html_entity_decode(mb_convert_encoding(strtr($text, $map), 'UTF-8', 'ISO-8859-2'), ENT_QUOTES, 'UTF-8');
    }
}
