<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
require_once __DIR__ . '/item_editor.php';
// Add facility: uses the shared modern form (item_editor.php). Old version: _old/adminpanel/oct6/add_service.php
$cfg = [
    'table' => 'services', 'noun' => 'facility', 'list_url' => 'Manage_services.php', 'list_label' => 'Manage Facilities',
    'heading_add' => 'Add Facility or Service', 'heading_edit' => 'Edit Facility or Service',
    'intro' => 'Shown under “Student Services on Campus” on the Facilities page.',
    'fields' => [
        'title'       => ['label' => 'Facility name', 'required' => true, 'max' => 150, 'placeholder' => 'e.g. Science Laboratory'],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'max' => 1000, 'rows' => 5,
                          'placeholder' => 'What the facility offers students…'],
    ],
    'image'   => ['column' => 'image', 'required' => true, 'label' => 'Picture', 'help' => 'Portrait or square photos fill the card best.'],
    'preview' => ['title' => 'title', 'text' => 'description', 'shape' => '3 / 4', 'fit' => 'cover', 'label' => 'Facility'],
];
item_editor_run($conn, $cfg, 'add');
