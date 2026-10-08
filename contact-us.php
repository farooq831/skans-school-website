<?php
$page_title = 'Contact Us';
$page_description = 'Contact SKANS School System, AECHS Campus, Rawalpindi: address, map, phone numbers, email, WhatsApp and a contact form.';
require __DIR__ . '/includes/bootstrap.php';

// ---------------------------------------------------------------------------
// Contact form. Saved to the table the admin panel already shows
// (Admin → Admissions & Contact-us Form → Contact forms).
// Spam protection: hidden "website" field, minimum fill time, form token,
// max 3 messages per visitor per hour, max 2 per email address per 10 minutes.
// ---------------------------------------------------------------------------
$values = ['full_name' => '', 'email' => '', 'phone' => '', 'message' => ''];
$errors = [];
$form_error = '';
$sent = isset($_GET['sent']);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach ($values as $key => $_) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    $started = (int) ($_POST['started'] ?? 0);
    $recent = array_filter($_SESSION['contact_sent'] ?? [], function ($t) { return $t > time() - 3600; });

    if (!form_token_ok($_POST['form_token'] ?? null)) {
        $form_error = 'Your session expired. Please send the form again.';
    } elseif (($_POST['sk_check'] ?? '') !== '' || $started === 0 || time() - $started < 3) {
        // Robot: pretend it worked, save nothing. Logged so a wrongly blocked person can be traced.
        error_log(sprintf('Contact form blocked as spam (trap field: %s, seconds on page: %d, email: %s)',
            ($_POST['sk_check'] ?? '') !== '' ? 'filled' : 'empty', time() - $started, $values['email']));
        header('Location: contact-us.php?sent=1#contact-form');
        exit;
    } elseif (count($recent) >= 3) {
        $form_error = 'You have sent several messages already. Please call or WhatsApp us instead.';
    } else {
        if (mb_strlen($values['full_name']) < 2 || mb_strlen($values['full_name']) > 100) {
            $errors['full_name'] = 'Please enter your full name.';
        }
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($values['email']) > 150) {
            $errors['email'] = 'Please enter a valid email address, for example name@example.com.';
        }
        if ($values['phone'] !== '' && !preg_match('/^\+?[0-9 ()-]{7,20}$/', $values['phone'])) {
            $errors['phone'] = 'Please enter a valid phone number, for example 0333 1234567.';
        }
        if (mb_strlen($values['message']) < 10) {
            $errors['message'] = 'Please write a message of at least 10 characters.';
        } elseif (mb_strlen($values['message']) > 2000) {
            $errors['message'] = 'Please keep your message under 2,000 characters.';
        }

        if (!$errors && $db_ok) {
            $since = date('Y-m-d H:i:s', time() - 600);
            $check = $conn->prepare('SELECT COUNT(*) FROM contact_form_submissions WHERE email = ? AND submitted_at > ?');
            $check->bind_param('ss', $values['email'], $since);
            $check->execute();
            $check->bind_result($same_email);
            $check->fetch();
            $check->close();

            if ($same_email >= 2) {
                $form_error = 'We have already received your message. We will get back to you soon.';
            } else {
                $phone = $values['phone'] !== '' ? $values['phone'] : null;
                $save = $conn->prepare('INSERT INTO contact_form_submissions (full_name, email, phone, message) VALUES (?, ?, ?, ?)');
                $save->bind_param('ssss', $values['full_name'], $values['email'], $phone, $values['message']);
                if ($save->execute()) {
                    $recent[] = time();
                    $_SESSION['contact_sent'] = array_values($recent);
                    header('Location: contact-us.php?sent=1#contact-form');
                    exit;
                }
                error_log('Contact form save failed: ' . $save->error);
                $form_error = 'Sorry, your message could not be sent. Please call or email us instead.';
            }
        } elseif (!$errors) {
            $form_error = 'Sorry, your message could not be sent. Please call or email us instead.';
        }
    }
}

require __DIR__ . '/includes/header.php';
$map_embed = 'https://www.google.com/maps?q=' . rawurlencode($site['address']) . '&output=embed';

