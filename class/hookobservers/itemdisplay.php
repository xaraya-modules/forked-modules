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
sys::import('xaraya.structures.hooks.observer');

/**
 * display changelog entry for a module item - hook for ('item','display','GUI')
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @return bool true on success, false on failure
 * @throws BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
class ChangelogItemDisplayObserver extends EventObserver implements ixarEventObserver
{
    public $module = 'changelog';

    public function notify(ixarEventSubject $subject)
    {
        // get extrainfo from subject (array containing module, module_id, itemtype, itemid)
        $extrainfo = $subject->getExtrainfo();

        // everything is already validated in HookSubject, except possible empty objectid/itemid for create/display
        $modname = $extrainfo['module'];
        $itemtype = $extrainfo['itemtype'];
        $itemid = $extrainfo['itemid'];
        $modid = $extrainfo['module_id'];

        $changes = xarMod::apiFunc(
            'changelog',
            'admin',
            'getchanges',
            ['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
                'numitems' => 1]
        );
        // return empty string here
        if (empty($changes) || !is_array($changes) || count($changes) == 0) {
            return '';
        }

        $data = array_pop($changes);

        if (xarSecurity::check('AdminChangeLog', 0)) {
            $data['showhost'] = 1;
        } else {
            $data['showhost'] = 0;
        }

        $data['profile'] = xarController::URL(
            'roles',
            'user',
            'display',
            ['id' => $data['editor']]
        );
        if (!$data['showhost']) {
            $data['hostname'] = '';
        }
        if (!empty($data['remark'])) {
            $data['remark'] = xarVar::prepForDisplay($data['remark']);
        }
        $data['link'] = xarController::URL(
            'changelog',
            'admin',
            'showlog',
            ['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid]
        );

        // TODO: use custom template per module + itemtype ?
        return xarTpl::module(
            'changelog',
            'user',
            'displayhook',
            $data
        );
    }
}
