/*
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

pimcore.registerNS('pimcore_element_manager.duplication_index.item');

pimcore_element_manager.duplication_index.item = Class.create(coreshop.resource.item, {
    iconCls: 'pimcore_element_manager_duplication_icon_indexes',

    getPanel: function() {
        return new Ext.TabPanel({
            activeTab: 0,
            title: this.data.className,
            closable: true,
            deferredRender: false,
            forceLayout: true,
            iconCls: this.iconCls,
            items: this.getItems(),
        });
    },

    getItems: function() {
        return [
            this.getCurrentPanel(),
            this.getDeclinedPanel(),
        ];
    },

    getCurrentPanel: function() {
        const panel = new Ext.panel.Panel({
            title: t('pimcore_element_manager_duplicates_current'),
            layout: 'border',
            items: [this.createGrid(this.createStore(false))],
        });

        return panel;
    },

    getDeclinedPanel: function() {
        const panel = new Ext.panel.Panel({
            title: t('pimcore_element_manager_duplicates_declined'),
            layout: 'border',
            items: [this.createGrid(this.createStore(true))],
        });

        return panel;
    },

    createGrid: function(store) {
        const columns = [{
            text: t('id'),
            dataIndex: 'objectId',
        }];

        const listFields = this.data.listFields.map(function(field) {
            if (!Ext.isArray(field)) {
                field = [field];
            }

            return field.join(',');
        });

        Ext.each(listFields, function(field) {
            columns.push({
                text: field,
                dataIndex: field,
                flex: 1,
            });
        });

        columns.push({
            xtype: 'gridcolumn',
            dataIndex: '_isFirstColumn',
            width: 50,
            align: 'right',
            renderer: function(value, metadata, record, rowIndex, colIndex, store) {
                if (!value) {
                    return;
                }

                const id = Ext.id();

                Ext.defer(function() {
                    if (Ext.get(id)) {
                        new Ext.button.Button({
                            renderTo: id,
                            iconCls: 'pimcore_icon_delete',
                            flex: 1,
                            scale: 'small',
                            handler: function() {
                                let url = '/admin/pimcore_element_manager/potential_duplicates/decline';

                                if (record.get('declined')) {
                                    url = '/admin/pimcore_element_manager/potential_duplicates/undecline';
                                }

                                Ext.Ajax.request({
                                    url: url,
                                    method: 'post',
                                    params: {
                                        id: record.get('duplicationId')
                                    },
                                    success: function() {
                                        store.store.load();
                                    }.bind(this),
                                });
                            },
                        });
                    }
                }, 200);

                return Ext.String.format('<div id="{0}"></div>', id);
            },
        });

        if (this.data.options.merge_supported) {
            columns.push({
                xtype: 'gridcolumn',
                dataIndex: '_isFirstColumn',
                width: 50,
                align: 'right',
                renderer: function(value, metadata, record, rowIndex, colIndex, store) {
                    if (!value) {
                        return;
                    }

                    const id = Ext.id();

                    Ext.defer(function() {
                        if (Ext.get(id)) {
                            new Ext.button.Button({
                                renderTo: id,
                                iconCls: 'pimcore_icon_merge',
                                flex: 1,
                                scale: 'small',
                                handler: function() {
                                    new pimcore.plugin.objectmerger.panel(record.get('objectId'), record.get('objectIdOther'))
                                },
                            });
                        }
                    }, 200);

                    return Ext.String.format('<div id="{0}"></div>', id);
                },
            });
        }

        return Ext.create({
            xtype: 'grid',
            store: store,
            region: 'center',
            columns: columns,
            bbar: pimcore.helpers.grid.buildDefaultPagingToolbar(store),
            features: [
                {
                    ftype:'grouping',
                    collapsible: false,
                },
            ],
        });
    },

    createStore: function(declined) {
        const listFields = this.data.listFields.map(function(field) {
            if (!Ext.isArray(field)) {
                field = [field];
            }

            return field.join(',');
        });

        return Ext.create('Ext.data.Store', {
            fields: listFields,
            groupField: 'duplicationId',
            autoLoad: true,
            proxy: {
                type: 'ajax',
                url: '/admin/pimcore_element_manager/potential_duplicates/get-potential-duplicates',
                extraParams: {
                    className: this.data.className,
                    declined: declined,
                },
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    totalProperty: 'total',
                    idProperty: 'extId',
                },
            },
        });
    },
});
