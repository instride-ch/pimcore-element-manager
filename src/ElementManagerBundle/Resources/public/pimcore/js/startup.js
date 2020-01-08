/**
 * Data Definitions.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2020 w-vision AG (https://www.w-vision.ch)
 * @license    https://github.com/w-vision/DataDefinitions/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

pimcore.registerNS('wvision.*');
pimcore.registerNS('wvision.element_manager.*');
pimcore.registerNS('wvision.element_manager.duplication_index.*');
pimcore.registerNS('pimcore.plugin.element_manager');

pimcore.plugin.element_manager = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return 'pimcore.plugin.element_manager';
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {

        var user = pimcore.globalmanager.get('user');

        if (user.isAllowed('plugins')) {

            var duplicationsMenu = new Ext.Action({
                text: t('wvision_element_manager_duplication_indexes'),
                iconCls: 'wvision_element_manager_duplication_nav_icon_indexes',
                handler: this.openDuplications
            });

            layoutToolbar.settingsMenu.add(duplicationsMenu);

            coreshop.global.addStore('wvision_element_manager_duplication_indexes', 'wvision_element_manager/potential_duplicates');
        }
    },

    openDuplications: function () {
        try {
            pimcore.globalmanager.get('wvision_element_manager_duplication_indexes_panel').activate();
        } catch (e) {
            pimcore.globalmanager.add('wvision_element_manager_duplication_indexes_panel', new wvision.element_manager.duplication_index.panel());
        }
    }
});

new pimcore.plugin.element_manager();

