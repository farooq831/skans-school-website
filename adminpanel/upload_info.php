<?php
// adminpanel/upload_info.php : under every picture field in the admin panel, shows the size limit,
// and after a file is chosen its name, file size and pixel size. Files over 2 MB (IMAGE_MAX_BYTES)
// are refused straight away. Included once by nav.php. The server checks the size again on saving.
?>
<style>
    .adm-upinfo { display: block; margin-top: .4rem; padding: .45rem .7rem; border-radius: 8px; font-size: .86rem; line-height: 1.4; background: #F4F1EA; color: #3B4558; border-left: 3px solid #BD852C; }
    .adm-upinfo strong { color: #192A4A; }
    .adm-upinfo.is-ok { background: #EEF7F0; border-left-color: #1F6B3A; color: #174F2B; }
    .adm-upinfo.is-ok strong { color: #174F2B; }
    .adm-upinfo.is-error { background: #FFF4F4; border-left-color: #A12A2A; color: #7E1F1F; }
    .adm-upinfo.is-error strong { color: #7E1F1F; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var MAX = <?= (int) IMAGE_MAX_BYTES ?>, MAX_LABEL = <?= json_encode(IMAGE_MAX_LABEL) ?>;
    function size(bytes) { return bytes >= 1048576 ? (bytes / 1048576).toFixed(2) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB'; }
    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

    document.querySelectorAll('input[type="file"]').forEach(function (input) {
        var info = document.createElement('small');
        info.className = 'adm-upinfo';
        info.setAttribute('aria-live', 'polite');
        var hint = 'Maximum size <strong>' + MAX_LABEL + '</strong> · JPG, PNG or GIF. Large phone photos may need to be resized first.';
        info.innerHTML = hint;
        input.insertAdjacentElement('afterend', info);

        input.addEventListener('change', function () {
            var f = input.files && input.files[0];
            info.className = 'adm-upinfo';
            if (!f) { info.innerHTML = hint; return; }
            if (f.size > MAX) {
                info.className = 'adm-upinfo is-error';
                info.innerHTML = '<strong>Too large:</strong> ' + esc(f.name) + ' is ' + size(f.size) + '. Maximum is ' + MAX_LABEL + '. Please choose a smaller picture (or resize it).';
                input.value = '';
                return;
            }
            if (!/^image\//.test(f.type)) {
                info.className = 'adm-upinfo is-error';
                info.innerHTML = '<strong>Not a picture:</strong> ' + esc(f.name) + '. Please choose a JPG, PNG or GIF file.';
                input.value = '';
                return;
            }
            info.className = 'adm-upinfo is-ok';
            info.innerHTML = '<strong>' + esc(f.name) + '</strong> · ' + size(f.size) + ' · reading size…';
            var url = URL.createObjectURL(f), img = new Image();
            img.onload = function () {
                info.innerHTML = '<strong>' + esc(f.name) + '</strong> · ' + size(f.size) + ' · ' + img.naturalWidth + ' × ' + img.naturalHeight + ' px';
                URL.revokeObjectURL(url);
            };
            img.onerror = function () {
                info.innerHTML = '<strong>' + esc(f.name) + '</strong> · ' + size(f.size);
                URL.revokeObjectURL(url);
            };
            img.src = url;
        });
    });
});
</script>
