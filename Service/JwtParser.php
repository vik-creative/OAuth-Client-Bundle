<?php declare(strict_types=1);

namespace Creative\AuthClientBundle\Service;

use Lcobucci\JWT\Decoder;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\LocalFileReference;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

class JwtParser
{
    private Decoder $decoder;

    public function __construct(private string $publicKeyPath, Decoder | null $decoder = null)
    {
        $this->decoder = $decoder ?? new JoseEncoder();
    }

    public function parse(string $key): UnencryptedToken
    {
        $token = (new Token\Parser($this->decoder))->parse($key);
        $this->verify($token);

        if (!$token instanceof UnencryptedToken) {
            throw new \InvalidArgumentException(\sprintf('Token must implements %s, %s instance given', UnencryptedToken::class, \get_debug_type($token)));
        }

        return $token;
    }

    protected function verify(Token $token): void
    {
        $keyObject = LocalFileReference::file($this->publicKeyPath, '66acbb5f79e0ccbff224be4d814bde72');
        $signer = new Sha256();

        (new SignedWith($signer, $keyObject))->assert($token);
    }
}
