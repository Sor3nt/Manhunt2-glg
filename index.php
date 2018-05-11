<?php

    /**
     * Class Manhunt2Gig
     *
     * Able to extract and compress any Manhunt 2 GLG Config File
     */
    class Manhunt2Gig{

        private $header = [
            // the gig header
            'start' => '5A32484D',

            // content lenght
            'length' => '00000000',

            // i quess its just a separator
            'separator' => '78DA'
        ];

        public function pack($content){

            $length = dechex(strlen($content));
            if (strlen($length) == 1) $length = '0' . $length;
            $length = str_pad($length, strlen($this->header['length']), 0);

            $content = $this->compress($content);

            $packed =
                $this->header['start'] .
                $length .
                $this->header['separator'] .
                $content
            ;

            return hex2bin($packed);
        }

        public function unpack($content){

            $content = substr( bin2hex($content),
                strlen($this->header['start']) +
                strlen($this->header['length']) +
                strlen($this->header['separator'])
            );

            return zlib_decode(hex2bin($content));
        }

        private function compress($string){

            $hex = bin2hex(zlib_encode($string, ZLIB_ENCODING_DEFLATE));

            if (substr($hex, 0, 4) !== '789c'){
                throw new Exception('Unknown header start');
            }

            $hex = substr($hex, 4);

            return $hex;
        }
    }

    array_shift($argv);

    if (count($argv) != 2){
        echo "Manhunt 2 GLG Pack/Unpack\n";
        echo str_repeat('¯', 48) . "\n";
        echo "=> Usage: mh2-glg <pack|unpack> <file>\n";
        echo str_repeat('¯', 48) . "\n";
        exit;
    }

    $doPack = $argv[0] === 'p' || $argv[0] === 'pack';
    $file = $argv[1];

    $mh2Mdl = new Manhunt2Gig();

    $fileContent = file_get_contents($file);

    if ($doPack){
        file_put_contents(str_replace('.unpack', '', $file), $mh2Mdl->pack($fileContent));
    }else{
        file_put_contents($file . '.unpack', $mh2Mdl->unpack($fileContent));
    }

    echo "Done";
