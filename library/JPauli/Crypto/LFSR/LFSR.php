<?php
namespace JPauli\Crypto\LFSR;

use JPauli\Crypto\InvalidArgumentException;
use JPauli\Crypto\RuntimeException;

/******************************************************
 * Galois Linear Feedback Shift Register written in PHP
 ******************************************************
 *
 * If you ignore what this crucial structure is, read
 * https://en.wikipedia.org/wiki/Linear_feedback_shift_register
 *
 * This is the basic structure behind pseudo random number generator
 *
 * This is a POC. This should be useless to you.
 * This is usually implemented in C in many crypto libraries, like OpenSSL
 * This can also be embed in hardware, like in DVD players (for CSS),
 *  BR players (AACS), or GSM (A5/1)...
 *
 * Remember that the output of a single LFSR can be easilly
 * reverse engineered by Berlekamp-Massey algo, with only 2n data
 *
 * @author Julien Pauli <jpauli@php.net>
 */
class LFSR
{
    /* We stopped at 32bits, feel free to go until 64bits */
    public const LFSR_MAX_DEGREE = 32;

    private const DEFAULT_DISPLAY_SPEED = 1;

    /* You may use the reverse coefficient polynomial as well */
    private const POLYNOMIAL_PRIME_COEFF = [
        3 => [2],
        4 => [3],                 /* P(X) = 1 + x^3 + x^4 */
        5 => [3],                 /* P(X) = 1 + x^3 + x^5 */
        6 => [5],                 /* P(X) = 1 + x^5 + x^6 */
        7 => [6],                 /* P(X) = 1 + x^6 + x^7 */
        8 => [6, 5, 4],             /* P(X) = 1 + x^4 + x^5 + x^6 + x^8 */
        9 => [5],                 /* P(X) = 1 + x^5 + x^9 */
        10 => [7],                /* P(X) = 1 + x^7 + x^10 */
        11 => [9],                /* P(X) = 1 + x^9 + x^11 */
        12 => [6, 4, 1],            /* P(X) = 1 + x^1 + x^4 + x^6 + x^12 */
        13 => [4, 3, 1],            /* P(X) = 1 + x^1 + x^3 + x^4 + x^13 */
        14 => [5, 3, 1],            /* P(X) = 1 + x^1 + x^3 + x^5 + x^14 */
        15 => [14],               /* P(X) = 1 + x^14 + x^15 */
        16 => [14, 13, 11],         /* P(X) = 1 + x^11 + x^13 + x^14 + x^16 */
        17 => [14],               /* P(X) = 1 + x^14 + x^17 */
        18 => [11],               /* P(X) = 1 + x^11 + x^18 */
        19 => [6, 2, 1],            /* P(X) = 1 + x^1 + x^2 + x^6 + x^19 */
        20 => [17],               /* P(X) = 1 + x^17 + x^20 */
        21 => [19],               /* P(X) = 1 + x^19 + x^21 */
        22 => [21],               /* P(X) = 1 + x^21 + x^22 */
        23 => [18],               /* P(X) = 1 + x^18 + x^23 */
        24 => [23, 22, 17],         /* P(X) = 1 + x^17 + x^22 + x^23 + x^24 */
        25 => [22],               /* P(X) = 1 + x^22 + x^25 */
        26 => [6, 2, 1],            /* P(X) = 1 + x^1 + x^2 + x^6 + x^26 */
        27 => [5, 2, 1],            /* P(X) = 1 + x^1 + x^2 + x^5 + x^27 */
        28 => [25],               /* P(X) = 1 + x^25 + x^28 */
        29 => [27],               /* P(X) = 1 + x^27 + x^29 */
        30 => [6, 4, 1],            /* P(X) = 1 + x^1 + x^4 + x^6 + x^30 */
        31 => [28],               /* P(X) = 1 + x^28 + x^31 */
        32 => [22, 2, 1],           /* P(X) = 1 + x^1 + x^2 + x^22 + x^32 */
    ];

    /* Display speed */
    private int $speed = self::DEFAULT_DISPLAY_SPEED;

    /* Polynomial degree. The higher, the more values
     * will be generated ( 2^degree - 1 in case of m-sequence)
     * Should be set between 3 and 32
     */
    private int $degree;

