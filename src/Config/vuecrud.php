<?php

return [
    //default button labels and classes can be set up here
    'buttons' => [
        'details' => [
            'class'       => 'btn btn-outline-primary',
            'html'        => __('Details'),
        ],
        'edit'   => [
            'class'       => 'btn btn-outline-secondary',
            'html'        => __('Edit'),
        ],
        'delete' => [
            'class'       => 'btn btn-outline-danger',
            'html'        => __('Delete'),
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
    'vueCrudDefaultView' => 'vendor.vue-crud.model-manager'
];