/** Field attributes for errors (aria-invalid + aria-describedby). */
function field_state(array $errors, string $name): string
{
    return isset($errors[$name]) ? ' aria-invalid="true" aria-describedby="' . $name . '-error"' : '';
}
?>
<?php page_banner('Contact Us', 'Whether you are a parent, a prospective student or a member of the community, we are happy to help.', [['Contact Us', '']]); ?>

<section class="sk-section">
    <div class="sk-container">
        <div class="sk-grid">
            <article class="sk-card sk-card--accent sk-contact-card">
                <div class="sk-card__body">
                    <h2 class="sk-contact-card__title"><span class="sk-icon-badge"><?= icon('pin') ?></span>Visit us</h2>
                    <p class="sk-iconline"><?= icon('pin') ?><span><?= e($site['full_name']) ?><br><?= e($site['address']) ?></span></p>
                    <a class="sk-iconline sk-card__link" href="<?= e($site['map_url']) ?>" target="_blank" rel="noopener"><?= icon('map') ?><span>Open in Google Maps →</span></a>
                </div>
            </article>
            <article class="sk-card sk-card--accent sk-contact-card">
                <div class="sk-card__body">
                    <h2 class="sk-contact-card__title"><span class="sk-icon-badge"><?= icon('phone') ?></span>Call us</h2>
<?php foreach ($site['phones'] as $phone): ?>
                    <p class="sk-iconline"><?= icon('phone') ?><span><?= e(trim(str_replace('/ WhatsApp', '', $phone['label']))) ?>: <a class="sk-strong-link sk-nowrap" href="tel:<?= e($phone['tel']) ?>"><?= e($phone['display']) ?></a></span></p>
<?php endforeach; ?>
                </div>
            </article>
            <article class="sk-card sk-card--accent sk-contact-card">
                <div class="sk-card__body">
                    <h2 class="sk-contact-card__title"><span class="sk-icon-badge"><?= icon('mail') ?></span>Email or WhatsApp</h2>
                    <p class="sk-iconline"><?= icon('mail') ?><a class="sk-strong-link sk-email" href="mailto:<?= e($site['email']) ?>"><?= str_replace('@', '@<wbr>', e($site['email'])) ?></a></p>
                    <a class="sk-iconline sk-card__link" href="https://wa.me/<?= e($site['whatsapp']) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?><span>Chat on WhatsApp →</span></a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="sk-section sk-section--white sk-anchor" id="contact-form">
    <div class="sk-container sk-split">
        <div>
            <div class="sk-section-header">
                <span class="sk-eyebrow">Send us a message</span>
                <h2>Contact form</h2>
                <span class="sk-rule"></span>
                <p>Have a question about admissions, fees or school life? Write to us and the school office will reply by email or phone.</p>
            </div>

<?php if ($sent): ?>
            <div class="sk-alert sk-alert--success" role="status">
                <strong>Thank you, your message has been sent.</strong>
                <p>The school office will get back to you soon. For urgent questions, please call us.</p>
            </div>
<?php endif; ?>
<?php if ($form_error !== ''): ?>
            <div class="sk-alert sk-alert--error" role="alert"><?= e($form_error) ?></div>
<?php elseif ($errors): ?>
            <div class="sk-alert sk-alert--error" role="alert">Please check the fields marked below.</div>
<?php endif; ?>

            <form class="sk-form" id="sk-contact-form" method="post" action="contact-us.php#contact-form" novalidate>
                <input type="hidden" name="form_token" value="<?= e(form_token()) ?>">
                <input type="hidden" name="started" value="<?= time() ?>">
                <div class="sk-hp" aria-hidden="true">
                    <label for="sk_check">Do not type anything here</label>
                    <input type="text" id="sk_check" name="sk_check" value="" tabindex="-1" autocomplete="off" data-lpignore="true" data-1p-ignore>
                </div>

                <div class="sk-field">
                    <label for="full_name">Full name <span class="sk-req">(required)</span></label>
                    <input type="text" id="full_name" name="full_name" maxlength="100" autocomplete="name" required value="<?= e($values['full_name']) ?>"<?= field_state($errors, 'full_name') ?>>
