<?php include 'header.php'; ?>
<style>
/* General Styles - Desktop Default */
section.section {
    padding-top: 10px; /* Adjust the value based on your navigation bar height */
}

.hero-section-video {
    padding-top: 60px; /* Adjust the value based on your navigation bar height */
}

.hero-contentt {
    height: 100vh; /* Sets the height to the viewport height */
    display: flex; /* Ensures content is centered */
    align-items: center; /* Centers content vertically */
    justify-content: center; /* Centers content horizontally */
    position: relative; /* Ensures absolute positioning of the video is within this container */
    overflow: hidden; /* Prevents scrolling */
}

.hero-section-video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1; /* Places the video behind other content */
}

#hero-video {
    width: 100%;
    height: 100%;
    object-fit: cover; 
    filter: brightness(80%);
}

.hero-section-content-block {
    position: relative;
    z-index: 1; /* Places text and buttons above the video */
}

.wrapperr h2 {
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); /* Add shadow for better contrast */
    font-size: 4rem; /* Adjust size if needed */
    line-height: 1.2; /* Adjust line height for better readability */
}

.wrapperr p {
    color: white;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7); /* Add shadow for better contrast */
    font-weight: normal;
    font-size: 1.25rem; /* Adjust size if needed */
    line-height: 1.5;
}

@media (max-width: 768px) { /* For mobile devices (portrait mode) */
    .hero-contentt {
        flex-direction: column; /* Stack content vertically */
    }

    .hero-section-video {
        padding-top: 0; /* Remove padding for better fit */
    }
}

        /* Popup Background */
        #popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 10px;
        }

        /* Popup Content */
        .popup-content {
            background: #f8f1d2;
            padding: 20px;
            border-radius: 10px;
            width: 95%;
            max-width: 900px;
            text-align: center;
            position: relative;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        }

        /* Close Button */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 50%;
        }

        /* Header Style */
        .popup-title {
            background: #123a7d;
            color: white;
            font-size: 22px;
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
        }

        /* Scrollable Table */
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
        }

        .job-table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        .job-table th, .job-table td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }

        .job-table th {
            background: #123a7d;
            color: white;
        }

        /* Responsive Design */
        @media screen and (max-width: 600px) {
            .popup-content {
                width: 100%;
                max-width: 95%;
                padding: 15px;
            }

            .popup-title {
                font-size: 18px;
                padding: 8px;
            }

            .job-table th, .job-table td {
                padding: 8px;
                font-size: 14px;
            }
        }
    
