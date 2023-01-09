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
 * utility function pass individual menu items to the main menu
 *
 * @author mikespub
 * @return array containing the menulinks for the main menu items.
 */
function changelog_adminapi_getmenulinks()
{
    $menulinks = [];
    // Security Check
    if (xarSecurity::check('AdminChangeLog')) {
        $menulinks[] = ['url'   => xarController::URL(
            'changelog',
            'admin',
            'view'
        ),
                              'title' => xarML('View changelog entries per module'),
                              'label' => xarML('View Changes')];
        $menulinks[] = ['url'   => xarController::URL(
            'changelog',
            'admin',
            'hooks'
        ),
                              'title' => xarML('Configure changelog hooks for other modules'),
                              'label' => xarML('Enable Hooks')];
        $menulinks[] = ['url'   => xarController::URL(
            'changelog',
            'admin',
            'modifyconfig'
        ),
                              'title' => xarML('Modify the changelog configuration'),
                              'label' => xarML('Modify Config')];
    }

    return $menulinks;
}
