<?php
/**
 * Hooks shows the configuration of hooks for other modules
 *
 * @package modules
 * @copyright (C) copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage changelog
 * @link http://xaraya.com/index.php/release/177.html
 * @author Change Log Module Development Team
 */
/**
 * Hooks shows the configuration of hooks for other modules
 *
 * @author the Changelog module development team
 * @return array xarTpl::module with $data containing template data
 * @since 4 March 2006
 */
function changelog_admin_hooks()
{
    /* Security Check */
    if (!xarSecurity::check('AdminChangelog', 0)) {
        return;
    }

    $data = [];

    return $data;
}