</style>
<!--<div id="popup">-->
<!--    <div class="popup-content">-->
<!--        <span class="close-btn" onclick="closePopup()">×</span>-->
<!--        <h2 class="popup-title">JOB OPPORTUNITY</h2>-->
<!--        <p>The following positions are open at <strong>SKANS School of Accountancy</strong>. Suitable candidates who meet the required criteria may send their CVs with the vacancy title in the subject line to <a href="mailto:hr@skans.pk"><strong>hr@skans.pk</strong></a> by <strong>12th February 2025</strong>.</p>-->
<!--        <br>-->
<!--        <p><strong>Location:</strong> SKANS School of Accountancy, Pothohar Plaza, besides Nisar Hospital, Peshawar Road, Rawalpindi</p>-->
<!--        <div class="table-container">-->
<!--            <table class="job-table">-->
<!--                <tr>-->
<!--                    <th>Job Title</th>-->
<!--                    <th>Required Qualification</th>-->
<!--                    <th>Experience</th>-->
<!--                    <th>No. of Post</th>-->
<!--                    <th>Job Location</th>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                        <td><strong>Finance Manager</strong></td>-->
<!--                        <td>MBA Finance, BS/MS Accounting, and Finance or relevant specialization.</td>-->
<!--                        <td>1 year of experience and proficiency in accounting software.</td>-->
<!--                        <td>01</td>-->
<!--                        <td>Rawalpindi</td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                        <td><strong>Accounts Officers</strong></td>-->
<!--                        <td>MBA Finance, BS/MS Accounting and Finance or relevant specialization.</td>-->
<!--                        <td>1 year of experience in relevant Field.</td>-->
<!--                        <td>04</td>-->
<!--                        <td> Rawalpindi</td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td><strong>Network Administrator</strong></td>-->
<!--                    <td>Bachelor's degree in IT or related field. Certification in CCNA, CCNP.</td>-->
<!--                    <td>Experience in troubleshooting, IT infrastructure, CISCO, routers, switches, security, and network design.</td>-->
<!--                    <td>01</td>-->
<!--                    <td>Rawalpindi</td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td><strong>Robotics Teachers</strong></td>-->
<!--                    <td>Bachelor’s or Master’s in Electrical/Electronic Engineering or a related field.</td>-->
<!--                    <td>Teaching experience preferred but not mandatory.</td>-->
<!--                    <td>03</td>-->
<!--                    <td>Rawalpindi</td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td><strong>IMO Instructor (Mathematics)</strong></td>-->
<!--                    <td>BS / MS / M.Phil in Mathematics.</td>-->
<!--                    <td>Minimum of 2 years of teaching experience at the college level.</td>-->
<!--                    <td>01</td>-->
<!--                    <td>Rawalpindi</td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <td><strong>HOD Mathematics</strong></td>-->
<!--                    <td>MS / M.Phil in Mathematics.</td>-->
<!--                    <td>Minimum of 2 years of teaching experience at the higher school/college level.</td>-->
<!--                    <td>01</td>-->
<!--                    <td>Rawalpindi</td>-->
<!--                </tr>-->
<!--            </table>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->

<script>
    function closePopup() {
        document.getElementById("popup").style.display = "none";
    }
</script>

<!-- Hero: SKANS video and poster photo kept; new layout (Oct 2026). Old: _old/index-before-home-polish.php -->
<div class="sk-part">
<section class="sk-hero" aria-labelledby="hero-title">
    <video class="sk-hero__video" id="hero-video" muted loop playsinline preload="metadata" poster="assets/video/hero-poster.jpg" aria-hidden="true">
        <source src="assets/video/hero.mp4" type="video/mp4">
    </video>
    <div class="sk-hero__shade" aria-hidden="true"></div>
    <div class="sk-container sk-hero__content">
        <span class="sk-eyebrow sk-hero__eyebrow"><?= e($site['name']) ?> · <?= e($site['campus']) ?></span>
        <h1 class="sk-hero__title" id="hero-title">Empowering Minds, Inspiring Futures</h1>
        <p class="sk-hero__lead">Welcome to <?= e($site['full_name']) ?> <?= e($site['city']) ?>: <?= e($site['classes']) ?>.</p>
        <div class="sk-actions">
            <a class="sk-btn sk-btn--accent" href="about.php">About School</a>
            <a class="sk-btn sk-btn--outline sk-btn--on-dark" href="contact-us.php">Contact Us</a>
        </div>
    </div>
</section>
</div>

<script>
// Lazy loading video with IntersectionObserver
document.addEventListener('DOMContentLoaded', function() {
    var video = document.getElementById('hero-video');
    if ('IntersectionObserver' in window) {
        let observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    video.play(); // Start video when in view
                } else {
                    video.pause(); // Pause video when out of view
                }
            });
        });
        observer.observe(video);
   }
});
</script>
<!-- Admissions call-to-action -->
<div class="sk-part">
<section class="sk-section sk-section--tight">
    <div class="sk-container">
        <!-- Original SKANS banner background (yellow + pattern) kept; new text and buttons -->
        <div class="sk-cta sk-cta--yellow">
            <div class="sk-cta__inner">
                <div class="sk-cta__text">
                    <span class="sk-eyebrow">Admissions</span>
                    <h2 class="sk-cta__title">Join Our Welcoming Community through SKANS Admissions</h2>
                </div>
                <div class="sk-actions sk-mt-0">
                    <a class="sk-btn sk-btn--primary" href="admissions.php">Admission</a>
                    <a class="sk-btn sk-btn--outline" href="fee-structure.php">Fee Structure</a>
                </div>
            </div>
            <div class="sk-cta__pattern" aria-hidden="true"></div>
        </div>
    </div>
