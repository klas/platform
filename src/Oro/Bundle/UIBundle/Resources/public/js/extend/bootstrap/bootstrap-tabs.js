define(function(require) {
    'use strict';

    var $ = require('jquery');
    var DATA_KEY = 'bs.tab';
    var EVENT_KEY = '.' + DATA_KEY;
    var Event = {
        HIDDEN: 'hidden' + EVENT_KEY,
        SHOWN: 'shown' + EVENT_KEY
    };
    var ClassName = {
        ACTIVE: 'active',
        SHOW: 'show'
    };

    var mediator = require('oroui/js/mediator');
    var Util = require('bootstrap-util');

    $(document)
        .on(Event.HIDDEN, function(event) {
            var prevEl = $(event.relatedTarget);

            // Remove active state from element which placed outside of NAV_LIST_GROUP container
            if (prevEl.data('extra-toggle') === 'tab') {
                prevEl
                    .removeClass(ClassName.SHOW + ' ' + ClassName.ACTIVE)
                    .attr('aria-selected', false);
            }
            var selector = Util.getSelectorFromElement(event.target);
            mediator.trigger('content:hidden', $(selector));
        })
        .on(Event.SHOWN, function(event) {
            var selector = Util.getSelectorFromElement(event.target);
            mediator.trigger('content:shown', $(selector));
            mediator.trigger('layout:reposition');
        });
});
