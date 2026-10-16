/* SKANS chatbot - questions and answers.  Edit in cPanel File Manager, then Save. Every website updates within an hour.
 *
 * Each question is one { ... } block:
 *   q         the question (shown as a button)
 *   a         the answer. Start a new line with \n
 *             {campus} {classes} {phone} are filled in with the campus name, classes line and phone (campuses.js)
 *   keywords  words parents may type, used by the search box (lowercase; English and Roman Urdu)
 *   show      true = also listed under the "Common questions" button. Keep this list SHORT (about 8).
 *             Without "show", a question is only found by typing in the search box.
 *   campus    "all", or one campus id from campuses.js (e.g. "golrasr") to use it only there
 *   only      "main"   = only on the main site (questions that compare campuses)
 *             "campus" = only on a campus website
 *   per       a different answer per campus: { "golrasr": "...", "*": "answer for every other campus" }
 *   topic     optional: adds a button to that page of the chosen campus:
 *             fees, admissions, policy, programs, special, facilities, faculty, news, about, contact, policies
 *   link      optional: a full web address for a button instead of a topic, with "linkText" for its label
 * Only write facts the school has confirmed. Keep the commas between blocks.
 */
window.SKANS_CHAT_FAQ = [

  /* ===== COMMON QUESTIONS (show: true) ===== */
  { "q": "How can I apply for admission?", "show": true,
    "a": "1. Meet the Principal at the campus.\n2. Pay the registration fee at the admin and accounts office.\n3. Fill in the admission form.\n4. Your child takes the admission test and interview.\n5. The Principal approves the admission.",
    "keywords": ["apply", "admission", "admissions", "admit", "enrol", "enroll", "enrolment", "join", "register", "registration", "procedure", "process", "how to apply", "dakhla", "dakhila", "dakhla kaise", "admission kaise"],
    "campus": "all", "topic": "admissions" },

  { "q": "Which documents are required for admission?", "show": true,
    "a": "• School leaving certificate and progress report of the previous school\n• Copy of Birth Certificate / NADRA B-Form (mandatory)\n• Copy of father’s CNIC (mandatory)\n• Two passport-size photographs of the child",
    "keywords": ["document", "documents", "papers", "required", "requirements", "b-form", "bform", "b form", "birth certificate", "cnic", "id card", "photo", "photos", "photographs", "pictures", "leaving certificate", "kagzat", "kaghzat"],
    "campus": "all", "topic": "policy" },

  { "q": "What is the minimum age for each class?", "show": true,
    "a": "The Admissions page of {campus} shows the minimum age for every class.",
    "keywords": ["age", "ages", "minimum age", "age limit", "how old", "years old", "age criteria", "playgroup age", "nursery age", "umar", "umer", "kitni umar"],
    "campus": "all", "topic": "admissions" },

  { "q": "What are the fees?", "show": true,
    "a": "Fees differ by class. The Fee Structure page of {campus} shows the registration, admission, security, annual and monthly tuition fees for every class.",
    "keywords": ["fee", "fees", "fee structure", "fees structure", "fee schedule", "tuition", "tuition fee", "charges", "cost", "price", "how much", "monthly fee", "fess", "feez", "fess structure", "kitni fees", "fees kitni", "fees kya", "kharcha", "paisay"],
    "campus": "all", "topic": "fees" },

  { "q": "When is the monthly fee due?", "show": true,
    "a": "The monthly tuition fee must be paid by the 10th of every month. After that a late fine is charged.\nIf it is still unpaid, notices are sent on the 11th, 20th and 26th, and the student’s name is struck off by the 10th of the following month.",
    "keywords": ["due date", "last date", "10th", "late fee", "late fine", "fine", "penalty", "fee date", "when to pay", "fees kab", "kab tak"],
    "campus": "all", "topic": "policy" },

  { "q": "School timings, transport and uniform", "show": true,
    "a": "Please contact the {campus} office for school timings, transport and uniform details. They will be happy to help.",
    "keywords": ["timing", "timings", "time", "hours", "school time", "start time", "off time", "transport", "van", "bus", "pick", "drop", "pick and drop", "uniform", "dress", "shoes", "waqt", "time kya"],
    "campus": "all", "topic": "contact" },

  { "q": "Where is the campus and how can I contact it?", "show": true,
    "a": "The Contact page has the address, map, phone numbers and email of {campus}. You can also call {phone}.",
    "keywords": ["address", "location", "located", "where", "map", "directions", "contact", "phone", "number", "phone number", "email", "call", "whatsapp", "kahan", "pata"],
    "campus": "all", "topic": "contact" },

  { "q": "Can I visit the campus?", "show": true,
    "a": "Yes, you are welcome. Please call or WhatsApp {campus} to arrange a visit and a meeting with the Principal.",
    "keywords": ["visit", "tour", "see the school", "come to school", "meet", "meeting", "appointment"],
    "campus": "all", "topic": "contact" },

  { "q": "Which campus offers O Level, A Level or IGCSE?", "show": true, "only": "main",
    "a": "Golra Senior Campus offers IGCSE and O / A Level (Class 6 to A Level).\nWestridge Senior Campus offers IGCSE / O Level with Oxford AQA alongside Matric.",
    "keywords": ["o level", "a level", "o-level", "a-level", "olevel", "alevel", "o levels", "igcse", "cambridge", "oxford aqa", "aqa", "international"],
    "campus": "all" },

  /* ===== FOUND BY SEARCH ONLY ===== */
  { "q": "Do you offer O Level, A Level or IGCSE?", "only": "campus",
    "per": {
      "golrasr": "Yes. Golra Senior Campus offers IGCSE and O / A Level, alongside Matric and Intermediate with FBISE.",
      "westridgesr": "Yes. Westridge Senior Campus offers IGCSE / O Level with Oxford AQA (UK board), alongside Matric with FBISE.",
      "*": "{campus} offers {classes}. For O Level, A Level or IGCSE, please ask the campus office for guidance." },
    "a": "",
    "keywords": ["o level", "a level", "o-level", "a-level", "olevel", "alevel", "o levels", "igcse", "cambridge", "oxford aqa", "aqa", "international"],
    "campus": "all", "topic": "programs" },

  { "q": "Which campus offers Intermediate (FSc, ICS, ICOM)?", "only": "main",
    "a": "Golra Senior Campus offers Intermediate (Class 11 and 12): Pre-Medical, Pre-Engineering, ICS, ICOM and F.A, with FBISE.",
    "keywords": ["intermediate", "fsc", "f.sc", "ics", "icom", "i.com", "fa", "pre-medical", "pre medical", "pre-engineering", "pre engineering", "hssc", "class 11", "class 12", "college", "first year", "second year"],
    "campus": "all" },

  { "q": "Do you offer Intermediate (FSc, ICS, ICOM)?", "only": "campus",
    "per": {
      "golrasr": "Yes. Golra Senior Campus offers Intermediate (Class 11 and 12): Pre-Medical, Pre-Engineering, ICS, ICOM and F.A, with FBISE.",
      "*": "{campus} offers {classes}. For Intermediate (Class 11 and 12), please ask the campus office for guidance." },
    "a": "",
    "keywords": ["intermediate", "fsc", "f.sc", "ics", "icom", "i.com", "fa", "pre-medical", "pre medical", "pre-engineering", "pre engineering", "hssc", "class 11", "class 12", "college", "first year", "second year"],
    "campus": "all", "topic": "programs" },

  { "q": "Which classes does the campus offer?",
    "a": "Each campus has its own classes. Choose a campus to see its programs, or look at the list of campuses.",
    "per": { "*": "{campus} offers {classes}. The Programs page shows each level and what students learn there." },
    "keywords": ["classes", "class", "grades", "grade", "levels", "which class", "programs", "programmes", "sections", "playgroup", "nursery", "kg", "kindergarten", "matric"],
    "campus": "all", "topic": "programs" },

  { "q": "Is there an admission test?",
    "a": "Yes. Admission is given on merit, after the admission test and an interview. The school management decides on every admission.",
    "keywords": ["test", "entry test", "admission test", "interview", "merit", "exam for admission", "test kaisa"],
    "campus": "all", "topic": "admissions" },

  { "q": "When are admissions open?",
    "a": "The Admissions page explains the admission process. To check seats in a class right now, please call or WhatsApp {campus}.",
    "keywords": ["admission open", "admissions open", "open admission", "seat", "seats", "seat available", "available seats", "new session", "session start", "mid session", "when admission", "last date for admission"],
    "campus": "all", "topic": "admissions" },

  { "q": "How do I get the admission form?",
    "a": "The campus office gives you the admission form after your meeting with the Principal and the registration. The Admissions page explains every step.",
    "keywords": ["form", "admission form", "online admission", "apply online", "online form", "download form", "registration form"],
    "campus": "all", "topic": "admissions" },

  { "q": "Can my child join from another school?",
    "a": "Yes. Bring the school leaving certificate and the progress report of the previous school. Admission is on merit, after the admission test and an interview.",
    "keywords": ["another school", "other school", "previous school", "change school", "shift school", "switch school", "new student", "join mid"],
    "campus": "all", "topic": "admissions" },

  { "q": "What do I pay at the time of admission?",
    "a": "The first payment is made in advance and includes the admission fee, security fee, registration fee and one month’s tuition fee.",
    "keywords": ["first payment", "at admission", "admission fee", "security fee", "registration fee", "advance", "total fee", "total amount"],
    "campus": "all", "topic": "fees" },

  { "q": "How can I pay the fee?",
    "a": "All fees are paid in the bank or online. The school accounts office does not accept cash.",
    "keywords": ["pay", "payment", "bank", "online", "online fee", "cash", "challan", "voucher", "fee voucher", "fee card", "deposit", "jazzcash", "easypaisa"],
    "campus": "all", "topic": "policy" },

  { "q": "Is there an annual fee?",
    "a": "Yes. Annual charges are paid in addition to the monthly tuition fee. Fee increases apply when approved by the school management.",
    "keywords": ["annual", "annual charges", "annual fee", "yearly", "resource charges", "increase", "fee increase"],
    "campus": "all", "topic": "fees" },

  { "q": "Is the security fee refunded?",
    "a": "The security fee is refunded when the student leaves, after all school dues are cleared. Please request the school leaving certificate and refund one month in advance; with shorter notice, one month’s fee is adjusted from the security.",
    "keywords": ["security", "refund", "refundable", "deposit back", "return", "money back", "wapis"],
    "campus": "all", "topic": "policy" },

  { "q": "Are there scholarships or discounts?",
    "a": "For scholarships, sibling discounts or fee concessions, please speak to the {campus} office. They will explain what is available.",
    "keywords": ["scholarship", "scholarships", "discount", "discounts", "concession", "sibling", "siblings", "brother", "sister", "rebate", "installment", "instalment", "kam fees", "fee kam"],
    "campus": "all", "topic": "contact" },

  { "q": "How do I get a school leaving certificate?",
    "a": "Submit a request to the campus office one month in advance. The certificate is issued after all school dues are cleared.",
    "keywords": ["leaving certificate", "slc", "leave school", "withdraw", "withdrawal", "transfer", "migration", "character certificate"],
    "campus": "all", "topic": "policy" },

  { "q": "What is the attendance requirement?",
    "a": "Students need at least 90% attendance to sit the annual exams. A student absent for more than 15 days may have their name struck off.",
    "keywords": ["attendance", "absent", "absence", "leave", "sick leave", "90%", "struck off", "chutti leni"],
    "campus": "all", "topic": "policy" },

  { "q": "Which board are Matric and Intermediate affiliated with?",
    "a": "Matric (SSC) and Intermediate (HSSC) are with the Federal Board of Intermediate and Secondary Education (FBISE).",
    "keywords": ["board", "fbise", "federal board", "matric", "ssc", "affiliation", "affiliated", "registered"],
    "campus": "all", "topic": "programs" },

  { "q": "What will my child study? (syllabus and books)",
    "a": "The Programs page explains what each level studies. For the syllabus and book list of a class, please ask the {campus} office.",
    "keywords": ["syllabus", "curriculum", "books", "book list", "course", "courses", "subjects", "textbook", "textbooks", "stationery", "kitabain"],
    "campus": "all", "topic": "programs" },

  { "q": "Is Islamic education part of the school?",
    "a": "SKANS brings together modern education and Islamic values, and Islamiat is part of the curriculum. For Quran classes at {campus}, see the Programs and Facilities pages or ask the office.",
    "keywords": ["quran", "islamic", "islam", "islamiat", "islamiyat", "nazra", "hifz", "namaz", "prayer", "deen", "religious", "religion", "arabic"],
    "campus": "all", "topic": "programs" },

  { "q": "What special programs are offered?",
    "a": "SKANS campuses run special programs such as STEM, robotics, coding, competitions and leadership activities. The Special Programs page shows what {campus} offers.",
    "keywords": ["special", "special programs", "robotics", "stem", "coding", "python", "scratch", "olympiad", "imo", "competition", "competitions", "activities", "extra", "co-curricular", "clubs"],
    "campus": "all", "topic": "special" },

  { "q": "What facilities does the campus have?",
    "a": "The Facilities page shows the facilities and co-curricular activities of {campus}.",
    "keywords": ["facilities", "facility", "lab", "labs", "library", "computer lab", "science lab", "playground", "smart board", "smart class", "sports", "taekwondo", "ground"],
    "campus": "all", "topic": "facilities" },

  { "q": "Who are the teachers?",
    "a": "The Faculty page shows the leadership team and teachers of {campus}.",
    "keywords": ["teacher", "teachers", "faculty", "staff", "coordinator", "qualified", "ustad", "ustani"],
    "campus": "all", "topic": "faculty" },

  { "q": "How can I meet the Principal?",
    "a": "You can meet the Principal by appointment: please call or WhatsApp the {campus} office. The Principal’s message is on the About page.",
    "keywords": ["principal", "head", "headmistress", "headmaster", "vice principal", "meet principal", "management"],
    "campus": "all", "topic": "about" },

  { "q": "What are the latest news and events?",
    "a": "The News & Events page shows the latest announcements, news and events of {campus}.",
    "keywords": ["news", "events", "event", "announcement", "announcements", "notice", "notices", "latest", "update", "updates"],
    "campus": "all", "topic": "news" },

  { "q": "When are the holidays?",
    "a": "Holidays and vacation dates are announced by the campus. Please see the News & Events page or ask the {campus} office.",
    "keywords": ["holiday", "holidays", "vacation", "vacations", "summer vacation", "winter vacation", "break", "closed", "chutti", "chuttian", "chuttiyan", "chhutti"],
    "campus": "all", "topic": "news" },

  { "q": "Exams, results and report cards",
    "a": "Exam dates, results and report cards are shared by the campus. Please see the News & Events page or ask the {campus} office.",
    "keywords": ["exam", "exams", "examination", "result", "results", "report card", "date sheet", "datesheet", "paper", "papers schedule", "ptm", "parent teacher", "marks"],
    "campus": "all", "topic": "news" },

  { "q": "Where can I read the SKANS policies?",
    "a": "The SKANS Policies page has the admission, fee and attendance rules and the harassment policy.",
    "keywords": ["policy", "policies", "rules", "regulations", "harassment", "code of conduct", "discipline"],
    "campus": "all", "topic": "policies" },

  { "q": "How can I give feedback or make a complaint?",
    "a": "We value your feedback. Please contact the {campus} office, or send a message from the Contact page; it reaches the campus directly.",
    "keywords": ["complaint", "complain", "feedback", "suggestion", "suggestions", "problem", "issue", "concern", "shikayat"],
    "campus": "all", "topic": "contact" },

  { "q": "Canteen, lunch, safety and other campus questions",
    "a": "Please ask the {campus} office about this. They will be happy to help.",
    "keywords": ["canteen", "lunch", "tiffin", "food", "summer camp", "hostel", "boarding", "air conditioning", "ac", "medical", "nurse", "first aid", "cctv", "safety", "safe", "guard"],
    "campus": "all", "topic": "contact" },

  { "q": "Are there any job vacancies?",
    "a": "Teachers and staff can send their CV to hr@skans.pk, with the job title in the subject line.",
    "keywords": ["job", "jobs", "vacancy", "vacancies", "career", "careers", "cv", "resume", "hiring", "teach", "teaching job", "work", "naukri"],
    "campus": "all", "link": "mailto:hr@skans.pk", "linkText": "Email hr@skans.pk" }
];

