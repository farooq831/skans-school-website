/* SKANS chatbot - common questions.  Edit in cPanel File Manager, then Save. Every website updates at once.
 *
 * Each question is one { ... } block:
 *   q         the question (shown as a button)
 *   a         the answer. Start a new line with \n
 *   keywords  words parents may type, used by the search box (lowercase)
 *   campus    "all", or one campus id from campuses.js (e.g. "golrasr") to show it only there
 *   topic     optional: adds a button to that page of the chosen campus:
 *             fees, admissions, policy, programs, special, facilities, faculty, news, about, contact, policies
 *   link      optional: a full web address for a button instead of a topic, with "linkText" for its label
 * Only write facts the school has confirmed. Keep the commas between blocks.
 */
window.SKANS_CHAT_FAQ = [
  { "q": "How can I apply for admission?",
    "a": "1. Meet the Principal at the campus.\n2. Pay the registration fee at the admin and accounts office.\n3. Fill in the admission form.\n4. Your child takes the admission test and interview.\n5. The Principal approves the admission.",
    "keywords": ["apply", "admission", "admit", "enrol", "enroll", "join", "register", "registration", "procedure", "process"],
    "campus": "all", "topic": "admissions" },

  { "q": "Which documents are required for admission?",
    "a": "• School leaving certificate and progress report of the previous school\n• Copy of Birth Certificate / NADRA B-Form (mandatory)\n• Copy of father’s CNIC (mandatory)\n• Two passport-size photographs of the child",
    "keywords": ["document", "documents", "papers", "b-form", "bform", "birth certificate", "cnic", "photo", "photographs", "leaving certificate"],
    "campus": "all", "topic": "policy" },

  { "q": "Is there an admission test?",
    "a": "Yes. Admission is given on merit, after the admission test and an interview. The school management decides on every admission.",
    "keywords": ["test", "entry test", "interview", "merit", "exam for admission"],
    "campus": "all", "topic": "admissions" },

  { "q": "What is the minimum age for each class?",
    "a": "Each campus shows its age guide on the Admissions page, class by class.",
    "keywords": ["age", "minimum age", "age limit", "how old", "years old", "playgroup age", "nursery age"],
    "campus": "all", "topic": "admissions" },

  { "q": "What are the fees?",
    "a": "Fees differ by campus and by class. The Fee Structure page shows the registration, admission, security, annual and monthly tuition fees.",
    "keywords": ["fee", "fees", "tuition", "charges", "cost", "price", "how much", "monthly fee", "fee structure"],
    "campus": "all", "topic": "fees" },

  { "q": "What do I pay at the time of admission?",
    "a": "The first payment is made in advance and includes the admission fee, security fee, registration fee and one month’s tuition fee.",
    "keywords": ["first payment", "at admission", "admission fee", "security fee", "registration fee", "advance"],
    "campus": "all", "topic": "fees" },

  { "q": "When is the monthly fee due?",
    "a": "The monthly tuition fee must be paid by the 10th of every month. After that a late fine is charged.\nIf it is still unpaid, notices are sent on the 11th, 20th and 26th, and the student’s name is struck off by the 10th of the following month.",
    "keywords": ["due date", "last date", "10th", "late fee", "fine", "penalty", "fee date", "when to pay"],
    "campus": "all", "topic": "policy" },

  { "q": "How can I pay the fee?",
    "a": "All fees are paid in the bank or online. The school accounts office does not accept cash.",
    "keywords": ["pay", "payment", "bank", "online", "cash", "challan", "deposit"],
    "campus": "all", "topic": "policy" },

  { "q": "Is there an annual fee?",
    "a": "Yes. Annual charges are paid in addition to the monthly tuition fee. Fee increases apply when approved by the school management.",
    "keywords": ["annual", "annual charges", "yearly", "resource charges", "increase"],
    "campus": "all", "topic": "fees" },

  { "q": "Is the security fee refunded?",
    "a": "The security fee is refunded when the student leaves, after all school dues are cleared. Please request the school leaving certificate and refund one month in advance; with shorter notice, one month’s fee is adjusted from the security.",
    "keywords": ["security", "refund", "refundable", "deposit back", "return"],
    "campus": "all", "topic": "policy" },

  { "q": "How do I get a school leaving certificate?",
    "a": "Submit a request to the campus office one month in advance. The certificate is issued after all school dues are cleared.",
    "keywords": ["leaving certificate", "slc", "leave school", "withdraw", "transfer", "migration"],
    "campus": "all", "topic": "policy" },

  { "q": "What is the attendance requirement?",
    "a": "Students need at least 90% attendance to sit the annual exams. A student absent for more than 15 days may have their name struck off.",
    "keywords": ["attendance", "absent", "absence", "leave", "90%", "struck off"],
    "campus": "all", "topic": "policy" },

  { "q": "Which classes does each campus offer?",
    "a": "Each campus has its own classes. Choose a campus to see its programs, or look at the list of campuses.",
    "keywords": ["classes", "class", "grades", "levels", "which class", "programs", "sections"],
    "campus": "all", "topic": "programs" },

  { "q": "Which campus offers O Level, A Level or IGCSE?",
    "a": "Golra Senior Campus offers IGCSE and O / A Level (Class 6 to A Level).\nWestridge Senior Campus offers IGCSE / O Level with Oxford AQA alongside Matric.",
    "keywords": ["o level", "a level", "o-level", "a-level", "igcse", "cambridge", "oxford aqa", "aqa", "international"],
    "campus": "all" },

  { "q": "Which campus offers Intermediate (FSc, ICS, ICOM)?",
    "a": "Golra Senior Campus offers Intermediate (Class 11 and 12): Pre-Medical, Pre-Engineering, ICS, ICOM and F.A, with FBISE.",
    "keywords": ["intermediate", "fsc", "f.sc", "ics", "icom", "i.com", "fa", "pre-medical", "pre medical", "pre-engineering", "hssc", "class 11", "class 12", "college"],
    "campus": "all" },

  { "q": "Which board are Matric and Intermediate affiliated with?",
    "a": "Matric (SSC) and Intermediate (HSSC) are with the Federal Board of Intermediate and Secondary Education (FBISE).",
    "keywords": ["board", "fbise", "federal board", "matric", "ssc", "affiliation", "affiliated"],
    "campus": "all", "topic": "programs" },

  { "q": "What special programs are offered?",
    "a": "SKANS campuses run special programs such as STEM, robotics, coding, competitions and leadership activities. Each campus shows its own programs.",
    "keywords": ["special", "robotics", "stem", "coding", "python", "olympiad", "imo", "competition", "activities", "extra"],
    "campus": "all", "topic": "special" },

  { "q": "What facilities does the campus have?",
    "a": "The Facilities page shows the campus facilities and co-curricular activities.",
    "keywords": ["facilities", "lab", "library", "computer lab", "playground", "smart board", "sports", "taekwondo"],
    "campus": "all", "topic": "facilities" },

  { "q": "Who are the teachers?",
    "a": "The Faculty page shows the leadership team and teachers of the campus.",
    "keywords": ["teacher", "teachers", "faculty", "staff", "principal", "coordinator"],
    "campus": "all", "topic": "faculty" },

  { "q": "What are the latest news and events?",
    "a": "The News & Events page shows the latest announcements, news and events of the campus.",
    "keywords": ["news", "events", "event", "announcement", "announcements", "holiday", "notice", "result"],
    "campus": "all", "topic": "news" },

  { "q": "School timings, transport and uniform",
    "a": "Please contact the campus office for timings, transport and uniform details. They will be happy to help.",
    "keywords": ["timing", "timings", "time", "hours", "transport", "van", "bus", "pick", "drop", "uniform", "dress", "books", "stationery"],
    "campus": "all", "topic": "contact" },

  { "q": "Can I visit the campus?",
    "a": "Yes, you are welcome. Please call or WhatsApp the campus to arrange a visit and a meeting with the Principal.",
    "keywords": ["visit", "tour", "see the school", "meet", "meeting", "appointment"],
    "campus": "all", "topic": "contact" },

  { "q": "Where is the campus and how can I contact it?",
    "a": "The Contact page has the address, map, phone numbers and email of the campus.",
    "keywords": ["address", "location", "where", "map", "contact", "phone", "number", "email", "call", "whatsapp"],
    "campus": "all", "topic": "contact" },

  { "q": "Where can I read the SKANS policies?",
    "a": "The SKANS Policies page has the admission, fee and attendance rules and the harassment policy.",
    "keywords": ["policy", "policies", "rules", "harassment", "code of conduct"],
    "campus": "all", "topic": "policies" },

  { "q": "Are there any job vacancies?",
    "a": "Teachers and staff can send their CV to hr@skans.pk, with the job title in the subject line.",
    "keywords": ["job", "jobs", "vacancy", "vacancies", "career", "careers", "cv", "hiring", "teach", "work"],
    "campus": "all", "link": "mailto:hr@skans.pk", "linkText": "Email hr@skans.pk" }
];
