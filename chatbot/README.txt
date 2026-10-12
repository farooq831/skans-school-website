SKANS CHATBOT (buttons + links + fixed answers, no AI)   - version 2 (October 2026)
====================================================================================
Files (upload all to  public_html/chatbot/  on the main-site cPanel):
  chatbot.js     the chat window (do not edit)
  chatbot.css    its design (navy + gold, waving SKANS button)
  campuses.js    the 9 campuses: name, web address, classes line, phone, WhatsApp   <- you edit
  faq.js         the common questions and answers                                    <- you edit
  demo.html      test page (optional)
The mentor demo (chatbot-demo-for-mentor.html) is NOT needed on the server.

INSTALL ON THE MAIN SITE (skansschool.edu.pk)
  1. File Manager > public_html > + Folder "chatbot" > upload chatbot-upload.zip > Extract > delete the zip.
  2. Test: https://skansschool.edu.pk/chatbot/demo.html  (round SKANS button bottom-left; greeting after 2-3 s)
  3. Back up public_html/index.html (Copy > index.html.bak), Edit index.html, and just BEFORE </body> paste:
       <script src="/chatbot/chatbot.js?v=2" defer></script>
  4. Open https://skansschool.edu.pk > Ctrl+F5 > the SKANS button is at the bottom-left.

ADD TO A CAMPUS WEBSITE (new design: AECHS, PWD, Chakri, Harley, Golra Jr, Golra Sr)
  Edit  includes/footer.php  (backup first) and just BEFORE </body> paste:
       <script src="https://skansschool.edu.pk/chatbot/chatbot.js?v=2" defer></script>
  The campus is recognised from the web address automatically (campuses.js "url").
  New update zips already contain this line (includes/footer.php), so it is kept after updates.
  Old-design campuses (Westridge): paste the same line before </body> in their footer file.

CHANGE QUESTIONS OR NUMBERS
  Edit faq.js or campuses.js in File Manager > Save. Keep the "quotes" and commas.
  Browsers may keep the old file for a few hours: to force it, change ?v=2 to ?v=3 in the <script> lines.
  Mistake = chat button disappears (never a broken page): undo your last edit.

POSITION AND BEHAVIOUR
  Bottom-left by default (the campus sites have WhatsApp bottom-right).
  For bottom-right add  data-position="right"  to the <script> line.
  The "Hi there!" greeting shows once per visit, 2-3 seconds after the page opens.
