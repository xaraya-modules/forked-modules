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
 * Manage definition of instances for privileges (unfinished)
 */
function changelog_admin_privileges($args)
{
    // Security Check
    if (!xarSecurity::check('AdminChangeLog')) {
        return;
    }

    extract($args);

    // fixed params
    if (!xarVar::fetch('moduleid', 'isset', $moduleid, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('itemtype', 'isset', $itemtype, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('itemid', 'isset', $itemid, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('apply', 'isset', $apply, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('extpid', 'isset', $extpid, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('extname', 'isset', $extname, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('extrealm', 'isset', $extrealm, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('extmodule', 'isset', $extmodule, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('extcomponent', 'isset', $extcomponent, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('extinstance', 'isset', $extinstance, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('extlevel', 'isset', $extlevel, null, xarVar::DONT_SET)) {
        return;
    }

    if (!empty($extinstance)) {
        $parts = explode(':', $extinstance);
        if (count($parts) > 0 && !empty($parts[0])) {
            $moduleid = $parts[0];
        }
        if (count($parts) > 1 && !empty($parts[1])) {
            $itemtype = $parts[1];
        }
        if (count($parts) > 2 && !empty($parts[2])) {
            $itemid = $parts[2];
        }
    }

    // Get the list of all modules currently hooked to categories
    $hookedmodlist = xarMod::apiFunc(
        'modules',
        'admin',
        'gethookedmodules',
        ['hookModName' => 'changelog']
    );
    if (!isset($hookedmodlist)) {
        $hookedmodlist = [];
    }
    $modlist = [];
    foreach ($hookedmodlist as $modname => $val) {
        if (empty($modname)) {
            continue;
        }
        $modid = xarMod::getRegId($modname);
        if (empty($modid)) {
            continue;
        }
        $modinfo = xarMod::getInfo($modid);
        $modlist[$modid] = $modinfo['displayname'];
    }

    if (empty($moduleid) || $moduleid == 'All' || !is_numeric($moduleid)) {
        $moduleid = 0;
    }
    if (empty($itemtype) || $itemtype == 'All' || !is_numeric($itemtype)) {
        $itemtype = 0;
    }
    if (empty($itemid) || $itemid == 'All' || !is_numeric($itemid)) {
        $itemid = 0;
    }

    // define the new instance
    $newinstance = [];
    $newinstance[] = empty($moduleid) ? 'All' : $moduleid;
    $newinstance[] = empty($itemtype) ? 'All' : $itemtype;
    $newinstance[] = empty($itemid) ? 'All' : $itemid;

    if (!empty($apply)) {
        // create/update the privilege
        $pid = xarPrivileges::external($extpid, $extname, $extrealm, $extmodule, $extcomponent, $newinstance, $extlevel);
        if (empty($pid)) {
            return; // throw back
        }

        // redirect to the privilege
        xarResponse::Redirect(xarController::URL(
            'privileges',
            'admin',
            'modifyprivilege',
            ['pid' => $pid]
        ));
        return true;
    }

    /*
        if (!empty($moduleid)) {
            $numitems = xarMod::apiFunc('categories','user','countitems',
                                      array('modid' => $moduleid,
                                            'cids'  => (empty($cid) ? null : array($cid))
                                           ));
        } else {
            $numitems = xarML('probably');
        }
    */
    $numitems = xarML('probably');

    $data = [
                  'moduleid'     => $moduleid,
                  'itemtype'     => $itemtype,
                  'itemid'       => $itemid,
                  'modlist'      => $modlist,
                  'numitems'     => $numitems,
                  'extpid'       => $extpid,
                  'extname'      => $extname,
                  'extrealm'     => $extrealm,
                  'extmodule'    => $extmodule,
                  'extcomponent' => $extcomponent,
                  'extlevel'     => $extlevel,
                  'extinstance'  => xarVar::prepForDisplay(join(':', $newinstance)),
                 ];

    $data['refreshlabel'] = xarML('Refresh');
    $data['applylabel'] = xarML('Finish and Apply to Privilege');

    return $data;
}
