/**
 * Pimcore Element Manager.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright 2024 instride AG (https://instride.ch)
 * @license   https://github.com/instride-ch/pimcore-element-manager/blob/main/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

class PimcoreElementManager {
    init() {
        const user = pimcore.globalmanager.get('user');

        if (user.isAllowed('plugins')) {
            const duplicationsMenu = new Ext.Action({
                id: 'pimcore_element_manager_duplication_indexes',
                text: t('pimcore_element_manager_duplication_indexes'),
                iconCls: 'pimcore_element_manager_duplication_nav_icon_indexes',
                handler: this.openDuplicationIndexes.bind(this),
            });

            if (layoutToolbar.settingsMenu) {
                layoutToolbar.settingsMenu.add(duplicationsMenu);
            }

            coreshop.global.addStore('pimcore_element_manager_duplication_indexes', 'pimcore_element_manager/potential_duplicates');
        }
    }

    openDuplicationIndexes() {
        try {
            pimcore.globalmanager.get('pimcore_element_manager_duplication_indexes_panel').activate();
        } catch (e) {
            pimcore.globalmanager.add('pimcore_element_manager_duplication_indexes_panel', new pimcore_element_manager.duplication_index.panel());
        }
    }
}

const pimcoreElementManagerHandler = new PimcoreElementManager();

document.addEventListener(pimcore.events.pimcoreReady, pimcoreElementManagerHandler.init.bind(pimcoreElementManagerHandler));