</section>
</div>
     
    <!-- Campus Facilities: same icons and texts, consistent cards -->
<?php
$home_facilities = [
    ['assets/img/facilities/staff.jpeg', 'Highly Qualified and Experienced Staff', 'Our educators are highly qualified, with advanced degrees and professional backgrounds, ensuring exceptional educational experiences. Committed to professional development, they tailor their teaching to meet diverse student needs, fostering both academic and personal growth.'],
    ['assets/img/facilities/taekwondo.png', 'Taekwondo', 'Our Taekwondo program helps students build self-discipline, strength, and confidence. Through structured training, they learn important life skills like focus and perseverance while improving their physical abilities.'],
    ['assets/img/facilities/computer-lab.png', 'Well-Equipped Computer Lab', 'Our computer lab is outfitted with modern technology and high-speed internet, giving students the tools for a comprehensive digital literacy education. From basic computing to advanced programming, students engage in hands-on activities that develop essential technical skills.'],
    ['assets/img/facilities/art-and-craft.png', 'Art and Craft', 'Our Arts and Crafts program encourages creativity through diverse mediums like drawing, painting, and sculpture. These activities promote fine motor skills and imaginative thinking while supporting emotional and cognitive development.'],
    ['assets/img/facilities/sports.png', 'Sports and Games', 'We offer a variety of sports and games to promote physical fitness, teamwork, and healthy competition. These activities support students’ physical development and encourage a balanced, active lifestyle.'],
    ['assets/img/facilities/islamic-education.png', 'Islamic Education', 'Our Islamic Education program focuses on teaching core Islamic values such as respect, kindness, and love for Allah. Students engage with Quranic stories, prayers, and morals, fostering a strong spiritual foundation.'],
    ['assets/img/facilities/eyeris-room.png', 'Eyeris Room', 'The Eyeris Room is equipped with smart whiteboards and high-definition projectors, allowing for interactive lessons and engaging presentations. These tools foster collaboration and enhance the learning experience through technology.'],
    ['assets/img/facilities/smart-classroom.png', 'Temperature-Controlled Classrooms', 'Our classrooms feature climate control systems that ensure a comfortable learning environment year-round. By regulating temperature and air quality, we enhance student concentration and create an optimal atmosphere for learning.'],
];
?>
<div class="sk-part">
<section class="sk-section">
    <div class="sk-container">
        <div class="sk-head-row">
            <div class="sk-section-header sk-mb-0">
                <span class="sk-eyebrow">Life on campus</span>
                <h2>Campus Facilities</h2>
                <span class="sk-rule"></span>
            </div>
            <a class="sk-btn sk-btn--outline" href="services.php">View all facilities</a>
        </div>
        <div class="sk-facility-grid">
<?php foreach ($home_facilities as [$icon, $title, $text]): ?>
            <article class="sk-facility">
                <div class="sk-facility__icon"><img src="<?= e($icon) ?>" alt="" loading="lazy" width="96" height="96"></div>
                <h3 class="sk-facility__title"><?= e($title) ?></h3>
                <p class="sk-facility__text"><?= e($text) ?></p>
            </article>
<?php endforeach; ?>
        </div>
    </div>
</section>
</div>
<!-- Academic Programs: same section as the Programs Offered page (includes/levels-section.php) -->
<div class="sk-part sk-home-levels">
<?php include __DIR__ . '/includes/levels-section.php'; ?>
</div>
  
