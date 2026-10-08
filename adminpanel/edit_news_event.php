<?php
require __DIR__ . '/auth.php'; // login check
include 'db.php';
require_once __DIR__ . '/item_editor.php';
// Edit News & Event: uses the shared modern form (item_editor.php). Old version: _old/adminpanel/oct6/edit_news_event.php
$categories = [];
foreach ($conn->query('SELECT id, name FROM categories ORDER BY name')->fetch_all(MYSQLI_ASSOC) as $c) {
    $categories[(string) $c['id']] = $c['name'];
}
$cfg = [
    'table' => 'news_events', 'noun' => 'news post', 'list_url' => 'manage_news_events.php', 'list_label' => 'Manage News & Events',
    'heading_add' => 'Add News & Events', 'heading_edit' => 'Edit News & Event',
    'intro' => 'Shown on the News & Events page; the 3 newest also appear on the Home page.',
    'fields' => [
        'title'       => ['label' => 'Title', 'required' => true, 'max' => 150, 'placeholder' => 'e.g. Sports Day 2026'],
        'category_id' => ['label' => 'Category', 'type' => 'select', 'required' => true, 'int' => true, 'options' => $categories],
        'date'        => ['label' => 'Date of the news or event', 'type' => 'date', 'required' => true, 'default' => date('Y-m-d')],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'max' => DESCRIPTION_MAX, 'rows' => 6,
                          'placeholder' => 'What happened, who took part, why it matters…'],
    ],
    'image'   => ['column' => 'image_url', 'required' => true, 'label' => 'Picture', 'help' => 'Any shape; it is always shown complete on the website.'],
    'preview' => ['title' => 'title', 'text' => 'description', 'meta' => 'category_id', 'date' => 'date', 'shape' => '1 / 1', 'label' => 'News'],
    'quota'   => true,
    'insert_extra' => ['created_at' => 'NOW()'], // when it was added: for the monthly post limit
];
item_editor_run($conn, $cfg, 'edit');
