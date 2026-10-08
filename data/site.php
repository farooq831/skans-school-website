<?php
// data/site.php : school details used on every page (header, footer, contact, programs).
// Edit here once; every page updates. '[CONTENT NEEDED]' shows a visible marker on the site.

return [
    // Shared defaults for every campus. Each campus's own name, address, phones, email and social
    // links are in campus-data/<campus>/config.php ('site'); the admin panel (Settings) can change them.
    // A new campus shows '[CONTENT NEEDED]' until its details are filled in.
    'name'      => 'SKANS School System',
    'campus'    => '[CONTENT NEEDED] Campus',
    'full_name' => 'SKANS School System',
    'city'      => 'Rawalpindi',
    'classes'   => 'Playgroup – Matric',
    'tagline'   => 'Education for life',

    'address'   => '[CONTENT NEEDED]',
    'map_url'   => '',
    'email'     => '',
    'phones'    => [],
    'whatsapp'  => '',

    // Announcements leave the website automatically this many days after they were added.
    // They stay saved in the database (admin can still edit, re-use or delete them).
    'announcement_days' => 30,
    'facebook'  => '',
    'instagram' => '',

    // Programs offered: 3 levels (agreed with SKANS on 3 Oct 2026).
    // Objectives are the SKANS "Academic Programs" admin entries #1 (Early Years), #2 (Juniors), #3 (Seniors).
    // The earlier 4-level version (with a separate Middle level) is kept in _old/data-site-4-levels.php.
    'levels' => [
        [
            'id'      => 'early-years',
            'subject_classes' => ['Playgroup', 'Nursery', 'Kindergarten', 'Class 1', 'Class 2'],
            'name'    => 'Early Years',
            'range'   => 'Playgroup to Class 2',
            'classes' => [['Playgroup', '2.5+ years'], ['Nursery', '3.5+ years'], ['Kindergarten', '4.5+ years'], ['Class 1', '5.5+ years'], ['Class 2', '6.5+ years']],
            'image'   => 'assets/img/level-early-years.jpeg',
            'objectives' => [
                'Enhancing motor skills and sensory development through hands-on activities',
                'Promoting social interaction and effective communication in group settings',
                'Introducing fundamental concepts like letters, numbers and basic math',
                'Encouraging creativity and emotional expression through art and play',
                'Supporting holistic cognitive, emotional and social growth of learners',
                'Teaching personal care habits like hygiene, dressing and self-care',
                'Ensuring safety awareness and creating a secure learning environment',
                'Fostering strong parental involvement to support learning at home',
            ],
        ],
        [
            'id'      => 'juniors',
            'subject_classes' => ['Class 3', 'Class 4', 'Class 5'],
            'name'    => 'Juniors',
            'range'   => 'Class 3 to Class 5',
            'classes' => [['Class 3', '7.5+ years'], ['Class 4', '8.5+ years'], ['Class 5', '9.5+ years']],
            'image'   => 'assets/img/level-juniors.jpeg',
            'objectives' => [
                'Developing literacy and numeracy skills through interactive lessons and activities',
                'Promoting teamwork, collaboration and communication in group projects',
                'Encouraging problem-solving and analytical thinking across subjects',
                'Fostering creativity and self-expression through writing, art and presentations',
                'Supporting emotional and social growth by building empathy and resilience',
                'Instilling healthy habits like organization, personal responsibility and time management',
                'Encouraging awareness of safety, respect and community in school settings',
            ],
        ],
        [
            'id'      => 'seniors',
            'subject_classes' => ['Class 6', 'Class 7', 'Pre 9', 'Class 9', 'Class 10'],
            'name'    => 'Seniors',
            'range'   => 'Class 6 to Class 10 (Matric)',
            'classes' => [['Class 6', '10.5+ years'], ['Class 7', '11.5+ years'], ['Pre 9', '12.5+ years'], ['Class 9', '13.5+ years'], ['Class 10 (Matric)', '14.5+ years']],
            'image'   => 'assets/img/level-seniors.jpeg',
            'board'   => 'Federal Board of Intermediate and Secondary Education (FBISE)',
            'objectives' => [
                'Enhancing subject mastery in core areas like mathematics, science and language arts',
                'Encouraging independent thinking, research and problem-solving across disciplines',
                'Developing effective communication and collaboration through debates, group work and presentations',
                'Nurturing creativity and critical analysis in both academic and creative projects',
                'Supporting emotional and social maturity by promoting leadership, empathy and self-awareness',
                'Teaching effective study habits, time management and personal organization for academic success',
                'Building awareness of safety, ethics and responsible behavior in both school and society',
                'Encouraging active parental engagement to support academic goals and personal growth',
            ],
        ],
    ],

    // Confirmed by SKANS on 3 Oct 2026: the same subjects are taught at AECHS campus.
    // Subjects for each class, from the SKANS course sheets "GXS Course (op.course).xlsx" (Playgroup–Class 5)
    // and "GXS Course (op.course) (1).xlsx" (Class 6–10), supplied 3 Oct 2026. FA/FSc/ICS, IGCSE and
    // A Levels in the sheet are not offered at this campus and are left out.
    // Spelling made consistent ("Islamiat", "Mathematics", "English"); subjects listed in the same order for every class.
    'subjects_by_class' => [
        'Playgroup' => ['Communication, Language & Literacy', 'Mathematical Development', 'Understanding the World', 'Urdu',
                        'Personal, Social & Emotional Development', 'Nazra', 'Art & Design', 'Physical Education', 'Library'],
        'Nursery'   => ['Communication, Language & Literacy', 'Mathematical Development', 'Understanding the World', 'Urdu',
                        'Personal, Social & Emotional Development', 'Nazra', 'Art & Design', 'Physical Education',
                        'Information & Communication Technology', 'Library'],
        'Kindergarten' => ['Communication, Language & Literacy', 'Mathematical Development', 'Understanding the World', 'Urdu',
                        'Personal, Social & Emotional Development', 'Nazra', 'Art & Design', 'Physical Education',
                        'Information & Communication Technology', 'Library'],
        'Class 1'   => ['English', 'Urdu', 'Mathematics', 'Science', 'Social Studies', 'Islamiat', 'Nazra',
                        'Information & Communication Technology', 'Robotics', 'Physical Education', 'Library'],
        'Class 2'   => ['English', 'Urdu', 'Mathematics', 'Science', 'Social Studies', 'Islamiat', 'Nazra',
                        'Information & Communication Technology', 'Robotics', 'Physical Education', 'Library'],
        'Class 3'   => ['English', 'Urdu', 'Mathematics', 'Science', 'Social Studies', 'Islamiat', 'Nazra',
                        'Information & Communication Technology', 'Computer Languages', 'Robotics', 'Physical Education', 'Library'],
        'Class 4'   => ['English', 'Urdu', 'Mathematics', 'Science', 'Social Studies', 'Islamiat', 'Nazra',
                        'Information & Communication Technology', 'Computer Languages', 'Robotics', 'Physical Education', 'Library'],
        'Class 5'   => ['English', 'Urdu', 'Mathematics', 'Science', 'Social Studies', 'Islamiat', 'Nazra',
                        'Information & Communication Technology', 'Computer Languages', 'Robotics', 'Physical Education', 'Library'],
        'Class 6'   => ['English', 'Urdu', 'Mathematics', 'Science', 'History', 'Geography', 'Islamiat', 'Nazra',
                        'Information & Communication Technology', 'Computer Languages', 'Robotics', 'Physical Education'],
        'Class 7'   => ['English', 'Urdu', 'Mathematics', 'Science', 'History', 'Geography', 'Islamiat', 'Nazra',
                        'Information & Communication Technology', 'Computer Languages', 'Robotics', 'Physical Education'],
        'Pre 9'     => ['English Compulsory', 'Urdu Compulsory', 'Mathematics', 'Physics', 'Chemistry', 'Biology',
                        'Computer Science', 'Islamiat', 'Translation of the Holy Quran'],
        'Class 9'   => ['English Compulsory', 'Urdu Compulsory', 'Mathematics', 'Physics', 'Chemistry', 'Biology',
                        'Computer Science', 'Islamiat', 'Translation of the Holy Quran'],
        'Class 10'  => ['English Compulsory', 'Urdu Compulsory', 'Mathematics', 'Physics', 'Chemistry', 'Biology',
                        'Computer Science', 'Pakistan Studies', 'Translation of the Holy Quran'],
    ],
];
