<?php
// adminpanel/announcement_form.php : the form shared by Add and Edit announcement.
// Expects: $form_title, $form_values (heading, announcement_text, image), $form_errors, $submit_label.
$preview_src = upload_url($form_values['image']);
?>
<style>
    .ann-wrap { max-width: 1100px; }
    .ann-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 16px rgba(25,42,74,.08); padding: 28px; }
    .ann-card h1 { font-size: 1.6rem; color: #192A4A; margin-bottom: 4px; }
    .ann-help { color: #5B6475; font-size: .9rem; }
    .ann-label { font-weight: 600; color: #192A4A; }
    .ann-drop { border: 2px dashed #C9B48A; border-radius: 10px; padding: 18px; background: #FCFAF6; text-align: center; }
    .ann-drop input { display: block; margin: 8px auto 0; }
    /* Preview: exactly the website's picture style - the whole picture, never cropped or stretched. */
    .ann-preview { border: 1px solid #E4DCCD; border-radius: 12px; overflow: hidden; background: #fff; }
    .ann-media { position: relative; aspect-ratio: 4 / 3; background: #EEF1F6; overflow: hidden; display: grid; place-items: center; }
    .ann-media::before { content: ""; position: absolute; inset: -20px; background-image: var(--img); background-size: cover; background-position: center; filter: blur(18px) saturate(1.1); opacity: .55; }
    .ann-media img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; }
    .ann-media .ann-empty { position: relative; color: #192A4A; font-family: "Times New Roman", serif; font-size: 1.2rem; }
    .ann-body { padding: 16px 18px; }
    .ann-date { font-size: .8rem; color: #8A5F1C; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .ann-body h3 { font-family: "Times New Roman", serif; font-weight: 700; color: #192A4A; font-size: 1.25rem; margin: 6px 0; overflow-wrap: anywhere; }
    .ann-body p { color: #3B4558; white-space: pre-line; margin: 0; overflow-wrap: anywhere; }
    .ann-count { font-size: .8rem; color: #5B6475; text-align: right; }
</style>

<div class="container ann-wrap my-4">
    <p><a href="view_announcements.php">← Back to announcements</a></p>
    <div class="ann-card">
        <h1><?= h($form_title) ?></h1>
        <p class="ann-help mb-4">The newest announcement appears at the top of the News &amp; Events page. Each announcement stays on the website for 30 days, then leaves it automatically (it stays saved in the admin panel).</p>

<?php if ($form_errors): ?>
        <div class="alert alert-danger" role="alert">
            <strong>Please fix the following:</strong>
            <ul class="mb-0"><?php foreach ($form_errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?></ul>
        </div>
<?php endif; ?>

        <div class="row">
            <div class="col-lg-6">
                <form method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= h(admin_csrf_token()) ?>">

                    <div class="form-group">
                        <label class="ann-label" for="heading">Title <span class="text-muted font-weight-normal">(required)</span></label>
                        <input type="text" id="heading" name="heading" class="form-control" maxlength="150" required value="<?= h($form_values['heading']) ?>">
                        <div class="ann-count"><span id="heading-count">0</span> / 150</div>
                    </div>

                    <div class="form-group">
                        <label class="ann-label" for="announcement_text">Announcement text <span class="text-muted font-weight-normal">(required)</span></label>
                        <textarea id="announcement_text" name="announcement_text" class="form-control" rows="6" maxlength="<?= DESCRIPTION_MAX ?>" required><?= h($form_values['announcement_text']) ?></textarea>
                        <div class="ann-count"><span id="text-count">0</span> / <?= DESCRIPTION_MAX ?> · new lines are kept on the website</div>
                    </div>

                    <div class="form-group">
                        <span class="ann-label d-block">Picture <span class="text-muted font-weight-normal">(optional)</span></span>
                        <div class="ann-drop">
                            <span class="ann-help">Any shape works: tall poster, square or wide photo. It is always shown complete.<br>JPG, PNG, WebP or GIF, up to <?= IMAGE_MAX_LABEL ?>.</span>
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif" aria-label="Choose picture">
                        </div>
<?php if ($form_values['image']): ?>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                            <label class="form-check-label" for="remove_image">Remove the current picture</label>
                        </div>
                        <p class="ann-help mt-1 mb-0">Choose a new file only if you want to replace the current picture.</p>
<?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" style="background:#192A4A;border-color:#192A4A"><?= h($submit_label) ?></button>
                </form>
            </div>

            <div class="col-lg-6 mt-4 mt-lg-0">
                <span class="ann-label d-block mb-2">Preview: how it will look on the website</span>
                <div class="ann-preview" aria-live="polite">
                    <div class="ann-media" id="pv-media" style="--img: url('<?= h($preview_src) ?>')">
                        <img id="pv-img" src="<?= h($preview_src) ?>" alt="" <?= $preview_src ? '' : 'hidden' ?>>
                        <span class="ann-empty" id="pv-empty" <?= $preview_src ? 'hidden' : '' ?>>No picture</span>
                    </div>
                    <div class="ann-body">
                        <div class="ann-date"><?= h(date('j M Y')) ?></div>
                        <h3 id="pv-title"><?= h($form_values['heading']) ?: 'Your title' ?></h3>
                        <p id="pv-text"><?= h($form_values['announcement_text']) ?: 'Your announcement text.' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var heading = document.getElementById('heading'), text = document.getElementById('announcement_text'),
        file = document.getElementById('image'), remove = document.getElementById('remove_image'),
        media = document.getElementById('pv-media'), img = document.getElementById('pv-img'), empty = document.getElementById('pv-empty'),
        original = img.getAttribute('src');
    function update() {
        document.getElementById('pv-title').textContent = heading.value.trim() || 'Your title';
        document.getElementById('pv-text').textContent = text.value.trim() || 'Your announcement text.';
        document.getElementById('heading-count').textContent = heading.value.length;
        document.getElementById('text-count').textContent = text.value.length;
    }
    function showImage(src) {
        img.hidden = !src; empty.hidden = !!src;
        if (src) { img.src = src; media.style.setProperty('--img', 'url("' + src + '")'); }
        else { img.removeAttribute('src'); media.style.setProperty('--img', 'none'); }
    }
    heading.addEventListener('input', update);
    text.addEventListener('input', update);
    file.addEventListener('change', function () {
        var f = file.files[0];
        if (!f) { showImage(remove && remove.checked ? '' : original); return; }
        if (f.size > <?= IMAGE_MAX_BYTES ?>) { file.value = ''; showImage(remove && remove.checked ? '' : original); return; } // message shown by upload_info.php
        var reader = new FileReader();
        reader.onload = function (e) { showImage(e.target.result); };
        reader.readAsDataURL(f);
    });
    if (remove) remove.addEventListener('change', function () { if (!file.files[0]) showImage(remove.checked ? '' : original); });
    update();
})();
</script>
