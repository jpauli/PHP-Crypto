<?php

namespace JPauli\Crypto\LFSR;

use JPauli\Crypto\RuntimeException;

/*
 * A simple stream cipher using one LFSR
 *
 * @author Julien Pauli <jpauli@php.net>
 */

class StreamCipher
{
    private int $degree;

    public function __construct(private readonly string $seed, private readonly int $dataSize, private readonly bool $debug = false)
    {
        $degree = (int)ceil(log($dataSize * 8, 2));

        if ($degree > LFSR::LFSR_MAX_DEGREE) {
            throw new RuntimeException("Your data stream is too large :%d , maximum is %d bytes", $dataSize, 2 ** LFSR::LFSR_MAX_DEGREE - 1, LFSR::LFSR_MAX_DEGREE);
        }

        $this->degree = $degree;
    }

    private function getRandomByte(LFSR $lfsr): string
    {
        $random = 0;
        $run = $lfsr->run();

        for ($j = 0; $j < 8; $j++) {
            $random |= $lfsr->getCurrentBit() << $j;
            $run->next();
        }

        return $random;
    }

    private function debugPrintf(string $text, ...$args) : void
    {
        if (!$this->debug) {
            return;
        }
        vprintf($text . "\n", $args);
    }

    public function cipher(string $input): string
    {
        if (strlen($input) > $this->dataSize) {
            throw new RuntimeException("Your data size is too large, this cipher will work on %d bytes maximum", $this->dataSize);
        }

        $dataSize = strlen($input);
        $lfsr = new LFSR($this->degree, $this->seed);
        $i = 0;
        $output = '';

        $this->debugPrintf("Your input data is '%s'\n", $input);
        $this->debugPrintf("We are now going to crypt it byte per byte \n");
        $this->debugPrintf("\n");

        do {
            $random = $this->getRandomByte($lfsr);
            $this->debugPrintf("Random byte got from LFSR : %08b\n", $random);
            $data = unpack('C', $input[$i]);
            $this->debugPrintf("Next byte from your input : %08b (%1\$c)\n", $data[1]);
            $output .= pack('C', $outputByte = $data[1] ^ $random);
            $this->debugPrintf("------------------------------------\n");
            $this->debugPrintf("XORed crypted output byte : %08b\n", $outputByte);
            $this->debugPrintf("\n");
        } while (++$i < $dataSize);

        return $output;
    }
}