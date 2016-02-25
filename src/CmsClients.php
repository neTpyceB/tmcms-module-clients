<?php

namespace TMCms\Modules\Clients;

use TMCms\Admin\Menu;
use TMCms\Admin\Messages;
use TMCms\Admin\Users;
use TMCms\Config\Configuration;
use TMCms\HTML\BreadCrumbs;
use TMCms\HTML\Cms\CmsForm;
use TMCms\HTML\Cms\CmsTable;
use TMCms\HTML\Cms\Column\ColumnActive;
use TMCms\HTML\Cms\Column\ColumnData;
use TMCms\HTML\Cms\Column\ColumnDelete;
use TMCms\HTML\Cms\Column\ColumnEdit;
use TMCms\HTML\Cms\Columns;
use TMCms\HTML\Cms\Element\CmsButton;
use TMCms\HTML\Cms\Element\CmsInputEmail;
use TMCms\HTML\Cms\Element\CmsInputPassword;
use TMCms\HTML\Cms\Element\CmsInputText;
use TMCms\HTML\Cms\Element\CmsSelect;
use TMCms\Log\App;
use TMCms\Modules\Clients\Entity\ClientEntity;
use TMCms\Modules\Clients\Entity\ClientGroupEntity;
use TMCms\Modules\Clients\Entity\ClientGroupEntityRepository;
use TMCms\Modules\Clients\Entity\ClientEntityRepository;
use TMCms\Modules\ModuleManager;
use TMCms\Modules\Sessions\Entity\SessionEntity;
use TMCms\Modules\Sessions\Entity\SessionEntityRepository;

defined('INC') or exit;

Menu::getInstance()
    ->addSubMenuItem('groups')
    ->addSubMenuItem('sessions')
;

class CmsClients
{
    /** Clients */

    public static function _default()
    {
        echo Columns::getInstance()
            ->add('<a class="btn btn-success" href="?p=' . P . '&do=add">Add Client</a>', ['align' => 'right'])
        ;

        echo '<br>';

        $clients = new ClientEntityRepository();
        $clients->addOrderByField('id');

        $groups = new ClientGroupEntityRepository();

        echo CmsTable::getInstance()
            ->addData($clients)
            ->addColumn(ColumnData::getInstance('login')
                ->enableOrderableColumn()
            )
            ->addColumn(ColumnData::getInstance('email')
                ->enableOrderableColumn()
            )
            ->addColumn(ColumnData::getInstance('name')
                ->enableOrderableColumn()
            )
            ->addColumn(ColumnData::getInstance('company')
                ->enableOrderableColumn()
            )
            ->addColumn(ColumnData::getInstance('phone')
                ->enableOrderableColumn()
            )
            ->addColumn(ColumnData::getInstance('group_id')
                ->enableOrderableColumn()
                ->setPairedDataOptionsForKeys($groups->getPairs('title'))
            )
            ->addColumn(ColumnEdit::getInstance('edit'))
            ->addColumn(ColumnActive::getInstance('active'))
            ->addColumn(ColumnDelete::getInstance())
        ;
    }

    private static function __clients_add_edit_form()
    {
        $client_groups = new ClientGroupEntityRepository();

        return CmsForm::getInstance()
            ->addField('Group', CmsSelect::getInstance('group_id')
                ->setOptions($client_groups->getPairs('title'))
            )
            ->addField('Login', CmsInputText::getInstance('login'))
            ->addField('Email', CmsInputEmail::getInstance('email'))
            ->addField('Name', CmsInputText::getInstance('name'))
            ->addField('Company', CmsInputText::getInstance('company'))
            ->addField('Phone', CmsInputText::getInstance('phone'))
            ->addField('Password', CmsInputPassword::getInstance('password')
                ->reveal(true)
                ->help('Leave empty to keep current')
            );
    }

    public static function add()
    {
        echo self::__clients_add_edit_form()
            ->setAction('?p=' . P . '&do=_add')
            ->setSubmitButton(new CmsButton('Add'));
    }

    public static function edit()
    {
        $id = (int)$_GET['id'];

        $client = new ClientEntity($id);

        echo self::__clients_add_edit_form()
            ->addData($client->getAsArray())
            ->setAction('?p=' . P . '&do=_edit&id=' . $id)
            ->setSubmitButton(new CmsButton('Update'));
    }

    public static function _add()
    {
        $_POST['hash'] = ModuleClients::generateHash($_POST['password']); // Hash password

        $client = new ClientEntity();
        $client->loadDataFromArray($_POST);
        $client->save();

        go('?p=' . P . '&highlight=' . $client->getId());
    }

    public static function _edit()
    {
        $id = (int)$_GET['id'];

        $_POST['password'] = trim($_POST['password']);
        if ($_POST['password']) {
            $_POST['hash'] = ModuleClients::generateHash($_POST['password']);
        } else {
            unset($_POST['password']);
        }

        $client = new ClientEntity($id);
        $client->loadDataFromArray($_POST);
        $client->save();

        go('?p=' . P . '&highlight=' . $id);
    }

    public static function _active()
    {
        $id = (int)$_GET['id'];

        $client = new ClientEntity($id);
        $client->flipBoolValue('active');
        $client->save();

        App::add('Client "'. $client->getEmail() .'" updated');
        Messages::sendGreenAlert('Client updated');

        if (IS_AJAX_REQUEST) {
            die('1');
        }

        back();
    }

    public static function _delete()
    {
        $id = (int)$_GET['id'];

        $group = new ClientEntity($id);
        $group->deleteObject();

        back();
    }



    /** Groups */

