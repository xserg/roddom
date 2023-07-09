<?php

return [
    /**
     * Tree model fields
     */
    'column_name' => [
        'order' => 'order',
        'parent' => 'referrer_id',
        'title' => 'email',
    ],
    /**
     * Tree model default parent key
     */
    'default_parent_id' => null,
    /**
     * Tree model default children key name
     */
    'default_children_key_name' => 'recursiveChildren',
];
