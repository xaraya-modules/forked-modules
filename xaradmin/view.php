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
 * View changelog entries
 */
function changelog_admin_view()
{
    // Security Check
    if (!xarSecurity::check('AdminChangeLog')) {
        return;
    }

    if (!xarVar::fetch('modid', 'isset', $modid, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('itemtype', 'isset', $itemtype, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('itemid', 'isset', $itemid, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('sort', 'isset', $sort, null, xarVar::DONT_SET)) {
        return;
    }
    if (!xarVar::fetch('startnum', 'isset', $startnum, 1, xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!xarVar::fetch('editor', 'isset', $editor, null, xarVar::DONT_SET)) {
        return;
    }

    if (empty($editor) || !is_numeric($editor)) {
        $editor = null;
    }

    $data = [];
    $data['editor'] = $editor;

    $modlist = xarMod::apiFunc(
        'changelog',
        'user',
        'getmodules',
        ['editor' => $editor]
    );

    if (empty($modid)) {
        $data['moditems'] = [];
        $data['numitems'] = 0;
        $data['numchanges'] = 0;
        foreach ($modlist as $modid => $itemtypes) {
            $modinfo = xarMod::getInfo($modid);
            // Get the list of all item types for this module (if any)
            $mytypes = xarMod::apiFunc(
                $modinfo['name'],
                'user',
                'getitemtypes',
                // don't throw an exception if this function doesn't exist
                [],
                0
            );
            foreach ($itemtypes as $itemtype => $stats) {
                $moditem = [];
                $moditem['numitems'] = $stats['items'];
                $moditem['numchanges'] = $stats['changes'];
                if ($itemtype == 0) {
                    $moditem['name'] = ucwords($modinfo['displayname']);
                //    $moditem['link'] = xarController::URL($modinfo['name'],'user','main');
                } else {
                    if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                        $moditem['name'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                    //    $moditem['link'] = $mytypes[$itemtype]['url'];
                    } else {
                        $moditem['name'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                    //    $moditem['link'] = xarController::URL($modinfo['name'],'user','view',array('itemtype' => $itemtype));
                    }
                }
                $moditem['link'] = xarController::URL(
                    'changelog',
                    'admin',
                    'view',
                    ['modid' => $modid,
                          'itemtype' => empty($itemtype) ? null : $itemtype,
                          'editor' => $editor]
                );
                $moditem['delete'] = xarController::URL(
                    'changelog',
                    'admin',
                    'delete',
                    ['modid' => $modid,
                          'itemtype' => empty($itemtype) ? null : $itemtype,
                          'editor' => $editor]
                );
                $data['moditems'][] = $moditem;
                $data['numitems'] += $moditem['numitems'];
                $data['numchanges'] += $moditem['numchanges'];
            }
        }
        $data['delete'] = xarController::URL(
            'changelog',
            'admin',
            'delete',
            ['editor' => $editor]
        );
    } else {
        $modinfo = xarMod::getInfo($modid);
        if (empty($itemtype)) {
            $data['modname'] = ucwords($modinfo['displayname']);
            $itemtype = null;
            if (isset($modlist[$modid][0])) {
                $stats = $modlist[$modid][0];
            }
        } else {
            // Get the list of all item types for this module (if any)
            $mytypes = xarMod::apiFunc(
                $modinfo['name'],
                'user',
                'getitemtypes',
                // don't throw an exception if this function doesn't exist
                [],
                0
            );
            if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
            //    $data['modlink'] = $mytypes[$itemtype]['url'];
            } else {
                $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
            //    $data['modlink'] = xarController::URL($modinfo['name'],'user','view',array('itemtype' => $itemtype));
            }
            if (isset($modlist[$modid][$itemtype])) {
                $stats = $modlist[$modid][$itemtype];
            }
        }
        if (isset($stats)) {
            $data['numitems'] = $stats['items'];
            $data['numchanges'] = $stats['changes'];
        } else {
            $data['numitems'] = 0;
            $data['numchanges'] = '';
        }
        $numstats = xarModVars::get('changelog', 'numstats');
        if (empty($numstats)) {
            $numstats = 100;
        }
        // pager
        $data['startnum'] = $startnum;
        $data['total'] = $data['numitems'];
        $data['urltemplate'] = xarController::URL(
            'changelog',
            'admin',
            'view',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'editor' => $editor,
                  'sort' => $sort,
                  'startnum' => '%%']
        );
        $data['itemsperpage'] = $numstats;

        $data['modid'] = $modid;
        $getitems = xarMod::apiFunc(
            'changelog',
            'user',
            'getitems',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'editor' => $editor,
                  'numitems' => $numstats,
                  'startnum' => $startnum,
                  'sort' => $sort]
        );
        $showtitle = xarModVars::get('changelog', 'showtitle');
        if (!empty($showtitle)) {
            $itemids = array_keys($getitems);
            $itemlinks = xarMod::apiFunc(
                $modinfo['name'],
                'user',
                'getitemlinks',
                ['itemtype' => $itemtype,
                      'itemids' => $itemids],
                0
            ); // don't throw an exception here
        } else {
            $itemlinks = [];
        }
        $data['moditems'] = [];
        foreach ($getitems as $itemid => $numchanges) {
            $data['moditems'][$itemid] = [];
            $data['moditems'][$itemid]['numchanges'] = $numchanges;
            $data['moditems'][$itemid]['showlog'] = xarController::URL(
                'changelog',
                'admin',
                'showlog',
                ['modid' => $modid,
                      'itemtype' => $itemtype,
                      'itemid' => $itemid]
            );
            $data['moditems'][$itemid]['delete'] = xarController::URL(
                'changelog',
                'admin',
                'delete',
                ['modid' => $modid,
                      'itemtype' => $itemtype,
                      'itemid' => $itemid,
                      'editor' => $editor]
            );
            if (isset($itemlinks[$itemid])) {
                $data['moditems'][$itemid]['link'] = $itemlinks[$itemid]['url'];
                $data['moditems'][$itemid]['title'] = $itemlinks[$itemid]['label'];
            }
        }
        unset($getitems);
        unset($itemlinks);
        $data['delete'] = xarController::URL(
            'changelog',
            'admin',
            'delete',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'editor' => $editor]
        );
        $data['sortlink'] = [];
        if (empty($sort) || $sort == 'itemid') {
            $data['sortlink']['itemid'] = '';
        } else {
            $data['sortlink']['itemid'] = xarController::URL(
                'changelog',
                'admin',
                'view',
                ['modid' => $modid,
                      'itemtype' => $itemtype,
                      'editor' => $editor]
            );
        }
        if (!empty($sort) && $sort == 'numchanges') {
            $data['sortlink']['numchanges'] = '';
        } else {
            $data['sortlink']['numchanges'] = xarController::URL(
                'changelog',
                'admin',
                'view',
                ['modid' => $modid,
                      'itemtype' => $itemtype,
                      'editor' => $editor,
                      'sort' => 'numchanges']
            );
        }
    }

    return $data;
}
