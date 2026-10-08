<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
require_once __DIR__ . '/item_editor.php';
// Add faculty member: uses the shared modern form (item_editor.php). Old version: _old/adminpanel/oct6/add_faculty.php
$cfg = [
    'table' => 'faculty', 'noun' => 'faculty member', 'list_url' => 'manage_faculty.php', 'list_label' => 'Manage Faculty',
    'heading_add' => 'Add Faculty Member', 'heading_edit' => 'Edit Faculty Member',
    'intro' => 'Position “Teacher” is shown under Teaching Faculty; any other position under Leadership & Management.',
    'fields' => [
        'name'           => ['label' => 'Full name', 'required' => true, 'max' => 100, 'placeholder' => 'e.g. Ms Ayesha Khan'],
        'position'       => ['label' => 'Position', 'required' => true, 'max' => 100, 'placeholder' => 'e.g. Teacher',
                             'datalist' => ['Teacher', 'Principal', 'Vice Principal', 'Campus Coordinator', 'Administrator'],
                             'help' => 'Type “Teacher” for teaching staff.'],
        'experience'     => ['label' => 'Experience', 'type' => 'textarea', 'required' => true, 'max' => 500, 'rows' => 3, 'placeholder' => 'e.g. 8 years'],
        'qualifications' => ['label' => 'Education', 'type' => 'textarea', 'required' => true, 'max' => 500, 'rows' => 3,
                             'placeholder' => 'e.g. B.Ed, M.Ed', 'help' => 'Separate degrees with commas or put each on a new line; they are shown as a list.'],
    ],
    'image'   => ['column' => 'image', 'required' => true, 'label' => 'Photo', 'help' => 'Portrait photo (taller than wide), face in the upper part.'],
    'preview' => ['title' => 'name', 'text' => 'experience', 'meta' => null, 'shape' => '3 / 4', 'fit' => 'cover', 'label' => 'Faculty'],
];
item_editor_run($conn, $cfg, 'add');