    /* Also called the seed of the generator
     * Usually, the key is mixed up with some
     * initialization vector to have a maximum
     * unguessable starting state */
    private string $start;

    private bool $isPrepared = false;

    /* Feedback function */
    private int $ff = 0;

    /* LFSR taps re-entered (feedback function) */
    private array $taps = [];

    private int $iterations = 0;

    private string $currentState;

    private bool $running = false;

    public function __construct(int $degree, string|int $start, ?int $speed = null)
    {
        $this->setDegree($degree);
        $this->setStart($start);

        if ($speed) {
            $this->setSpeed($speed);
        }
    }

    public function pause(int $c = 1): self
    {
        usleep($c * 800000 / $this->speed);

        return $this;
    }

    public function setDegree(int $degree): self
    {
        if ($degree < 3 || $degree > self::LFSR_MAX_DEGREE) {
            throw new InvalidArgumentException("Degree must be between 3 and %d, %d given", self::LFSR_MAX_DEGREE, $degree);
        }

        $this->degree = $degree;

        return $this;
    }

    public function getDegree(): int
    {
        return $this->degree;
    }

    public function setSpeed(int $speed): self
    {
        if ($speed < 1 || $speed > 100) {
            throw new InvalidArgumentException("Speed must be between 1 and 100");
        }
        $this->speed = $speed;

        return $this;
    }

    public function getSpeed(): int
    {
        return $this->speed;
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function setStart(string|int $start): self
    {
        if (!is_numeric($start)) {
            $l = unpack("C*", $start);
            $start = array_sum($l);
        }
        $this->start = (int)$start;

        return $this;
    }

    public function getIterations(): int
    {
        return $this->iterations;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    private function prepare() : void
    {
        if ($this->isPrepared) {
            return;
        }

        $this->taps[] = $this->degree;

        for ($i = 0; $i < count(self::POLYNOMIAL_PRIME_COEFF[$this->degree]); $i++) {
            $this->taps[] = self::POLYNOMIAL_PRIME_COEFF[$this->degree][$i];
            $this->ff |= (1 << self::POLYNOMIAL_PRIME_COEFF[$this->degree][$i]);
        }

        /* LFSR always has first and last bit set */
        $this->ff |= 1 << ($this->degree);
        $this->ff |= 1;

        $this->isPrepared = true;
    }

    public function reset(): self
    {
        $this->prepare();

        return $this;
    }

    public function run(): \Generator
    {
        $this->prepare();

        $this->currentState = $this->start;
        $this->iterations = 0;
        $this->running = true;

        /* yield initial state */
        yield $this->iterations => $this->currentState;

        do {
            $this->currentState >>= 1; /* Shift register */

            yield ++$this->iterations => $this->currentState;

            if ($this->currentState & 1) {
                $this->currentState ^= $this->ff; /* re-enter as Galois */
            }

        } while ($this->currentState != $this->start);

        $this->running = false;
    }

    public function demoRun(): \Generator
    {
        $this->prepare();

        echo "\n\n";

        printf("**Simple Galois LFSR, degree %d (%d states m-sequence)**\n", $this->getDegree(), 2 ** $this->getDegree() - 1);
        printf("Used register bits for feedback : %s\n", implode(' ', $this->taps));
        printf("Deducted Feedback function      : %b (0X%1\$X) \n", $this->ff >> 1);

        echo "\n\n";

        $this->pause(4);

        printf("Your initial state is : %032b (%1\$u)\n", $this->start);
        printf("Let's now start the Linear Feedback Shift Register\n");

        $this->pause(4);

        echo "\n\n";

        printf("[Iteration] [-------Internal Register -------] [PRandom bit]\n");
        printf("    |                      |                        |       \n");
        printf("    v                      v                        v       \n");

        yield from $this->run();
    }

    public function getCurrentState(): int
    {
        return $this->currentState;
    }

    public function getCurrentBit(): int
    {
        return $this->currentState & 1;
    }

    public function printCurrentState(): ?self
    {
        if (!$this->running) {
            return null;
        }

        printf("%10d - %032b     [ %d ]\n", $this->iterations, $this->currentState, $this->getCurrentBit());

        return $this;
    }

    public function __debugInfo()
    {
        /* Hide internal state */
        return [];
    }
}