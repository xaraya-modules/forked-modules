<?php
/**
 * Change Log Module version information
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage changelog
 * @link http://xaraya.com/index.php/release/185.html
 * @author mikespub
 */
/**
 * modify an entry for a module item - hook for ('item','modify','GUI')
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @return string hook output in HTML
 * @throws BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function changelog_admin_modifyhook($args)
{
    extract($args);

    // everything is already validated in HookSubject, except possible empty objectid/itemid for create/display
    $modname = $extrainfo['module'];
    $itemtype = $extrainfo['itemtype'];
    $itemid = $extrainfo['itemid'];
    $modid = $extrainfo['module_id'];
    if (empty($itemid)) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = ['item id', 'admin', 'modifyhook', 'changelog'];
        throw new BadParameterException($vars, $msg);
    }

    if (!empty($extrainfo['changelog_remark'])) {
        $remark = $extrainfo['changelog_remark'];
    } else {
        xarVar::fetch('changelog_remark', 'str:1:', $remark, null, xarVar::NOT_REQUIRED);
        if (empty($remark)) {
            $remark = '';
        }
    }

    if (xarSecurity::check('ReadChangeLog', 0, 'Item', "$modid:$itemtype:$itemid")) {
        $link = xarController::URL(
            'changelog',
            'admin',
            'showlog',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'itemid' => $itemid]
        );
    } else {
        $link = '';
    }

    return xarTpl::module(
        'changelog',
        'admin',
        'modifyhook',
        ['remark' => $remark,
              'link' => $link]
    );
}
