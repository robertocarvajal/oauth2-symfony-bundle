<?php

/**
 * This file is part of the authbucket/oauth2-symfony-bundle package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AuthBucket\Bundle\OAuth2Bundle\Tests\GrantType;

use AuthBucket\Bundle\OAuth2Bundle\Tests\WebTestCase;
use AuthBucket\OAuth2\GrantType\GrantTypeHandlerFactory;
use AuthBucket\OAuth2\TokenType\TokenTypeHandlerFactory;

class GrantTypeHandlerFactoryTest extends WebTestCase
{
    /**
     * @expectedException \AuthBucket\OAuth2\Exception\UnsupportedGrantTypeException
     */
    public function testNonExistsGrantTypeHandler()
    {
        $classes = ['foo' => 'AuthBucket\\Bundle\\OAuth2Bundle\\Tests\\GrantType\\NonExistsGrantTypeHandler'];
        $factory = new GrantTypeHandlerFactory(
            $this->get('security.token_storage'),
            $this->get('test.security.encoder_factory'),
            $this->get('validator'),
            $this->get('test.authbucket_oauth2.model_manager.factory'),
            $this->get('test.'.TokenTypeHandlerFactory::class),
            null,
            $classes
        );
    }

    /**
     * @expectedException \AuthBucket\OAuth2\Exception\UnsupportedGrantTypeException
     */
    public function testBadAddGrantTypeHandler()
    {
        $classes = ['foo' => 'AuthBucket\\Bundle\\OAuth2Bundle\\Tests\\GrantType\\FooGrantTypeHandler'];
        $factory = new GrantTypeHandlerFactory(
            $this->get('security.token_storage'),
            $this->get('test.security.encoder_factory'),
            $this->get('validator'),
            $this->get('test.authbucket_oauth2.model_manager.factory'),
            $this->get('test.'.TokenTypeHandlerFactory::class),
            null,
            $classes
        );
    }

    /**
     * @expectedException \AuthBucket\OAuth2\Exception\UnsupportedGrantTypeException
     */
    public function testBadGetGrantTypeHandler()
    {
        $classes = ['bar' => 'AuthBucket\\Bundle\\OAuth2Bundle\\Tests\\GrantType\\BarGrantTypeHandler'];
        $factory = new GrantTypeHandlerFactory(
            $this->get('security.token_storage'),
            $this->get('test.security.encoder_factory'),
            $this->get('validator'),
            $this->get('test.authbucket_oauth2.model_manager.factory'),
            $this->get('test.'.TokenTypeHandlerFactory::class),
            null,
            $classes
        );
        $handler = $factory->getGrantTypeHandler('foo');
    }

    public function testGoodGetGrantTypeHandler()
    {
        $classes = ['bar' => 'AuthBucket\\Bundle\\OAuth2Bundle\\Tests\\GrantType\\BarGrantTypeHandler'];
        $factory = new GrantTypeHandlerFactory(
            $this->get('security.token_storage'),
            $this->get('test.security.encoder_factory'),
            $this->get('validator'),
            $this->get('test.authbucket_oauth2.model_manager.factory'),
            $this->get('test.'.TokenTypeHandlerFactory::class),
            null,
            $classes
        );
        $handler = $factory->getGrantTypeHandler('bar');
        $this->assertSame($factory->getGrantTypeHandlers(), $classes);
    }
}
