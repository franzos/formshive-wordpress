/**
 * Formshive Gutenberg Blocks
 */

(function() {
    'use strict';

    const { registerBlockType } = wp.blocks;
    const { createElement } = wp.element;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, SelectControl, Placeholder, Notice } = wp.components;
    const { __ } = wp.i18n;

    // Get forms data from localized script
    const formsData = formshive_blocks.forms || [];

    // Create options for form selector
    const formOptions = [
        { label: __('Select a form...', 'formshive'), value: '' }
    ].concat(
        formsData.map(form => ({
            label: `${form.name} (${form.type === 'embed' ? 'Embed' : 'Manual'})`,
            value: form.id.toString()
        }))
    );

    // Framework options
    const frameworkOptions = [
        { label: __('Bootstrap', 'formshive'), value: 'bootstrap' },
        { label: __('Bulma', 'formshive'), value: 'bulma' }
    ];

    /**
     * Register Formshive Form Block
     */
    registerBlockType('formshive/form', {
        title: __('Formshive Form', 'formshive'),
        description: __('Embed a Formshive form.', 'formshive'),
        icon: 'forms',
        category: 'widgets',
        keywords: [
            __('form', 'formshive'),
            __('formshive', 'formshive'),
            __('contact', 'formshive')
        ],
        attributes: {
            formId: {
                type: 'string',
                default: ''
            },
            framework: {
                type: 'string',
                default: 'bootstrap'
            }
        },
        supports: {
            html: false,
            customClassName: false
        },

        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { formId, framework } = attributes;

            // Get selected form data
            const selectedForm = formsData.find(form => form.id.toString() === formId);

            const onChangeForm = function(newFormId) {
                setAttributes({ formId: newFormId });
            };

            const onChangeFramework = function(newFramework) {
                setAttributes({ framework: newFramework });
            };

            // Block inspector controls
            const inspectorControls = createElement(InspectorControls, null,
                createElement(PanelBody, {
                    title: __('Form Settings', 'formshive'),
                    initialOpen: true
                },
                    createElement(SelectControl, {
                        label: __('Select Form', 'formshive'),
                        value: formId,
                        options: formOptions,
                        onChange: onChangeForm,
                        help: __('Choose which form to display.', 'formshive')
                    }),
                    createElement(SelectControl, {
                        label: __('CSS Framework', 'formshive'),
                        value: framework,
                        options: frameworkOptions,
                        onChange: onChangeFramework,
                        help: __('Choose the CSS framework for styling.', 'formshive')
                    })
                )
            );

            // Block content
            let blockContent;

            if (!formId) {
                // Show placeholder when no form is selected
                blockContent = createElement(Placeholder, {
                    icon: 'forms',
                    label: __('Formshive Form', 'formshive'),
                    instructions: __('Select a form from the block settings to display it here.', 'formshive')
                },
                    createElement(SelectControl, {
                        label: __('Select Form', 'formshive'),
                        value: formId,
                        options: formOptions,
                        onChange: onChangeForm
                    })
                );
            } else if (selectedForm) {
                // Show form preview
                const formType = selectedForm.type === 'embed' ? __('Embedded Form', 'formshive') : __('Manual Form', 'formshive');
                
                blockContent = createElement('div', {
                    className: 'formshive-block-preview'
                },
                    createElement('div', {
                        className: 'formshive-block-header'
                    },
                        createElement('h4', null, selectedForm.name),
                        createElement('p', null, 
                            `${formType} • ${__('Framework:', 'formshive')} ${framework}`
                        )
                    ),
                    createElement('div', {
                        className: 'formshive-block-placeholder'
                    },
                        createElement('p', null, 
                            __('Form will be displayed here on the frontend.', 'formshive')
                        ),
                        selectedForm.type === 'embed' && createElement('p', {
                            style: { fontSize: '12px', color: '#666' }
                        }, 
                            __('Embedded forms are loaded dynamically from Formshive.', 'formshive')
                        )
                    )
                );
            } else {
                // Show error when form not found
                blockContent = createElement(Notice, {
                    status: 'error',
                    isDismissible: false
                }, __('Selected form not found. Please choose a different form.', 'formshive'));
            }

            return createElement('div', null, inspectorControls, blockContent);
        },

        save: function() {
            // Return null to use PHP render callback
            return null;
        }
    });

})();