<?php

namespace TMCms\Modules\Clients\Entity;

use TMCms\Orm\Entity;

/**
 * Class Client
 * @package TMCms\Modules\Clients\Object
 *
 * @method string getCompany()
 * @method string getEmail()
 * @method int getGroupId()
 * @method string getLogin()
 * @method string getName()
 * @method string getPhone()
 * @method setActive(bool $flag)
 * @method setPassword(string $password)
 */
class ClientEntity extends Entity {
    protected $db_table = 'm_clients';
}