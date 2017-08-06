<?php

namespace TMCms\Modules\Clients\Entity;

use TMCms\Orm\EntityRepository;

/**
 * Class ClientRepository
 * @package TMCms\Modules\Clients\Entity
 *
 * @method setWhereActive(bool $flag)
 * @method setWhereEmail(string $email)
 * @method setWhereGroupId(int $id)
 */
class ClientEntityRepository extends EntityRepository {
    protected $table_structure = [
        'fields' => [
            'group_id' => [
                'type' => 'index',
            ],
            'active' => [
                'type' => 'bool',
            ],
            'login' => [
                'type' => 'varchar',
            ],
            'email' => [
                'type' => 'varchar',
            ],
            'name' => [
                'type' => 'varchar',
            ],
            'company' => [
                'type' => 'varchar',
            ],
            'phone' => [
                'type' => 'varchar',
            ],
            'hash' => [
                'type'   => 'varchar',
                'length' => 255,
            ],
        ],
    ];
}