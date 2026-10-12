SKANS CHATBOT (buttons + links + fixed answers, no AI)
======================================================
Files (upload all to  public_html/chatbot/  on the main-site cPanel):
  chatbot.js     the chat window (do not edit)
  chatbot.css    its design (navy + gold)
  campuses.js    the 9 campuses: name, web address, classes line, phone, WhatsApp   <- you edit
  faq.js         the common questions and answers                                    <- you edit
  demo.html      test page (optional)

INSTALL ON THE MAIN SITE (skansschool.edu.pk)
  1. File Manager > public_html > + Folder "chatbot" > upload the files above.
  2. Test: https://skansschool.edu.pk/chatbot/demo.html  (Help button bottom-left)
  3. Back up public_html/index.html (Copy > index.html.bak), Edit index.html, and just BEFORE </body> paste:
       <script src="/chatbot/chatbot.js?v=1" defer></script>
  4. Open https://skansschool.edu.pk > Ctrl+F5 > Help button bottom-left.

ADD TO A CAMPUS WEBSITE
  Edit  includes/footer.php  (backup first) and just BEFORE </body> paste:
       <script src="https://skansschool.edu.pk/chatbot/chatbot.js?v=1" defer></script>
  The campus is recognised from the web address automatically (campuses.js "url").
  Old-design campuses (Westridge): paste the same line before </body> in their footer file.

CHANGE QUESTIONS OR NUMBERS
  Edit faq.js or campuses.js in File Manager > Save. Keep the "quotes" and commas.
  Then in the <script> line change ?v=1 to ?v=2 (only on the main site's index.html is enough for
  testing; browsers otherwise may keep the old file for a few hours).
  Mistake = chat button disappears (never a broken page): undo your last edit.

POSITION
  Bottom-left by default (the campus sites have WhatsApp bottom-right).
  For bottom-right add  data-position="right"  to the <script> line.
