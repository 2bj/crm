/* global define */
define(['underscore', 'backbone', 'jquery.select2'],
function(_, Backbone) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  orocrm/contactphone/view
     * @class   orocrm.contactphone.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        events: {
            'change': 'selectionChanged'
        },

        /**
         * Constructor
         *
         * @param options {Object}
         */
        initialize: function(options) {
            
            this.target = $(options.target);
            this.$simpleEl = $(options.simpleEl);

            this.target.closest('.controls').append(this.$simpleEl);
            
            this.showSelect = options.showSelect;
            this.template = $('#contactphone-chooser-template').html();
            this.$simpleEl.attr('type', 'text');

            if (!this.showSelect) {
                this.$simpleEl.show();
            } else {
                this.$simpleEl.hide();
            }

            this.displaySelect2(this.showSelect);
            this.target.on('select2-init', _.bind(function() {
                this.displaySelect2(this.showSelect);
            }, this));

            this.listenTo(this.collection, 'reset', this.render);
        },

        /**
         * Show/hide select 2 element
         *
         * @param {Boolean} display
         */
        displaySelect2: function(display) {
            if (display) {
                this.target.select2('container').show();
            } else {
                this.target.select2('container').hide();
            }
        },

        getInputLabel: function(el) {
            return el.parent().parent().find('label');
        },

        /**
         * Trigger change event
         */
        sync: function() {
            if (this.target.val() == '' && this.$el.val() != '') {
                this.$el.trigger('change');
            }
        },

        /**
         * onChange event listener
         *
         * @param e {Object}
         */
        selectionChanged: function(e) {
            var contactId = $(e.currentTarget).val();
            this.collection.setContactId(contactId);
            this.collection.fetch();
        },

        render: function() {
            this.uniform = $('#uniform-' + this.target[0].id);

            if (this.collection.models.length > 0) {
                this.target.show();
                this.displaySelect2(true);
                this.uniform.show();

                this.target.val('').trigger('change');
                this.target.find('option[value!=""]').remove();
                this.target.append(_.template(this.template, {contactphones: this.collection.models}));

                this.$simpleEl.hide();
                this.$simpleEl.val('');

            } else {                
                this.target.hide();
                this.target.val('');
                this.displaySelect2(false);
                this.uniform.hide();
                this.$simpleEl.show();
            }
        }
    });
});
