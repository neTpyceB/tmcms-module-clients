<?php

namespace TMCms\Modules\Clients\Entity;

use TMCms\Orm\EntityRepository;
use TMCms\Orm\TableStructure;

/**
 * Class ClientGroupRepository
 * @package TMCms\Modules\Clients\Entity
 *
 * @method setDefault(bool $flag)
 */
class ClientGroupEntityRepository extends EntityRepository {
    protected $db_table = 'm_clients_groups';
    protected $translation_fields = ['title'];
    protected $table_structure = [
        'fields' => [
            'title' => [
                'type' => TableStructure::FIELD_TYPE_TRANSLATION,
            ],
            'active' => [
                'type' => 'bool',
            ],
            'default' => [
                'type' => 'bool',
            ],
        ],
    ];
}
