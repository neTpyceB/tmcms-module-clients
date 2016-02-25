<?php

namespace TMCms\Modules\Clients\Entity;

use TMCms\Orm\Entity;

/**
 * Class ClientGroup
 * @package TMCms\Modules\Clients\Entity
 *
 * @method bool getDefault()
 * @method setDefault(bool $flag)
 */
class ClientGroupEntity extends Entity {
    protected $db_table = 'm_clients_groups';
    protected $translation_fields = ['title'];
}