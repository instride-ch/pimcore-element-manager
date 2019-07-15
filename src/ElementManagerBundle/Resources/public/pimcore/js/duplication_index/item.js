/*
 * Element Manager.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2016-2018 w-vision AG (https://www.w-vision.ch)
 * @license    https://github.com/w-vision/ImportDefinitions/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

pimcore.registerNS('wvision.element_manager.duplication_index.item');

wvision.element_manager.duplication_index.item = Class.create(coreshop.resource.item, {

    iconCls: 'wvision_element_manager_duplication__icon_indexes',

    getPanel: function () {
        return new Ext.TabPanel({
            activeTab: 0,
            title: this.data.className,
            closable: true,
            deferredRender: false,
            forceLayout: true,
            iconCls: this.iconCls,
            items: this.getItems()
        });
    },

    getItems: function () {
        return [
            this.getCurrentPanel(),
            this.getDeclinedPanel(),
        ];
    },

    getCurrentPanel: function () {
        var panel = new Ext.panel.Panel({
            title: t('wvision_element_manager_duplicates_current'),
            layout: 'border',
            items: [this.createGrid(this.createStore(false))]
        });

        return panel;
    },

    getDeclinedPanel: function () {
        var panel = new Ext.panel.Panel({
            title: t('wvision_element_manager_duplicates_current'),
            layout: 'border',
            items: [this.createGrid(this.createStore(true))]
        });

        return panel;
    },

    createGrid: function(store) {
        var columns = [{
            text: t('id'),
            dataIndex: 'objectId',
        }];

        var listFields = this.data.listFields.map(function(field) {
            if (!Ext.isArray(field)) {
                field = [field];
            }

            return field.join(',');
        });

        Ext.each(listFields, function(field) {
            columns.push({
                text: field,
                dataIndex: field,
            });
        });

        return Ext.create({
            xtype: 'grid',
            store: store,
            columns: columns,
            features: [{ftype:'grouping'}],
        });
    },

    createStore: function(declined)
    {
        var listFields = this.data.listFields.map(function(field) {
            if (!Ext.isArray(field)) {
                field = [field];
            }

            return field.join(',');
        });

        var store = Ext.create('Ext.data.Store', {
            fields: listFields,
            groupField: 'duplicationId',
            autoLoad: true,
            proxy: {
                type: 'ajax',
                url: '/admin/wvision_element_manager/potential_duplicates/get-potential-duplicates',
                extraParams: {
                    className: this.data.className,
                    declined: declined
                },
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'total',
                    idProperty: 'extId'
                }
            },
        });

        return store;
    }
});