<?php if (isset($errors['full_name'])): ?>
                    <p class="sk-field-error" id="full_name-error"><?= e($errors['full_name']) ?></p>
<?php endif; ?>
                </div>

                <div class="sk-form__row">
                    <div class="sk-field">
                        <label for="email">Email <span class="sk-req">(required)</span></label>
                        <input type="email" id="email" name="email" maxlength="150" autocomplete="email" inputmode="email" required value="<?= e($values['email']) ?>"<?= field_state($errors, 'email') ?>>
<?php if (isset($errors['email'])): ?>
                        <p class="sk-field-error" id="email-error"><?= e($errors['email']) ?></p>
<?php endif; ?>
                    </div>
                    <div class="sk-field">
                        <label for="phone">Phone <span class="sk-muted">(optional)</span></label>
                        <input type="tel" id="phone" name="phone" maxlength="20" autocomplete="tel" inputmode="tel" value="<?= e($values['phone']) ?>"<?= field_state($errors, 'phone') ?>>
<?php if (isset($errors['phone'])): ?>
                        <p class="sk-field-error" id="phone-error"><?= e($errors['phone']) ?></p>
<?php endif; ?>
                    </div>
                </div>

                <div class="sk-field">
                    <label for="message">Message <span class="sk-req">(required)</span></label>
                    <textarea id="message" name="message" rows="6" maxlength="2000" required<?= field_state($errors, 'message') ?>><?= e($values['message']) ?></textarea>
<?php if (isset($errors['message'])): ?>
                    <p class="sk-field-error" id="message-error"><?= e($errors['message']) ?></p>
<?php endif; ?>
                </div>

                <p class="sk-muted sk-small">We use your details only to reply to your message.</p>
                <button class="sk-btn sk-btn--primary" type="submit">Send message</button>
            </form>
        </div>

        <div>
            <div class="sk-section-header">
                <span class="sk-eyebrow">Find us</span>
                <h2>Location</h2>
                <span class="sk-rule"></span>
                <p><?= e($site['address']) ?></p>
            </div>
            <div class="sk-map">
                <iframe src="<?= e($map_embed) ?>" title="Map showing <?= e($site['full_name']) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
            <p class="sk-mt-5"><a href="<?= e($site['map_url']) ?>" target="_blank" rel="noopener">Get directions in Google Maps →</a></p>
        </div>
    </div>
</section>

<section class="sk-section sk-section--tight">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">Stay connected</span>
            <h2>Follow us</h2>
            <span class="sk-rule"></span>
        </div>
        <div class="sk-actions sk-mt-0">
            <a class="sk-btn sk-btn--outline" href="<?= e($site['facebook']) ?>" target="_blank" rel="noopener">Facebook</a>
            <a class="sk-btn sk-btn--outline" href="<?= e($site['instagram']) ?>" target="_blank" rel="noopener">Instagram</a>
            <a class="sk-btn sk-btn--outline" href="https://wa.me/<?= e($site['whatsapp']) ?>" target="_blank" rel="noopener">WhatsApp</a>
<?php if (!empty($site['youtube'])): ?>
            <a class="sk-btn sk-btn--outline" href="<?= e($site['youtube']) ?>" target="_blank" rel="noopener">YouTube</a>
<?php endif; ?>
<?php if (!empty($site['tiktok'])): ?>
            <a class="sk-btn sk-btn--outline" href="<?= e($site['tiktok']) ?>" target="_blank" rel="noopener">TikTok</a>
<?php endif; ?>
        </div>
    </div>
</section>

<script>
// A real person is sending the form: clear the hidden robot-trap field in case the browser's
// autofill typed into it. Robots that do not run JavaScript are still caught.
document.getElementById('sk-contact-form').addEventListener('submit', function () {
    document.getElementById('sk_check').value = '';
});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
