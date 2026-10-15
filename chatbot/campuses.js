/* SKANS chatbot - the campuses.  Edit in cPanel File Manager, then Save. Every website updates at once.
 *
 * Keep the format: each campus is one { ... } block, text inside "double quotes", a comma between blocks.
 *   id        short name, lowercase (do not change once used)
 *   name      shown on the buttons
 *   url       the campus website, ending with /
 *   classes   short line under the name ("" = not shown)
 *   phone     shown on the Call button ("" = no Call button)        tel: digits for dialling, with +92
 *   whatsapp  digits only, with 92 ("" = no WhatsApp button)
 *   pages     only for campuses still on the OLD website design: which old page each topic opens
 */
window.SKANS_CHAT_CAMPUSES = [
  { "id": "aechs",  "name": "AECHS Campus",            "url": "https://aechscampus.skansschool.edu.pk/",
    "classes": "Playgroup – Matric",   "phone": "+92 51 8468475",  "tel": "+92518468475",  "whatsapp": "923335352965" },

  { "id": "pwd",    "name": "PWD Campus",              "url": "https://pwdcampus.skansschool.edu.pk/",
    "classes": "Playgroup – Class 8",  "phone": "+92 51 8773775",  "tel": "+92518773775",  "whatsapp": "923315354843" },

  { "id": "chakri", "name": "Chakri Road Campus",      "url": "https://chakricampus.skansschool.edu.pk/",
    "classes": "",                     "phone": "+92 51 5575082",  "tel": "+92515575082",  "whatsapp": "923359483369" },

  { "id": "harley", "name": "Harley Street Campus",    "url": "https://hscampus.skansschool.edu.pk/",
    "classes": "Playgroup – Matric",   "phone": "+92 51 5176066",  "tel": "+92515176066",  "whatsapp": "923328922225" },

  { "id": "golra",  "name": "Golra Junior Campus",     "url": "https://golrajuniorcampus.skansschool.edu.pk/",
    "classes": "Playgroup – Class 5",  "phone": "+92 51 2227475",  "tel": "+92512227475",  "whatsapp": "923205005858" },

  { "id": "golrasr","name": "Golra Senior Campus",     "url": "https://golraseniorcampus.skansschool.edu.pk/",
    "classes": "Class 6 – A Level",    "phone": "+92 51 2229454",  "tel": "+92512229454",  "whatsapp": "923467845010" },

  { "id": "westridgesr", "name": "Westridge Senior Campus", "url": "https://westridgeseniorcampus.skansschool.edu.pk/",
    "classes": "Class 3 – O Level", "phone": "+92 51 8355546", "tel": "+92518355546", "whatsapp": "923345359458" },

  { "id": "westridgejr", "name": "Westridge Junior Campus", "url": "https://westridgejuniorcampus.skansschool.edu.pk/",
    "classes": "Grade 1 – Grade 2",    "phone": "+92 51 8773091",  "tel": "+92518773091",  "whatsapp": "923005070778" },

  { "id": "westridgeeye", "name": "Westridge EYE Campus", "url": "https://westridgeeyecampus.skansschool.edu.pk/",
    "classes": "Playgroup – Kindergarten", "phone": "+92 51 8890534", "tel": "+92518890534", "whatsapp": "923140512814" }
];
