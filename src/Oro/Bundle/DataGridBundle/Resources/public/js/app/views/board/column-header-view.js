define(function(require) {
    'use strict';

    /**
     * Displays header of board column
     * @augments BaseView
     */
    var ColumnHeaderView;
    var BaseView = require('oroui/js/app/views/base/view');
    var HintView = require('orodatagrid/js/app/views/hint-view');

    ColumnHeaderView = BaseView.extend({
        /**
         * @inheritDoc
         */
        className: 'board-column-header',

        /**
         * @inheritDoc
         */
        template: require('tpl!../../../../templates/board/column-header-view.html'),

        events: {
            mouseenter: 'onMouseEnter',
            mouseleave: 'onMouseLeave'
        },

        /**
         * @inheritDoc
         */
        constructor: function ColumnHeaderView() {
            ColumnHeaderView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.boardCollection = options.boardCollection;
            this.listenTo(this.boardCollection, 'add remove reset sort', this.markIfEmpty);
            ColumnHeaderView.__super__.initialize.call(this, options);
            this.markIfEmpty();
        },

        /**
         * Add empty css class to root element if modal is empty
         */
        markIfEmpty: function() {
            this.$el.toggleClass('empty', this.model.get('items').length === 0);
        },

        /**
         * Mouse Enter on column name to show popover
         *
         * @param {Event} e
         */
        onMouseEnter: function(e) {
            this.subview('hint', new HintView({
                el: this.$('[data-grid-header-cell-label]'),
                offsetOfEl: this.$el,
                autoRender: true,
                popoverConfig: {
                    content: this.model.get('label')
                }
            }));

            this.hintTimeout = setTimeout(function() {
                var hint = this.subview('hint');

                if (hint && !hint.fullLabelIsVisible()) {
                    hint.show();
                }
            }.bind(this), 300);
        },

        /**
         * Mouse Leave from column name to hide popover
         *
         * @param {Event} e
         */
        onMouseLeave: function(e) {
            clearTimeout(this.hintTimeout);
            if (this.subview('hint')) {
                this.removeSubview('hint');
            }
        }
    });

    return ColumnHeaderView;
});
