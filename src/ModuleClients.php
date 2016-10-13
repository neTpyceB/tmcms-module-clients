<?php

namespace TMCms\Modules\Clients;

use TMCms\Admin\Users;
use TMCms\Config\Configuration;
use TMCms\Modules\Clients\Entity\ClientEntity;
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

	public static function register($post) {
		$errors = [];

		// Check e-mail address (required)
		if (empty($post['email']) || !filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
			$errors[] = w('Email_field_error');
		}
		else {
			// Check existing user
			$existing_client = ClientEntityRepository::findOneEntityByCriteria([
				'email' => $post['email']
			]);
			if ($existing_client) {
				$errors[] = w('Client_with_this_email_already_exist');
			}
		}

		// Check password match (required)
		if (empty($post['password']) || $post['password'] != $post['repeat_password']) {
			$errors[] = w('Password_field_error_or_passwords_does_not_match');
		}
		else {
			$post['hash'] = self::generateHash($post['password']);
			unset($post['password']);
			unset($post['repeat_password']);
		}

		// Return errors if exist
		if (!empty($errors)) {
			return $errors;
		}

		// Set client `active`
		$post['active'] = 1;

		// Create client
		$new_client = new ClientEntity();
		$new_client->loadDataFromArray($post);
		$new_client->save();

		return true;

	}

}