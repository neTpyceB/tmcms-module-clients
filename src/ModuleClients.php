<?php

namespace TMCms\Modules\Clients;

use TMCms\Admin\Users;
use TMCms\Config\Configuration;
use TMCms\Modules\Clients\Entity\ClientEntityRepository;
use TMCms\Modules\IModule;
use TMCms\Strings\UID;
use TMCms\Traits\singletonInstanceTrait;

defined('INC') or exit;

class ModuleClients implements IModule {
	use singletonInstanceTrait;

	private static $_password_salt = 'fgfdg#EGTU$%!)<vdg';

	public static function authorize($login, $password) {
		return ClientEntityRepository::findOneEntityByCriteria([
			'login' => $login,
			'hash' => self::generateHash($password),
			'active' => 1,
		]);
	}

	public static function generateHash($password) {
		return Users::getInstance()->generateHash($password, self::$_password_salt . Configuration::getInstance()->get('cms')['unique_key']);
	}
}