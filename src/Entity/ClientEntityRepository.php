<?php

namespace TMCms\Modules\Clients\Entity;

use TMCms\Orm\EntityRepository;
use TMCms\Orm\TableStructure;

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
            'group_id'   => [
                'type' => 'index',
            ],
            'active'     => [
                'type' => 'bool',
            ],
            'ts_created' => [
                'type' => TableStructure::FIELD_TYPE_UNSIGNED_INTEGER,
            ],
            'login'      => [
                'type' => 'varchar',
            ],
            'email'      => [
                'type' => 'varchar',
            ],
            'name'       => [
                'type' => 'varchar',
            ],
            'company'    => [
                'type' => 'varchar',
            ],
            'phone'      => [
                'type' => 'varchar',
            ],
            'hash'       => [
                'type'   => 'varchar',
                'length' => 255,
            ],
        ],
    ];
}
