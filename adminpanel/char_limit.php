<?php
// adminpanel/char_limit.php : the 500-character limit for descriptions (news, events,
// activities, announcements). Include near the end of a page that has a description box.
// Every <textarea maxlength="..."> on the page gets a live "123 / 500" counter.
?>
<script>
document.querySelectorAll('textarea[maxlength]').forEach(function (box) {
    var max = parseInt(box.getAttribute('maxlength'), 10);
    var counter = document.createElement('small');
    counter.className = 'form-text text-right';
    box.insertAdjacentElement('afterend', counter);
    function update() {
        var n = box.value.length;
        counter.textContent = n + ' / ' + max + ' characters' + (n > max ? ' - please shorten the text' : '');
        counter.style.color = n > max ? '#A12A2A' : (n > max - 50 ? '#8A5F1C' : '#5B6475');
    }
    box.addEventListener('input', update);
    update();
});
</script>
