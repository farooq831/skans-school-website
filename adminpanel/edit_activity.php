<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
require_once __DIR__ . '/item_editor.php';
// Edit activity: uses the shared modern form (item_editor.php). Old version: _old/adminpanel/oct6/edit_activity.php
$cfg = [
    'table' => 'activities', 'noun' => 'activity', 'list_url' => 'manage_activities.php', 'list_label' => 'Manage Activities',
    'heading_add' => 'Add Co-curricular Activity', 'heading_edit' => 'Edit Activity',
    'intro' => 'Shown under “Co-curricular Activities” on the Facilities page.',
    'fields' => [
        'title'       => ['label' => 'Activity title', 'required' => true, 'max' => 150, 'placeholder' => 'e.g. Science Fair'],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'max' => DESCRIPTION_MAX, 'rows' => 6,
                          'placeholder' => 'What the students did and learned…'],
    ],
    'image'   => ['column' => 'image', 'required' => true, 'label' => 'Picture', 'help' => 'Portrait or square photos fill the card best.'],
    'preview' => ['title' => 'title', 'text' => 'description', 'shape' => '3 / 4', 'fit' => 'cover', 'label' => 'Activity'],
    'quota'   => true,
];
item_editor_run($conn, $cfg, 'edit');
