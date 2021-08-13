<?php

return [
    //default button labels and classes can be set up here
    //when generating the button faces, the __label__ placeholder in the html string
    //will be replaced by the translationLabel ran through the __ function
    'buttons' => [
        'details' => [
            'class'       => 'btn btn-outline-primary',
            'html'        => '<span title="__label__">?</span>',
            'translationLabel' => 'Details',
        ],
        'edit'   => [
            'class'       => 'btn btn-outline-secondary',
            'html'        => '<span title="__label__">⚙</span>',
            'translationLabel' => 'Edit',
        ],
        'delete' => [
            'class'       => 'btn btn-outline-danger',
            'html'        => '<span title="__label__">✕</span>',
            'translationLabel' => 'Delete',
        ],
        'moveUp' => [
            'class'       => 'btn btn-outline-secondary',
            'html'        => '↑',
        ],
        'moveDown' => [
            'class'       => 'btn btn-outline-secondary',
            'html'        => '↓',
        ],

    ],
    'vueCrudDefaultView' => 'packages.vue-crud.model-manager'


    /*
     * optional fields
     *
     * formdatabuilder.defaultContainerClass
     * formdatabuilder.defaultInputClass
     * translateConstants
     * alertPopupCSSClassPrefix
     */
];