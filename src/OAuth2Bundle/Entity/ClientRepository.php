<?php

/**
 * This file is part of the authbucket/oauth2-symfony-bundle package.
 *
 * (c) Wong Hoi Sing Edison <hswong3i@pantarei-design.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AuthBucket\Bundle\OAuth2Bundle\Entity;

use AuthBucket\OAuth2\Model\ClientManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * ClientRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClientRepository extends EntityRepository implements ClientManagerInterface
{
    use ModelManagerEntityRepository;
}