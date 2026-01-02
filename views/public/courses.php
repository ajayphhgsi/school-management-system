<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - School Management System</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 100px 0;
        }
        .card {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
        }
        .btn-primary:hover {
            background: var(--secondary-color);
        }
        .course-card {
            height: 100%;
        }
        .course-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-school"></i> School Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/about">About</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/courses">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="/events">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="/gallery">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1>Our Academic Programs</h1>
            <p class="lead">Comprehensive education programs designed to nurture talent and foster excellence</p>
        </div>
    </section>

    <!-- Courses Section -->
    <section class="py-5">
        <div class="container">
            <?php if (!empty($courses)): ?>
                <div class="row">
                    <?php foreach ($courses as $course): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card course-card">
                                <?php if ($course['image_path']): ?>
                                    <img src="/uploads/<?php echo $course['image_path']; ?>" class="card-img-top" alt="<?php echo $course['title']; ?>" style="height: 200px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body text-center">
                                    <div class="course-icon bg-primary text-white">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($course['content'], 0, 150)) . '...'; ?></p>
                                    <a href="/contact" class="btn btn-primary">Learn More</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Default Courses -->
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card course-card">
                            <div class="card-body text-center">
                                <div class="course-icon bg-primary text-white">
                                    <i class="fas fa-atom"></i>
                                </div>
                                <h5 class="card-title">Science</h5>
                                <p class="card-text">Comprehensive science education covering Physics, Chemistry, and Biology with hands-on laboratory experience.</p>
                                <a href="/contact" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card course-card">
                            <div class="card-body text-center">
                                <div class="course-icon bg-success text-white">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <h5 class="card-title">Mathematics</h5>
                                <p class="card-text">Advanced mathematics curriculum focusing on problem-solving skills and logical reasoning.</p>
                                <a href="/contact" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card course-card">
                            <div class="card-body text-center">
                                <div class="course-icon bg-info text-white">
                                    <i class="fas fa-language"></i>
                                </div>
                                <h5 class="card-title">Languages</h5>
                                <p class="card-text">Multi-language education including English, Hindi, and regional languages for global communication.</p>
                                <a href="/contact" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card course-card">
                            <div class="card-body text-center">
                                <div class="course-icon bg-warning text-white">
                                    <i class="fas fa-palette"></i>
                                </div>
                                <h5 class="card-title">Arts & Crafts</h5>
                                <p class="card-text">Creative arts education fostering imagination and artistic expression through various mediums.</p>
                                <a href="/contact" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card course-card">
                            <div class="card-body text-center">
                                <div class="course-icon bg-danger text-white">
                                    <i class="fas fa-running"></i>
                                </div>
                                <h5 class="card-title">Physical Education</h5>
                                <p class="card-text">Sports and physical fitness programs promoting health, teamwork, and discipline.</p>
                                <a href="/contact" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card course-card">
                            <div class="card-body text-center">
                                <div class="course-icon bg-secondary text-white">
                                    <i class="fas fa-computer"></i>
                                </div>
                                <h5 class="card-title">Computer Science</h5>
                                <p class="card-text">Modern computer education covering programming, digital literacy, and technology skills.</p>
                                <a href="/contact" class="btn btn-primary">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Curriculum Overview -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Academic Curriculum</h2>
                <p class="lead">Structured learning path from foundation to advanced levels</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-primary">Primary (Grades 1-5)</h4>
                            <p>Foundation building with focus on basic literacy, numeracy, and social skills.</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>English & Languages</li>
                                <li><i class="fas fa-check text-success me-2"></i>Mathematics</li>
                                <li><i class="fas fa-check text-success me-2"></i>Environmental Science</li>
                                <li><i class="fas fa-check text-success me-2"></i>Arts & Crafts</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-success">Middle (Grades 6-8)</h4>
                            <p>Intermediate level with introduction to advanced concepts and critical thinking.</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Advanced Mathematics</li>
                                <li><i class="fas fa-check text-success me-2"></i>Science (Physics/Chemistry/Biology)</li>
                                <li><i class="fas fa-check text-success me-2"></i>Social Studies</li>
                                <li><i class="fas fa-check text-success me-2"></i>Computer Applications</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-info">Secondary (Grades 9-12)</h4>
                            <p>Advanced curriculum preparing students for higher education and career choices.</p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Specialized Subjects</li>
                                <li><i class="fas fa-check text-success me-2"></i>Board Examination Prep</li>
                                <li><i class="fas fa-check text-success me-2"></i>Vocational Training</li>
                                <li><i class="fas fa-check text-success me-2"></i>Career Counseling</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Admission Process -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Admission Process</h2>
                <p class="lead">Simple and transparent admission procedure</p>
            </div>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <span class="fw-bold">1</span>
                        </div>
                        <h5>Application</h5>
                        <p>Submit admission application with required documents</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <span class="fw-bold">2</span>
                        </div>
                        <h5>Assessment</h5>
                        <p>Entrance test and interview evaluation</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <span class="fw-bold">3</span>
                        </div>
                        <h5>Approval</h5>
                        <p>Admission decision and fee payment</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="text-center">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                            <span class="fw-bold">4</span>
                        </div>
                        <h5>Welcome</h5>
                        <p>Join our school community and start learning</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="/contact" class="btn btn-primary btn-lg">Apply Now</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>School Management System</h5>
                    <p>Providing quality education for over 50 years.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="/" class="text-white">Home</a></li>
                        <li><a href="/about" class="text-white">About Us</a></li>
                        <li><a href="/events" class="text-white">Events</a></li>
                        <li><a href="/contact" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <p><i class="fas fa-map-marker-alt"></i> 123 School Street, City, State</p>
                    <p><i class="fas fa-phone"></i> +1-234-567-8900</p>
                    <p><i class="fas fa-envelope"></i> info@school.com</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; 2024 School Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>