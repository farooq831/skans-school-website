<?php
// data/fees.php : fee structure, admission policy and admission forms.
// Edit only this file to change the Fee Structure, Admission Policy and Admission Form pages.
// Amounts are whole rupees without commas (e.g. 5000). null = not charged (shown as "–").
// '[CONTENT NEEDED]' shows a visible marker on the site.
//
// Sources: fee table supplied by SKANS on 3 Oct 2026; tuition 16,000/month and lab charges
// per month confirmed on 3 Oct 2026; rules copied from "Admission Form Pg-G-7 (updated 10th June 2026)".

$standard = ['registration' => 5000, 'admission' => 20000, 'security' => 20000, 'annual' => 10000, 'tuition' => 16000, 'lab' => null];

return [
    'session'  => '2026–2027',

    // Website switches (3 Oct 2026). Data below stays in this file; set to true to show it again.
    'forms_public'       => false, // admission form downloads (Admission Form page, menu and buttons)
    'readmission_public' => false, // re-admission rules and fee
    'currency' => 'Rs.',

    // Column headings, in display order. 'period' is shown under the heading.
    'columns' => [
        'registration' => ['label' => 'Registration Fee', 'period' => 'one time'],
        'admission'    => ['label' => 'Admission Fee',    'period' => 'one time'],
        'security'     => ['label' => 'Security Fee',     'period' => 'refundable, see policy'],
        'annual'       => ['label' => 'Annual Charges',   'period' => 'per year'],
        'tuition'      => ['label' => 'Tuition Fee',      'period' => 'per month'],
        'lab'          => ['label' => 'Lab Charges',      'period' => 'per month'],
    ],

    'classes' => [
        ['class' => 'Playgroup']    + $standard,
        ['class' => 'Nursery']      + $standard,
        ['class' => 'Kindergarten'] + $standard,
        ['class' => 'Class 1']      + $standard,
        ['class' => 'Class 2']      + $standard,
        ['class' => 'Class 3']      + $standard,
        ['class' => 'Class 4']      + $standard,
        ['class' => 'Class 5']      + $standard,
        ['class' => 'Class 6']      + $standard,
        ['class' => 'Class 7']      + $standard,
        ['class' => 'Pre 9']        + $standard,
        ['class' => 'Class 9',  'lab' => 700] + $standard,
        ['class' => 'Class 10', 'lab' => 700] + $standard,
    ],

    'readmission_fee' => 30000,

    // Fee rules from the admission form (grammar lightly corrected, meaning unchanged).
    'fee_rules' => [
        'School monthly tuition fee is charged on a monthly basis in advance, except at the time of admission.',
        'An annual fee will be charged in addition to the tuition fee.',
        'Increase in fee will be applicable as and when approved by school management.',
        'All school fees must be deposited in the bank or online. No cash payment will be accepted by the school accounts office.',
        'First payment, which includes admission fee, security fee, registration fee and one month’s tuition fee, must be paid in advance.',
        'Monthly tuition fee must be paid by the 10th of every month. After the due date, a fine will be charged as per policy.',
        'In case of non-payment of fee by the 10th of the month, the first notice will be sent on the 11th, the second on the 20th and the final notice will be sent home on the 26th of each month along with the late fee penalty. The student’s name will be struck off from school data by the 10th of the following month.',
        'It is necessary for all students to clear the dues of the session before receiving the Roll Number slip of SSC-II/HSSC.',
    ],

    'admission_policy' => [
        'general' => [
            'Rights of granting admission are reserved with the school management.',
            'Merit is the sole criterion for admission.',
            'Admission is granted after passing the Admission Test and interview.',
        ],
        'documents' => [
            'School leaving certificate and progress report of the previous school',
            'Copy of Birth Certificate / NADRA Registration Form (B-Form) – mandatory',
            'Copy of father’s CNIC – mandatory',
            'Two passport-size photographs of the child',
        ],
        'attendance' => [
            'Students are required to maintain a minimum of 90% attendance to appear in the annual exams.',
            'A student’s name will be struck off from school if the student remains absent for more than 15 days.',
        ],
        'readmission' => [
            'intro' => 'The school reserves the right to grant re-admission, subject to the payment of:',
            'items' => ['All past outstanding fee', 'Full security fee', 'Re-admission fee of Rs. 30,000'],
            'note'  => 'The tuition fee prevailing at the time of re-admission will be applicable.',
        ],
        'leaving' => [
            'School leaving certificates will be issued subject to the clearance of all school dues.',
            'For a school leaving certificate and security refund, a request must be submitted one month in advance. Where less than one month’s notice is served, a full month’s fee will be applicable (once a fee challan is issued) and the security deposit refund will be adjusted accordingly.',
            'Students who plan to leave school in April or after the Final Examination are required to pay all outstanding monthly fees, including the summer vacation fees for June and July, along with other school dues.',
        ],
        'declaration' => 'Parents sign a declaration on the admission form confirming that they have read and understood these rules and will abide by them, and that the information given about the child and family is correct.',
    ],

    // Downloadable admission forms. 'file' => '' shows a "content needed" marker instead of a download.
    'forms' => [
        [
            'title'   => 'Admission Form – Playgroup to Class 7',
            'updated' => '10 June 2026',
            'files'   => [
                ['label' => 'PDF (print)',        'file' => 'downloads/skans-aechs-admission-form-playgroup-to-class-7.pdf'],
                ['label' => 'Word (fill on computer)', 'file' => 'downloads/skans-aechs-admission-form-playgroup-to-class-7.docx'],
            ],
        ],
        [
            'title'   => 'Admission Form – Pre 9 to Class 10',
            'updated' => '',
            'files'   => [], // [CONTENT NEEDED] SKANS will upload this form
        ],
    ],
];
