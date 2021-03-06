'use strict';

/**
 * Module used to display the generals properties of a channel
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/channel/tab/properties/general',
        'pim/user-context',
        'oro/mediator',
        'pim/common/property',
        'jquery.select2'
    ],
    function (
        _,
        __,
        BaseForm,
        FetcherRegistry,
        template,
        UserContext,
        mediator,
        propertyAccessor
    ) {
        return BaseForm.extend({
            className: 'tabsection',
            template: _.template(template),
            catalogLocale: UserContext.get('catalogLocale'),
            errors: [],
            events: {
                'change input.channel-code': 'updateCode'
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:bad_request',
                    this.setValidationErrors.bind(this)
                );
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.render.bind(this));

                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:post_fetch',
                    this.resetValidationErrors.bind(this)
                );

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(this.template({
                    code: this.getFormData().code,
                    hasId: _.has(this.getFormData().meta, 'id'),
                    sectionTitle: __('pim_enrich.form.channel.tab.properties.general'),
                    catalogLocale: this.catalogLocale,
                    errors: this.getValidationErrorsForField('code'),
                    label: __('pim_enrich.form.channel.tab.properties.code'),
                    requiredLabel: __('pim_enrich.form.required')
                }));

                this.delegateEvents();
                this.renderExtensions();
            },

            /**
             * Updates state of code property on event.
             *
             * @param {Object} event
             */
            updateCode: function (event) {
                var data = this.getFormData();
                data.code = event.target.value;

                this.setData(data);
            },

            /**
             * Get the validation errors for the given field
             *
             * @param {string} field
             *
             * @return {mixed}
             */
            getValidationErrorsForField: function (field) {
                return propertyAccessor
                    .accessProperty(
                        this.errors,
                        field,
                        []
                    );
            },

            /**
             * Sets errors
             *
             * @param {Object} errors
             */
            setValidationErrors: function (errors) {
                this.errors = errors.response;
            },

            resetValidationErrors: function () {
                this.errors = {};
            }
        });
    }
);
