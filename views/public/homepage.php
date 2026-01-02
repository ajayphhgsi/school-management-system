<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($school_name); ?></title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Modern Theme */
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --text-color: #2b2d42;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --shadow: 0 10px 30px rgba(0,0,0,0.08);
            --border-radius: 20px;
            --gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            --hero-bg: var(--gradient);
            --card-shadow: var(--shadow);
            --btn-bg: var(--primary-color);
            --btn-hover: var(--secondary-color);
            --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        /* Classic Theme */
        [data-theme="classic"] {
            --primary-color: #003366; /* Navy Blue */
            --secondary-color: #FFD700; /* Gold */
            --accent-color: #8B4513; /* Brown */
            --text-color: #2c3e50;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --shadow: 0 2px 8px rgba(0,0,0,0.15);
            --border-radius: 8px;
            --gradient: var(--primary-color);
            --hero-bg: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            --card-shadow: var(--shadow);
            --btn-bg: var(--primary-color);
            --btn-hover: var(--secondary-color);
            --transition: all 0.3s ease;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Roboto', sans-serif;
            transition: var(--transition);
            line-height: 1.8;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Roboto', sans-serif;
            font-weight: 700;
            color: var(--text-color);
        }

        [data-theme="classic"] h1,
        [data-theme="classic"] h2,
        [data-theme="classic"] h3,
        [data-theme="classic"] h4,
        [data-theme="classic"] h5,
        [data-theme="classic"] h6 {
            font-family: 'Playfair Display', serif;
        }

        .section-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 15px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            border-radius: 2px;
        }

        .section-subtitle {
            color: var(--text-color);
            opacity: 0.8;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .hero-section {
            background: var(--hero-bg);
            color: white;
            padding: 150px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .carousel-item {
            height: 600px;
        }

        .carousel-image {
            object-fit: cover;
            height: 100%;
            width: 100%;
            filter: brightness(0.8);
        }

        .carousel-caption {
            bottom: 30%;
            left: 10%;
            right: 10%;
            text-align: left;
        }

        .carousel-caption h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
        }

        .carousel-caption p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.5);
        }

        .btn-light {
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-light:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .card {
            border: none;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            background: var(--card-bg);
            border-radius: var(--border-radius);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
            transition: var(--transition);
        }

        .card:hover .card-img-top {
            transform: scale(1.05);
        }

        .card-body {
            padding: 2rem;
        }

        .btn-primary {
            background: var(--btn-bg);
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: var(--btn-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .btn-lg {
            padding: 15px 40px;
            font-size: 1.1rem;
        }

        .testimonial-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 30px;
            margin: 15px;
            box-shadow: var(--card-shadow);
            border-left: 5px solid var(--primary-color);
            transition: var(--transition);
        }

        .testimonial-card:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .testimonial-card p {
            font-style: italic;
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 1.5rem;
        }

        .testimonial-card h6 {
            color: var(--primary-color);
            font-weight: 600;
        }

        .gallery-card {
            overflow: hidden;
            border-radius: var(--border-radius);
        }

        .social-icons a {
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .social-icons a:hover {
            color: var(--primary-color) !important;
            transform: translateY(-3px);
        }

        .text-decoration-none:hover {
            color: var(--primary-color) !important;
        }

        .gallery-img {
            height: 180px;
            object-fit: cover;
            transition: var(--transition);
        }

        .gallery-card:hover .gallery-img {
            transform: scale(1.1);
        }

        .event-meta {
            background: rgba(233, 236, 239, 0.5);
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }

        .badge {
            font-size: 0.85rem;
            padding: 5px 12px;
        }

        .shadow-lg {
            box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
        }

        .rounded-4 {
            border-radius: 20px !important;
        }

        .navbar {
            background: var(--card-bg) !important;
            box-shadow: var(--card-shadow);
            padding: 15px 0;
            transition: var(--transition);
        }

        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
            margin: 0 10px;
            position: relative;
            transition: var(--transition);
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: var(--transition);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .bg-light {
            background: var(--bg-color) !important;
        }

        .bg-primary {
            background: var(--primary-color) !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .theme-toggle {
            background: var(--primary-color);
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.6rem;
            border-radius: 50%;
            transition: var(--transition);
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-toggle:hover {
            background: var(--secondary-color);
            transform: rotate(180deg);
        }

        [data-theme="classic"] .theme-toggle {
            background: var(--primary-color);
            color: white;
        }

        [data-theme="classic"] .theme-toggle:hover {
            background: var(--secondary-color);
        }

        /* Statistics Counter */
        .stat-counter {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .stat-counter:nth-child(1) { animation-delay: 0.1s; }
        .stat-counter:nth-child(2) { animation-delay: 0.2s; }
        .stat-counter:nth-child(3) { animation-delay: 0.3s; }
        .stat-counter:nth-child(4) { animation-delay: 0.4s; }

        .counter {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, #ffffff, #f8f9fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Section animations */
        .section-fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out;
        }

        .section-fade-in.animate {
            opacity: 1;
            transform: translateY(0);
        }

        /* Enhanced hero section */
        .hero-section {
            position: relative;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.05"/><circle cx="10" cy="50" r="0.5" fill="white" opacity="0.05"/><circle cx="90" cy="30" r="0.5" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }

        .hero-title-gradient {
            background: linear-gradient(45deg, #ffffff, #e9ecef, #ffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: textGlow 3s ease-in-out infinite alternate;
        }

        @keyframes textGlow {
            from { filter: brightness(1); }
            to { filter: brightness(1.2); }
        }

        /* Enhanced buttons */
        .btn-enhanced {
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
            z-index: -1;
        }

        .btn-enhanced:hover::before {
            left: 100%;
        }

        /* Newsletter section */
        .newsletter-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .newsletter-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .newsletter-form {
            position: relative;
            z-index: 2;
        }

        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .skeleton-text {
            height: 1.2em;
            border-radius: 4px;
        }

        .skeleton-title {
            height: 2em;
            width: 60%;
            margin: 0 auto 1em;
            border-radius: 4px;
        }

        .skeleton-card {
            height: 200px;
            border-radius: 8px;
        }

        /* Enhanced mobile responsiveness */
        @media (max-width: 768px) {
            .hero-section {
                padding: 100px 0;
            }

            .carousel-caption h1 {
                font-size: 2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .counter {
                font-size: 2rem;
            }

            .stat-counter i {
                font-size: 2rem;
            }
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Enhanced card hover effects */
        .card-enhanced {
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .card-enhanced:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-graduation-cap text-primary me-2"></i>
                <span class="text-primary"><?php echo htmlspecialchars($school_name); ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="/courses">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="/events">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="/gallery">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary text-white px-3 py-1 rounded-pill" href="/login">Login</a></li>
                    <li class="nav-item ms-3">
                        <button class="theme-toggle" id="themeToggle" title="Toggle Theme">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Carousel -->
    <section class="hero-section">
        <div class="hero-content">
            <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php if (!empty($carousel)): ?>
                        <?php foreach ($carousel as $index => $slide): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="/uploads/<?php echo $slide['image_path']; ?>" class="d-block w-100 carousel-image" alt="<?php echo $slide['title']; ?>">
                                <div class="carousel-caption d-none d-md-block">
                                    <h1 class="display-4 fw-bold mb-4 hero-title-gradient"><?php echo $slide['title']; ?></h1>
                                    <p class="lead mb-4"><?php echo $slide['content']; ?></p>
                                    <?php if ($slide['link']): ?>
                                        <a href="<?php echo $slide['link']; ?>" class="btn btn-light btn-lg btn-enhanced">Learn More</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="carousel-item active">
                            <div class="d-flex align-items-center justify-content-center h-100 bg-primary">
                                <div class="text-center">
                                    <h1 class="display-4 fw-bold mb-4">Welcome to Our School</h1>
                                    <p class="lead mb-4">Excellence in Education</p>
                                    <a href="/login" class="btn btn-light btn-lg">Get Started</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <?php if ($about): ?>
    <section class="py-5 bg-white section-fade-in">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <img src="/uploads/<?php echo $about['image_path']; ?>" class="img-fluid rounded-4 shadow-lg mb-4 mb-lg-0" alt="About Us">
                </div>
                <div class="col-lg-6">
                    <h2 class="section-title"><?php echo $about['title']; ?></h2>
                    <p class="section-subtitle">Learn more about our institution</p>
                    <p class="lead mb-4"><?php echo $about['content']; ?></p>
                    <a href="/about" class="btn btn-primary btn-lg">Read More</a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Statistics Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="stat-counter">
                        <i class="fas fa-users fa-3x mb-3 text-white"></i>
                        <h2 class="counter" data-target="1500">0</h2>
                        <p class="mb-0">Happy Students</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-counter">
                        <i class="fas fa-chalkboard-teacher fa-3x mb-3 text-white"></i>
                        <h2 class="counter" data-target="50">0</h2>
                        <p class="mb-0">Expert Teachers</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-counter">
                        <i class="fas fa-graduation-cap fa-3x mb-3 text-white"></i>
                        <h2 class="counter" data-target="25">0</h2>
                        <p class="mb-0">Years of Excellence</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stat-counter">
                        <i class="fas fa-award fa-3x mb-3 text-white"></i>
                        <h2 class="counter" data-target="98">0</h2>
                        <p class="mb-0">Success Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Courses Section -->
    <?php if (!empty($courses)): ?>
    <section class="py-5 bg-light section-fade-in">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Courses</h2>
                <p class="section-subtitle">Discover our comprehensive educational programs</p>
            </div>
            <div class="row g-4">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <?php if ($course['image_path']): ?>
                                <img src="/uploads/<?php echo $course['image_path']; ?>" class="card-img-top" alt="<?php echo $course['title']; ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-3"><?php echo $course['title']; ?></h5>
                                <p class="card-text text-muted"><?php echo substr($course['content'], 0, 100) . '...'; ?></p>
                                <a href="/courses" class="btn btn-primary stretched-link">Learn More</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Events Section -->
    <?php if (!empty($events)): ?>
    <section class="py-5 bg-white section-fade-in">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Upcoming Events</h2>
                <p class="section-subtitle">Stay updated with our latest activities and events</p>
            </div>
            <div class="row g-4">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title fw-bold mb-0"><?php echo $event['title']; ?></h5>
                                    <span class="badge bg-primary">Upcoming</span>
                                </div>
                                <p class="card-text text-muted mb-4"><?php echo substr($event['description'], 0, 100) . '...'; ?></p>
                                <div class="event-meta mb-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                                        <span><?php echo date('M d, Y', strtotime($event['event_date'])); ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock text-primary me-2"></i>
                                        <span><?php echo $event['event_time']; ?></span>
                                    </div>
                                </div>
                                <a href="/events" class="btn btn-primary stretched-link">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Gallery Section -->
    <?php if (!empty($gallery)): ?>
    <section class="py-5 bg-light section-fade-in">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Photo Gallery</h2>
                <p class="section-subtitle">Capturing moments of learning and achievement</p>
            </div>
            <div class="row g-3">
                <?php foreach ($gallery as $image): ?>
                    <div class="col-md-3">
                        <div class="card gallery-card">
                            <img src="/uploads/<?php echo $image['image_path']; ?>" class="card-img-top gallery-img" alt="<?php echo $image['title']; ?>">
                            <div class="card-body">
                                <h6 class="card-title fw-bold mb-0"><?php echo $image['title']; ?></h6>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="/gallery" class="btn btn-primary btn-lg">View All Photos</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Testimonials Section -->
    <?php if (!empty($testimonials)): ?>
    <section class="py-5 bg-white section-fade-in">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">What Our Students Say</h2>
            </div>
            <div class="row g-4">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="col-md-6">
                        <div class="testimonial-card">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-quote-left text-primary fa-2x me-3"></i>
                                <p class="mb-0">"<?php echo $testimonial['content']; ?>"</p>
                            </div>
                            <h6 class="mb-0">- <?php echo $testimonial['title']; ?></h6>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Newsletter Section -->
    <section class="newsletter-section py-5 text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="display-5 fw-bold mb-3">Stay Updated</h2>
                    <p class="lead mb-0">Subscribe to our newsletter for the latest news, events, and educational updates.</p>
                </div>
                <div class="col-lg-6">
                    <div class="newsletter-form">
                        <form class="row g-3">
                            <div class="col-md-8">
                                <input type="email" class="form-control form-control-lg" placeholder="Enter your email address" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-light btn-lg w-100 btn-enhanced">Subscribe</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5" style="background: var(--gradient); color: white;">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-3">Ready to Join Our Community?</h2>
            <p class="lead mb-4">Contact us today to learn more about our programs and admission process.</p>
            <a href="/contact" class="btn btn-light btn-lg px-4 py-2 btn-enhanced">Contact Us</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-primary mb-3"><?php echo htmlspecialchars($school_name); ?></h5>
                    <p class="text-muted">Providing quality education for over 50 years.</p>
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="/about" class="text-muted text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="/courses" class="text-muted text-decoration-none">Courses</a></li>
                        <li class="mb-2"><a href="/events" class="text-muted text-decoration-none">Events</a></li>
                        <li class="mb-2"><a href="/contact" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Contact Info</h5>
                    <p class="text-muted mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i> <?php echo htmlspecialchars($school_address ?: '123 School Street, City, State'); ?></p>
                    <p class="text-muted mb-2"><i class="fas fa-phone text-primary me-2"></i> <?php echo htmlspecialchars($school_phone ?: '+1-234-567-8900'); ?></p>
                    <p class="text-muted mb-0"><i class="fas fa-envelope text-primary me-2"></i> <?php echo htmlspecialchars($school_email ?: 'info@school.com'); ?></p>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center py-3">
                <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($school_name); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme switching functionality
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'modern';
        html.setAttribute('data-theme', savedTheme);

        // Update toggle icon based on theme
        function updateToggleIcon() {
            const icon = themeToggle.querySelector('i');
            if (html.getAttribute('data-theme') === 'classic') {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
        }

        updateToggleIcon();

        // Toggle theme on button click
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'classic' ? 'modern' : 'classic';

            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateToggleIcon();
        });

        // Counter Animation
        function animateCounter(counter) {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000; // 2 seconds
            const step = target / (duration / 16); // 60fps
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    counter.textContent = target;
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current);
                }
            }, 16);
        }

        // Intersection Observer for counter animation
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.counter');
                    counters.forEach(counter => animateCounter(counter));
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        // Observe statistics section
        const statsSection = document.querySelector('.bg-primary');
        if (statsSection) {
            counterObserver.observe(statsSection);
        }

        // Scroll Animation
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, { threshold: 0.1 });

        // Observe all sections with fade-in class
        document.querySelectorAll('.section-fade-in').forEach(section => {
            sectionObserver.observe(section);
        });

        // Back to Top Button
        const backToTopBtn = document.getElementById('backToTop');

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Enhanced navigation smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Newsletter form handling
        const newsletterForm = document.querySelector('.newsletter-form form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const email = this.querySelector('input[type="email"]').value;
                // Here you would typically send the email to your server
                alert('Thank you for subscribing! We\'ll keep you updated with our latest news.');
                this.reset();
            });
        }

        // Add loading animation to cards
        document.querySelectorAll('.card').forEach(card => {
            card.classList.add('card-enhanced');
        });

        // Preload hero images for better performance
        const heroImages = document.querySelectorAll('.carousel-image');
        heroImages.forEach(img => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = img.src;
            document.head.appendChild(link);
        });
    </script>
</body>
</html>