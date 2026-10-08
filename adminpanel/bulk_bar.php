<?php
// adminpanel/bulk_bar.php : "select all / delete selected" bar for list pages.
// Before including set $bulk_type ('news' or 'activities'). Rows need: <input type="checkbox" class="adm-check" name="ids[]" value="ID" form="bulk-form">
?>
<form id="bulk-form" method="POST" action="bulk_delete.php"
      onsubmit="var n = document.querySelectorAll('input[name=&quot;ids[]&quot;]:checked').length; return n > 0 && confirm('Delete ' + n + ' selected item(s)? This cannot be undone.');">
    <input type="hidden" name="type" value="<?= h($bulk_type) ?>">
    <input type="hidden" name="csrf_token" value="<?= h(admin_csrf_token()) ?>">
    <div class="adm-bulk">
        <label class="mb-0 d-flex align-items-center" style="gap:.5rem; cursor:pointer">
            <input type="checkbox" class="adm-check" id="bulk-all"> <strong>Select all</strong>
        </label>
        <span class="adm-count" id="bulk-count">0 selected</span>
        <button type="submit" class="btn btn-danger btn-sm ml-auto" id="bulk-delete" disabled><i class="fas fa-trash"></i> Delete selected</button>
    </div>
</form>
<script>
(function () {
    var all = document.getElementById('bulk-all'), btn = document.getElementById('bulk-delete'), count = document.getElementById('bulk-count');
    function boxes() { return document.querySelectorAll('input[name="ids[]"]'); }
    function update() {
        var n = document.querySelectorAll('input[name="ids[]"]:checked').length, total = boxes().length;
        count.textContent = n + ' selected';
        btn.disabled = n === 0;
        btn.innerHTML = '<i class="fas fa-trash"></i> Delete selected' + (n ? ' (' + n + ')' : '');
        all.checked = n > 0 && n === total;
        all.indeterminate = n > 0 && n < total;
    }
    all.addEventListener('change', function () { boxes().forEach(function (b) { b.checked = all.checked; }); update(); });
    document.addEventListener('change', function (e) { if (e.target.name === 'ids[]') update(); });
    update();
})();
</script>
