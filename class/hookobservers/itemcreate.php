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

namespace Xaraya\Modules\ChangeLog\HookObservers;

use HookObserver;
use ixarEventObserver;
use ixarEventSubject;
use xarMod;
use xarModVars;
use xarModHooks;
use xarDB;
use xarUser;
use xarServer;
use xarVar;
use sys;

sys::import('xaraya.structures.hooks.observer');

/**
 * create an entry for a module item - hook for ('item','create','GUI')
 * Optional $extrainfo['changelog_remark'] from arguments, or 'changelog_remark' from input
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @return array extrainfo array
 * @throws BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
class ItemCreateObserver extends HookObserver implements ixarEventObserver
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

        xarMod::loadDbInfo('changelog', 'changelog');
        $dbconn = xarDB::getConn();
        $xartable = xarDB::getTables();
        $changelogtable = $xartable['changelog'];

        $editor = xarUser::getVar('id');
        $forwarded = xarServer::getVar('HTTP_X_FORWARDED_FOR');
        if (!empty($forwarded)) {
            $hostname = preg_replace('/,.*/', '', $forwarded);
        } else {
            $hostname = xarServer::getVar('REMOTE_ADDR');
        }
        $date = time();
        $status = 'created';
        if (isset($extrainfo['changelog_remark']) && is_string($extrainfo['changelog_remark'])) {
            $remark = $extrainfo['changelog_remark'];
        } else {
            xarVar::fetch('changelog_remark', 'str:1:', $remark, null, xarVar::NOT_REQUIRED);
            if (empty($remark)) {
                $remark = '';
            }
        }

        if (!empty($itemtype)) {
            $getlist = xarModVars::get('changelog', $modname.'.'.$itemtype);
        }
        if (!isset($getlist)) {
            $getlist = xarModVars::get('changelog', $modname);
        }
        if (!empty($getlist)) {
            $fieldlist = explode(',', $getlist);
        }
        $fields = [];
        foreach ($extrainfo as $field => $value) {
            // skip some common uninteresting fields
            if ($field == 'module' || $field == 'itemtype' || $field == 'itemid' ||
                $field == 'mask' || $field == 'pass' || $field == 'changelog_remark') {
                continue;
            }
            // skip fields we don't want here
            if (!empty($fieldlist) && !in_array($field, $fieldlist)) {
                continue;
            }
            $fields[$field] = $value;
        }
        // Check if we need to include any DD fields
        $withdd = xarModVars::get('changelog', 'withdd');
        if (empty($withdd)) {
            $withdd = '';
        }
        $withdd = explode(';', $withdd);
        if (xarModHooks::isHooked('dynamicdata', $modname, $itemtype) && !empty($withdd) &&
            (in_array($modname, $withdd) || in_array("$modname.$itemtype", $withdd))) {
            // Note: we need to make sure the DD hook is called before the changelog hook here
            $ddfields = xarMod::apiFunc(
                'dynamicdata',
                'user',
                'getitem',
                ['module_id' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid]
            );
            if (!empty($ddfields)) {
                foreach ($ddfields as $field => $value) {
                    // skip fields we don't want here
                    if (!empty($fieldlist) && !in_array($field, $fieldlist)) {
                        continue;
                    }
                    $fields[$field] = $value;
                }
            }
        }
        $content = serialize($fields);
        $fields = [];

        // Get a new changelog ID
        $nextId = $dbconn->GenId($changelogtable);
        // Create new changelog
        $query = "INSERT INTO $changelogtable(xar_logid,
                                        xar_moduleid,
                                        xar_itemtype,
                                        xar_itemid,
                                        xar_editor,
                                        xar_hostname,
                                        xar_date,
                                        xar_status,
                                        xar_remark,
                                        xar_content)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $bindvars = [$nextId,
                        (int) $modid,
                        (int) $itemtype,
                        (int) $itemid,
                        (int) $editor,
                        (string) $hostname,
                        (int) $date,
                        (string) $status,
                        (string) $remark,
                        (string) $content];

        $result = $dbconn->Execute($query, $bindvars);
        if (!$result) {
            return $extrainfo;
        }

        $logid = $dbconn->PO_Insert_ID($changelogtable, 'xar_logid');

        // Return the extra info with the id of the newly created item
        // (not that this will be of any used when called via hooks, but
        // who knows where else this might be used)
        $extrainfo['changelogid'] = $logid;

        return $extrainfo;
    }
}