<section class="section margin-top-176-mobile-144">
    <div class="w-layout-blockcontainer container w-container">
        <div class="wrapper">
            <h2 class="h2 max-width-432-mobile-320 margin-bottom-24">Discover What's Happening in School</h2>
            <div data-current="All" data-easing="ease-in-out" data-duration-in="300" data-duration-out="100" class="tabs w-tabs">
                <div class="tabs-menu w-tab-menu">
                    <a href="news-events.php" data-w-tab="All" class="tab-link w-inline-block w-tab-link w--current">
                        <div class="regular-m">All</div>
                    </a>

                    <a data-w-tab="News" class="tab-link w-inline-block w-tab-link" onclick="filterItems('News')">
                        <div class="regular-m">News</div>
                    </a>
                    <a data-w-tab="Events" class="tab-link w-inline-block w-tab-link" onclick="filterItems('Events')">
                        <div class="regular-m">Events</div>
                    </a>
                </div>
                <div class="tabs-content w-tab-content">
                    <div data-w-tab="All" class="w-tab-pane w--tab-active">
                        <div class="collection-list-wrapper-type-two w-dyn-list">
                            <div role="list" class="collection-list-type-two w-dyn-items">
                                <?php
                                include 'adminpanel/db.php'; // Ensure this path is correct and includes your database connection

                                // Check connection
                                if ($conn->connect_error) {
                                    die("Connection failed: " . $conn->connect_error);
                                }

                                // Fetch the latest 3 news/events from the database
                                $sql = "SELECT news_events.*, categories.name AS category_name 
                                        FROM news_events 
                                        JOIN categories ON news_events.category_id = categories.id 
                                        ORDER BY news_events.date DESC 
                                        LIMIT 3";
                                $result = $conn->query($sql);
                                require_once __DIR__ . '/includes/photo-card.php'; // plain_text()

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $image_url = upload_url($row["image_url"]) ?: 'default-image.jpg'; // this campus's pictures folder; fallback image
                                        $date_formatted = date("M d", strtotime($row["date"]));
                                        echo '<div role="listitem" class="collection-item-type-two w-dyn-item" data-type="' . e($row["category_name"]) . '">';
                                        echo '<div class="collection-image-type-two">';
                                        echo '<div class="image-container">';
                                        echo '<img src="' . $image_url . '" loading="lazy" alt="' . e($row["title"]) . '" class="collection-image-type-two image" />';
                                        // Arrow button opens the details over the photo (same as the faculty cards; replaces the blue hover overlay).
                                        $panel_id = 'home-news-' . (int) $row['id'];
                                        echo '<div class="sk-teacher__panel sk-news-panel" id="' . $panel_id . '" role="region" aria-label="About ' . e($row['title']) . '" hidden>'
                                           . '<button type="button" class="sk-teacher__close" data-teacher-close aria-label="Close details">✕</button>'
                                           . '<p class="sk-teacher__panel-name">' . e($row['title']) . '</p>'
                                           . '<p class="sk-teacher__panel-role">' . e($row['category_name']) . ' · ' . e(nice_date($row['date'])) . '</p>'
                                           . '<p>' . nl2br(e(plain_text($row['description']))) . '</p>'
                                           . '<p class="sk-news-panel__more"><a href="news-events.php#news-' . (int) $row['id'] . '">Open in News &amp; Events →</a></p>'
                                           . '</div>';
                                        echo '<button type="button" class="sk-teacher__toggle" data-teacher-toggle aria-expanded="false" aria-controls="' . $panel_id . '">'
                                           . '<span class="sk-visually-hidden">View details for ' . e($row['title']) . '</span>'
                                           . '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M7 17 17 7M9 7h8v8" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
                                           . '</button>';
                                        echo '</div></div>';
                                        echo '<div class="wrapper"><div class="wrapper collection-item-type-two-info margin-bottom-12"><div class="regular-m">' . e($row["category_name"]) . '</div><div class="regular-s">' . $date_formatted . '</div></div>';
                                        echo '<div class="wrapper padding-right-32"><h4 class="h4">' . e($row["title"]) . '</h4></div>';
                                        echo '</div></div>';
                                    }
                                } else {
                                    echo '<p>No news or events found.</p>';
                                }

                                $conn->close();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<script>
    function filterItems(type) {
        const items = document.querySelectorAll('.collection-item-type-two');
        items.forEach(item => {
            if (type === 'All' || item.getAttribute('data-type') === type) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>

    <!-- Three "Explore" banners: original colours and pattern images kept, shown as cards side by side
     (they used to slide over each other while scrolling). Old: _old/index-before-banner-cards.php -->
<div class="sk-part">
<section class="sk-section">
    <div class="sk-container">
        <div class="sk-promos">
            <article class="sk-promo sk-reveal" style="--reveal-delay: 0ms; --promo-bg: #B3D4F2; --promo-pattern: url('<?= e(site_base()) ?>assets/img/patterns/pattern-2.svg')">
                <div class="sk-promo__body">
                    <span class="sk-eyebrow">Academics</span>
                    <h2 class="sk-promo__title">Explore Academic Programs Available In SKANS <?= e(str_replace(' Campus', '', $site['campus'])) ?> Campus</h2>
                    <a href="departments.php" class="sk-btn sk-btn--primary">Explore Academic Programs</a>
                </div>
                <div class="sk-promo__pattern" aria-hidden="true"></div>
            </article>
            <article class="sk-promo sk-reveal" style="--reveal-delay: 150ms; --promo-bg: #C3EFC8; --promo-pattern: url('<?= e(site_base()) ?>assets/img/patterns/pattern-3.svg')">
                <div class="sk-promo__body">
                    <span class="sk-eyebrow">Special programs</span>
                    <h2 class="sk-promo__title">Embark on Academic Excellence: Explore Special Programs</h2>
                    <a href="advanced-placement-program.php" class="sk-btn sk-btn--primary">About Program</a>
                </div>
                <div class="sk-promo__pattern" aria-hidden="true"></div>
            </article>
            <article class="sk-promo sk-reveal" style="--reveal-delay: 300ms; --promo-bg: #E2D4F4; --promo-pattern: url('<?= e(site_base()) ?>assets/img/patterns/pattern-4.svg')">
                <div class="sk-promo__body">
                    <span class="sk-eyebrow">Admissions</span>
                    <h2 class="sk-promo__title">SKANS School Admissions</h2>
                    <a href="admissions.php" class="sk-btn sk-btn--primary">About Admissions</a>
                </div>
                <div class="sk-promo__pattern" aria-hidden="true"></div>
            </article>
        </div>
    </div>
</section>
</div>
    <!-- Our Faculty: same teacher cards as the Faculty page (includes/faculty-card.php) -->
<?php
require_once __DIR__ . '/includes/faculty-card.php';
$home_faculty = [];
if ($db_ok) {
    include 'adminpanel/db.php'; // the sections above close the shared connection, so open it again
    $r = $conn->query('SELECT id, name, position, image, experience, qualifications FROM faculty ORDER BY RAND() LIMIT 3');
    $home_faculty = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}
?>
<div class="sk-part sk-home-faculty">
<section class="sk-section">
    <div class="sk-container">
        <div class="sk-section-header">
            <span class="sk-eyebrow">The minds behind our excellence</span>
            <h2>Our Faculty</h2>
            <span class="sk-rule"></span>
        </div>
        <div class="sk-teachers sk-teachers--three">
<?php foreach ($home_faculty as $t) echo faculty_card($t, 'home-fac'); ?>
        </div>
        <div class="sk-actions">
            <a class="sk-btn sk-btn--primary" href="faculty.php">Meet all our faculty</a>
        </div>
    </div>
</section>
</div>

    <?php include __DIR__ . '/includes/announcement-popup.php'; // latest announcement pop-up ?>
<?php   include 'footer.php' ?>