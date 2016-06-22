<?php

namespace Raegmaen\OpenIdConnect\Tests\ValueObjects;

use Raegmaen\OpenIdConnect\Exceptions\InvalidArgumentException;
use Raegmaen\OpenIdConnect\Token\OauthAccessToken;

/**
 * @see OauthAccessToken
 */
class OauthAccessTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider provideValidCreationData
     *
     * @param string      $tokenString
     * @param int         $expireIn
     * @param string      $type
     * @param string|null $refreshTokenString
     *
     * @throws InvalidArgumentException
     */
    public function shouldCreateAnOauthAccessToken($tokenString, $expireIn, $type, $refreshTokenString)
    {
        $expireAt = new \DateTime('@' . (time() + $expireIn));
        $token = OauthAccessToken::create($tokenString, $expireIn, $type, $refreshTokenString);

        $this->assertEquals($tokenString, $token->getTokenString());
        $this->assertEquals($expireAt->getTimestamp(), $token->getExpireAt()->getTimestamp());
        $this->assertFalse($token->isExpired());
        $this->assertEquals($type, $token->getType());
        $this->assertEquals($refreshTokenString, $refreshTokenString);

        if (null === $refreshTokenString) {
            $this->assertFalse($token->canBeRefreshed());
        } else {
            $this->assertTrue($token->canBeRefreshed());
        }
    }

    /**
     * @return \Generator
     */
    public function provideValidCreationData()
    {
        yield 'Valid data #1' => [
            'tokenString' => '345678905456790543',
            'expireIn' => 3600,
            'type' => 'Bearer',
            'refreshTokenString' => '9898798798798798797',
        ];

        yield 'Valid data #2 - no refreshTokenString' => [
            'tokenString' => '345678905456790543',
            'expireIn' => 3600,
            'type' => 'Bearer',
            'refreshTokenString' => null,
        ];

        yield 'Valid data #3 - almost expired' => [
            'tokenString' => '345678905456790543',
            'expireIn' => 1,
            'type' => 'Bearer',
            'refreshTokenString' => '9898798798798798797',
        ];
    }

    /**
     * @test
     * @dataProvider provideInvalidCreationData
     *
     * @param string      $tokenString
     * @param int         $expireIn
     * @param string      $type
     * @param string|null $refreshTokenString
     * @param string      $expectedExceptionMessage
     *
     * @throws InvalidArgumentException
     */
    public function shouldThrowInvalidArgumentException(
        $tokenString,
        $expireIn,
        $type,
        $refreshTokenString,
        $expectedExceptionMessage
    ) {
        $this->setExpectedException(InvalidArgumentException::class, $expectedExceptionMessage);
        OauthAccessToken::create($tokenString, $expireIn, $type, $refreshTokenString);
    }

    /**
     * @return \Generator
     */
    public function provideInvalidCreationData()
    {
        yield 'Invalid tokenString #1' => [
            'tokenString' => '',
            'expireIn' => 4,
            'type' => 'Bearer',
            'refreshTokenString' => null,
            'expectedExceptionMessage' => "Invalid token string ''",
        ];

        yield 'Invalid tokenString #2' => [
            'tokenString' => 123,
            'expireIn' => 4,
            'type' => 'Bearer',
            'refreshTokenString' => null,
            'expectedExceptionMessage' => "Invalid token string '123'",
        ];

        yield 'Token expired already #1' => [
            'tokenString' => '5654645645654',
            'expireIn' => 0,
            'type' => 'Bearer',
            'refreshTokenString' => null,
            'expectedExceptionMessage' => "Token already expired!",
        ];

        yield 'Token expired already #2' => [
            'tokenString' => '5654645645654',
            'expireIn' => -1000,
            'type' => 'Bearer',
            'refreshTokenString' => null,
            'expectedExceptionMessage' => "Token already expired!",
        ];

        yield 'Invalid type #1' => [
            'tokenString' => '456456456',
            'expireIn' => 4,
            'type' => '',
            'refreshTokenString' => null,
            'expectedExceptionMessage' => "Invalid type ''",
        ];

        yield 'Invalid type #2' => [
            'tokenString' => '456456456',
            'expireIn' => 4,
            'type' => 123,
            'refreshTokenString' => null,
            'expectedExceptionMessage' => "Invalid type '123'",
        ];

        yield 'Invalid refreshTokenString #1' => [
            'tokenString' => '456456456',
            'expireIn' => 4,
            'type' => 'Bearer',
            'refreshTokenString' => '',
            'expectedExceptionMessage' => "Invalid refresh token string ''",
        ];

        yield 'Invalid refreshTokenString #2' => [
            'tokenString' => '456456456',
            'expireIn' => 4,
            'type' => 'Bearer',
            'refreshTokenString' => 123123,
            'expectedExceptionMessage' => "Invalid refresh token string '123123'",
        ];
    }
}