    public static function groups()
    {
        $groups = new ClientGroupEntityRepository();
        $groups->addOrderByField('id');

        echo Columns::getInstance()
            ->add('<a class="btn btn-success" href="?p=' . P . '&do=groups_add">Add Group</a>', ['align' => 'right'])
        ;

        echo '<br>';

        echo CmsTable::getInstance()
            ->addData($groups)
            ->addColumn(ColumnData::getInstance('title')
                ->enableOrderableColumn()
                ->enableTranslationColumn()
            )
            ->addColumn(ColumnEdit::getInstance('edit')
                ->setHref('?p=' . P . '&do=groups_edit&id={%id%}')
            )
            ->addColumn(ColumnActive::getInstance('default')
                ->setHref('?p=' . P . '&do=_groups_default&id={%id%}')
                ->enableOrderableColumn()
                ->disableNewlines()
            )
            ->addColumn(ColumnActive::getInstance('active')
                ->setHref('?p=' . P . '&do=_groups_active&id={%id%}')
            )
            ->addColumn(ColumnDelete::getInstance()
                ->setHref('?p=' . P . '&do=_groups_delete&id={%id%}')
            )
        ;
    }

    private static function __groups_add_edit_form()
    {
        return CmsForm::getInstance()
            ->addField('Title', CmsInputText::getInstance('title')
                ->enableTranslationField()
            );
    }

    public static function groups_add()
    {
        echo self::__groups_add_edit_form()
            ->setAction('?p=' . P . '&do=_groups_add')
            ->setSubmitButton(new CmsButton('Add'));
    }

    public static function groups_edit()
    {
        $id = (int)$_GET['id'];

        $group = new ClientGroupEntity($id);

        echo self::__groups_add_edit_form()
            ->addData($group->getAsArray())
            ->setAction('?p=' . P . '&do=_groups_edit&id=' . $id)
            ->setSubmitButton(new CmsButton('Update'));
    }

    public static function _groups_add()
    {
        $group = new ClientGroupEntity();
        $group->loadDataFromArray($_POST);
        $group->save();

        go('?p=' . P . '&do=groups&highlight=' . $group->getId());
    }

    public static function _groups_edit()
    {
        $id = (int)$_GET['id'];

        $group = new ClientGroupEntity($id);
        $group->loadDataFromArray($_POST);
        $group->save();

        go('?p=' . P . '&do=groups&highlight=' . $id);
    }

    public static function _groups_delete()
    {
        $id = (int)$_GET['id'];

        $group = new ClientGroupEntity($id);
        $group->deleteObject();

        back();
    }

    public static function _groups_active()
    {
        $id = (int)$_GET['id'];

        $group = new ClientGroupEntity($id);
        $group->flipBoolValue('active');
        $group->save();

        App::add('Group "'. $group->getTitle() .'" updated');
        Messages::sendGreenAlert('Group updated');

        if (IS_AJAX_REQUEST) {
            die('1');
        }

        back();
    }

    public static function _groups_default()
    {
        $id = (int)$_GET['id'];

        // Disable to all
        $groups = new ClientGroupEntityRepository();
        $groups->setDefault(false);
        $groups->save();

        // Enable on selected
        $group = new ClientGroupEntity($id);
        $group->setDefault(true);
        $group->save();

        App::add('Group "'. $group->getTitle() .'" set as default');
        Messages::sendGreenAlert('Group updated');

        back();
    }


    /** SESSIONS */

    /**
     * AdminUser's Sessions
     */
    public function sessions()
    {
        if (!ModuleManager::moduleExists('sessions')) {
            error('No Module Sessions installed');
        }

        echo BreadCrumbs::getInstance()
            ->addCrumb(ucfirst(P))
            ->addCrumb('Client sessions')
        ;

        $clients = new ClientEntityRepository();
        $clients->addSimpleSelectFields(['login']);

        $groups = new ClientGroupEntityRepository();
        $groups->addSimpleSelectFields(['title']);

        $sessions = new SessionEntityRepository();
        $sessions->addSimpleSelectFields(['id', 'sid', 'ip', 'ts']);
        $sessions->addOrderByField('ts', true);

        $clients->mergeWithCollection($groups, 'group_id');
        $clients->mergeWithCollection($sessions, 'id', 'user_id');

        echo CmsTable::getInstance()
            ->addData($clients)
            ->setCallbackFunction(function ($data)
            {
                foreach ($data as & $v) {
                    $v['sid'] = substr($v['sid'], 0, 16) . '...';
                }
                return $data;
            })
            ->addColumn(ColumnData::getInstance('login')
                ->enableOrderableColumn()
            )
            ->addColumn(ColumnData::getInstance('title')
                ->enableOrderableColumn()
                ->enableTranslationColumn()
                ->setTitle('Group')
            )
            ->addColumn(ColumnData::getInstance('sid')
                ->enableOrderableColumn()
                ->setTitle('Session ID')
                ->allowHtml()
            )
            ->addColumn(ColumnData::getInstance('ip'))
            ->addColumn(ColumnData::getInstance('ts')
                ->orderBy('`ts`')
                ->dataType('ts2datetime')
                ->setTitle('Date')
            )
            ->addColumn(ColumnDelete::getInstance('kick')
                ->setHref('?p=' . P . '&do=_kick&id={%id%}')
            );
    }

    /**
     * Action for Kick AdminUser's session
     */
    public function _kick()
    {
        $id = $_GET['id'];

        $session = new SessionEntity($id);
        $session->deleteObject();

        Messages::sendGreenAlert('Client session kicked');

        App::add('Client session kicked');

        back();
    }
}