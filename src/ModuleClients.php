<?php

namespace TMCms\Modules\Clients;

use TMCms\Admin\Users;
use TMCms\Config\Configuration;
use TMCms\Modules\Clients\Entity\ClientEntity;
use TMCms\Modules\Clients\Entity\ClientEntityRepository;
use TMCms\Modules\IModule;
use TMCms\Modules\Sessions\ModuleSessions;
use TMCms\Routing\Structure;
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

	private static function validateFields($post, $create_client = true, $required_fields) {

		$result = [];
		$errors = [];

		// Required fields
		if ($required_fields) {

			foreach ($post as $key => $field) {
				if (in_array($key,$required_fields) && empty($field)) {
					$errors[] = w('Need_to_fill_field') . ': '. $key;
				}
			}
		}

		// Check e-mail address (required)
		if (isset($post['email'])) {
			if (empty($post['email']) || !filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
				$errors[] = w('Email_field_error');
			}
			else {
				// Check for existing user
				$existing_client = ClientEntityRepository::findOneEntityByCriteria([
						'email' => $post['email']
				]);

				if ($create_client) {
					if ($existing_client) {
						$errors[] = w('Client_with_this_email_already_exist');
					}
				}
				else {
					if ($existing_client && $existing_client->getId() != $post['id']) {
						$errors[] = w('Client_with_this_email_already_exist');
					}
				}
			}
		}

		// Check password match (required)
		if ($create_client) { // for registration
			if (empty($post['password']) || $post['password'] != $post['repeat_password']) {
				$errors[] = w('Password_field_error_or_passwords_does_not_match');
			}
			else {
				$post['hash'] = self::generateHash($post['password']);
			}
		}
		else { // for profile save
			if (!empty($post['password'])) {
				if ($post['password'] != $post['repeat_password']) {
					$errors[] = w('Password_field_error_or_passwords_does_not_match');
				}
				else {
					$post['hash'] = self::generateHash($post['password']);
				}
			}
		}

		unset($post['password']);
		unset($post['repeat_password']);

		$result['errors'] = $errors;
		$result['post'] = $post;

		return $result;
	}

	public static function register($post) {

		// Validate profile fields
		$validate = self::validateFields($post);
		if (!empty($validate['errors'])) {
			return ['errors' => $validate['errors']];
		}

		// Update variable after validations (needs for password hash)
		$post = $validate['post'];
		// Set client `active`
		$post['active'] = 1;

		// Create client
		$new_client = new ClientEntity();
		$new_client->loadDataFromArray($post);
		$new_client->save();

		return ['result' => true];

	}

	public static function saveProfile($post, $required = []) {

		// Check for client ID
		$id = (!empty($post['id']) ? $post['id'] : 0);
		$client = new ClientEntity($id);

		// Stop session and go to login page
		if (!$client) {
			ModuleSessions::stop();
			go(Structure::getPathByLabel('login'));
		}

		// Validate profile fields
		$validate = self::validateFields($post, false, $required);
		if (!empty($validate['errors'])) {
			return ['errors' => $validate['errors']];
		}

		// Update variable after validations (needs for password hash)
		$post = $validate['post'];
		unset($post['id']);

		// Save
		$client->loadDataFromArray($post);
		$client->save();

		return ['result' => true];
	}

}