/* SMALL TALK - friendly replies (never listed as buttons). "pre" is said first when the message also asks
 * a real question, e.g. "hello, what are the fees?". "help": true adds the WhatsApp / Call buttons. */
window.SKANS_CHAT_SMALLTALK = [
  { "q": "Hello", "pre": "Hello! 👋",
    "a": "Hello and Assalam o Alaikum! 👋 Welcome to {campus}. Ask me about admissions, fees, classes, timings or contact details, or pick a topic below.",
    "keywords": ["hi", "hii", "hello", "helo", "hey", "salam", "salaam", "assalam", "asalam", "aoa", "assalamualaikum", "assalam o alaikum", "asalam o alaikum", "assalam-o-alaikum", "good morning", "good afternoon", "good evening"] },

  { "q": "How are you?", "pre": "I’m doing well, thank you! 😊",
    "a": "I’m doing well, thank you for asking! 😊 How can I help you today?",
    "keywords": ["how are you", "how r u", "how are u", "how r you", "hows it going", "whats up", "kaise ho", "kaisay ho", "kesay ho", "kese ho", "kya haal", "kia haal", "kya hal", "theek ho"] },

  { "q": "Who are you?", "help": true,
    "a": "I’m the SKANS help assistant. I give quick answers from our school websites. To talk to a person, call or WhatsApp the {campus} office:",
    "keywords": ["who are you", "your name", "are you a bot", "are you human", "robot", "bot", "real person", "talk to human", "talk to someone", "talk to a person", "agent", "representative"] },

  { "q": "What can you do?",
    "a": "I can help with admissions, documents, ages, fees and fee rules, classes and programs, facilities, teachers, news and holidays, and the address and phone of {campus}. Type your question or pick a topic.",
    "keywords": ["what can you do", "help", "help me", "options", "what can i ask", "guide me", "madad"] },

  { "q": "Thank you",
    "a": "You’re welcome! 😊 Is there anything else I can help you with?",
    "keywords": ["thanks", "thank you", "thankyou", "thank u", "thx", "ty", "shukriya", "shukria", "jazakallah", "jazak allah", "ok", "okay", "great", "nice", "good", "perfect", "got it"] },

  { "q": "Goodbye",
    "a": "Allah Hafiz! 👋 Thank you for visiting {campus}. We hope to see you soon.",
    "keywords": ["bye", "goodbye", "good bye", "allah hafiz", "khuda hafiz", "see you", "take care", "tc"] }
];
