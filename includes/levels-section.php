<?php
// includes/levels-section.php : "Academic Programs" band with the three level cards.
// Used on the Programs Offered page and the Home page, so both always show the same thing.
// Data: $site['levels'] (data/site.php + admin Academic Programs #1-#3, see load_levels()).
?>
<section class="sk-section sk-section--navy sk-levels-band">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow"><?= e(campus_text('Playgroup to Matric')) ?></span>
            <h2>Academic Programs</h2>
            <span class="sk-rule"></span>
        </div>
        <div class="sk-levels">
<?php foreach ($site['levels'] as $level): ?>
            <article class="sk-card sk-level" id="<?= e($level['id']) ?>">
<?php if ($level['image'] !== '' && is_file(SK_ROOT . '/' . $level['image'])): ?>
                <div class="sk-card__media"><img src="<?= e($level['image']) ?>" alt="<?= e($level['name']) ?> students at <?= e('SKANS ' . $site['campus']) ?>" loading="lazy" width="600" height="400"<?= !empty($level['image_position']) ? ' style="object-position: ' . e($level['image_position']) . '"' : '' ?>></div>
<?php else: ?>
                <div class="sk-card__media sk-card__media--empty" aria-hidden="true"><?= e($level['name']) ?></div>
<?php endif; ?>
                <div class="sk-card__body">
                    <h3 class="sk-card__title"><?= e($level['name']) ?></h3>
                    <p class="sk-level__range"><?= e($level['range']) ?></p>
<?php if (campus_feature('level_classes')): // config 'features' => ['level_classes' => false] hides it ?>
                    <ul class="sk-level__classes" aria-label="Classes">
<?php foreach ($level['classes'] as [$class, $age]): ?>
                        <li><?= e($class) ?> <span>· <?= e($age) ?></span></li>
<?php endforeach; ?>
                    </ul>
<?php endif; ?>
<?php if (isset($level['board']) && campus_feature('level_board')): ?>
                    <p class="sk-card__meta">Examination board: <?= content($level['board'], 'board name') ?></p>
<?php endif; ?>
                    <h4>Key objectives</h4>
<?php if (is_missing($level['objectives'][0] ?? '')): ?>
                    <p><?= content('', 'objectives for ' . $level['name']) ?></p>
<?php else: ?>
                    <ul class="sk-checklist">
<?php foreach ($level['objectives'] as $objective): ?>
                        <li><?= e($objective) ?></li>
<?php endforeach; ?>
                    </ul>
<?php endif; ?>
                </div>
            </article>
<?php endforeach; ?>
        </div>
        <p class="sk-levels-hint" aria-hidden="true">Swipe to see all three levels →</p>
    </div>
</section>
