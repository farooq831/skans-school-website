<?php
// includes/footer.php : footer, WhatsApp button, closing tags.
$legacy_page = !empty($legacy_page);
?>
<?= $legacy_page ? '</div>' : '</main>' ?>

<footer class="sk-footer">
    <div class="sk-container sk-footer__grid">
        <section class="sk-footer__col" aria-labelledby="ft-campus">
            <h2 class="sk-footer__title" id="ft-campus"><?= e($site['full_name']) ?></h2>
            <ul class="sk-footer__list sk-footer__list--icons">
                <li>
                    <?= icon('pin') ?>
                    <span>
                        <address class="sk-footer__address"><?= e($site['address']) ?></address>
                        <a href="<?= e($site['map_url']) ?>" target="_blank" rel="noopener">View on Google Maps</a>
                    </span>
                </li>
            </ul>
        </section>

        <section class="sk-footer__col" aria-labelledby="ft-contact">
            <h2 class="sk-footer__title" id="ft-contact">Contact</h2>
            <ul class="sk-footer__list sk-footer__list--icons">
<?php foreach ($site['phones'] as $phone): ?>
                <li>
                    <?= icon('phone') ?>
                    <span><span class="sk-footer__label"><?= e(trim(str_replace('/ WhatsApp', '', $phone['label']))) ?></span>
                    <a class="sk-nowrap" href="tel:<?= e($phone['tel']) ?>"><?= e($phone['display']) ?></a></span>
                </li>
<?php endforeach; ?>
                <li>
                    <?= icon('whatsapp') ?>
                    <span><span class="sk-footer__label">WhatsApp</span>
                    <a class="sk-nowrap" href="https://wa.me/<?= e($site['whatsapp']) ?>" target="_blank" rel="noopener">Chat with us</a></span>
                </li>
                <li>
                    <?= icon('mail') ?>
                    <span><span class="sk-footer__label">Email</span>
                    <a class="sk-break" href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></span>
                </li>
            </ul>
        </section>

        <section class="sk-footer__col" aria-labelledby="ft-links">
            <h2 class="sk-footer__title" id="ft-links">Quick links</h2>
            <ul class="sk-footer__list">
                <li><a href="admissions.php">Admission Process</a></li>
                <li><a href="fee-structure.php">Fee Structure</a></li>
                <li><a href="admission-policy.php">Admission Policy</a></li>
                <li><a href="departments.php">Programs Offered</a></li>
                <li><a href="advanced-placement-program.php">Special Programs</a></li>
                <li><a href="policies.php">SKANS Policies</a></li>
            </ul>
        </section>

        <section class="sk-footer__col" aria-labelledby="ft-follow">
            <h2 class="sk-footer__title" id="ft-follow">Follow us</h2>
            <ul class="sk-footer__list sk-footer__list--icons">
                <li><?= icon('facebook') ?><a href="<?= e($site['facebook']) ?>" target="_blank" rel="noopener">Facebook</a></li>
                <li><?= icon('instagram') ?><a href="<?= e($site['instagram']) ?>" target="_blank" rel="noopener">Instagram</a></li>
                <li><?= icon('whatsapp') ?><a href="https://wa.me/<?= e($site['whatsapp']) ?>" target="_blank" rel="noopener">WhatsApp</a></li>
<?php if (!empty($site['youtube'])): ?>
                <li><?= icon('youtube') ?><a href="<?= e($site['youtube']) ?>" target="_blank" rel="noopener">YouTube</a></li>
<?php endif; ?>
<?php if (!empty($site['tiktok'])): ?>
                <li><?= icon('tiktok') ?><a href="<?= e($site['tiktok']) ?>" target="_blank" rel="noopener">TikTok</a></li>
<?php endif; ?>
            </ul>
        </section>
    </div>
    <div class="sk-container sk-footer__bottom">
        <p>© <?= date('Y') ?> <?= e($site['name']) ?>. All rights reserved.</p>
    </div>
</footer>

<a class="sk-whatsapp" href="https://wa.me/<?= e($site['whatsapp']) ?>" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp">
    <svg viewBox="0 0 32 32" width="28" height="28" aria-hidden="true" focusable="false"><path fill="currentColor" d="M16 3C8.8 3 3 8.7 3 15.8c0 2.5.7 4.9 2 7L3 29l6.4-2c2 1.1 4.3 1.7 6.6 1.7 7.2 0 13-5.7 13-12.8S23.2 3 16 3zm0 23.4c-2.1 0-4.1-.6-5.9-1.7l-.4-.3-3.8 1.2 1.2-3.7-.3-.4c-1.2-1.8-1.9-3.9-1.9-6 0-5.9 4.9-10.7 11-10.7s11 4.8 11 10.7-4.9 10.9-10.9 10.9zm6-8c-.3-.2-1.9-.9-2.2-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1-.3-.2-1.4-.5-2.6-1.6-1-.9-1.6-1.9-1.8-2.2-.2-.3 0-.5.1-.7l.5-.6c.2-.2.2-.4.3-.6.1-.2 0-.4 0-.6l-1-2.4c-.3-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.7s1.2 3.2 1.4 3.4c.2.2 2.4 3.6 5.8 5 .8.3 1.4.5 1.9.7.8.3 1.5.2 2.1.1.6-.1 1.9-.8 2.2-1.5.3-.7.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>
</a>
</body>
</html>
