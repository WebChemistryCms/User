<?php

declare(strict_types=1);

namespace Thunbolt\User;

use Nette\Security\IAuthenticator;
use Nette\Security;
use Thunbolt\User\Interfaces\IUserDAO;

class Authenticator implements IAuthenticator {

	/** @var IUserDAO */
	private $userDAO;

	public function __construct(string $repository, IUserDAO $userDAO) {
		$this->userDAO = $userDAO;
	}

	/**
	 * @param array $credentials
	 * @throws BadPasswordException
	 * @throws UserException
	 * @throws UserNotFoundException
	 * @return Security\IIdentity|Identity
	 */
	public function authenticate(array $credentials): Security\IIdentity {
		list($email, $password) = $credentials;

		$repository = $this->userDAO->getRepository();

		$row = $repository->login($email);
		if (!$row) {
			throw new UserNotFoundException();

		} elseif (!Security\Passwords::verify($password, $row->getPassword())) {
			throw new BadPasswordException();

		} elseif (Security\Passwords::needsRehash($row->getPassword())) {
			$row->setPassword($password);
			$this->userDAO->merge($row);
		}

		return new Identity($row->getId(), $row);
	}

}
