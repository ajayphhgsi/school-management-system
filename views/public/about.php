<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - School Management System</title>
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
        .stats-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 12px;
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
                    <li class="nav-item"><a class="nav-link active" href="/about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="/courses">Courses</a></li>
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
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>About Our School</h1>
                    <p class="lead">Excellence in education for over 50 years, shaping tomorrow's leaders today.</p>
                    <a href="/contact" class="btn btn-light btn-lg">Get In Touch</a>
                </div>
                <div class="col-lg-6">
                    <img src="/assets/images/school-building.jpg" alt="School Building" class="img-fluid rounded" onerror="this.src='https://via.placeholder.com/600x400/667eea/white?text=School+Building'">
                </div>
            </div>
        </div>
    </section>

    <!-- About Content -->
    <?php if ($about): ?>
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2><?php echo htmlspecialchars($about['title']); ?></h2>
                    <p class="lead"><?php echo nl2br(htmlspecialchars($about['content'])); ?></p>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Statistics -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-users fa-3x opacity-75"></i>
                            </div>
                            <h3>2000+</h3>
                            <p class="mb-0">Students</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-chalkboard-teacher fa-3x opacity-75"></i>
                            </div>
                            <h3>150+</h3>
                            <p class="mb-0">Teachers</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-graduation-cap fa-3x opacity-75"></i>
                            </div>
                            <h3>95%</h3>
                            <p class="mb-0">Pass Rate</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-calendar fa-3x opacity-75"></i>
                            </div>
                            <h3>50+</h3>
                            <p class="mb-0">Years</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <i class="fas fa-bullseye fa-4x text-primary"></i>
                            </div>
                            <h3>Our Mission</h3>
                            <p>To provide quality education that empowers students to become responsible citizens and lifelong learners in a rapidly changing world.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <i class="fas fa-eye fa-4x text-success"></i>
                            </div>
                            <h3>Our Vision</h3>
                            <p>To be a leading educational institution that nurtures creativity, critical thinking, and character development in every student.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Core Values</h2>
                <p class="lead">The principles that guide everything we do</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-handshake fa-3x text-primary"></i>
                            </div>
                            <h5>Integrity</h5>
                            <p>We uphold the highest standards of honesty, ethics, and moral courage in all our endeavors.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-lightbulb fa-3x text-warning"></i>
                            </div>
                            <h5>Innovation</h5>
                            <p>We embrace new ideas and technologies to enhance learning and adapt to changing needs.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-users fa-3x text-success"></i>
                            </div>
                            <h5>Community</h5>
                            <p>We foster a supportive environment where students, teachers, and parents work together.</p>
                        </div>
                    </div>
                </div>
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
                        <li><a href="/courses" class="text-white">Courses</a></li>
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