SKANS CHATBOT (buttons + links + fixed answers, no AI)   - version 3 (October 2026)
====================================================================================
Files (upload all to  public_html/chatbot/  on the main-site cPanel):
  chatbot.js     the chat window (do not edit)
  chatbot.css    its design (navy + gold, waving SKANS button)
  campuses.js    the 9 campuses: name, web address, classes line, phone, WhatsApp   <- you edit
  faq.js         questions, answers, keywords and small talk                         <- you edit
  demo.html      test page (optional)
  .htaccess      makes browsers pick up chatbot updates at once (hidden file: Settings > Show Hidden Files)
The mentor demo (chatbot-demo-for-mentor.html) is NOT needed on the server.

INSTALL ON THE MAIN SITE (skansschool.edu.pk)
  1. File Manager > public_html > + Folder "chatbot" > upload chatbot-upload.zip > Extract > delete the zip.
  2. Test: https://skansschool.edu.pk/chatbot/demo.html  (round SKANS button bottom-left; greeting after 2-3 s)
  3. Back up public_html/index.html (Copy > index.html.bak), Edit index.html, and just BEFORE </body> paste:
       <script src="/chatbot/chatbot.js?v=3" defer></script>
  4. Open https://skansschool.edu.pk > Ctrl+F5 > the SKANS button is at the bottom-left.

ADD TO A CAMPUS WEBSITE (new design: AECHS, PWD, Chakri, Harley, Golra Jr, Golra Sr)
  Edit  includes/footer.php  (backup first) and just BEFORE </body> paste:
       <script src="https://skansschool.edu.pk/chatbot/chatbot.js?v=3" defer></script>
  The campus is recognised from the web address automatically (campuses.js "url").
  New update zips already contain this line (includes/footer.php), so it is kept after updates.
  Old-design campuses (Westridge): paste the same line before </body> in their footer file.

CHANGE QUESTIONS OR NUMBERS
  Edit faq.js or campuses.js in File Manager > Save. Keep the "quotes" and commas.
  Visitors get the edited faq.js / campuses.js within an hour (no need to touch the <script> lines).
  After uploading a new chatbot.js or chatbot.css: test with Ctrl+F5.

HOW THE QUESTIONS WORK (version 3)
  "show": true      the question is listed under the "Common questions" button (keep about 8).
  no "show"         the question is NOT listed, but is found when someone types, e.g. "fee structure",
                    "fees kitni hai", "documents required". Add as many as you like.
  keywords          what parents may type (English and Roman Urdu). One wrong letter in longer words is OK.
  only "main"       questions comparing campuses: main site only.
  only "campus"     questions for a campus website; "per" gives each campus its own answer.
  {campus} {classes} {phone} in an answer are filled in from campuses.js.
  SMALL TALK (bottom of faq.js): hello, how are you, who are you, thanks, bye (also Roman Urdu).
  On a campus website the chat only talks about that campus. If someone types another campus,
  it gives a link to that campus's own website (no details). The main site can still switch campus.
  Mistake = chat button disappears (never a broken page): undo your last edit.

REAL FEES IN THE CHAT ("class 5 fee", "5 class ki fee", "10th class ki fees", "kg fees")
  On a campus website the chat reads the amounts from that campus's own Fee Structure page
  (Admin > Settings > Fee structure), so they always match the website. Nothing to upload on the campus.
  Optional: chat-fees.php in a campus folder (next to index.php) is used first if present, and also lets
  the chat on the MAIN site show amounts (the main site cannot read another site's page).
  If the amounts cannot be read, the chat shows the Fee Structure link instead (no error).

POSITION AND BEHAVIOUR
  Bottom-left by default (the campus sites have WhatsApp bottom-right).
  For bottom-right add  data-position="right"  to the <script> line.
  The "Hi there!" greeting shows once per visit, 2-3 seconds after the page opens.
